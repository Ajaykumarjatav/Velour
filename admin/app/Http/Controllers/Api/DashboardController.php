<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\PosTransaction;
use App\Models\Staff;
use App\Models\InventoryItem;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /* ── GET /dashboard ─────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');

        return response()->json([
            'kpis'         => $this->getKpis($salonId),
            'today'        => $this->getToday($salonId),
            'upcoming'     => $this->getUpcoming($salonId),
            'recent_sales' => $this->getRecentSales($salonId),
            'alerts'       => $this->getAlerts($salonId),
        ]);
    }

    /* ── GET /dashboard/kpis ────────────────────────────────────────────── */
    public function kpis(Request $request): JsonResponse
    {
        return response()->json($this->getKpis($request->attributes->get('salon_id')));
    }

    /* ── GET /dashboard/today ───────────────────────────────────────────── */
    public function today(Request $request): JsonResponse
    {
        return response()->json($this->getToday($request->attributes->get('salon_id')));
    }

    /* ── GET /dashboard/upcoming ────────────────────────────────────────── */
    public function upcoming(Request $request): JsonResponse
    {
        return response()->json($this->getUpcoming($request->attributes->get('salon_id')));
    }

    /* ── GET /dashboard/recent-sales ────────────────────────────────────── */
    public function recentSales(Request $request): JsonResponse
    {
        return response()->json($this->getRecentSales($request->attributes->get('salon_id')));
    }

    /* ── GET /dashboard/alerts ──────────────────────────────────────────── */
    public function alerts(Request $request): JsonResponse
    {
        return response()->json($this->getAlerts($request->attributes->get('salon_id')));
    }

    /* ── Private helpers ─────────────────────────────────────────────────── */

    private function getKpis(int $salonId): array
    {
        $now       = now();
        $thisMonth = [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()];
        $lastMonth = [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()];

        // Revenue
        $revThis  = PosTransaction::where('salon_id', $salonId)->where('status', 'completed')
                        ->whereBetween('completed_at', $thisMonth)->sum('total');
        $revLast  = PosTransaction::where('salon_id', $salonId)->where('status', 'completed')
                        ->whereBetween('completed_at', $lastMonth)->sum('total');

        // Appointments
        $apptThis = Appointment::where('salon_id', $salonId)
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->whereBetween('starts_at', $thisMonth)->count();
        $apptLast = Appointment::where('salon_id', $salonId)
                        ->whereNotIn('status', ['cancelled', 'no_show'])
                        ->whereBetween('starts_at', $lastMonth)->count();

        // New clients
        $newThis  = Client::where('salon_id', $salonId)->whereBetween('created_at', $thisMonth)->count();
        $newLast  = Client::where('salon_id', $salonId)->whereBetween('created_at', $lastMonth)->count();

        // Avg rating
        $rating   = Review::where('salon_id', $salonId)->where('is_public', true)->avg('rating') ?? 0;

        $pct = fn($curr, $prev) => $prev > 0 ? round((($curr - $prev) / $prev) * 100, 1) : null;

        return [
            'revenue' => [
                'current' => round($revThis, 2),
                'previous'=> round($revLast, 2),
                'change'  => $pct($revThis, $revLast),
            ],
            'appointments' => [
                'current' => $apptThis,
                'previous'=> $apptLast,
                'change'  => $pct($apptThis, $apptLast),
            ],
            'new_clients' => [
                'current' => $newThis,
                'previous'=> $newLast,
                'change'  => $pct($newThis, $newLast),
            ],
            'avg_rating' => round($rating, 1),
            'total_clients'=> Client::where('salon_id', $salonId)->where('status', 'active')->count(),
            'active_staff' => Staff::where('salon_id', $salonId)->where('is_active', true)->count(),
        ];
    }

    private function getToday(int $salonId): array
    {
        $today = now()->toDateString();

        $appointments = Appointment::with(['client', 'staff', 'services'])
            ->where('salon_id', $salonId)
            ->whereDate('starts_at', $today)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('starts_at')
            ->get();

        $revenue = PosTransaction::where('salon_id', $salonId)
            ->where('status', 'completed')
            ->whereDate('completed_at', $today)
            ->sum('total');

        return [
            'date'              => $today,
            'total_appointments'=> $appointments->count(),
            'checked_in'        => $appointments->where('status', 'checked_in')->count(),
            'completed'         => $appointments->where('status', 'completed')->count(),
            'upcoming'          => $appointments->whereIn('status', ['confirmed', 'pending'])->count(),
            'revenue_today'     => round($revenue, 2),
            'appointments'      => $appointments,
        ];
    }

    private function getUpcoming(int $salonId): array
    {
        $upcoming = Appointment::with(['client', 'staff', 'services'])
            ->where('salon_id', $salonId)
            ->where('starts_at', '>', now())
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('starts_at')
            ->limit(15)
            ->get();

        return ['count' => $upcoming->count(), 'appointments' => $upcoming];
    }

    private function getRecentSales(int $salonId): array
    {
        $sales = PosTransaction::with(['client', 'staff'])
            ->where('salon_id', $salonId)
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        // Revenue by day for last 30 days
        $daily = PosTransaction::where('salon_id', $salonId)
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(29))
            ->selectRaw("DATE(completed_at) as date, SUM(total) as revenue, COUNT(*) as transactions")
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'recent'     => $sales,
            'daily_chart'=> $daily,
        ];
    }

    private function getAlerts(int $salonId): array
    {
        $alerts = [];

        // Low stock
        $lowStockCount = InventoryItem::where('salon_id', $salonId)
            ->whereColumn('stock_quantity', '<', 'min_stock_level')
            ->where('is_active', true)
            ->count();

        if ($lowStockCount > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'box',
                'message' => "{$lowStockCount} product(s) are below minimum stock level.",
                'action'  => '/inventory?filter=low_stock',
            ];
        }

        // Unconfirmed appointments today
        $unconfirmed = Appointment::where('salon_id', $salonId)
            ->whereDate('starts_at', now()->toDateString())
            ->where('status', 'pending')
            ->count();

        if ($unconfirmed > 0) {
            $alerts[] = [
                'type'    => 'info',
                'icon'    => 'calendar',
                'message' => "{$unconfirmed} appointment(s) today are pending confirmation.",
                'action'  => '/calendar',
            ];
        }

        // No-shows yesterday
        $noShows = Appointment::where('salon_id', $salonId)
            ->whereDate('starts_at', now()->subDay()->toDateString())
            ->where('status', 'no_show')
            ->count();

        if ($noShows > 0) {
            $alerts[] = [
                'type'    => 'warning',
                'icon'    => 'person',
                'message' => "{$noShows} no-show(s) yesterday. Consider a follow-up message.",
                'action'  => '/clients',
            ];
        }

        // Unanswered reviews
        $unrepliedReviews = Review::where('salon_id', $salonId)
            ->whereNull('owner_reply')
            ->where('is_public', true)
            ->count();

        if ($unrepliedReviews > 0) {
            $alerts[] = [
                'type'    => 'info',
                'icon'    => 'star',
                'message' => "{$unrepliedReviews} review(s) are awaiting your reply.",
                'action'  => '/reviews',
            ];
        }

        return $alerts;
    }
}
