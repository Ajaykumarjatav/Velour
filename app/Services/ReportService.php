<?php

namespace App\Services;

use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\InventoryItem;
use App\Models\Voucher;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Appointment;
use App\Models\SalonNotification;
use App\Models\MarketingCampaign;
use App\Models\LinkVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class ReportService
{
    public function revenue(int $salonId, string $from, string $to): array
    {
        $tx = \App\Models\PosTransaction::where('salon_id', $salonId)
            ->where('status', 'completed')
            ->whereBetween('completed_at', [$from, $to . ' 23:59:59']);

        $daily = (clone $tx)
            ->selectRaw("DATE(completed_at) as date, SUM(total) as revenue, SUM(tip_amount) as tips, COUNT(*) as transactions")
            ->groupBy('date')->orderBy('date')->get();

        $byMethod = (clone $tx)
            ->selectRaw("payment_method, SUM(total) as total, COUNT(*) as count")
            ->groupBy('payment_method')->get();

        return [
            'total_revenue'  => round((clone $tx)->sum('total'), 2),
            'total_tips'     => round((clone $tx)->sum('tip_amount'), 2),
            'total_tax'      => round((clone $tx)->sum('tax_amount'), 2),
            'transactions'   => (clone $tx)->count(),
            'avg_transaction'=> round((clone $tx)->avg('total') ?? 0, 2),
            'daily'          => $daily,
            'by_method'      => $byMethod,
        ];
    }

    public function appointments(int $salonId, string $from, string $to): array
    {
        $appts = \App\Models\Appointment::where('salon_id', $salonId)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59']);

        $byStatus = (clone $appts)
            ->selectRaw("status, COUNT(*) as count")
            ->groupBy('status')->get()->keyBy('status');

        $bySource = (clone $appts)
            ->selectRaw("source, COUNT(*) as count")
            ->groupBy('source')->get();

        $noShowRate = (clone $appts)->count() > 0
            ? round((($byStatus['no_show']->count ?? 0) / (clone $appts)->count()) * 100, 1)
            : 0;

        return [
            'total'        => (clone $appts)->count(),
            'completed'    => $byStatus['completed']->count ?? 0,
            'cancelled'    => $byStatus['cancelled']->count ?? 0,
            'no_shows'     => $byStatus['no_show']->count ?? 0,
            'no_show_rate' => $noShowRate,
            'by_status'    => $byStatus,
            'by_source'    => $bySource,
        ];
    }

    public function staff(int $salonId, string $from, string $to): array
    {
        $staff = \App\Models\Staff::where('salon_id', $salonId)->where('is_active', true)->get();

        return $staff->map(function ($s) use ($from, $to) {
            $appts   = \App\Models\Appointment::where('staff_id', $s->id)
                ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->count();
            $revenue = \App\Models\PosTransaction::where('staff_id', $s->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$from, $to . ' 23:59:59'])->sum('total');
            $commission = round($revenue * ($s->commission_rate / 100), 2);

            return [
                'id'          => $s->id,
                'name'        => $s->first_name . ' ' . $s->last_name,
                'role'        => $s->role,
                'appointments'=> $appts,
                'revenue'     => round($revenue, 2),
                'commission'  => $commission,
                'commission_rate' => $s->commission_rate,
            ];
        })->sortByDesc('revenue')->values()->toArray();
    }

    public function clients(int $salonId, string $from, string $to): array
    {
        $total   = \App\Models\Client::where('salon_id', $salonId)->count();
        $newThisPeriod = \App\Models\Client::where('salon_id', $salonId)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])->count();
        $returning = \App\Models\Client::where('salon_id', $salonId)->where('visit_count', '>', 1)->count();
        $vip       = \App\Models\Client::where('salon_id', $salonId)->where('is_vip', true)->count();
        $lapsed    = \App\Models\Client::where('salon_id', $salonId)->where('last_visit_at', '<', now()->subDays(90))->count();

        return [
            'total'          => $total,
            'new_this_period'=> $newThisPeriod,
            'returning'      => $returning,
            'vip'            => $vip,
            'lapsed_90d'     => $lapsed,
            'retention_rate' => $total > 0 ? round(($returning / $total) * 100, 1) : 0,
        ];
    }

    public function services(int $salonId, string $from, string $to): array
    {
        return \App\Models\AppointmentService::join('appointments', 'appointment_services.appointment_id', '=', 'appointments.id')
            ->join('services', 'appointment_services.service_id', '=', 'services.id')
            ->where('appointments.salon_id', $salonId)
            ->where('appointments.status', 'completed')
            ->whereBetween('appointments.starts_at', [$from, $to . ' 23:59:59'])
            ->selectRaw("services.name, appointment_services.service_id, COUNT(*) as bookings, SUM(appointment_services.price) as revenue")
            ->groupBy('services.name', 'appointment_services.service_id')
            ->orderByDesc('bookings')
            ->get()
            ->toArray();
    }

    public function inventory(int $salonId): array
    {
        $items = \App\Models\InventoryItem::where('salon_id', $salonId)->where('is_active', true)->get();

        return [
            'total_products'  => $items->count(),
            'total_value_cost'=> round($items->sum(fn($i) => $i->cost_price * $i->stock_quantity), 2),
            'total_value_retail'=> round($items->sum(fn($i) => $i->retail_price * $i->stock_quantity), 2),
            'low_stock_count' => $items->filter(fn($i) => $i->stock_quantity < $i->min_stock_level)->count(),
            'out_of_stock'    => $items->filter(fn($i) => $i->stock_quantity === 0)->count(),
        ];
    }

    public function marketing(int $salonId, string $from, string $to): array
    {
        $campaigns = \App\Models\MarketingCampaign::where('salon_id', $salonId)
            ->where('status', 'sent')
            ->whereBetween('sent_at', [$from, $to . ' 23:59:59'])
            ->get();

        return [
            'campaigns_sent' => $campaigns->count(),
            'total_sent'     => $campaigns->sum('sent_count'),
            'avg_open_rate'  => round($campaigns->avg('open_rate') ?? 0, 1),
            'avg_click_rate' => round($campaigns->avg('click_rate') ?? 0, 1),
            'bookings'       => $campaigns->sum('booking_count'),
            'revenue'        => round($campaigns->sum('revenue_generated'), 2),
            'campaigns'      => $campaigns,
        ];
    }

    public function payroll(int $salonId, string $month): array
    {
        $from = \Illuminate\Support\Carbon::parse($month)->startOfMonth();
        $to   = $from->copy()->endOfMonth();

        $staff = \App\Models\Staff::where('salon_id', $salonId)->where('is_active', true)->get();

        $rows = $staff->map(function ($s) use ($from, $to) {
            $revenue    = \App\Models\PosTransaction::where('staff_id', $s->id)
                ->where('status', 'completed')
                ->whereBetween('completed_at', [$from, $to])->sum('total');
            $commission = round($revenue * ($s->commission_rate / 100), 2);

            return [
                'staff_id'        => $s->id,
                'name'            => $s->first_name . ' ' . $s->last_name,
                'role'            => $s->role,
                'commission_rate' => $s->commission_rate,
                'gross_revenue'   => round($revenue, 2),
                'commission_due'  => $commission,
            ];
        });

        return [
            'month'       => $from->format('F Y'),
            'staff'       => $rows,
            'total_revenue'    => round($rows->sum('gross_revenue'), 2),
            'total_commission' => round($rows->sum('commission_due'), 2),
        ];
    }
}
