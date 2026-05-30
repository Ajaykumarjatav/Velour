<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Appointment;
use App\Models\Staff;
use App\Support\SalonTime;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $customRangeActive = false;
        $rangeFromYmd = null;
        $rangeToYmd = null;
        $rangeSpanDays = null;

        if ($view === 'week' && $request->filled('from') && $request->filled('to')) {
            try {
                $from = SalonTime::parseLocalDate($salon, (string) $request->get('from'))->startOfDay();
                $to = SalonTime::parseLocalDate($salon, (string) $request->get('to'))->endOfDay();
                if ($to->lt($from)) {
                    [$from, $to] = [$to->startOfDay(), $from->endOfDay()];
                }
                // Guardrail for UI and query performance.
                if ($from->diffInDays($to) > 60) {
                    $to = $from->copy()->addDays(60)->endOfDay();
                }
                [$start, $end] = [$from, $to];
                $date = $from->copy();
                $customRangeActive = true;
                $rangeFromYmd = $from->toDateString();
                $rangeToYmd = $to->toDateString();
                $rangeSpanDays = $from->diffInDays($to) + 1;
            } catch (\Throwable) {
                [$start, $end] = [$date->copy()->startOfWeek(Carbon::MONDAY), $date->copy()->endOfWeek(Carbon::SUNDAY)];
            }
        } else {
            match ($view) {
                'day' => [$start, $end] = [$date->copy(), $date->copy()->endOfDay()],
                'month' => [
                    $start = $date->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY),
                    $end = $date->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY)->endOfDay(),
                ],
                default => [$start, $end] = [$date->copy()->startOfWeek(Carbon::MONDAY), $date->copy()->endOfWeek(Carbon::SUNDAY)],
            };
        }

        $startUtc = $start->copy()->utc();
        $endUtc = $end->copy()->utc();

        $filterStaffId = $request->filled('staff_id')
            ? (int) $request->get('staff_id')
            : null;
        if ($filterStaffId && ! Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->whereKey($filterStaffId)->exists()) {
            $filterStaffId = null;
        }

        $scopedStaffId = Auth::user()->dashboardScopedStaffId();
        if ($scopedStaffId !== null) {
            $filterStaffId = $scopedStaffId;
        }

        $appointments = Appointment::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->whereBetween('starts_at', [$startUtc, $endUtc])
            ->when($filterStaffId, fn ($q) => $q->where('staff_id', $filterStaffId))
            ->with(['client', 'staff', 'services'])
            ->get()
            ->map(function ($a) use ($tz) {
                $durationMinutes = (int) ($a->duration_minutes ?: max(1, $a->starts_at->diffInMinutes($a->ends_at)));
                $startsLocal = $a->starts_at->copy()->timezone($tz);
                $endsLocal = $a->ends_at->copy()->timezone($tz);

                return [
                    'id'               => $a->id,
                    'title'            => trim(($a->client?->first_name ?? '') . ' ' . ($a->client?->last_name ?? '')),
                    'start'            => $a->starts_at->toIso8601String(),
                    'end'              => $a->ends_at->toIso8601String(),
                    'status'           => $a->status,
                    'staff'            => $a->staff?->name,
                    'staff_id'         => $a->staff_id,
                    'staff_color'      => $a->staff?->color ?: '#7C3AED',
                    'reference'        => $a->reference,
                    'url'              => route('appointments.show', $a->id),
                    'color'            => $this->statusColor($a->status),
                    'services_label'   => $a->services->pluck('service_name')->filter()->implode(', ') ?: 'Appointment',
                    'duration_minutes' => $durationMinutes,
                    'duration_label'   => $this->formatDurationLabel($durationMinutes),
                    'time_label'       => $startsLocal->format('H:i') . ' – ' . $endsLocal->format('H:i'),
                ];
            });

        $staffQuery = Staff::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->where('is_active', true);
        if ($scopedStaffId !== null) {
            $staffQuery->whereKey($scopedStaffId);
        }
        $staff = $staffQuery
            ->orderBy('sort_order')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $selectedStaff = $filterStaffId
            ? Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->whereKey($filterStaffId)->first()
            : null;

        [$hourStart, $hourEnd] = $this->resolveHourBounds($salon, $selectedStaff);
        $availabilityByDate = $this->buildAvailabilityByDate($start, $end, $salon, $selectedStaff, $tz);

        $staffSidebarGrid = in_array($view, ['week', 'day', 'month'], true)
            ? $this->buildStaffSidebarGrid(
                $staff,
                $appointments,
                $start,
                $end,
                $tz,
                $filterStaffId,
                $availabilityByDate,
                $view,
                $view === 'month' ? $date : null
            )
            : null;

        $calendarData = json_encode($appointments);
        $staffData    = json_encode($staff);

        $salonTz = $tz;
        $salonTodayYmd = Carbon::now($tz)->toDateString();
        $tzAbbrev = SalonTime::abbrev($salon);

        return view('calendar.index', compact(
            'salon', 'view', 'date', 'calendarData', 'staffData',
            'appointments', 'start', 'end', 'filterStaffId', 'staff',
            'salonTz', 'salonTodayYmd', 'tzAbbrev', 'hourStart', 'hourEnd',
            'availabilityByDate', 'selectedStaff', 'customRangeActive',
            'rangeFromYmd', 'rangeToYmd', 'rangeSpanDays', 'staffSidebarGrid'
        ));
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $appointments
     * @param  \Illuminate\Database\Eloquent\Collection<int, Staff>  $staff
     */
    private function buildStaffSidebarGrid($staff, $appointments, Carbon $start, Carbon $end, string $tz, ?int $filterStaffId, array $availabilityByDate, string $view, ?Carbon $focusMonth = null): array
    {
        $salonTodayYmd = Carbon::now($tz)->toDateString();
        $days = [];
        $d = $start->copy()->startOfDay();
        while ($d->lte($end)) {
            $ymd = $d->toDateString();
            $days[] = [
                'ymd'              => $ymd,
                'dow'              => $d->format('D'),
                'day_num'          => (int) $d->format('j'),
                'label'            => $d->format('D'),
                'is_today'         => $ymd === $salonTodayYmd,
                'is_current_month' => $focusMonth
                    ? ($d->month === $focusMonth->month && $d->year === $focusMonth->year)
                    : true,
                'day_url'          => route('calendar', array_filter([
                    'view'     => 'day',
                    'date'     => $ymd,
                    'staff_id' => $filterStaffId,
                ])),
            ];
            $d->addDay();
        }

        $layout = match ($view) {
            'month' => 'month',
            'day'   => 'day',
            default => 'week',
        };

        $staffForGrid = $filterStaffId
            ? $staff->where('id', $filterStaffId)->values()
            : $staff;

        $rows = [];
        $unassigned = $appointments->filter(fn ($a) => empty($a['staff_id']));
        if ($unassigned->isNotEmpty() && $filterStaffId === null) {
            $rows[] = $this->buildStaffSidebarRow(
                null,
                'Unassigned',
                'NA',
                null,
                '#9CA3AF',
                null,
                $unassigned,
                $days,
                $tz,
                $availabilityByDate,
                null,
                $layout
            );
        }

        foreach ($staffForGrid as $member) {
            $memberAppts = $appointments->filter(fn ($a) => (int) ($a['staff_id'] ?? 0) === (int) $member->id);
            $rows[] = $this->buildStaffSidebarRow(
                (int) $member->id,
                $member->name ?? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                $this->staffInitials($member),
                $member->avatar_url,
                $member->color ?: '#7C3AED',
                $member->role,
                $memberAppts,
                $days,
                $tz,
                $availabilityByDate,
                $member,
                $layout
            );
        }

        return [
            'days'   => $days,
            'rows'   => $rows,
            'layout' => $layout,
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $memberAppts
     * @param  list<array<string, mixed>>  $days
     */
    private function buildStaffSidebarRow(
        ?int $staffId,
        string $name,
        string $initials,
        ?string $avatarUrl,
        string $color,
        ?string $role,
        $memberAppts,
        array $days,
        string $tz,
        array $availabilityByDate,
        ?Staff $staffModel = null,
        string $layout = 'week'
    ): array {
        $totalMinutes = 0;
        $cells = [];

        foreach ($days as $day) {
            $ymd = $day['ymd'];
            $dayMeta = $availabilityByDate[$ymd] ?? null;
            $salonOpen = (bool) ($dayMeta['salon_open'] ?? true);
            $staffWorks = true;
            if ($staffModel) {
                $workingDays = is_array($staffModel->working_days) ? $staffModel->working_days : [];
                if ($workingDays !== [] && ! in_array($day['dow'], $workingDays, true)) {
                    $staffWorks = false;
                }
            }

            $dayBlocks = $memberAppts
                ->filter(fn ($a) => Carbon::parse($a['start'])->timezone($tz)->toDateString() === $ymd)
                ->sortBy(fn ($a) => $a['start'])
                ->map(function ($a) use ($color, &$totalMinutes, $tz, $layout) {
                    $totalMinutes += (int) ($a['duration_minutes'] ?? 0);
                    $startsLocal = Carbon::parse($a['start'])->timezone($tz);
                    $title = trim((string) ($a['title'] ?? ''));

                    return array_merge($a, [
                        'block_color' => $color,
                        'start_short' => $startsLocal->format('H:i'),
                        'title_short' => $title !== '' ? $title : 'Walk-in',
                        'compact'     => $layout === 'month',
                    ]);
                })
                ->values()
                ->all();

            $cells[$ymd] = [
                'blocks'     => $dayBlocks,
                'blocked'    => ! $salonOpen || ! $staffWorks,
                'create_url' => route('appointments.create', array_filter([
                    'date'     => $ymd,
                    'staff_id' => $staffId,
                ])),
            ];
        }

        return [
            'id'               => $staffId,
            'name'             => $name,
            'initials'         => $initials,
            'avatar_url'       => $avatarUrl,
            'color'            => $color,
            'role'             => $role,
            'week_hours_label' => $this->formatDurationLabel($totalMinutes),
            'cells'            => $cells,
        ];
    }

    private function staffInitials(Staff $member): string
    {
        $initials = trim((string) ($member->initials ?? ''));
        if ($initials !== '') {
            return $initials;
        }
        $name = $member->name ?? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
        if ($name === '') {
            return '?';
        }
        $parts = preg_split('/\s+/', $name) ?: [];

        return strtoupper(collect($parts)->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode(''));
    }

    private function formatDurationLabel(int $minutes): string
    {
        $minutes = max(0, $minutes);
        if ($minutes === 0) {
            return '0h';
        }
        $hours = intdiv($minutes, 60);
        $mins = $minutes % 60;
        if ($hours > 0 && $mins > 0) {
            return "{$hours}h{$mins}m";
        }
        if ($hours > 0) {
            return "{$hours}h";
        }

        return "{$mins}m";
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
