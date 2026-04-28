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

        $selectedStaff = $filterStaffId
            ? Staff::where('salon_id', $salon->id)->whereKey($filterStaffId)->first()
            : null;

        [$hourStart, $hourEnd] = $this->resolveHourBounds($salon, $selectedStaff);
        $availabilityByDate = $this->buildAvailabilityByDate($start, $end, $salon, $selectedStaff, $tz);

        $calendarData = json_encode($appointments);
        $staffData    = json_encode($staff);

        $salonTz = $tz;
        $salonTodayYmd = Carbon::now($tz)->toDateString();
        $tzAbbrev = SalonTime::abbrev($salon);

        return view('calendar.index', compact(
            'salon', 'view', 'date', 'calendarData', 'staffData',
            'appointments', 'start', 'end', 'filterStaffId', 'staff',
            'salonTz', 'salonTodayYmd', 'tzAbbrev', 'hourStart', 'hourEnd',
            'availabilityByDate', 'selectedStaff'
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

    private function resolveHourBounds($salon, ?Staff $staff): array
    {
        $openHours = [];
        $closeHours = [];
        $hours = is_array($salon->opening_hours) ? $salon->opening_hours : [];
        foreach ($hours as $day => $cfg) {
            if (! is_array($cfg) || empty($cfg['open'])) continue;
            $from = substr((string) ($cfg['from'] ?? $cfg['start'] ?? '09:00'), 0, 2);
            $to = substr((string) ($cfg['to'] ?? $cfg['end'] ?? '18:00'), 0, 2);
            if (is_numeric($from) && is_numeric($to)) {
                $openHours[] = (int) $from;
                $closeHours[] = (int) $to;
            }
        }

        if ($staff) {
            $sFrom = substr((string) ($staff->start_time ?? '09:00'), 0, 2);
            $sTo = substr((string) ($staff->end_time ?? '18:00'), 0, 2);
            if (is_numeric($sFrom) && is_numeric($sTo)) {
                $openHours[] = (int) $sFrom;
                $closeHours[] = (int) $sTo;
            }
        }

        $min = $openHours !== [] ? min($openHours) : 8;
        $max = $closeHours !== [] ? max($closeHours) : 20;

        $min = max(0, min(22, $min));
        $max = max($min + 1, min(23, $max));

        return [$min, $max];
    }

    private function buildAvailabilityByDate(Carbon $start, Carbon $end, $salon, ?Staff $staff, string $tz): array
    {
        $map = [];
        $d = $start->copy();
        while ($d->lte($end)) {
            $dateKey = $d->toDateString();
            $weekdayKey = strtolower($d->format('l'));
            $dayCfg = $salon->openingHoursForWeekdayKey($weekdayKey);
            $salonOpen = is_array($dayCfg) && ! empty($dayCfg['open']);

            $shopStart = (int) substr((string) ($dayCfg['from'] ?? $dayCfg['start'] ?? '09:00'), 0, 2);
            $shopEnd = (int) substr((string) ($dayCfg['to'] ?? $dayCfg['end'] ?? '18:00'), 0, 2);

            $staffWorks = true;
            $staffStart = 0;
            $staffEnd = 23;
            if ($staff) {
                $workingDays = is_array($staff->working_days) ? $staff->working_days : [];
                $dow = $d->format('D');
                if ($workingDays !== [] && ! in_array($dow, $workingDays, true)) {
                    $staffWorks = false;
                }
                $staffStart = (int) substr((string) ($staff->start_time ?? '09:00'), 0, 2);
                $staffEnd = (int) substr((string) ($staff->end_time ?? '18:00'), 0, 2);
            }

            $map[$dateKey] = [
                'salon_open' => $salonOpen,
                'shop_start' => $shopStart,
                'shop_end' => $shopEnd,
                'staff_works' => $staffWorks,
                'staff_start' => $staffStart,
                'staff_end' => $staffEnd,
            ];
            $d->addDay();
        }
        return $map;
    }
}
