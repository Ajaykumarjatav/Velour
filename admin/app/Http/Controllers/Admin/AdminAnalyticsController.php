<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * AdminAnalyticsController
 *
 * Platform-wide usage analytics for super-admins.
 *
 * Metrics:
 *   • Salon growth (new signups by month, last 12 months)
 *   • Appointment volume by month
 *   • Feature adoption rates (online booking, marketing, 2FA, mobile)
 *   • Plan conversion funnel (trial → paid → churn)
 *   • Geographic distribution (city/country)
 *   • Top active tenants (by appointment volume)
 *   • Retention (cohort summary: active after 30/60/90 days)
 *   • API usage (Sanctum token counts)
 *
 * Routes prefix: /admin/analytics
 * Guard: auth, verified, 2fa, super_admin
 */
class AdminAnalyticsController extends Controller
{
    public function index()
    {
        // ── Platform Totals ────────────────────────────────────────────────

        $totals = [
            'salons'       => Salon::withoutGlobalScopes()->count(),
            'active_salons'=> Salon::withoutGlobalScopes()->where('is_active', true)->count(),
            'users'        => User::count(),
            'appointments' => DB::table('appointments')->count(),
            'clients'      => DB::table('clients')->count(),
            'bookings_today'=> DB::table('appointments')
                ->whereDate('created_at', today())->count(),
        ];

        // ── Monthly Salon Growth (last 12 months) ─────────────────────────

        $salonGrowth = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $label = $month->format('M Y');
            $salonGrowth[$label] = Salon::withoutGlobalScopes()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // ── Monthly Appointment Volume (last 12 months) ───────────────────

        $appointmentVolume = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i);
            $label = $month->format('M Y');
            $appointmentVolume[$label] = DB::table('appointments')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();
        }

        // ── Feature Adoption ──────────────────────────────────────────────

        $totalSalons = max($totals['salons'], 1);

        $adoption = [
            'online_booking'  => [
                'label'   => 'Online Booking',
                'count'   => Salon::withoutGlobalScopes()->where('online_booking_enabled', true)->count(),
                'percent' => 0,
            ],
            'deposit_required' => [
                'label'   => 'Deposits Enabled',
                'count'   => Salon::withoutGlobalScopes()->where('deposit_required', true)->count(),
                'percent' => 0,
            ],
            'two_fa' => [
                'label'   => '2FA Enabled (owners)',
                'count'   => User::whereNotNull('two_factor_secret')->count(),
                'percent' => 0,
            ],
            'has_custom_domain' => [
                'label'   => 'Custom Domain',
                'count'   => Salon::withoutGlobalScopes()->whereNotNull('domain')->count(),
                'percent' => 0,
            ],
            'api_users' => [
                'label'   => 'API Users (active tokens)',
                'count'   => DB::table('personal_access_tokens')
                    ->distinct('tokenable_id')->count('tokenable_id'),
                'percent' => 0,
            ],
        ];

        foreach ($adoption as $key => &$item) {
            $item['percent'] = round(($item['count'] / $totalSalons) * 100);
        }
        unset($item);

        // ── Plan Conversion Funnel ────────────────────────────────────────

        $planFunnel = [
            'registered' => User::count(),
            'trialing'   => User::whereHas('subscriptions', fn ($q) =>
                $q->where('stripe_status', 'trialing'))->count(),
            'paid'       => User::whereHas('subscriptions', fn ($q) =>
                $q->whereIn('stripe_status', ['active'])
                  ->where('stripe_price', '!=', null))->count(),
            'churned'    => User::whereHas('subscriptions', fn ($q) =>
                $q->where('stripe_status', 'canceled'))->count(),
        ];

        // ── Geographic Distribution ───────────────────────────────────────

        $cityDistribution = Salon::withoutGlobalScopes()
            ->select('city', DB::raw('count(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->pluck('count', 'city');

        // ── Top 10 Active Salons by Appointment Volume (this month) ───────

        $topActiveSalons = DB::table('appointments')
            ->join('salons', 'appointments.salon_id', '=', 'salons.id')
            ->select('salons.id', 'salons.name', 'salons.slug', DB::raw('count(*) as count'))
            ->whereMonth('appointments.created_at', now()->month)
            ->groupBy('salons.id', 'salons.name', 'salons.slug')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // ── Retention Cohort Summary (simple: active after N days) ────────
        // Groups salons by signup month, shows % still active

        $cohorts = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->startOfMonth()->subMonths($i + 1);
            $label = $month->format('M Y');

            $cohortTotal = Salon::withoutGlobalScopes()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $stillActive = Salon::withoutGlobalScopes()
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->where('is_active', true)
                ->count();

            if ($cohortTotal > 0) {
                $cohorts[$label] = [
                    'total'   => $cohortTotal,
                    'active'  => $stillActive,
                    'percent' => round(($stillActive / $cohortTotal) * 100),
                ];
            }
        }

        return view('admin.analytics.index', compact(
            'totals', 'salonGrowth', 'appointmentVolume', 'adoption',
            'planFunnel', 'cityDistribution', 'topActiveSalons', 'cohorts'
        ));
    }
}
