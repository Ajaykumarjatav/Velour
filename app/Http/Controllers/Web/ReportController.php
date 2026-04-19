<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\PosTransaction;
use App\Models\Client;
use App\Models\Staff;
use App\Models\Service;
use App\Helpers\CurrencyHelper;
use App\Support\SalonTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    use ResolvesActiveSalon;

    public function index()
    {
        $salon = $this->activeSalon();

        return view('reports.index', compact('salon'));
    }

    public function analytics(Request $request)
    {
        $salon = $this->activeSalon();

        $period = $request->get('period', '12m');
        $days = match ($period) {
            '7d' => 7,
            '1m' => 30,
            '3m' => 90,
            default => 365,
        };

        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();
        $prevFrom = (clone $from)->subDays($days);
        $prevTo = (clone $to)->subDays($days);

        // Financial year label (Apr -> Mar)
        $fyStartYear = now()->month >= 4 ? now()->year : now()->year - 1;
        $fyLabel = 'FY ' . $fyStartYear;

        $revenue = (float) PosTransaction::where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$from, $to])
            ->sum('total');
        $prevRevenue = (float) PosTransaction::where('salon_id', $salon->id)
            ->where('status', 'completed')
            ->whereBetween('created_at', [$prevFrom, $prevTo])
            ->sum('total');

        $bookings = (int) Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to])
            ->count();
        $prevBookings = (int) Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$prevFrom, $prevTo])
            ->count();

        $ticketSize = $bookings > 0 ? $revenue / $bookings : 0.0;
        $prevTicket = $prevBookings > 0 ? $prevRevenue / $prevBookings : 0.0;

        $activeClientIds = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to])
            ->where('status', 'completed')
            ->whereNotNull('client_id')
            ->pluck('client_id')
            ->unique();
        $returningCount = Client::where('salon_id', $salon->id)
            ->whereIn('id', $activeClientIds)
            ->where('created_at', '<', $from)
            ->count();
        $retention = $activeClientIds->count() > 0 ? ($returningCount / $activeClientIds->count()) * 100 : 0.0;

        $prevActiveClientIds = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$prevFrom, $prevTo])
            ->where('status', 'completed')
            ->whereNotNull('client_id')
            ->pluck('client_id')
            ->unique();
        $prevReturningCount = Client::where('salon_id', $salon->id)
            ->whereIn('id', $prevActiveClientIds)
            ->where('created_at', '<', $prevFrom)
            ->count();
        $prevRetention = $prevActiveClientIds->count() > 0 ? ($prevReturningCount / $prevActiveClientIds->count()) * 100 : 0.0;

        // Monthly bars (last 12 months)
        $monthlyBars = [];
        for ($i = 11; $i >= 0; $i--) {
            $m = now()->subMonths($i);
            $mStart = $m->copy()->startOfMonth();
            $mEnd = $m->copy()->endOfMonth();
            $mRevenue = (float) PosTransaction::where('salon_id', $salon->id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->sum('total');
            // Conservative operational expense estimate to visualize trend.
            $mExpense = $mRevenue * 0.62;

            $monthlyBars[] = [
                'label' => $m->format('M'),
                'revenue' => $mRevenue,
                'expense' => $mExpense,
            ];
        }
        $maxMonthValue = max(1, (float) collect($monthlyBars)->flatMap(fn ($x) => [$x['revenue'], $x['expense']])->max());
        $monthlyBars = collect($monthlyBars)->map(function ($x) use ($maxMonthValue) {
            $x['revenue_h'] = (int) round(($x['revenue'] / $maxMonthValue) * 100);
            $x['expense_h'] = (int) round(($x['expense'] / $maxMonthValue) * 100);
            return $x;
        })->all();

        // Weekly line points (Mon-Sun)
        $weekStart = now()->startOfWeek(Carbon::MONDAY);
        $weeklyPoints = [];
        $weeklyMax = 1.0;
        for ($i = 0; $i < 7; $i++) {
            $d = $weekStart->copy()->addDays($i);
            $value = (float) PosTransaction::where('salon_id', $salon->id)
                ->where('status', 'completed')
                ->whereBetween('created_at', [$d->copy()->startOfDay(), $d->copy()->endOfDay()])
                ->sum('total');
            $weeklyMax = max($weeklyMax, $value);
            $weeklyPoints[] = ['label' => $d->format('D'), 'value' => $value];
        }
        $weeklyPoints = collect($weeklyPoints)->map(fn ($p, $idx) => [
            'label' => $p['label'],
            'value' => $p['value'],
            'x' => (int) round(($idx / 6) * 100),
            'y' => (int) round(100 - (($p['value'] / $weeklyMax) * 100)),
        ])->all();

        $staffRows = Staff::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->withCount(['appointments as appt_count' => fn ($q) => $q->whereBetween('starts_at', [$from, $to])])
            ->withSum(['appointments as revenue_sum' => fn ($q) =>
                $q->whereBetween('starts_at', [$from, $to])->where('status', 'completed')
            ], 'total_price')
            ->limit(12)
            ->get()
            ->map(function ($s) use ($from, $to) {
                $rating = (float) DB::table('reviews')
                    ->where('staff_id', $s->id)
                    ->whereBetween('created_at', [$from, $to])
                    ->avg('rating');
                $util = min(100, max(0, (int) round(((int) $s->appt_count / max(1, 22)) * 100)));

                $totalAppointments = (int) Appointment::where('staff_id', $s->id)
                    ->whereBetween('starts_at', [$from, $to])
                    ->count();
                $noShowCount = (int) Appointment::where('staff_id', $s->id)
                    ->whereBetween('starts_at', [$from, $to])
                    ->where('status', 'no_show')
                    ->count();
                $noShowRate = $totalAppointments > 0 ? round(($noShowCount / $totalAppointments) * 100, 1) : 0.0;

                $repeatRows = Appointment::where('staff_id', $s->id)
                    ->whereBetween('starts_at', [$from, $to])
                    ->where('status', 'completed')
                    ->whereNotNull('client_id')
                    ->select('client_id', DB::raw('COUNT(*) as c'))
                    ->groupBy('client_id')
                    ->get();
                $completedDistinctClients = $repeatRows->count();
                $repeatClients = (int) $repeatRows->where('c', '>=', 2)->count();
                $repeatClientRate = $completedDistinctClients > 0
                    ? round(($repeatClients / $completedDistinctClients) * 100, 1)
                    : 0.0;

                $topService = DB::table('appointment_services as aps')
                    ->join('appointments as ap', 'ap.id', '=', 'aps.appointment_id')
                    ->where('ap.staff_id', $s->id)
                    ->whereBetween('ap.starts_at', [$from, $to])
                    ->where('ap.status', 'completed')
                    ->select('aps.service_name', DB::raw('COUNT(*) as c'))
                    ->groupBy('aps.service_name')
                    ->orderByDesc('c')
                    ->value('aps.service_name');

                $revenueValue = (float) ($s->revenue_sum ?? 0);
                $commissionRate = (float) ($s->commission_rate ?? 0);
                $commissionEarned = round(($revenueValue * $commissionRate) / 100, 2);

                return [
                    'id' => $s->id,
                    'name' => $s->name,
                    'role' => $s->role,
                    'appts' => (int) $s->appt_count,
                    'revenue' => $revenueValue,
                    'rating' => $rating > 0 ? round($rating, 1) : null,
                    'utilization' => $util,
                    'initials' => strtoupper(substr($s->name, 0, 2)),
                    'color' => $s->color ?: '#7C3AED',
                    'commission_earned' => $commissionEarned,
                    'no_show_rate' => $noShowRate,
                    'top_service' => $topService ?: 'N/A',
                    'repeat_client_rate' => $repeatClientRate,
                ];
            });

        $change = function (float|int $current, float|int $previous): ?float {
            if ((float) $previous <= 0.0) {
                return null;
            }
            return round((((float) $current - (float) $previous) / (float) $previous) * 100, 1);
        };

        $kpis = [
            'revenue' => ['label' => 'Annual Revenue', 'value' => $revenue, 'delta' => $change($revenue, $prevRevenue)],
            'bookings' => ['label' => 'Total Bookings', 'value' => $bookings, 'delta' => $change($bookings, $prevBookings)],
            'ticket' => ['label' => 'Avg Ticket Size', 'value' => $ticketSize, 'delta' => $change($ticketSize, $prevTicket)],
            'retention' => ['label' => 'Client Retention', 'value' => $retention, 'delta' => $change($retention, $prevRetention)],
        ];

        return view('reports.analytics', compact(
            'salon',
            'period',
            'fyLabel',
            'kpis',
            'monthlyBars',
            'weeklyPoints',
            'staffRows'
        ));
    }

    public function show(Request $request, string $type)
    {
        $salon = $this->activeSalon();
        $from = $request->get('from', SalonTime::monthStartDateString($salon));
        $to = $request->get('to', SalonTime::todayDateString($salon));

        $data = match ($type) {
            'revenue' => $this->revenueReport($salon, $from, $to, $request),
            'appointments' => $this->appointmentsReport($salon, $from, $to),
            'staff' => $this->staffReport($salon, $from, $to),
            'clients' => $this->clientsReport($salon, $from, $to),
            'services' => $this->servicesReport($salon, $from, $to),
            default => abort(404),
        };

        return view("reports.$type", array_merge($data, compact('salon', 'from', 'to', 'type')));
    }

    /**
     * CSV export for completed POS transactions in the selected period (salon-local dates).
     */
    public function exportRevenue(Request $request): StreamedResponse
    {
        $salon = $this->activeSalon();
        $from = $request->get('from', SalonTime::monthStartDateString($salon));
        $to = $request->get('to', SalonTime::todayDateString($salon));
        [$rangeStart, $rangeEnd] = $this->revenueRecognizedRangeUtc($salon, $from, $to);
        $staffId = $request->filled('staff_id') ? (int) $request->get('staff_id') : null;
        if ($staffId && ! Staff::where('salon_id', $salon->id)->whereKey($staffId)->exists()) {
            $staffId = null;
        }
        $paymentMethod = $request->get('payment_method');

        $filename = 'revenue-' . $from . '-to-' . $to . '.csv';

        return response()->streamDownload(function () use ($salon, $rangeStart, $rangeEnd, $staffId, $paymentMethod) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['# Salon timezone (day boundaries): ' . SalonTime::timezone($salon)]);
            fputcsv($out, ['# Amounts in ' . CurrencyHelper::label($salon->currency ?? 'GBP')]);
            fputcsv($out, ['# Recognized at: UTC (ISO 8601).']);
            fputcsv($out, []);
            fputcsv($out, ['Reference', 'Recognized at (UTC)', 'Total', 'Payment method', 'Staff', 'Client']);

            $q = PosTransaction::query()
                ->where('salon_id', $salon->id)
                ->recognizedBetweenUtc($rangeStart, $rangeEnd)
                ->with(['client', 'staff'])
                ->when($staffId, fn ($q2) => $q2->where('staff_id', $staffId))
                ->when($paymentMethod, fn ($q2) => $q2->where('payment_method', $paymentMethod))
                ->orderByRaw('COALESCE(completed_at, created_at)');

            foreach ($q->cursor() as $tx) {
                $at = $tx->completed_at ?? $tx->created_at;
                fputcsv($out, [
                    $tx->reference,
                    $at?->toIso8601String(),
                    $tx->total,
                    $tx->payment_method,
                    $tx->staff?->name ?? '',
                    $tx->client
                        ? trim($tx->client->first_name . ' ' . $tx->client->last_name)
                        : 'Walk-in',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function revenueRecognizedRangeUtc($salon, string $from, string $to): array
    {
        return [
            SalonTime::dayRangeUtcFromYmd($salon, $from)[0],
            SalonTime::dayRangeUtcFromYmd($salon, $to)[1],
        ];
    }

    private function revenueReport($salon, string $from, string $to, Request $request): array
    {
        $tz = SalonTime::timezone($salon);
        [$rangeStart, $rangeEnd] = $this->revenueRecognizedRangeUtc($salon, $from, $to);

        $staffId = $request->filled('staff_id') ? (int) $request->get('staff_id') : null;
        if ($staffId && ! Staff::where('salon_id', $salon->id)->whereKey($staffId)->exists()) {
            $staffId = null;
        }

        $paymentMethod = $request->get('payment_method');
        $compare = $request->boolean('compare');

        $staffList = Staff::where('salon_id', $salon->id)->where('is_active', true)->withName()->orderBy('first_name')->get();
        $staffById = Staff::where('salon_id', $salon->id)->get()->keyBy('id');

        $daily = collect();
        $fromDay = Carbon::createFromFormat('Y-m-d', $from, $tz)->startOfDay();
        $toDay = Carbon::createFromFormat('Y-m-d', $to, $tz)->startOfDay();
        if ($fromDay->gt($toDay)) {
            [$fromDay, $toDay] = [$toDay, $fromDay];
        }

        $cursor = $fromDay->copy();
        while ($cursor->lte($toDay)) {
            $ymd = $cursor->toDateString();
            [$ds, $de] = SalonTime::dayRangeUtcFromYmd($salon, $ymd);
            $q = PosTransaction::where('salon_id', $salon->id)->recognizedBetweenUtc($ds, $de);
            if ($staffId) {
                $q->where('staff_id', $staffId);
            }
            if ($paymentMethod) {
                $q->where('payment_method', $paymentMethod);
            }
            $daily->push((object) [
                'date' => $ymd,
                'revenue' => (float) $q->sum('total'),
                'transactions' => (int) $q->count(),
            ]);
            $cursor->addDay();
        }

        $base = PosTransaction::where('salon_id', $salon->id)->recognizedBetweenUtc($rangeStart, $rangeEnd);
        if ($staffId) {
            $base->where('staff_id', $staffId);
        }
        if ($paymentMethod) {
            $base->where('payment_method', $paymentMethod);
        }

        $byMethod = (clone $base)->select('payment_method', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')
            ->get();

        $byStaff = (clone $base)->select('staff_id', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('staff_id')
            ->get()
            ->map(function ($row) use ($staffById) {
                $name = $row->staff_id
                    ? ($staffById->get($row->staff_id)?->name ?? 'Staff #' . $row->staff_id)
                    : 'Unassigned';

                return (object) [
                    'staff_id' => $row->staff_id,
                    'name' => $name,
                    'total' => (float) $row->total,
                    'count' => (int) $row->count,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $byService = DB::table('pos_transaction_items as pti')
            ->join('pos_transactions as pt', 'pt.id', '=', 'pti.transaction_id')
            ->where('pt.salon_id', $salon->id)
            ->where('pt.status', 'completed')
            ->whereRaw('COALESCE(pt.completed_at, pt.created_at) BETWEEN ? AND ?', [$rangeStart, $rangeEnd])
            ->when($staffId, fn ($q) => $q->where('pt.staff_id', $staffId))
            ->when($paymentMethod, fn ($q) => $q->where('pt.payment_method', $paymentMethod))
            ->where('pti.type', 'service')
            ->select('pti.name', DB::raw('SUM(pti.total) as total'), DB::raw('SUM(pti.quantity) as qty'))
            ->groupBy('pti.name')
            ->orderByDesc('total')
            ->limit(25)
            ->get();

        $totalRevenue = (float) (clone $base)->sum('total');
        $totalTransactions = (int) (clone $base)->count();

        $appointmentCountScheduled = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$rangeStart, $rangeEnd])
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->count();

        $prevTotalRevenue = null;
        $prevAppointmentCount = null;
        $prevFrom = null;
        $prevTo = null;
        if ($compare) {
            $days = $fromDay->diffInDays($toDay) + 1;
            $prevEnd = $fromDay->copy()->subDay();
            $prevStart = $prevEnd->copy()->subDays($days - 1);
            $prevFrom = $prevStart->toDateString();
            $prevTo = $prevEnd->toDateString();
            [$p0, $p1] = $this->revenueRecognizedRangeUtc($salon, $prevFrom, $prevTo);
            $pq = PosTransaction::where('salon_id', $salon->id)->recognizedBetweenUtc($p0, $p1);
            if ($staffId) {
                $pq->where('staff_id', $staffId);
            }
            if ($paymentMethod) {
                $pq->where('payment_method', $paymentMethod);
            }
            $prevTotalRevenue = (float) $pq->sum('total');
            $prevAppointmentCount = Appointment::where('salon_id', $salon->id)
                ->whereBetween('starts_at', [$p0, $p1])
                ->whereNotIn('status', ['cancelled', 'no_show'])
                ->count();
        }

        return compact(
            'daily',
            'byMethod',
            'byStaff',
            'byService',
            'totalRevenue',
            'totalTransactions',
            'appointmentCountScheduled',
            'staffList',
            'staffId',
            'paymentMethod',
            'compare',
            'prevTotalRevenue',
            'prevAppointmentCount',
            'prevFrom',
            'prevTo'
        );
    }

    private function appointmentsReport($salon, $from, $to): array
    {
        $byStatus = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $daily = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ->select(DB::raw('DATE(starts_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $total = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ->count();

        return compact('byStatus', 'daily', 'total');
    }

    private function staffReport($salon, $from, $to): array
    {
        $staff = Staff::where('salon_id', $salon->id)
            ->withCount(['appointments as appointment_count' => fn($q) =>
                $q->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ])
            ->withSum(['appointments as total_revenue' => fn($q) =>
                $q->where('status', 'completed')->whereBetween('starts_at', [$from, $to . ' 23:59:59'])
            ], 'total_price')
            ->get();

        return compact('staff');
    }

    private function clientsReport($salon, $from, $to): array
    {
        $newClients = Client::where('salon_id', $salon->id)
            ->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ->count();

        $returningClients = Client::where('salon_id', $salon->id)
            ->whereHas('appointments', fn($q) =>
                $q->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->where('status', 'completed')
            )
            ->where('created_at', '<', $from)
            ->count();

        $topClients = Client::where('salon_id', $salon->id)
            ->withSum(['transactions as total_spent' => fn($q) =>
                $q->where('status', 'completed')->whereBetween('created_at', [$from, $to . ' 23:59:59'])
            ], 'total')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(10)
            ->get();

        return compact('newClients', 'returningClients', 'topClients');
    }

    private function servicesReport($salon, $from, $to): array
    {
        $services = Service::where('salon_id', $salon->id)
            ->withCount(['appointmentServices as booking_count' => fn($q) =>
                $q->whereHas('appointment', fn($aq) =>
                    $aq->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->where('status', 'completed')
                )
            ])
            ->withSum(['appointmentServices as total_revenue' => fn($q) =>
                $q->whereHas('appointment', fn($aq) =>
                    $aq->whereBetween('starts_at', [$from, $to . ' 23:59:59'])->where('status', 'completed')
                )
            ], 'price')
            ->having('booking_count', '>', 0)
            ->orderByDesc('booking_count')
            ->get();

        return compact('services');
    }
}
