<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Staff;
use App\Models\Service;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    /* ── GET /calendar ──────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['view' => 'nullable|in:day,week,month', 'date' => 'nullable|date']);
        $view = $request->view ?? 'week';
        $date = Carbon::parse($request->date ?? now());

        return match ($view) {
            'day'   => $this->day($request),
            'month' => $this->month($request),
            default => $this->week($request),
        };
    }

    /* ── GET /calendar/day ──────────────────────────────────────────────── */
    public function day(Request $request): JsonResponse
    {
        $request->validate(['date' => 'nullable|date', 'staff_id' => 'nullable|integer']);
        $salonId = $request->attributes->get('salon_id');
        $date    = Carbon::parse($request->date ?? now());

        $query = Appointment::with(['client', 'staff', 'services'])
            ->where('salon_id', $salonId)
            ->whereDate('starts_at', $date->toDateString())
            ->whereNotIn('status', ['cancelled']);

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $appointments = $query->orderBy('starts_at')->get();

        $staff = Staff::where('salon_id', $salonId)->where('is_active', true)->orderBy('sort_order')->get();

        // Group by staff for timeline view
        $byStaff = $staff->map(fn($s) => [
            'staff'        => $s,
            'appointments' => $appointments->where('staff_id', $s->id)->values(),
        ]);

        return response()->json([
            'date'          => $date->toDateString(),
            'day_name'      => $date->format('l, j F Y'),
            'staff'         => $byStaff,
            'total'         => $appointments->count(),
            'completed'     => $appointments->where('status', 'completed')->count(),
        ]);
    }

    /* ── GET /calendar/week ─────────────────────────────────────────────── */
    public function week(Request $request): JsonResponse
    {
        $request->validate(['date' => 'nullable|date', 'staff_id' => 'nullable|integer']);
        $salonId = $request->attributes->get('salon_id');
        $start   = Carbon::parse($request->date ?? now())->startOfWeek();
        $end     = $start->copy()->endOfWeek();

        $query = Appointment::with(['client', 'staff', 'services'])
            ->where('salon_id', $salonId)
            ->whereBetween('starts_at', [$start, $end])
            ->whereNotIn('status', ['cancelled']);

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        $appointments = $query->orderBy('starts_at')->get();

        // Build 7-day structure
        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $day  = $start->copy()->addDays($i);
            $dayAppts = $appointments->filter(fn($a) =>
                Carbon::parse($a->starts_at)->isSameDay($day)
            )->values();

            $days[] = [
                'date'         => $day->toDateString(),
                'day_name'     => $day->format('D'),
                'day_number'   => $day->day,
                'is_today'     => $day->isToday(),
                'appointments' => $dayAppts,
                'count'        => $dayAppts->count(),
            ];
        }

        return response()->json([
            'week_start'  => $start->toDateString(),
            'week_end'    => $end->toDateString(),
            'days'        => $days,
            'total'       => $appointments->count(),
        ]);
    }

    /* ── GET /calendar/month ────────────────────────────────────────────── */
    public function month(Request $request): JsonResponse
    {
        $request->validate(['date' => 'nullable|date']);
        $salonId = $request->attributes->get('salon_id');
        $month   = Carbon::parse($request->date ?? now())->startOfMonth();
        $end     = $month->copy()->endOfMonth();

        $appointments = Appointment::where('salon_id', $salonId)
            ->whereBetween('starts_at', [$month, $end])
            ->whereNotIn('status', ['cancelled'])
            ->selectRaw("DATE(starts_at) as date, COUNT(*) as count, SUM(total_price) as revenue")
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $days = [];
        $current = $month->copy();
        while ($current->lte($end)) {
            $date = $current->toDateString();
            $days[] = [
                'date'     => $date,
                'count'    => $appointments[$date]->count    ?? 0,
                'revenue'  => $appointments[$date]->revenue  ?? 0,
                'is_today' => $current->isToday(),
            ];
            $current->addDay();
        }

        return response()->json([
            'month'      => $month->format('F Y'),
            'year'       => $month->year,
            'month_num'  => $month->month,
            'days'       => $days,
            'total_appts'=> $appointments->sum('count'),
            'total_rev'  => round($appointments->sum('revenue'), 2),
        ]);
    }

    /* ── GET /calendar/slots ────────────────────────────────────────────── */
    public function availableSlots(Request $request): JsonResponse
    {
        $request->validate([
            'date'       => 'required|date|after_or_equal:today',
            'service_id' => 'required|integer',
            'staff_id'   => 'nullable|integer',
        ]);

        $salonId = $request->attributes->get('salon_id');
        $service = Service::where('salon_id', $salonId)->findOrFail($request->service_id);

        $slots = $this->bookingService->getAvailableSlots(
            $salonId,
            $service,
            Carbon::parse($request->date),
            $request->staff_id
        );

        return response()->json([
            'date'    => $request->date,
            'service' => $service->only(['id', 'name', 'duration_minutes']),
            'slots'   => $slots,
        ]);
    }

    /* ── POST /calendar/block ───────────────────────────────────────────── */
    public function blockTime(Request $request): JsonResponse
    {
        $data = $request->validate([
            'staff_id'   => 'required|integer',
            'starts_at'  => 'required|date',
            'ends_at'    => 'required|date|after:starts_at',
            'reason'     => 'nullable|string|max:255',
        ]);

        $salonId = $request->attributes->get('salon_id');

        // Create a "blocked" appointment placeholder
        $block = Appointment::create([
            'salon_id'         => $salonId,
            'staff_id'         => $data['staff_id'],
            'client_id'        => null,
            'reference'        => 'BLK-' . strtoupper(uniqid()),
            'starts_at'        => $data['starts_at'],
            'ends_at'          => $data['ends_at'],
            'duration_minutes' => Carbon::parse($data['starts_at'])->diffInMinutes($data['ends_at']),
            'total_price'      => 0,
            'status'           => 'confirmed',
            'source'           => 'manual',
            'internal_notes'   => $data['reason'] ?? 'Blocked time',
        ]);

        return response()->json(['message' => 'Time blocked.', 'block' => $block], 201);
    }

    /* ── DELETE /calendar/block/{id} ────────────────────────────────────── */
    public function removeBlock(Request $request, int $id): JsonResponse
    {
        $block = Appointment::where('salon_id', $request->attributes->get('salon_id'))
            ->where('total_price', 0)
            ->findOrFail($id);

        $block->delete();

        return response()->json(['message' => 'Block removed.']);
    }
}
