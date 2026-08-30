<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Billing\Plan;
use App\Models\SupportTicket;
use App\Models\TenantPlanOverride;
use App\Models\User;
use App\Services\Admin\AdminTenantDataService;
use App\Services\Admin\AdminPlanAssignmentService;
use App\Services\Admin\TenantBlockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminTenantOwnerController extends Controller
{
    public function __construct(
        private readonly AdminTenantDataService $data,
        private readonly TenantBlockService $blockService,
        private readonly AdminPlanAssignmentService $planAssignment,
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
}
