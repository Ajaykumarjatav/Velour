<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\Staff;
use App\Support\SalonTime;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    use ResolvesActiveSalon;

    public function index(Request $request)
    {
        $salon = $this->activeSalon();
        $tz = SalonTime::timezone($salon);

        $view = $request->get('view', 'week');
        $dateStr = $request->get('date', Carbon::now($tz)->toDateString());
        $date = SalonTime::parseLocalDate($salon, $dateStr);

        match ($view) {
            'day' => [$start, $end] = [$date->copy(), $date->copy()->endOfDay()],
            'month' => [$start, $end] = [$date->copy()->startOfMonth(), $date->copy()->endOfMonth()],
            default => [$start, $end] = [$date->copy()->startOfWeek(Carbon::MONDAY), $date->copy()->endOfWeek(Carbon::SUNDAY)],
        };

        $startUtc = $start->copy()->utc();
        $endUtc = $end->copy()->utc();

        $filterStaffId = $request->filled('staff_id')
            ? (int) $request->get('staff_id')
            : null;
        if ($filterStaffId && ! Staff::where('salon_id', $salon->id)->whereKey($filterStaffId)->exists()) {
            $filterStaffId = null;
        }

        $appointments = Appointment::where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$startUtc, $endUtc])
            ->when($filterStaffId, fn ($q) => $q->where('staff_id', $filterStaffId))
            ->with(['client', 'staff', 'services'])
            ->get()
            ->map(fn ($a) => [
                'id'        => $a->id,
                'title'     => $a->client?->first_name . ' ' . $a->client?->last_name,
                'start'     => $a->starts_at->toIso8601String(),
                'end'       => $a->ends_at->toIso8601String(),
                'status'    => $a->status,
                'staff'     => $a->staff?->name,
                'staff_id'  => $a->staff_id,
                'reference' => $a->reference,
                'url'       => route('appointments.show', $a->id),
                'color'     => $this->statusColor($a->status),
            ]);

        $staff = Staff::where('salon_id', $salon->id)
            ->where('is_active', true)
            ->withName()
            ->get();

        $calendarData = json_encode($appointments);
        $staffData    = json_encode($staff);

        $salonTz = $tz;
        $salonTodayYmd = Carbon::now($tz)->toDateString();
        $tzAbbrev = SalonTime::abbrev($salon);

        return view('calendar.index', compact(
            'salon', 'view', 'date', 'calendarData', 'staffData',
            'appointments', 'start', 'end', 'filterStaffId',
            'salonTz', 'salonTodayYmd', 'tzAbbrev'
        ));
    }

    private function statusColor(string $status): string
    {
        return match ($status) {
            'confirmed'  => '#7C3AED',
            'completed'  => '#059669',
            'cancelled'  => '#DC2626',
            'no_show'    => '#D97706',
            default      => '#6B7280',
        };
    }
}
