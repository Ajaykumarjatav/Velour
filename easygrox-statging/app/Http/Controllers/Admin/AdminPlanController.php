<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Admin\AdminPlanAssignmentService;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPlanController extends Controller
{
    public function __construct(
        protected AuditLogService $audit,
        protected AdminPlanAssignmentService $assignment,
    ) {}

    public function index()
    {
        $plans = Plan::all();

        $planStats = User::query()
            ->whereHas('salons')
            ->whereNotNull('plan')
            ->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $planData = $plans->map(function (Plan $plan) use ($planStats) {
            $count = $planStats[$plan->key] ?? 0;

            return [
                'plan'  => $plan,
                'count' => $count,
                'mrr'   => $count * $plan->priceMonthly,
                'arr'   => $count * $plan->priceMonthly * 12,
                'yearly' => $count * $plan->priceYearly,
            ];
        });

        $tenants = User::query()
            ->whereHas('salons')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'plan', 'trial_ends_at']);

        $recentAssignments = \App\Models\AuditLog::query()
            ->where('event', 'billing.plan_changed')
            ->recent(24 * 30)
            ->latest('occurred_at')
            ->limit(15)
            ->get();

        return view('admin.plans.index', compact('planData', 'tenants', 'recentAssignments'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'user_id'    => 'required|integer|exists:users,id',
            'plan'       => 'required|'.Plan::validationRule(),
            'trial_days' => 'nullable|integer|min:1|max:365',
            'note'       => 'nullable|string|max:500',
        ]);

        $user = User::query()
            ->whereHas('salons')
            ->findOrFail($request->integer('user_id'));

        $this->assignment->assign(
            $user,
            $request->string('plan')->toString(),
            $request->filled('trial_days') ? $request->integer('trial_days') : null,
            $request->input('note'),
        );

        $label = Plan::labelFor($request->plan);

        return back()->with('success', "{$user->name} is now on {$label}. No payment required — access is active immediately.");
    }

    /** @deprecated Use assign() — kept for old form posts */
    public function migratePlan(Request $request)
    {
        $request->merge([
            'user_id' => $request->input('user_id'),
            'note'    => $request->input('reason'),
        ]);

        return $this->assign($request);
    }

    public function bulkMigrate(Request $request)
    {
        $request->validate([
            'from_plan' => 'required|'.Plan::validationRule(),
            'to_plan'   => 'required|'.Plan::validationRule().'|different:from_plan',
            'confirm'   => 'required|accepted',
        ]);

        $owners = User::query()
            ->whereHas('salons')
            ->where('plan', $request->from_plan)
            ->get();

        foreach ($owners as $owner) {
            $this->assignment->assign($owner, $request->string('to_plan')->toString(), null, 'Bulk migration');
        }

        $count = $owners->count();

        $this->audit->admin(
            'admin.bulk_plan_migrate',
            "Bulk assigned {$count} tenants from {$request->from_plan} → {$request->to_plan}",
            null,
            ['from' => $request->from_plan, 'to' => $request->to_plan, 'count' => $count]
        );

        return back()->with('success', "{$count} tenant(s) moved to ".Plan::labelFor($request->to_plan).'.');
    }
}
