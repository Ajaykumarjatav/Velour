<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\SupportTicketNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * AdminSupportController
 *
 * Full support ticket queue for super-admins:
 *   • Ticket queue with priority/status/category filters
 *   • Per-ticket thread view with internal notes
 *   • Reply (public or internal)
 *   • Assign to admin team member
 *   • Change status / priority
 *   • Metrics: open count, avg response time, satisfaction score
 *
 * Routes prefix: /admin/support
 * Guard: auth, verified, 2fa, super_admin
 */
class AdminSupportController extends Controller
{
    public function __construct(
        protected AuditLogService $audit,
        protected SupportTicketNotifier $notifier,
    ) {}

    // ── Ticket Queue ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $query = SupportTicket::with(['user:id,name,email', 'salon:id,name,slug', 'assignee:id,name'])
            ->latest();

        // Filters
        if ($status = $request->status) {
            $query->where('status', $status);
        } else {
            // Default: show open tickets
            $query->whereNotIn('status', ['closed']);
        }

        if ($priority = $request->priority) {
            $query->where('priority', $priority);
        }

        if ($category = $request->category) {
            $query->where('category', $category);
        }

        if ($assignee = $request->assigned_to) {
            $assignee === 'unassigned'
                ? $query->whereNull('assigned_to')
                : $query->where('assigned_to', $assignee);
        }

        if ($search = $request->search) {
            $query->search($search);
        }

        // Summary stats
        $stats = [
            'open'       => SupportTicket::whereIn('status', ['open', 'in_progress'])->count(),
            'waiting'    => SupportTicket::where('status', 'waiting_on_customer')->count(),
            'unassigned' => SupportTicket::open()->whereNull('assigned_to')->count(),
            'urgent'     => SupportTicket::urgent()->whereIn('status', ['open', 'in_progress'])->count(),
        ];

        // Avg response time (minutes)
        $avgResponseMinutes = SupportTicket::whereNotNull('first_replied_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, first_replied_at)) as avg_minutes')
            ->value('avg_minutes');

        // Satisfaction score average (last 30 days)
        $avgSatisfaction = SupportTicket::whereNotNull('satisfaction_rating')
            ->where('created_at', '>=', now()->subDays(30))
            ->avg('satisfaction_rating');

        $admins   = User::where('system_role', 'super_admin')->get(['id', 'name']);
        $tickets  = $query->paginate(25)->withQueryString();

        return view('admin.support.index', compact(
            'tickets', 'stats', 'admins', 'avgResponseMinutes', 'avgSatisfaction'
        ));
    }

    // ── Ticket Detail ─────────────────────────────────────────────────────────

    public function show(SupportTicket $ticket)
    {
        $ticket->load([
            'user:id,name,email',
            'salon:id,name,slug',
            'assignee:id,name',
            'replies.author:id,name,email',
        ]);

        $admins = User::where('system_role', 'super_admin')->get(['id', 'name']);

        return view('admin.support.show', compact('ticket', 'admins'));
    }

    // ── Reply ─────────────────────────────────────────────────────────────────

    public function reply(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'body'        => 'required|string|max:10000',
            'is_internal' => 'nullable|boolean',
            'status'      => 'nullable|in:' . implode(',', SupportTicket::STATUSES),
        ]);

        $isInternal = $request->boolean('is_internal');

        $reply = SupportTicketReply::create([
            'ticket_id'    => $ticket->id,
            'user_id'      => Auth::id(),
            'body'         => $request->body,
            'is_admin_reply' => true,
            'is_internal'  => $isInternal,
        ]);

        // Mark first reply timestamp
        if (! $ticket->first_replied_at && ! $isInternal) {
            $ticket->first_replied_at = now();
        }

        $newStatus = $request->status ?? ($isInternal ? $ticket->status : 'waiting_on_customer');
        $ticket->status = $newStatus;

        if ($newStatus === 'resolved') {
            $ticket->resolved_at = $ticket->resolved_at ?? now();
        }
        if ($newStatus === 'closed') {
            $ticket->closed_at = $ticket->closed_at ?? now();
        }

        $ticket->save();

        if (! $isInternal) {
            $this->notifier->publicReply($ticket->fresh(['user', 'salon']), $reply);
        }

        return redirect()
            ->route('admin.support.show', $ticket)
            ->with('success', $isInternal ? 'Internal note added.' : 'Reply sent. The tenant was emailed.');
    }

    // ── Assign ────────────────────────────────────────────────────────────────

    public function assign(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'assigned_to' => 'required|integer|exists:users,id',
        ]);

        $ticket->update(['assigned_to' => $request->assigned_to]);

        return redirect()
            ->route('admin.support.show', $ticket)
            ->with('success', 'Ticket assigned.');
    }

    // ── Update Status / Priority ──────────────────────────────────────────────

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $request->validate([
            'status'   => 'nullable|in:' . implode(',', SupportTicket::STATUSES),
            'priority' => 'nullable|in:' . implode(',', SupportTicket::PRIORITIES),
        ]);

        $changes = array_filter($request->only('status', 'priority'));
        $previousStatus = $ticket->status;

        if (isset($changes['status'])) {
            if ($changes['status'] === 'resolved') $changes['resolved_at'] = now();
            if ($changes['status'] === 'closed')   $changes['closed_at']   = now();
        }

        $ticket->update($changes);

        if (isset($changes['status'])) {
            $this->notifier->statusChanged($ticket->fresh(['user', 'salon']), $previousStatus);
        }

        return redirect()
            ->route('admin.support.show', $ticket)
            ->with('success', 'Ticket updated.');
    }

    // ── Create Ticket (admin-initiated) ───────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'user_id'  => 'nullable|integer|exists:users,id',
            'salon_id' => 'nullable|integer|exists:salons,id',
            'subject'  => 'required|string|max:255',
            'body'     => 'required|string|max:10000',
            'category' => 'required|in:' . implode(',', SupportTicket::CATEGORIES),
            'priority' => 'required|in:' . implode(',', SupportTicket::PRIORITIES),
        ]);

        $ticket = SupportTicket::create([
            ...$request->only('user_id', 'salon_id', 'subject', 'body', 'category', 'priority'),
            'status'      => 'open',
            'assigned_to' => Auth::id(),
        ]);

        $this->notifier->created($ticket->fresh(['user', 'salon']));

        return redirect()->route('admin.support.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} created.");
    }
}
