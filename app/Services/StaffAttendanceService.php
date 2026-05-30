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
     * @return array{week_start: string, week_end: string, days: list<array{ymd: string, label: string, dow: string, is_today: bool}>, rows: list<array<string, mixed>>}
     */
    public function buildWeekGrid(Salon $salon, Carbon $weekAnchor, Collection $staff): array
    {
        $weekStart = $weekAnchor->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

        $days = [];
        for ($i = 0; $i < 7; $i++) {
            $d = $weekStart->copy()->addDays($i);
            $days[] = [
                'ymd'      => $d->toDateString(),
                'label'    => $d->format('D j'),
                'dow'      => $d->format('D'),
                'is_today' => $d->isToday(),
            ];
        }

        $records = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->whereBetween('attendance_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->keyBy(fn (StaffAttendanceRecord $r) => $r->staff_id . '|' . $r->attendance_date->toDateString());

        $rows = $staff->map(function (Staff $member) use ($salon, $days, $records) {
            $cells = [];
            foreach ($days as $day) {
                $cells[$day['ymd']] = $this->resolveCell($salon, $member, $day['ymd'], $records);
            }

            return [
                'staff' => $member,
                'cells' => $cells,
            ];
        })->values()->all();

        return [
            'week_start' => $weekStart->toDateString(),
            'week_end'   => $weekEnd->toDateString(),
            'days'       => $days,
            'rows'       => $rows,
        ];
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
