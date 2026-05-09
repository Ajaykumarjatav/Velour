<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\SalonActionItem;
use App\Models\SalonNotification;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
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

        $monthStartLocal = $now->copy()->startOfMonth();
        $monthStartUtc = $monthStartLocal->copy()->utc();
        $monthEndUtc = $now->copy()->utc();

        $monthRevenue = (float) $scopePos(
            PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->recognizedBetweenUtc($monthStartUtc, $monthEndUtc)
        )->sum('total');

        $lastMonthStartLocal = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEndLocal = $now->copy()->subMonthNoOverflow()->endOfMonth();
        $lastMonthRevenue = (float) $scopePos(
            PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->recognizedBetweenUtc($lastMonthStartLocal->copy()->utc(), $lastMonthEndLocal->copy()->utc())
        )->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : null;

        [$todayAptStart, $todayAptEnd] = [$todayStartUtc, $todayEndUtc];
        $todayAppointments = $scopeAppointment(
            Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->whereBetween('starts_at', [$todayAptStart, $todayAptEnd])
                ->whereNotIn('status', ['cancelled', 'no_show'])
        )->count();

        $upcomingAppointments = $scopeAppointment(
            Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->where('starts_at', '>=', now())
                ->where('status', 'confirmed')
        )
            ->with(['client', 'staff', 'services'])
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $clientQuery = Client::withoutGlobalScopes()->where('salon_id', $salon->id);
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
            PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->with('client')
                ->where('status', 'completed')
                ->whereRaw('COALESCE(completed_at, created_at) BETWEEN ? AND ?', [$todayStartUtc, $todayEndUtc])
        )
            ->latest(DB::raw('COALESCE(completed_at, created_at)'))
            ->limit(6)
            ->get();

        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->copy()->subDays($i)->toDateString();
            [$dStart, $dEnd] = SalonTime::dayRangeUtcFromYmd($salon, $d);
            $rev = (float) $scopePos(
                PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
                    ->recognizedBetweenUtc($dStart, $dEnd)
            )->sum('total');
            $weeklyRevenue[] = [
                'date' => Carbon::parse($d, $tz)->format('D'),
                'revenue' => round($rev, 2),
            ];
        }

        $notificationsQuery = SalonNotification::withoutGlobalScopes()->where('salon_id', $salon->id)
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
        $profileCompletion = ProfileCompletion::forSalon($salon);

        $user = Auth::user();
        $canManageDesk = ! $stylistDashboardScoped
            && ($user->hasAnyRole(['tenant_admin', 'manager', 'receptionist'])
                || $user->salons()->whereKey($salon->id)->exists());

        $pendingLeaveRequests = collect();
        $openDeskItems = collect();
        $myDeskSubmissions = collect();
        $deskStaffForAssign = collect();

        if ($canManageDesk) {
            $pendingLeaveRequests = StaffLeaveRequest::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('status', 'pending')
                ->with('staff')
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();

            $openDeskItems = SalonActionItem::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->whereIn('status', ['open', 'in_progress'])
                ->with(['staff', 'assignedStaff'])
                ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'normal' THEN 1 ELSE 2 END")
                ->orderByDesc('created_at')
                ->limit(25)
                ->get();

            $deskStaffForAssign = Staff::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('is_active', true)
                ->orderBy('first_name')
                ->orderBy('last_name')
                ->get();
        } elseif ($staffScopeId !== null) {
            $myDeskSubmissions = SalonActionItem::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('staff_id', $staffScopeId)
                ->whereIn('status', ['open', 'in_progress'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();
        }

        $deskKindLabels = SalonActionItem::kindLabels();

        return view('dashboard.index', compact(
            'salon',
            'monthRevenue',
            'lastMonthRevenue',
            'revenueChange',
            'todayAppointments',
            'upcomingAppointments',
            'totalClients',
            'newClientsThisMonth',
            'recentSales',
            'weeklyRevenue',
            'notifications',
            'tzAbbr',
            'profileCompletion',
            'stylistDashboardScoped',
            'canManageDesk',
            'pendingLeaveRequests',
            'openDeskItems',
            'myDeskSubmissions',
            'deskKindLabels',
            'deskStaffForAssign'
        ));
    }
}
