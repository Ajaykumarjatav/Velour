<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\Review;
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

        // ── Analytics widgets (period-based) ─────────────────────────────────
        $weekStartUtc = $now->copy()->startOfWeek()->utc();
        $yearStartUtc = $now->copy()->startOfYear()->utc();
        $nowUtc = $now->copy()->utc();

        $periods = [
            'today' => [$todayStartUtc, $todayEndUtc],
            'weekly' => [$weekStartUtc, $nowUtc],
            'monthly' => [$monthStartUtc, $nowUtc],
            'yearly' => [$yearStartUtc, $nowUtc],
        ];

        $analyticsWidgets = [];
        foreach ($periods as $period => [$pStart, $pEnd]) {
            $analyticsWidgets[$period] = [
                'appointments' => $scopeAppointment(
                    Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
                        ->whereBetween('starts_at', [$pStart, $pEnd])
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                )->count(),
                'pending_tasks' => SalonActionItem::withoutGlobalScopes()
                    ->where('salon_id', $salon->id)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->when($period !== 'yearly', fn ($q) => $q->where('created_at', '>=', $pStart))
                    ->count(),
                'new_customers' => (clone $clientQuery)->whereBetween('created_at', [$pStart, $pEnd])->count(),
                'new_bookings' => $scopeAppointment(
                    Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
                        ->whereBetween('created_at', [$pStart, $pEnd])
                )->count(),
                'staff_active' => Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true)->count(),
                'staff_on_leave' => StaffLeaveRequest::withoutGlobalScopes()
                    ->where('salon_id', $salon->id)
                    ->where('status', 'approved')
                    ->where('start_date', '<=', $now->toDateString())
                    ->where('end_date', '>=', $now->toDateString())
                    ->count(),
                'website_visits' => 0,
                'website_views' => 0,
                'reviews_count' => Review::withoutGlobalScopes()->where('salon_id', $salon->id)
                    ->whereBetween('created_at', [$pStart, $pEnd])->count(),
                'reviews_avg' => round((float) Review::withoutGlobalScopes()->where('salon_id', $salon->id)
                    ->avg('rating'), 1) ?: 0,
                'revenue' => (float) $scopePos(
                    PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
                        ->recognizedBetweenUtc($pStart, $pEnd)
                )->sum('total'),
            ];
        }

        $detailAppointments = $scopeAppointment(
            Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->where('starts_at', '>=', $yearStartUtc)
                ->whereNotIn('status', ['cancelled', 'no_show'])
        )
            ->with(['client', 'staff', 'services'])
            ->orderByDesc('starts_at')
            ->limit(20)
            ->get()
            ->map(fn ($a) => [
                'name' => trim(($a->client?->first_name ?? '') . ' ' . ($a->client?->last_name ?? '')) ?: 'Walk-in',
                'services' => $a->services->pluck('service.name')->filter()->join(', ') ?: 'Appointment',
                'time' => $a->starts_at->format('h:i A'),
                'date' => $a->starts_at->toDateString(),
                'color' => $a->staff?->color ?? '#7C3AED',
                'initial' => strtoupper(substr($a->client?->first_name ?? 'U', 0, 1)),
            ]);

        $detailTasks = SalonActionItem::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereIn('status', ['open', 'in_progress'])
            ->orderByDesc('created_at')
            ->limit(15)
            ->get()
            ->map(fn ($t) => [
                'title' => $t->title,
                'priority' => $t->priority,
                'date' => $t->created_at->toDateString(),
                'ago' => $t->created_at->diffForHumans(),
            ]);

        $detailClients = Client::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('created_at', '>=', $yearStartUtc)
            ->latest()
            ->limit(15)
            ->get(['id', 'first_name', 'last_name', 'email', 'created_at'])
            ->map(fn ($c) => [
                'name' => trim($c->first_name . ' ' . $c->last_name),
                'email' => $c->email ?? 'No email',
                'date' => $c->created_at->toDateString(),
                'ago' => $c->created_at->diffForHumans(),
                'initial' => strtoupper(substr($c->first_name ?? '?', 0, 1)),
            ]);

        $detailReviews = Review::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->with('client:id,first_name,last_name')
            ->latest()
            ->limit(15)
            ->get()
            ->map(fn ($r) => [
                'name' => $r->reviewer_name ?? $r->client?->first_name ?? 'Anonymous',
                'rating' => $r->rating,
                'comment' => \Illuminate\Support\Str::limit($r->comment ?? 'No comment', 80),
                'date' => $r->created_at->toDateString(),
                'ago' => $r->created_at->diffForHumans(),
            ]);

        $detailSales = $scopePos(
            PosTransaction::withoutGlobalScopes()->where('salon_id', $salon->id)
                ->where('status', 'completed')
                ->with('client')
        )
            ->latest(DB::raw('COALESCE(completed_at, created_at)'))
            ->limit(15)
            ->get()
            ->map(fn ($s) => [
                'name' => trim(($s->client?->first_name ?? 'Walk-in') . ' ' . ($s->client?->last_name ?? '')),
                'total' => number_format($s->total, 0),
                'date' => ($s->completed_at ?? $s->created_at)->toDateString(),
                'ago' => ($s->completed_at ?? $s->created_at)->diffForHumans(),
            ]);

        $detailStaff = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('is_active', true)
            ->orderBy('first_name')
            ->limit(10)
            ->get(['id', 'first_name', 'last_name', 'is_active', 'color'])
            ->map(fn ($s) => [
                'name' => trim($s->first_name . ' ' . $s->last_name),
                'color' => $s->color ?? '#7C3AED',
                'initials' => strtoupper(substr($s->first_name ?? '?', 0, 1)) . strtoupper(substr($s->last_name ?? '', 0, 1)),
            ]);

        $detailLists = [
            'appointments' => $detailAppointments->values(),
            'tasks' => $detailTasks->values(),
            'clients' => $detailClients->values(),
            'reviews' => $detailReviews->values(),
            'sales' => $detailSales->values(),
            'staff' => $detailStaff->values(),
        ];

        $periodBounds = [
            'today' => $now->toDateString(),
            'weekly' => $now->copy()->startOfWeek()->toDateString(),
            'monthly' => $now->copy()->startOfMonth()->toDateString(),
            'yearly' => $now->copy()->startOfYear()->toDateString(),
        ];

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
            'deskStaffForAssign',
            'analyticsWidgets',
            'detailLists',
            'periodBounds'
        ));
    }
}
