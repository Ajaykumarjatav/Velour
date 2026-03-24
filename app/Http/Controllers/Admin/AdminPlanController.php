<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Models\TenantPlanOverride;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * AdminPlanController
 *
 * Plan management for super-admins:
 *   • View all plans with subscriber counts and MRR
 *   • Change a tenant's plan (plan migration — bypasses Stripe, updates DB)
 *   • View and manage all active plan overrides
 *   • Expire overrides
 *   • Bulk migrate tenants to a new plan
 *
 * Note: Config-based plans (config/billing.php) cannot be created/deleted here;
 * that requires a deploy. This controller manages per-tenant overrides and
 * direct plan column changes.
 *
 * Routes prefix: /admin/plans
 * Guard: auth, verified, 2fa, super_admin
 */
class AdminPlanController extends Controller
{
    public function __construct(protected AuditLogService $audit) {}

    // ── Plan Overview ─────────────────────────────────────────────────────────

    public function index()
    {
        $plans = Plan::all();

        // Subscriber counts and MRR per plan
        $planStats = User::select('plan', DB::raw('count(*) as count'))
            ->whereNotNull('plan')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $planData = $plans->map(function (Plan $plan) use ($planStats) {
            $count = $planStats[$plan->key] ?? 0;
            return [
                'plan'     => $plan,
                'count'    => $count,
                'mrr'      => $count * $plan->priceMonthly,
                'arr'      => $count * $plan->priceMonthly * 12,
                'yearly'   => $count * $plan->priceYearly,
            ];
        });

        // Active overrides
        $overrides = TenantPlanOverride::active()
            ->with(['salon:id,name,slug', 'appliedBy:id,name'])
            ->latest()
            ->paginate(20, ['*'], 'overrides_page');

        // Recently migrated (plan changes in last 30 days via audit log)
        $recentMigrations = \App\Models\AuditLog::where('event', 'billing.plan_changed')
            ->recent(24 * 30)
            ->latest('occurred_at')
            ->limit(10)
            ->get();

        return view('admin.plans.index', compact('planData', 'overrides', 'recentMigrations'));
    }

    // ── Migrate Tenant Plan ───────────────────────────────────────────────────

    public function migratePlan(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'plan'    => 'required|in:free,starter,pro,enterprise',
            'reason'  => 'required|string|max:500',
        ]);

        $user    = User::findOrFail($request->user_id);
        $oldPlan = $user->plan;

        $user->update(['plan' => $request->plan]);

        $this->audit->billing(
            'billing.plan_changed',
            "Admin migrated {$user->email} from {$oldPlan} → {$request->plan}",
            $user,
            ['old_plan' => $oldPlan, 'new_plan' => $request->plan, 'reason' => $request->reason]
        );

        return back()->with('success', "{$user->name} moved from {$oldPlan} → {$request->plan}.");
    }

    // ── Expire Override ───────────────────────────────────────────────────────

    public function expireOverride(int $id)
    {
        $override = TenantPlanOverride::findOrFail($id);
        $override->update(['is_active' => false, 'expires_at' => now()]);

        $this->audit->admin('admin.override.expired', "Expired plan override #{$id}");

        return back()->with('success', 'Override expired.');
    }

    // ── Bulk Plan Migration ───────────────────────────────────────────────────

    public function bulkMigrate(Request $request)
    {
        $request->validate([
            'from_plan' => 'required|in:free,starter,pro,enterprise',
            'to_plan'   => 'required|in:free,starter,pro,enterprise|different:from_plan',
            'reason'    => 'required|string|max:500',
            'confirm'   => 'required|accepted',
        ]);

        $count = User::where('plan', $request->from_plan)->count();

        User::where('plan', $request->from_plan)
            ->update(['plan' => $request->to_plan]);

        $this->audit->admin('admin.bulk_plan_migrate',
            "Bulk migrated {$count} users from {$request->from_plan} → {$request->to_plan}",
            null,
            ['from' => $request->from_plan, 'to' => $request->to_plan, 'count' => $count, 'reason' => $request->reason]
        );

        return back()->with('success', "{$count} users migrated from {$request->from_plan} to {$request->to_plan}.");
    }
}
