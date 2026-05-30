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
                    'source'           => $a->source ?? 'manual',
                    'source_label'     => Appointment::sourceLabel($a->source),
                    'source_icon'      => $this->appointmentSourceIcon($a),
                    'is_referral'      => (bool) $a->client?->referred_by_client_id,
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

        $staffPage = max(1, (int) $request->get('staff_page', 1));

        $staffSidebarGrid = in_array($view, ['week', 'month'], true)
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

        $dayScheduleGrid = $view === 'day'
            ? $this->buildDayScheduleGrid(
                $staff,
                $appointments,
                $start,
                $tz,
                $filterStaffId,
                $availabilityByDate,
                $hourStart,
                $hourEnd,
                $staffPage,
                $salon
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
            'rangeFromYmd', 'rangeToYmd', 'rangeSpanDays', 'staffSidebarGrid',
            'dayScheduleGrid', 'staffPage'
        ));
    }

    /**
     * Day view: staff as columns with a vertical time axis (Fresha-style schedule).
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Staff>  $staff
     * @param  \Illuminate\Support\Collection<int, array<string, mixed>>  $appointments
     */
    private function buildDayScheduleGrid(
        $staff,
        $appointments,
        Carbon $date,
        string $tz,
        ?int $filterStaffId,
        array $availabilityByDate,
        int $hourStart,
        int $hourEnd,
        int $staffPage,
        $salon
    ): array {
        $sidebar = $this->buildStaffSidebarGrid(
            $staff,
            $appointments,
            $date,
            $date->copy()->endOfDay(),
            $tz,
            $filterStaffId,
            $availabilityByDate,
            'day',
            null
        );

        $ymd = $date->toDateString();
        $dayMeta = $sidebar['days'][0] ?? null;
        $slotMinutes = 30;
        $slotHeightPx = 52;
        $pxPerMinute = $slotHeightPx / $slotMinutes;

        $gridHourStart = $hourStart;
        $gridHourEnd = $hourEnd;

        foreach ($appointments as $a) {
            $startsLocal = Carbon::parse($a['start'])->timezone($tz);
            $endsLocal = Carbon::parse($a['end'])->timezone($tz);
            if ($startsLocal->toDateString() !== $ymd) {
                continue;
            }
            $gridHourStart = min($gridHourStart, (int) $startsLocal->format('G'));
            $gridHourEnd = max($gridHourEnd, (int) $endsLocal->format('G') + ($endsLocal->minute > 0 ? 1 : 0));
        }

        $gridHourStart = max(0, min(22, $gridHourStart));
        $gridHourEnd = max($gridHourStart + 1, min(23, $gridHourEnd));

        $dayStartMinutes = $gridHourStart * 60;
        $totalMinutes = ($gridHourEnd - $gridHourStart) * 60;
        $gridHeightPx = (int) round($totalMinutes * $pxPerMinute);

        $timeSlots = [];
        for ($offset = 0; $offset < $totalMinutes; $offset += $slotMinutes) {
            $absolute = $dayStartMinutes + $offset;
            $hour = intdiv($absolute, 60);
            $minute = $absolute % 60;
            $at = Carbon::createFromTime($hour, $minute, 0, $tz);
            $timeSlots[] = [
                'top_px'  => (int) round($offset * $pxPerMinute),
                'label'   => strtolower($at->format('g:ia')),
                'is_minor' => false,
            ];
            for ($minor = 10; $minor < $slotMinutes; $minor += 10) {
                $timeSlots[] = [
                    'top_px'   => (int) round(($offset + $minor) * $pxPerMinute),
                    'label'    => (string) $minor,
                    'is_minor' => true,
                ];
            }
        }
        $timeSlots[] = [
            'top_px'  => (int) round($totalMinutes * $pxPerMinute),
            'label'   => strtolower(Carbon::createFromTime($gridHourEnd, 0, 0, $tz)->format('g:ia')),
            'is_minor' => false,
        ];

        $perPage = 7;
        $allRows = $sidebar['rows'];
        $staffTotal = count($allRows);
        $lastPage = max(1, (int) ceil($staffTotal / $perPage));
        $staffPage = min($staffPage, $lastPage);
        $pageRows = array_slice($allRows, ($staffPage - 1) * $perPage, $perPage);

        $dayAvail = $availabilityByDate[$ymd] ?? [];
        $columns = [];
        foreach ($pageRows as $row) {
            $cell = $row['cells'][$ymd] ?? ['blocks' => [], 'blocked' => false, 'create_url' => '#'];
            $positioned = [];
            $bookedSlots = 0;
            $staffMember = $row['id'] ? $staff->firstWhere('id', $row['id']) : null;
            $totalSlots = $this->daySlotCountForStaff($staffMember, $date, $dayAvail, $slotMinutes, $salon);

            foreach ($cell['blocks'] as $block) {
                $startsLocal = Carbon::parse($block['start'])->timezone($tz);
                $endsLocal = Carbon::parse($block['end'])->timezone($tz);
                $startMin = ($startsLocal->hour * 60 + $startsLocal->minute) - $dayStartMinutes;
                $durMin = max(15, (int) ($block['duration_minutes'] ?? 60));
                $clientName = trim((string) ($block['title'] ?? ''));
                if ($clientName === '') {
                    $clientName = 'Walk-in';
                }

                $bookedSlots += max(1, (int) ceil($durMin / $slotMinutes));

                $positioned[] = array_merge($block, [
                    'top_px'           => max(0, (int) round($startMin * $pxPerMinute)),
                    'height_px'        => max(32, (int) round($durMin * $pxPerMinute) - 4),
                    'client_name'      => $clientName,
                    'time_range_label' => strtolower($startsLocal->format('g:ia')) . ' - ' . strtolower($endsLocal->format('g:ia')),
                    'status_color'     => $block['color'] ?? '#6B7280',
                    'source_icon'      => $block['source_icon'] ?? 'desk',
                    'source_label'     => $block['source_label'] ?? 'Manual',
                ]);
            }

            $columns[] = [
                'id'          => $row['id'],
                'name'        => $row['name'],
                'initials'    => $row['initials'],
                'avatar_url'  => $row['avatar_url'],
                'color'       => $row['color'],
                'role'        => $row['role'],
                'date_label'  => $dayMeta ? ($dayMeta['label'] . ' ' . $dayMeta['day_num']) : $date->format('D j'),
                'booked_slots' => $bookedSlots,
                'total_slots'  => $totalSlots,
                'slots_label'  => $totalSlots > 0 ? "{$bookedSlots}/{$totalSlots}" : (string) $bookedSlots,
                'blocks'      => $positioned,
                'blocked'     => (bool) ($cell['blocked'] ?? false),
                'create_url'  => $cell['create_url'] ?? '#',
            ];
        }

        return [
            'ymd'            => $ymd,
            'date_label'     => $date->format('l, j F Y'),
            'is_today'       => ($dayMeta['is_today'] ?? false),
            'hour_start'     => $gridHourStart,
            'hour_end'       => $gridHourEnd,
            'slot_height_px' => $slotHeightPx,
            'grid_height_px' => $gridHeightPx,
            'time_slots'     => $timeSlots,
            'staff_columns'  => $columns,
            'staff_page'     => $staffPage,
            'staff_per_page' => $perPage,
            'staff_total'    => $staffTotal,
            'staff_last_page' => $lastPage,
            'staff_range_label' => $this->dayStaffRangeLabel($staffPage, $perPage, $staffTotal),
        ];
    }

    private function dayStaffRangeLabel(int $page, int $perPage, int $total): string
    {
        if ($total === 0) {
            return '0';
        }
        $from = (($page - 1) * $perPage) + 1;
        $to = min($total, $page * $perPage);

        return "{$from} - {$to}";
    }

    private function appointmentSourceIcon(Appointment $appointment): string
    {
        if ($appointment->client?->referred_by_client_id) {
            return 'reference';
        }

        return match ($appointment->source) {
            'walk_in' => 'walk_in',
            'phone' => 'phone',
            'online', 'website_embed', 'qr_code', 'google', 'instagram', 'facebook', 'whatsapp' => 'online',
            default => 'desk',
        };
    }

    private function daySlotCountForStaff(?Staff $staff, Carbon $date, array $dayMeta, int $slotMinutes, $salon): int
    {
        if (! ($dayMeta['salon_open'] ?? true)) {
            return 0;
        }

        if ($staff) {
            $workingDays = is_array($staff->working_days) ? $staff->working_days : [];
            if ($workingDays !== [] && ! in_array($date->format('D'), $workingDays, true)) {
                return 0;
            }
            $startHour = (int) substr((string) ($staff->start_time ?? '09:00'), 0, 2);
            $endHour = (int) substr((string) ($staff->end_time ?? '18:00'), 0, 2);
        } else {
            $startHour = (int) ($dayMeta['shop_start'] ?? 9);
            $endHour = (int) ($dayMeta['shop_end'] ?? 18);
        }

        $weekdayKey = strtolower($date->format('l'));
        $dayCfg = $salon->openingHoursForWeekdayKey($weekdayKey);
        if (is_array($dayCfg) && ! empty($dayCfg['open'])) {
            $shopStart = (int) substr((string) ($dayCfg['from'] ?? $dayCfg['start'] ?? '09:00'), 0, 2);
            $shopEnd = (int) substr((string) ($dayCfg['to'] ?? $dayCfg['end'] ?? '18:00'), 0, 2);
            $startHour = max($startHour, $shopStart);
            $endHour = min($endHour, $shopEnd);
        }

        $minutes = max(0, ($endHour - $startHour) * 60);

        return (int) max(0, floor($minutes / max(1, $slotMinutes)));
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
