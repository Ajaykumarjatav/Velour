<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Billing\Plan;
use App\Mail\AdminTenantSupportMail;
use App\Models\SupportTicket;
use App\Models\TenantPlanOverride;
use App\Models\User;
use App\Services\Admin\AdminTenantDataService;
use App\Services\Admin\AdminPlanAssignmentService;
use App\Services\Admin\TenantBlockService;
use App\Services\AuditLogService;
use App\Services\TenantWelcomeWhatsAppService;
use App\Support\PurposeMail;
use App\Support\SupportContact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use RuntimeException;

class AdminTenantOwnerController extends Controller
{
    public function __construct(
        private readonly AdminTenantDataService $data,
        private readonly TenantBlockService $blockService,
        private readonly AdminPlanAssignmentService $planAssignment,
        private readonly TenantWelcomeWhatsAppService $welcomeWhatsApp,
        private readonly AuditLogService $audit,
    ) {}

    public function show(int $owner): View
    {
        $account = User::query()
            ->with(['salons' => fn ($q) => $q->withoutGlobalScopes()->withCount(['staff', 'clients', 'appointments'])])
            ->whereHas('salons')
            ->findOrFail($owner);

        $salonIds = $account->salons->pluck('id');
        $primarySalon = $account->salons->first();

        $aggregates = [
            'stores' => $salonIds->count(),
            'staff' => $account->salons->sum('staff_count'),
            'clients' => $account->salons->sum('clients_count'),
            'appointments' => $account->salons->sum('appointments_count'),
            'active_stores' => $account->salons->where('is_active', true)->count(),
        ];

        $revenueThisMonth = $salonIds->isEmpty() ? 0 : (float) DB::table('pos_transactions')
            ->whereIn('salon_id', $salonIds)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->startOfMonth())
            ->sum('total');

        $overrides = $primarySalon
            ? TenantPlanOverride::whereIn('salon_id', $salonIds)->with('appliedBy:id,name')->latest()->limit(10)->get()
            : collect();

        $suspensions = $salonIds->isEmpty()
            ? collect()
            : DB::table('salon_suspensions')->whereIn('salon_id', $salonIds)->orderByDesc('suspended_at')->limit(10)->get();

        $tickets = $salonIds->isEmpty()
            ? collect()
            : SupportTicket::whereIn('salon_id', $salonIds)->latest()->limit(5)->get();

        $subscription = $account->subscription('default');

        return view('admin.tenants.owners.show', [
            'account' => $account,
            'aggregates' => $aggregates,
            'revenueThisMonth' => $revenueThisMonth,
            'overrides' => $overrides,
            'suspensions' => $suspensions,
            'tickets' => $tickets,
            'subscription' => $subscription,
            'isBlocked' => $this->blockService->isBlocked($account),
        ]);
    }

    public function block(Request $request, int $owner)
    {
        $request->validate([
            'reason'           => 'required|in:payment_failure,policy_violation,fraud,abuse,requested,other',
            'notes'            => 'nullable|string|max:2000',
            'customer_message' => 'nullable|string|max:1000',
            'notify_owner'     => 'nullable|boolean',
        ]);

        $account = User::query()
            ->whereHas('salons')
            ->findOrFail($owner);

        if ($this->blockService->isBlocked($account)) {
            return back()->withErrors(['error' => 'This account is already blocked.']);
        }

        try {
            $this->blockService->block(
                $account,
                $request->reason,
                $request->notes,
                $request->customer_message,
                $request->boolean('notify_owner', true),
            );
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }

        return back()->with('success', "{$account->name} has been blocked. All stores are suspended and login is disabled.");
    }

    public function unblock(Request $request, int $owner)
    {
        $request->validate([
            'unsuspend_reason' => 'nullable|string|max:500',
            'customer_message' => 'nullable|string|max:1000',
            'notify_owner'     => 'nullable|boolean',
        ]);

        $account = User::query()
            ->whereHas('salons')
            ->findOrFail($owner);

        if (! $this->blockService->isBlocked($account)) {
            return back()->withErrors(['error' => 'This account is not blocked.']);
        }

        $this->blockService->unblock(
            $account,
            $request->unsuspend_reason,
            $request->customer_message,
            $request->boolean('notify_owner', true),
        );

        return back()->with('success', "{$account->name} has been unblocked. Login and stores are restored.");
    }

    public function assignPlan(Request $request, int $owner)
    {
        $request->validate([
            'plan'       => 'required|'.Plan::validationRule(),
            'trial_days' => 'nullable|integer|min:1|max:365',
            'note'       => 'nullable|string|max:500',
        ]);

        $account = User::query()
            ->whereHas('salons')
            ->findOrFail($owner);

        $this->planAssignment->assign(
            $account,
            $request->string('plan')->toString(),
            $request->filled('trial_days') ? $request->integer('trial_days') : null,
            $request->input('note'),
        );

        return back()->with('success', Plan::labelFor($request->plan).' assigned to '.$account->name.'.');
    }

    public function logs(int $owner): View
    {
        $account = User::query()
            ->with(['salons' => fn ($q) => $q->withoutGlobalScopes()->select('id', 'owner_id', 'name')])
            ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes())
            ->findOrFail($owner);

        $salonIds = $account->salons->pluck('id')->map(fn ($id) => (int) $id)->filter()->values();

        if (! Schema::hasTable('user_activity_logs') && ! Schema::hasTable('audit_logs')) {
            $logs = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 75);
            $logs->setPath(request()->url());

            return view('admin.tenants.owners.logs', [
                'account' => $account,
                'logs' => $logs,
            ]);
        }

        $parts = [];

        if (Schema::hasTable('user_activity_logs')) {
            $parts[] = DB::table('user_activity_logs')
                ->selectRaw("occurred_at, 'activity' as source, label as summary, user_name, user_email, ip_address, COALESCE(method, action) as kind")
                ->where(function ($q) use ($account, $salonIds) {
                    $q->where('user_id', $account->id);
                    if ($salonIds->isNotEmpty()) {
                        $q->orWhereIn('salon_id', $salonIds->all());
                    }
                });
        }

        if (Schema::hasTable('audit_logs')) {
            $parts[] = DB::table('audit_logs')
                ->selectRaw("occurred_at, 'audit' as source, COALESCE(NULLIF(description, ''), event) as summary, user_name, user_email, ip_address, event as kind")
                ->where(function ($q) use ($account, $salonIds) {
                    $q->where('user_id', $account->id);
                    if ($salonIds->isNotEmpty()) {
                        $q->orWhereIn('salon_id', $salonIds->all());
                    }
                });
        }

        $union = array_shift($parts);
        foreach ($parts as $part) {
            $union = $union->unionAll($part);
        }

        $logs = DB::query()
            ->fromSub($union, 'tenant_logs')
            ->orderByDesc('occurred_at')
            ->paginate(75);

        return view('admin.tenants.owners.logs', [
            'account' => $account,
            'logs' => $logs,
        ]);
    }

    public function sendWelcomeWhatsApp(Request $request, int $owner): JsonResponse
    {
        $account = User::query()
            ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes())
            ->findOrFail($owner);

        $markOnly = $request->boolean('already_sent');

        try {
            $result = $this->welcomeWhatsApp->sendOrConfirm($account, $markOnly);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'status' => 'error',
                'message' => 'WhatsApp could not be sent. Check Twilio settings and try again.',
            ], 502);
        }

        $already = $result['status'] === 'already_sent';

        return response()->json([
            'ok' => true,
            'status' => $result['status'],
            'message' => $result['message'],
            'hide_button' => true,
        ], $already && ! $markOnly ? 200 : 200);
    }

    public function sendSupportEmail(Request $request, int $owner): JsonResponse
    {
        $data = $request->validate([
            'subject' => ['required', 'string', 'max:200'],
            'body' => ['required', 'string', 'max:10000'],
        ]);

        $account = User::query()
            ->whereHas('salons', fn ($q) => $q->withoutGlobalScopes())
            ->findOrFail($owner);

        $email = trim((string) $account->email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json([
                'ok' => false,
                'message' => 'This account does not have a valid email address.',
            ], 422);
        }

        $subject = trim($data['subject']);
        $body = trim($data['body']);

        try {
            PurposeMail::send(
                PurposeMail::SUPPORT,
                $email,
                new AdminTenantSupportMail(
                    $account,
                    $subject,
                    $body,
                )
            );
        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'ok' => false,
                'message' => 'Email could not be sent. Check support mail settings and try again.',
            ], 502);
        }

        $primarySalonId = (int) ($account->salons()
            ->withoutGlobalScopes()
            ->orderBy('id')
            ->value('id') ?? 0);

        $this->audit->admin(
            'tenant.support_email.sent',
            'Support email sent to '.$email.' ('.$account->name.') — subject: '.$subject,
            $account,
            [
                'target_user_id' => $account->id,
                'target_email' => $email,
                'from' => SupportContact::emailDisplay(),
                'subject' => $subject,
                'body_preview' => mb_substr($body, 0, 280),
                'salon_id' => $primarySalonId > 0 ? $primarySalonId : null,
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Email sent to '.$email.' from '.SupportContact::emailDisplay().'.',
        ]);
    }
}
