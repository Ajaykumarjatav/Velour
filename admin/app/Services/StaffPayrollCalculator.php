<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\StaffAttendanceRecord;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Staff pay for a calendar month: pro-rated base (attendance) + commission on completed appointments.
 */
class StaffPayrollCalculator
{
    public function __construct(
        private readonly StaffAttendanceService $attendance,
    ) {}

    /**
     * @return array{
     *   staff_id: int,
     *   month: string,
     *   month_label: string,
     *   base_salary: float,
     *   base_payable: float,
     *   commission_rate: float,
     *   commission: float,
     *   revenue: float,
     *   appointments: int,
     *   scheduled_days: int,
     *   worked_days: float,
     *   present_days: int,
     *   late_days: int,
     *   half_days: int,
     *   absent_days: int,
     *   leave_days: int,
     *   gross: float,
     *   tax_rate: float,
     *   tax: float,
     *   net: float,
     *   suggested_title: string
     * }
     */
    public function forStaff(Salon $salon, Staff $staff, ?Carbon $month = null, float $taxRate = 0.10): array
    {
        $month = ($month ?? now())->copy()->startOfMonth();
        $from = $month->copy()->startOfMonth()->startOfDay();
        $to = $month->copy()->endOfMonth()->endOfDay();
        $today = now()->startOfDay();
        $rangeEnd = $to->lt($today) ? $to->copy() : $today->copy();

        $revenue = (float) Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->where('status', 'completed')
            ->whereBetween('starts_at', [$from, $to])
            ->sum('total_price');

        $appointments = (int) Appointment::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->whereBetween('starts_at', [$from, $to])
            ->count();

        $attendanceCounts = $this->attendanceCounts($salon, $staff, $from, $rangeEnd);
        $scheduledDays = $attendanceCounts['scheduled_days'];
        $workedDays = $attendanceCounts['worked_days'];

        $baseSalary = (float) ($staff->base_salary ?? 0);
        $basePayable = $baseSalary;
        if ($baseSalary > 0 && $scheduledDays > 0) {
            $basePayable = round($baseSalary * ($workedDays / $scheduledDays), 2);
        }

        $commPct = (float) ($staff->commission_rate ?? 0);
        $commission = round($revenue * $commPct / 100, 2);
        $gross = round($basePayable + $commission, 2);
        $tax = round($gross * $taxRate, 2);
        $net = round($gross - $tax, 2);

        $monthLabel = $month->format('F Y');

        return [
            'staff_id' => (int) $staff->id,
            'month' => $month->format('Y-m'),
            'month_label' => $monthLabel,
            'base_salary' => $baseSalary,
            'base_payable' => $basePayable,
            'commission_rate' => $commPct,
            'commission' => $commission,
            'revenue' => round($revenue, 2),
            'appointments' => $appointments,
            'scheduled_days' => $scheduledDays,
            'worked_days' => $workedDays,
            'present_days' => $attendanceCounts['present_days'],
            'late_days' => $attendanceCounts['late_days'],
            'half_days' => $attendanceCounts['half_days'],
            'absent_days' => $attendanceCounts['absent_days'],
            'leave_days' => $attendanceCounts['leave_days'],
            'gross' => $gross,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'net' => $net,
            'suggested_title' => 'Salary — '.$staff->name.' — '.$monthLabel,
            'suggested_amount' => $net > 0 ? $net : $gross,
        ];
    }

    /**
     * @param  Collection<int, Staff>  $staff
     * @return array<int, array<string, mixed>>
     */
    public function forStaffCollection(Salon $salon, Collection $staff, ?Carbon $month = null, float $taxRate = 0.10): array
    {
        $out = [];
        foreach ($staff as $member) {
            $row = $this->forStaff($salon, $member, $month, $taxRate);
            $out[(int) $member->id] = $row;
        }

        return $out;
    }

    /**
     * @return array{
     *   scheduled_days: int,
     *   worked_days: float,
     *   present_days: int,
     *   late_days: int,
     *   half_days: int,
     *   absent_days: int,
     *   leave_days: int
     * }
     */
    private function attendanceCounts(Salon $salon, Staff $staff, Carbon $from, Carbon $to): array
    {
        $records = StaffAttendanceRecord::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('staff_id', $staff->id)
            ->whereBetween('attendance_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn (StaffAttendanceRecord $r) => $r->attendance_date->toDateString());

        $scheduled = 0;
        $worked = 0.0;
        $present = 0;
        $late = 0;
        $half = 0;
        $absent = 0;
        $leave = 0;

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $ymd = $d->toDateString();
            $dow = $d->format('D');
            if (! $this->attendance->isScheduledWorkingDay($staff, $dow)) {
                continue;
            }
            $scheduled++;

            $status = $records->get($ymd)?->status;
            if ($status === null) {
                // No mark yet — count as full day only for past scheduled days with no record
                // (avoids zeroing pay when attendance unused). Treat as present for pay.
                if ($d->lt(now()->startOfDay())) {
                    $worked += 1.0;
                    $present++;
                }
                continue;
            }

            switch ($status) {
                case StaffAttendanceRecord::STATUS_PRESENT:
                    $worked += 1.0;
                    $present++;
                    break;
                case StaffAttendanceRecord::STATUS_LATE:
                    $worked += 1.0;
                    $late++;
                    break;
                case StaffAttendanceRecord::STATUS_HALF_DAY:
                    $worked += 0.5;
                    $half++;
                    break;
                case StaffAttendanceRecord::STATUS_ABSENT:
                    $absent++;
                    break;
                case StaffAttendanceRecord::STATUS_ON_LEAVE:
                    $leave++;
                    break;
            }
        }

        return [
            'scheduled_days' => $scheduled,
            'worked_days' => round($worked, 1),
            'present_days' => $present,
            'late_days' => $late,
            'half_days' => $half,
            'absent_days' => $absent,
            'leave_days' => $leave,
        ];
    }
}
