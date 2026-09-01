<?php

namespace App\Services;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\StaffAttendanceRecord;
use App\Models\StaffLeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class StaffAttendanceService
{
    /** @var list<string> */
    public const WEEK_DAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

    /**
     * @param  Collection<int, Staff>  $staff
     * @return array{
     *     period: string,
     *     range_start: string,
     *     range_end: string,
     *     days: list<array<string, mixed>>,
     *     rows: list<array{staff: Staff, cells: array<string, mixed>}>
     * }
     */
    public function buildAttendanceGrid(
        Salon $salon,
        string $period,
        Carbon $anchor,
        Collection $staff,
        ?int $filterStaffId = null
    ): array {
        if ($filterStaffId) {
            $staff = $staff->where('id', $filterStaffId)->values();
        }

        return match ($period) {
            'month' => $this->buildDailyRangeGrid(
                $salon,
                $anchor->copy()->startOfMonth()->startOfDay(),
                $anchor->copy()->endOfMonth()->startOfDay(),
                $staff,
                'month'
            ),
            'year' => $this->buildYearGrid($salon, $anchor->copy()->startOfYear(), $staff),
            default => $this->buildDailyRangeGrid(
                $salon,
                $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $anchor->copy()->startOfWeek(Carbon::MONDAY)->addDays(6)->startOfDay(),
                $staff,
                'week'
            ),
        };
    }

    /** @deprecated Use buildAttendanceGrid() */
    public function buildWeekGrid(Salon $salon, Carbon $weekAnchor, Collection $staff): array
    {
        $grid = $this->buildAttendanceGrid($salon, 'week', $weekAnchor, $staff);

        return [
            'week_start' => $grid['range_start'],
            'week_end'   => $grid['range_end'],
            'days'       => $grid['days'],
            'rows'       => $grid['rows'],
        ];
    }

    /**
     * @param  Collection<int, Staff>  $staff
     * @return array{period: string, range_start: string, range_end: string, days: list<array<string, mixed>>, rows: list<array{staff: Staff, cells: array<string, mixed>}>}
     */
    private function buildDailyRangeGrid(
        Salon $salon,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        Collection $staff,
        string $period
    ): array {
        $days = [];
        $d = $rangeStart->copy();
        while ($d->lte($rangeEnd)) {
            $days[] = [
                'ymd'      => $d->toDateString(),
                'label'    => $period === 'month' ? $d->format('j') : $d->format('D j'),
                'dow'      => $d->format('D'),
                'is_today' => $d->isToday(),
                'compact'  => $period === 'month',
            ];
            $d->addDay();
        }

        $records = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereBetween('attendance_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get()
            ->keyBy(fn (StaffAttendanceRecord $r) => $r->staff_id . '|' . $r->attendance_date->toDateString());

        $rows = $staff->map(function (Staff $member) use ($salon, $days, $records) {
            $cells = [];
            foreach ($days as $day) {
                $cells[$day['ymd']] = $this->resolveCell($salon, $member, $day['ymd'], $records);
            }

            return ['staff' => $member, 'cells' => $cells];
        })->values()->all();

        return [
            'period'      => $period,
            'range_start' => $rangeStart->toDateString(),
            'range_end'   => $rangeEnd->toDateString(),
            'days'        => $days,
            'rows'        => $rows,
        ];
    }

    /**
     * @param  Collection<int, Staff>  $staff
     * @return array{period: string, range_start: string, range_end: string, days: list<array<string, mixed>>, rows: list<array{staff: Staff, cells: array<string, mixed>}>}
     */
    private function buildYearGrid(Salon $salon, Carbon $yearStart, Collection $staff): array
    {
        $year = (int) $yearStart->format('Y');
        $rangeStart = $yearStart->copy()->startOfYear();
        $rangeEnd = $yearStart->copy()->endOfYear();

        $days = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStart = Carbon::create($year, $m, 1)->startOfMonth();
            $days[] = [
                'ymd'         => $monthStart->toDateString(),
                'month_key'   => $monthStart->format('Y-m'),
                'label'       => $monthStart->format('M'),
                'is_today'    => now()->format('Y-m') === $monthStart->format('Y-m'),
                'is_summary'  => true,
            ];
        }

        $records = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereBetween('attendance_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->get();

        $rows = $staff->map(function (Staff $member) use ($salon, $days, $records, $year) {
            $cells = [];
            foreach ($days as $day) {
                $cells[$day['month_key']] = $this->resolveYearMonthCell(
                    $salon,
                    $member,
                    $year,
                    (int) Carbon::parse($day['ymd'])->format('n'),
                    $records->where('staff_id', $member->id)
                );
            }

            return ['staff' => $member, 'cells' => $cells];
        })->values()->all();

        return [
            'period'      => 'year',
            'range_start' => $rangeStart->toDateString(),
            'range_end'   => $rangeEnd->toDateString(),
            'days'        => $days,
            'rows'        => $rows,
        ];
    }

    /**
     * @param  Collection<int, StaffAttendanceRecord>  $staffRecords
     * @return array<string, mixed>
     */
    private function resolveYearMonthCell(
        Salon $salon,
        Staff $staff,
        int $year,
        int $month,
        Collection $staffRecords
    ): array {
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $counts = [
            'present'  => 0,
            'absent'   => 0,
            'late'     => 0,
            'half_day' => 0,
            'on_leave' => 0,
            'day_off'  => 0,
            'unset'    => 0,
        ];

        $d = $monthStart->copy();
        while ($d->lte($monthEnd)) {
            $ymd = $d->toDateString();
            $subset = $staffRecords->filter(
                fn (StaffAttendanceRecord $r) => $r->attendance_date->toDateString() === $ymd
            )->keyBy(fn (StaffAttendanceRecord $r) => $r->staff_id . '|' . $r->attendance_date->toDateString());

            $cell = $this->resolveCell($salon, $staff, $ymd, $subset);
            $status = $cell['status'] ?? 'unset';
            if ($status === null) {
                $counts['unset']++;
            } elseif (isset($counts[$status])) {
                $counts[$status]++;
            }
            $d->addDay();
        }

        $parts = [];
        foreach (['present' => 'P', 'absent' => 'A', 'late' => 'L', 'half_day' => '½', 'on_leave' => 'Lv'] as $key => $short) {
            if ($counts[$key] > 0) {
                $parts[] = "{$short}:{$counts[$key]}";
            }
        }

        return [
            'status'    => 'summary',
            'label'     => $parts !== [] ? implode(' ', $parts) : '—',
            'readonly'  => true,
            'scheduled' => true,
            'on_leave'  => false,
            'counts'    => $counts,
            'clock_in'  => null,
            'clock_out' => null,
        ];
    }

    /**
     * Daily rows for CSV export.
     *
     * @return list<array{staff_name: string, date: string, day: string, status: string, clock_in: string, clock_out: string, notes: string}>
     */
    public function buildAttendanceExportRows(
        Salon $salon,
        Carbon $rangeStart,
        Carbon $rangeEnd,
        Collection $staff,
        ?int $filterStaffId = null
    ): array {
        if ($filterStaffId) {
            $staff = $staff->where('id', $filterStaffId)->values();
        }

        $records = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereBetween('attendance_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->when($filterStaffId, fn ($q) => $q->where('staff_id', $filterStaffId))
            ->get()
            ->keyBy(fn (StaffAttendanceRecord $r) => $r->staff_id . '|' . $r->attendance_date->toDateString());

        $out = [];
        $d = $rangeStart->copy();
        while ($d->lte($rangeEnd)) {
            $ymd = $d->toDateString();
            foreach ($staff as $member) {
                $cell = $this->resolveCell($salon, $member, $ymd, $records);
                $record = $records->get($member->id . '|' . $ymd);
                $out[] = [
                    'staff_name' => $member->name ?? trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                    'date'       => $ymd,
                    'day'        => $d->format('l'),
                    'status'     => $this->exportStatusLabel($cell, $record),
                    'clock_in'   => $cell['clock_in'] ?? '',
                    'clock_out'  => $cell['clock_out'] ?? '',
                    'notes'      => $record?->notes ?? '',
                ];
            }
            $d->addDay();
        }

        return $out;
    }

    /**
     * Plain ASCII status text for CSV (avoids em-dash mojibake in Excel).
     *
     * @param  array<string, mixed>  $cell
     */
    public function exportStatusLabel(array $cell, ?StaffAttendanceRecord $record): string
    {
        if ($record && in_array($record->status, StaffAttendanceRecord::STATUSES, true)) {
            return StaffAttendanceRecord::statusLabel($record->status);
        }

        $status = $cell['status'] ?? null;

        return match ($status) {
            'day_off' => 'Day off',
            'on_leave' => 'On leave',
            'present' => 'Present',
            'absent' => 'Absent',
            'late' => 'Late',
            'half_day' => 'Half day',
            null => ($cell['scheduled'] ?? true) ? 'Not marked' : 'Day off',
            default => StaffAttendanceRecord::statusLabel((string) $status),
        };
    }

    public function resolveExportDateRange(string $period, Carbon $anchor): array
    {
        return match ($period) {
            'month' => [
                $anchor->copy()->startOfMonth()->startOfDay(),
                $anchor->copy()->endOfMonth()->startOfDay(),
            ],
            'year' => [
                $anchor->copy()->startOfYear()->startOfDay(),
                $anchor->copy()->endOfYear()->startOfDay(),
            ],
            default => [
                $anchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay(),
                $anchor->copy()->startOfWeek(Carbon::MONDAY)->addDays(6)->startOfDay(),
            ],
        };
    }

    /**
     * @param  Collection<string, StaffAttendanceRecord>  $records
     * @return array<string, mixed>
     */
    public function resolveCell(Salon $salon, Staff $staff, string $dateYmd, Collection $records): array
    {
        $date = Carbon::parse($dateYmd);
        $dow = $date->format('D');
        $scheduled = $this->isScheduledWorkingDay($staff, $dow);
        $onLeave = StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staff->id, $dateYmd);

        $key = $staff->id . '|' . $dateYmd;
        /** @var StaffAttendanceRecord|null $record */
        $record = $records->get($key);

        if ($onLeave) {
            return [
                'status'       => StaffAttendanceRecord::STATUS_ON_LEAVE,
                'label'        => StaffAttendanceRecord::statusLabel(StaffAttendanceRecord::STATUS_ON_LEAVE),
                'scheduled'    => $scheduled,
                'on_leave'     => true,
                'readonly'     => true,
                'clock_in'     => null,
                'clock_out'    => null,
                'record_id'    => $record?->id,
            ];
        }

        if (! $scheduled) {
            return [
                'status'    => 'day_off',
                'label'     => 'Day off',
                'scheduled' => false,
                'on_leave'  => false,
                'readonly'  => true,
                'clock_in'  => null,
                'clock_out' => null,
                'record_id' => $record?->id,
            ];
        }

        if ($record) {
            return [
                'status'    => $record->status,
                'label'     => StaffAttendanceRecord::statusLabel($record->status),
                'scheduled' => true,
                'on_leave'  => false,
                'readonly'  => $record->status === StaffAttendanceRecord::STATUS_ON_LEAVE,
                'clock_in'  => $record->clock_in_at?->format('H:i'),
                'clock_out' => $record->clock_out_at?->format('H:i'),
                'record_id' => $record->id,
            ];
        }

        return [
            'status'    => null,
            'label'     => '—',
            'scheduled' => true,
            'on_leave'  => false,
            'readonly'  => false,
            'clock_in'  => null,
            'clock_out' => null,
            'record_id' => null,
        ];
    }

    public function isScheduledWorkingDay(Staff $staff, string $dow): bool
    {
        $days = $staff->working_days;

        return $days === null || in_array($dow, $days, true);
    }

    /** @return array<string, mixed> */
    public function freshCell(Salon $salon, Staff $staff, string $dateYmd): array
    {
        $records = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->whereDate('attendance_date', $dateYmd)
            ->get()
            ->keyBy(fn (StaffAttendanceRecord $r) => $r->staff_id . '|' . $r->attendance_date->toDateString());

        return $this->resolveCell($salon, $staff, $dateYmd, $records);
    }

    public function upsert(
        Salon $salon,
        Staff $staff,
        string $dateYmd,
        string $status,
        ?User $recordedBy = null,
        ?string $notes = null
    ): StaffAttendanceRecord {
        if (! in_array($status, StaffAttendanceRecord::STATUSES, true)) {
            throw new \InvalidArgumentException('Invalid attendance status.');
        }

        if (StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staff->id, $dateYmd)
            && $status !== StaffAttendanceRecord::STATUS_ON_LEAVE) {
            throw new \InvalidArgumentException('Staff is on approved leave for this date.');
        }

        $dow = Carbon::parse($dateYmd)->format('D');
        if (! $this->isScheduledWorkingDay($staff, $dow) && $status !== StaffAttendanceRecord::STATUS_ON_LEAVE) {
            throw new \InvalidArgumentException('Not a scheduled working day.');
        }

        return StaffAttendanceRecord::withoutGlobalScopes()->updateOrCreate(
            [
                'salon_id'         => $salon->id,
                'staff_id'         => $staff->id,
                'attendance_date'  => $dateYmd,
            ],
            [
                'status'      => $status,
                'notes'       => $notes,
                'recorded_by' => $recordedBy?->id,
            ]
        );
    }

    public function clockIn(Salon $salon, Staff $staff, ?User $recordedBy = null): StaffAttendanceRecord
    {
        $today = now()->toDateString();

        return StaffAttendanceRecord::withoutGlobalScopes()->updateOrCreate(
            [
                'salon_id'        => $salon->id,
                'staff_id'        => $staff->id,
                'attendance_date' => $today,
            ],
            [
                'status'      => StaffAttendanceRecord::STATUS_PRESENT,
                'clock_in_at' => now(),
                'recorded_by' => $recordedBy?->id,
            ]
        );
    }

    public function clockOut(Salon $salon, Staff $staff, ?User $recordedBy = null): StaffAttendanceRecord
    {
        $today = now()->toDateString();

        $record = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $record) {
            $record = $this->clockIn($salon, $staff, $recordedBy);
        }

        $record->update([
            'clock_out_at' => now(),
            'recorded_by'  => $recordedBy?->id ?? $record->recorded_by,
        ]);

        return $record->fresh();
    }

    /** Mark approved leave dates as on_leave for reporting. */
    public function syncLeaveToAttendance(StaffLeaveRequest $leave): void
    {
        if ($leave->status !== 'approved') {
            return;
        }

        $staff = $leave->staff;
        if (! $staff) {
            return;
        }

        $salon = $leave->salon;
        if (! $salon) {
            return;
        }

        $d = $leave->start_date->copy();
        while ($d->lte($leave->end_date)) {
            StaffAttendanceRecord::withoutGlobalScopes()->updateOrCreate(
                [
                    'salon_id'        => $salon->id,
                    'staff_id'        => $leave->staff_id,
                    'attendance_date' => $d->toDateString(),
                ],
                [
                    'status'      => StaffAttendanceRecord::STATUS_ON_LEAVE,
                    'recorded_by' => auth()->id(),
                ]
            );
            $d->addDay();
        }
    }

    public function todayStatus(Salon $salon, Staff $staff): ?string
    {
        $today = now()->toDateString();

        if (StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staff->id, $today)) {
            return StaffAttendanceRecord::STATUS_ON_LEAVE;
        }

        $dow = now()->format('D');
        if (! $this->isScheduledWorkingDay($staff, $dow)) {
            return 'day_off';
        }

        $record = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->whereDate('attendance_date', $today)
            ->first();

        return $record?->status;
    }

    /**
     * When set, no appointment slots should be offered for this staff on this date.
     */
    public function attendanceBookingBlockReason(int $salonId, int $staffId, string $dateYmd): ?string
    {
        $record = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->where('staff_id', $staffId)
            ->whereDate('attendance_date', $dateYmd)
            ->first();

        if (! $record) {
            return null;
        }

        return match ($record->status) {
            StaffAttendanceRecord::STATUS_ABSENT => 'This staff member is marked absent for this date. Update under Availability → Attendance or choose another day.',
            StaffAttendanceRecord::STATUS_ON_LEAVE => 'This staff member is on leave for this date (attendance).',
            StaffAttendanceRecord::STATUS_HALF_DAY => 'This staff member is on a half day. Confirm timing with your manager or update Attendance.',
            default => null,
        };
    }

    /**
     * Whole-day block for slot grids: approved leave, weekly day off, or blocking attendance.
     */
    public function daySchedulingBlockReason(Salon $salon, Staff $staff, string $dateYmd): ?string
    {
        if (StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staff->id, $dateYmd)) {
            return 'Approved leave or time off on this date — see Availability → Leave.';
        }

        $dow = Carbon::parse($dateYmd)->format('D');
        if (! $this->isScheduledWorkingDay($staff, $dow)) {
            return 'This staff member is not scheduled on this day — check Availability or Staff weekly schedule.';
        }

        return $this->attendanceBookingBlockReason($salon->id, $staff->id, $dateYmd);
    }

    public function isOnDutyToday(Salon $salon, Staff $staff): bool
    {
        if (! $staff->is_active) {
            return false;
        }

        $status = $this->todayStatus($salon, $staff);

        if ($status === 'day_off' || $status === StaffAttendanceRecord::STATUS_ON_LEAVE) {
            return false;
        }

        if ($status === StaffAttendanceRecord::STATUS_ABSENT) {
            return false;
        }

        if ($status === null) {
            return ! StaffLeaveRequest::approvedBlockingLeaveExists($salon->id, $staff->id, now()->toDateString());
        }

        return in_array($status, [
            StaffAttendanceRecord::STATUS_PRESENT,
            StaffAttendanceRecord::STATUS_LATE,
            StaffAttendanceRecord::STATUS_HALF_DAY,
        ], true);
    }
}
