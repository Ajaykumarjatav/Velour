<?php

namespace App\Services\Admin;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\Review;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Models\StaffAttendanceRecord;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminTenantDataService
{
    /** @return array<string, int> */
    public function moduleCounts(int $salonId): array
    {
        return [
            'clients' => Client::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'appointments' => Appointment::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'staff' => Staff::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'pos_transactions' => PosTransaction::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'services' => Service::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'inventory' => InventoryItem::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'expenses' => Expense::withoutGlobalScopes()->where('salon_id', $salonId)->where('status', 'recorded')->count(),
            'reviews' => Review::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'marketing' => MarketingCampaign::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'leave' => StaffLeaveRequest::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
            'attendance' => StaffAttendanceRecord::withoutGlobalScopes()->where('salon_id', $salonId)->count(),
        ];
    }

    public function revenueStats(int $salonId): array
    {
        $monthStart = now()->startOfMonth();

        return [
            'month' => (float) DB::table('pos_transactions')
                ->where('salon_id', $salonId)
                ->where('status', 'completed')
                ->where('created_at', '>=', $monthStart)
                ->sum('total'),
            'all_time' => (float) DB::table('pos_transactions')
                ->where('salon_id', $salonId)
                ->where('status', 'completed')
                ->sum('total'),
            'appointments_month' => Appointment::withoutGlobalScopes()
                ->where('salon_id', $salonId)
                ->where('created_at', '>=', $monthStart)
                ->count(),
        ];
    }

    /** @return Collection<int, object> */
    public function monthlyRevenue(int $salonId, int $months = 6): Collection
    {
        return DB::table('pos_transactions')
            ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"), DB::raw('SUM(total) as revenue'))
            ->where('salon_id', $salonId)
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
    }

    /** @return array{appointments: Collection, pos: Collection, clients: Collection} */
    public function recentActivity(int $salonId): array
    {
        return [
            'appointments' => Appointment::withoutGlobalScopes()
                ->where('salon_id', $salonId)
                ->with(['client:id,first_name,last_name', 'staff:id,first_name,last_name'])
                ->latest('starts_at')
                ->limit(5)
                ->get(),
            'pos' => PosTransaction::withoutGlobalScopes()
                ->where('salon_id', $salonId)
                ->with('client:id,first_name,last_name')
                ->latest()
                ->limit(5)
                ->get(),
            'clients' => Client::withoutGlobalScopes()
                ->where('salon_id', $salonId)
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    public function hubPayload(Salon $salon): array
    {
        $id = (int) $salon->id;

        return [
            'counts' => $this->moduleCounts($id),
            'revenue' => $this->revenueStats($id),
            'monthlyRevenue' => $this->monthlyRevenue($id),
            'recent' => $this->recentActivity($id),
        ];
    }
}
