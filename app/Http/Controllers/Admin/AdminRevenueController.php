<?php

namespace App\Http\Controllers\Admin;

use App\Billing\Plan;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * AdminRevenueController
 *
 * Comprehensive revenue intelligence for the super-admin panel.
 *
 * Metrics:
 *   • MRR / ARR (current and 12-month history)
 *   • Net MRR movement (new, expansion, contraction, churn)
 *   • Plan distribution with revenue per plan
 *   • Monthly churn rate
 *   • Average Revenue Per User (ARPU)
 *   • Customer Lifetime Value (LTV)
 *   • Top revenue tenants (by MRR contribution)
 *   • New vs churned comparison by month
 *   • CSV export
 *
 * Routes prefix: /admin/revenue
 * Guard: auth, verified, 2fa, super_admin
 */
class AdminRevenueController extends Controller
{
    public function index()
    {
        $plans = Plan::all()->filter->isPaid()->keyBy('key');

        // ── Current MRR & ARR ─────────────────────────────────────────────

        $planCounts = User::whereHas('subscriptions', fn ($q) =>
                $q->whereIn('stripe_status', ['active', 'trialing'])
            )
            ->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $mrr = $planCounts->reduce(function ($carry, $count, $plan) use ($plans) {
            return $carry + $count * ($plans[$plan]?->priceMonthly ?? 0);
        }, 0);

        $arr          = $mrr * 12;
        $totalPaying  = $planCounts->sum();
        $arpu         = $totalPaying > 0 ? round($mrr / $totalPaying, 2) : 0;

        // ── Churn ─────────────────────────────────────────────────────────

        // Churned this month
        $churnedThisMonth = User::whereHas('subscriptions', fn ($q) =>
            $q->where('stripe_status', 'canceled')
              ->whereMonth('ends_at', now()->month)
              ->whereYear('ends_at',  now()->year)
        )->count();

        // Active at start of month (approximation)
        $activeAtMonthStart = $totalPaying + $churnedThisMonth;
        $churnRate = $activeAtMonthStart > 0
            ? round(($churnedThisMonth / $activeAtMonthStart) * 100, 1) : 0;

        // ── LTV ───────────────────────────────────────────────────────────
        // Simple: ARPU / churn_rate  (avoid div-by-zero)
        $ltv = $churnRate > 0 ? round($arpu / ($churnRate / 100), 2) : null;

        // ── 12-Month MRR History ──────────────────────────────────────────
        // Approximated from current subscribers joined within each month
        $mrrHistory = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $label = $month->format('M Y');

            $count = User::whereHas('subscriptions', fn ($q) =>
                    $q->whereIn('stripe_status', ['active', 'trialing'])
                      ->where('created_at', '<=', $month->endOfMonth())
                )
                ->select('plan', DB::raw('count(*) as count'))
                ->groupBy('plan')
                ->pluck('count', 'plan');

            $monthMrr = $count->reduce(fn ($c, $n, $p) => $c + $n * ($plans[$p]?->priceMonthly ?? 0), 0);
            $mrrHistory[$label] = $monthMrr;
        }

        // ── Plan Distribution ─────────────────────────────────────────────

        $allPlanCounts = User::select('plan', DB::raw('count(*) as count'))
            ->whereNotNull('plan')
            ->groupBy('plan')
            ->pluck('count', 'plan');

        $totalUsers = $allPlanCounts->sum() ?: 1;

        $planDistribution = Plan::all()->map(function (Plan $plan) use ($allPlanCounts, $totalUsers, $plans) {
            $count = $allPlanCounts[$plan->key] ?? 0;
            return [
                'plan'    => $plan,
                'count'   => $count,
                'percent' => round(($count / $totalUsers) * 100),
                'mrr'     => $count * ($plans[$plan->key]?->priceMonthly ?? 0),
            ];
        });

        // ── Monthly Signups & Churns (last 12 months) ─────────────────────

        $monthlyGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $label = $month->format('M Y');

            $new = User::whereMonth('created_at', $month->month)
                       ->whereYear('created_at', $month->year)
                       ->count();

            $churned = User::whereHas('subscriptions', fn ($q) =>
                    $q->where('stripe_status', 'canceled')
                      ->whereMonth('ends_at', $month->month)
                      ->whereYear('ends_at',  $month->year)
                )->count();

            $monthlyGrowth[$label] = ['new' => $new, 'churned' => $churned];
        }

        // ── Top 10 Revenue Tenants ────────────────────────────────────────

        $topTenants = User::with('salons:id,name,slug,owner_id')
            ->whereHas('subscriptions', fn ($q) =>
                $q->whereIn('stripe_status', ['active', 'trialing'])
            )
            ->whereIn('plan', ['starter', 'pro', 'enterprise'])
            ->get(['id', 'name', 'email', 'plan'])
            ->map(function (User $user) use ($plans) {
                return [
                    'user'  => $user,
                    'salon' => $user->salons->first(),
                    'mrr'   => $plans[$user->plan]?->priceMonthly ?? 0,
                ];
            })
            ->sortByDesc('mrr')
            ->take(10)
            ->values();

        // ── Summary stats ─────────────────────────────────────────────────

        $trialCount = User::whereHas('subscriptions', fn ($q) =>
            $q->where('stripe_status', 'trialing'))->count();

        $pastDueCount = User::whereHas('subscriptions', fn ($q) =>
            $q->where('stripe_status', 'past_due'))->count();

        return view('admin.revenue.index', compact(
            'mrr', 'arr', 'arpu', 'ltv', 'churnRate', 'churnedThisMonth',
            'totalPaying', 'mrrHistory', 'planDistribution', 'monthlyGrowth',
            'topTenants', 'trialCount', 'pastDueCount'
        ));
    }

    public function export()
    {
        $plans = Plan::all()->keyBy('key');

        $users = User::with('salons:id,name,owner_id')
            ->whereHas('subscriptions')
            ->get(['id', 'name', 'email', 'plan', 'created_at']);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="revenue-' . now()->format('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($users, $plans) {
            $h = fopen('php://output', 'w');
            fputcsv($h, ['User ID', 'Name', 'Email', 'Salon', 'Plan', 'MRR (£)', 'Customer Since']);
            foreach ($users as $u) {
                fputcsv($h, [
                    $u->id, $u->name, $u->email,
                    $u->salons->first()?->name ?? '—',
                    $u->plan,
                    $plans[$u->plan]?->priceMonthly ?? 0,
                    $u->created_at->toDateString(),
                ]);
            }
            fclose($h);
        };

        return response()->stream($callback, 200, $headers);
    }
}
