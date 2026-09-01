<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use App\Services\SupportTicketNotifier;
use App\Support\SupportTicketHtml;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TenantSupportController extends Controller
{
    use ResolvesActiveSalon;

    public function __construct(protected SupportTicketNotifier $notifier) {}

    public function index(): View
    {
        $salon = $this->activeSalon();
        $base = SupportTicket::query()->where('salon_id', $salon->id);

        $stats = [
            'open' => (clone $base)->whereIn('status', ['open', 'in_progress'])->count(),
            'waiting' => (clone $base)->where('status', 'waiting_on_customer')->count(),
            'closed' => (clone $base)->whereIn('status', ['resolved', 'closed'])->count(),
            'total' => (clone $base)->count(),
        ];

        $tickets = $base->latest()->paginate(20);

        return view('support-tickets.index', compact('salon', 'tickets', 'stats'));
    }

    public function create(): View
    {
        return view('support-tickets.create', [
            'salon' => $this->activeSalon(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $salon = $this->activeSalon();
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'category' => ['required', 'in:'.implode(',', SupportTicket::CATEGORIES)],
            'priority' => ['required', 'in:'.implode(',', SupportTicket::PRIORITIES)],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240', 'mimes:png,jpg,jpeg,gif,pdf'],
        ]);

        $body = SupportTicketHtml::sanitize($data['body']);
        if (SupportTicketHtml::plainLength($body) < 10) {
            throw ValidationException::withMessages([
                'body' => 'Please describe what happened in a bit more detail (at least 10 characters).',
            ]);
        }
        if (SupportTicketHtml::plainLength($body) > 3000) {
            throw ValidationException::withMessages([
                'body' => 'Keep the description under 3,000 characters.',
            ]);
        }

        $ticket = SupportTicket::create([
            'user_id' => Auth::id(),
            'salon_id' => $salon->id,
            'subject' => $data['subject'],
            'body' => $body,
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => 'open',
        ]);

        $saved = [];
        $seen = [];
        foreach ($request->file('attachments', []) as $file) {
            if (! $file) {
                continue;
            }
            $key = $file->getSize().'|'.$file->getClientMimeType();
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $path = $file->store('support-tickets/'.$salon->id.'/'.$ticket->id, 'public');
            $saved[] = [
                'name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ];
        }
        if ($saved !== []) {
            $ticket->update(['attachments' => $saved]);
        }

        $this->notifier->created($ticket->fresh());

        return redirect()
            ->route('support-tickets.show', [
                'store' => \App\Support\SalonUrl::key($salon),
                'ticket' => $ticket,
            ])
            ->with('success', "Ticket {$ticket->ticket_number} submitted. We emailed you a confirmation.");
    }

    public function show(SupportTicket $ticket): View
    {
        $this->authoriseTicket($ticket);
        $ticket->load(['user:id,name,email', 'publicReplies.author:id,name']);

        return view('support-tickets.show', [
            'salon' => $this->activeSalon(),
            'ticket' => $ticket,
        ]);
    }

    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        $this->authoriseTicket($ticket);
        abort_if($ticket->isClosed(), 403, 'This ticket is closed.');

        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $reply = SupportTicketReply::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'body' => $data['body'],
            'is_admin_reply' => false,
            'is_internal' => false,
        ]);

        $ticket->update(['status' => 'open']);
        $this->notifier->publicReply($ticket->fresh(), $reply);

        $salon = $this->activeSalon();

        return redirect()
            ->route('support-tickets.show', [
                'store' => \App\Support\SalonUrl::key($salon),
                'ticket' => $ticket,
            ])
            ->with('success', 'Reply sent. Support has been notified.');
    }

    private function authoriseTicket(SupportTicket $ticket): void
    {
        abort_unless((int) $ticket->salon_id === (int) $this->activeSalon()->id, 404);
    }
}
