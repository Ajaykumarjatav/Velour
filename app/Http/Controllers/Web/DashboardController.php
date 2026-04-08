<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\SalonNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $activeSalonId = (int) session('active_salon_id', 0);
        $salon = $activeSalonId > 0
            ? $user->salons()->where('id', $activeSalonId)->first()
            : null;
        $salon = $salon ?: $user->salons()->firstOrFail();

        // KPI Metrics
        $today          = today();
        $startOfMonth   = $today->copy()->startOfMonth();
        $startOfLastMonth = $today->copy()->subMonth()->startOfMonth();
        $endOfLastMonth = $today->copy()->subMonth()->endOfMonth();

        $todayRevenue = PosTransaction::where('salon_id', $salon->id)
            ->whereDate('created_at', $today)
            ->where('status', 'completed')
            ->sum('total');

        $monthRevenue = PosTransaction::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$startOfMonth, now()])
            ->where('status', 'completed')
            ->sum('total');

        $lastMonthRevenue = PosTransaction::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])
            ->where('status', 'completed')
            ->sum('total');

        $revenueChange = $lastMonthRevenue > 0
            ? round((($monthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;

        $todayAppointments = Appointment::where('salon_id', $salon->id)
            ->whereDate('starts_at', $today)
            ->count();

        $upcomingAppointments = Appointment::where('salon_id', $salon->id)
            ->where('starts_at', '>=', now())
            ->where('status', 'confirmed')
            ->with(['client', 'staff', 'services'])
            ->orderBy('starts_at')
            ->limit(8)
            ->get();

        $totalClients = Client::where('salon_id', $salon->id)->count();

        $newClientsThisMonth = Client::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$startOfMonth, now()])
            ->count();

        // Recent sales
        $recentSales = PosTransaction::where('salon_id', $salon->id)
            ->with('client')
            ->latest()
            ->limit(6)
            ->get();

        // Appointment status breakdown for chart
        $appointmentStats = Appointment::where('salon_id', $salon->id)
            ->whereDate('starts_at', $today)
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // Weekly revenue chart (last 7 days)
        $weeklyRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $rev = PosTransaction::where('salon_id', $salon->id)
                ->whereDate('created_at', $date)
                ->where('status', 'completed')
                ->sum('total');
            $weeklyRevenue[] = [
                'date'    => $date->format('D'),
                'revenue' => round($rev, 2),
            ];
        }

        // Notifications
        $notifications = SalonNotification::where('salon_id', $salon->id)
            ->where('is_read', false)
            ->latest()
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'salon',
            'todayRevenue',
            'monthRevenue',
            'revenueChange',
            'todayAppointments',
            'upcomingAppointments',
            'totalClients',
            'newClientsThisMonth',
            'recentSales',
            'appointmentStats',
            'weeklyRevenue',
            'notifications'
        ));
    }
}
