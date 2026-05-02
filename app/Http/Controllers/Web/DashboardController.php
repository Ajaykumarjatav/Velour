<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\SalonNotification;
use App\Support\ProfileCompletion;
use App\Support\SalonTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use ResolvesActiveSalon;

    public function index()
    {
        $salon = $this->activeSalon();
        $tz = SalonTime::timezone($salon);
        $now = Carbon::now($tz);

        $staffScopeId = Auth::user()->dashboardScopedStaffId();
        $stylistDashboardScoped = $staffScopeId !== null;

        $scopePos = fn ($q) => $staffScopeId === null ? $q : $q->where('staff_id', $staffScopeId);
        $scopeAppointment = fn ($q) => $staffScopeId === null ? $q : $q->where('staff_id', $staffScopeId);

        [$todayStartUtc, $todayEndUtc] = SalonTime::dayRangeUtcFromYmd($salon, $now->toDateString());

        $todayRevenue = (float) $scopePos(
            PosTransaction::where('salon_id', $salon->id)
                ->recognizedBetweenUtc($todayStartUtc, $todayEndUtc)
        )->sum('total');

        $monthStartLocal = $now->copy()->startOfMonth();
        $monthStartUtc = $monthStartLocal->copy()->utc();
        $monthEndUtc = $now->copy()->utc();

        $monthRevenue = (float) $scopePos(
            PosTransaction::where('salon_id', $salon->id)
                ->recognizedBetweenUtc($monthStartUtc, $monthEndUtc)
        )->sum('total');

        $lastMonthStartLocal = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEndLocal = $now->copy()->subMonthNoOverflow()->endOfMonth();
        $lastMonthRevenue = (float) $scopePos(
            PosTransaction::where('salon_id', $salon->id)
                ->recognizedBetweenUtc($lastMonthStartLocal->copy()->utc(), $lastMonthEndLocal->copy()->utc())
        )->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : null;

        [$todayAptStart, $todayAptEnd] = [$todayStartUtc, $todayEndUtc];
        $todayAppointments = $scopeAppointment(
            Appointment::where('salon_id', $salon->id)
                ->whereBetween('starts_at', [$todayAptStart, $todayAptEnd])
                ->whereNotIn('status', ['cancelled', 'no_show'])
        )->count();

        $completedVisitsToday = $scopeAppointment(
            Appointment::where('salon_id', $salon->id)
                ->where('status', 'completed')
                ->whereBetween('ends_at', [$todayAptStart, $todayAptEnd])
        )->count();

        $upcomingAppointments = $scopeAppointment(
            Appointment::where('salon_id', $salon->id)
                ->where('starts_at', '>=', now())
                ->where('status', 'confirmed')
        )
            ->with(['client', 'staff', 'services'])
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $clientQuery = Client::where('salon_id', $salon->id);
        if ($staffScopeId !== null) {
            $clientQuery->whereHas(
                'appointments',
                fn ($q) => $q->where('staff_id', $staffScopeId)
            );
        }
        $totalClients = (clone $clientQuery)->count();

        $newClientsThisMonth = (clone $clientQuery)
            ->whereBetween('created_at', [$monthStartUtc, $monthEndUtc])
            ->count();

        $recentSales = $scopePos(
            PosTransaction::where('salon_id', $salon->id)
                ->with('client')
                ->where('status', 'completed')
                ->whereRaw('COALESCE(completed_at, created_at) BETWEEN ? AND ?', [$todayStartUtc, $todayEndUtc])
        )
            ->latest(DB::raw('COALESCE(completed_at, created_at)'))
            ->limit(6)
            ->get();

        $appointmentStats = $scopeAppointment(
            Appointment::where('salon_id', $salon->id)
                ->whereBetween('starts_at', [$todayAptStart, $todayAptEnd])
        )
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            [$dStart, $dEnd] = SalonTime::dayRangeUtcFromYmd($salon, $d);
            $rev = (float) $scopePos(
                PosTransaction::where('salon_id', $salon->id)
                    ->recognizedBetweenUtc($dStart, $dEnd)
            )->sum('total');
            $weeklyRevenue[] = [
                'date' => Carbon::parse($d, $tz)->format('D'),
                'revenue' => round($rev, 2),
            ];
        }

        $notificationsQuery = SalonNotification::where('salon_id', $salon->id)
            ->where('is_read', false);
        if ($staffScopeId !== null) {
            $notificationsQuery->where(function ($q) use ($staffScopeId) {
                $q->whereNull('staff_id')->orWhere('staff_id', $staffScopeId);
            });
        }
        $notifications = $notificationsQuery
            ->latest()
            ->limit(5)
            ->get();

        $tzAbbr = SalonTime::abbrev($salon);
        $todayLabel = $now->format('d M Y');
        $profileCompletion = ProfileCompletion::forSalon($salon);

        return view('dashboard.index', compact(
            'salon',
            'todayRevenue',
            'monthRevenue',
            'lastMonthRevenue',
            'revenueChange',
            'todayAppointments',
            'completedVisitsToday',
            'upcomingAppointments',
            'totalClients',
            'newClientsThisMonth',
            'recentSales',
            'appointmentStats',
            'weeklyRevenue',
            'notifications',
            'tzAbbr',
            'todayLabel',
            'profileCompletion',
            'stylistDashboardScoped'
        ));
    }
}
