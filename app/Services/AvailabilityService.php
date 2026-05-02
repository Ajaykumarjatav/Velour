<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\StaffLeaveRequest;
use App\Services\Scheduling\ScheduleValidationResult;
use App\Support\SalonTime;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Single entry point for "can this appointment exist here?" — used by
 * manual booking, reschedule, and online booking flows.
 */
class AvailabilityService
{
    /**
     * @param  bool  $requireBookableOnline            When true, staff must be bookable online (public booking).
     * @param  bool  $enforceEndsWithinLocalCalendarDay When false (slot-grid probe only), skip the "must end by next local midnight"
     *                                                 check so long *assumed* windows do not grey out the whole grid; salon hours,
     *                                                 staff shift, and overlap still apply.
     */
    public function validateProposedWindow(
        Salon $salon,
        Staff $staff,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeAppointmentId = null,
        bool $requireBookableOnline = false,
        bool $enforceEndsWithinLocalCalendarDay = true,
    ): ScheduleValidationResult {
        $reasons = [];

        if ((int) $staff->salon_id !== (int) $salon->id) {
            $reasons[] = ['code' => 'staff_wrong_salon', 'message' => 'Selected staff does not belong to this salon.'];
        }

        if (! $staff->is_active) {
            $reasons[] = ['code' => 'staff_inactive', 'message' => 'This staff member is inactive.'];
        }

        if ($requireBookableOnline && ! $staff->bookable_online) {
            $reasons[] = ['code' => 'staff_not_bookable_online', 'message' => 'This staff member is not available for online booking.'];
        }

        if ($reasons !== []) {
            return ScheduleValidationResult::failure($reasons);
        }

        $tz = SalonTime::timezone($salon);
        $localStart = $startsAt->copy()->timezone($tz);
        $localEnd   = $endsAt->copy()->timezone($tz);

        if ($enforceEndsWithinLocalCalendarDay) {
            // Allow an end time exactly at the next local midnight (e.g. 09:00 + 15h). Reject
            // only if the window extends strictly past the start of the next calendar day.
            $exclusiveEndOfLocalStartDay = $localStart->copy()->startOfDay()->addDay();
            if ($localEnd->gt($exclusiveEndOfLocalStartDay)) {
                $reasons[] = [
                    'code' => 'spans_multiple_calendar_days',
                    'message' => 'This booking would cross midnight in the business timezone. Split services or pick a shorter window.',
                ];

                return ScheduleValidationResult::failure($reasons);
            }
        }

        $this->pushLeaveReasons($salon->id, $staff->id, $startsAt, $endsAt, $reasons);
        if ($reasons !== []) {
            return ScheduleValidationResult::failure($reasons);
        }

        $this->pushSalonHoursReasons($salon, $localStart, $localEnd, $reasons);
        if ($reasons !== []) {
            return ScheduleValidationResult::failure($reasons);
        }

        $this->pushStaffScheduleReasons($staff, $localStart, $localEnd, $reasons);
        if ($reasons !== []) {
            return ScheduleValidationResult::failure($reasons);
        }

        $this->pushOverlapReasons($salon->id, $staff->id, $startsAt, $endsAt, $excludeAppointmentId, $reasons);
        if ($reasons !== []) {
            return ScheduleValidationResult::failure($reasons);
        }

        return ScheduleValidationResult::success();
    }

    /**
     * @param  list<array{code: string, message: string}>  $reasons
     */
    private function pushLeaveReasons(int $salonId, int $staffId, Carbon $startsAt, Carbon $endsAt, array &$reasons): void
    {
        $cursor = $startsAt->copy()->startOfDay();
        $endDay = $endsAt->copy()->startOfDay();
        if ($endDay->lt($cursor)) {
            $endDay = $cursor;
        }

        while ($cursor->lte($endDay)) {
            if (StaffLeaveRequest::approvedBlockingLeaveExists($salonId, $staffId, $cursor->toDateString())) {
                $reasons[] = [
                    'code' => 'staff_on_leave',
                    'message' => 'This staff member has approved leave on that date. Choose another day or assign a different staff member.',
                ];

                return;
            }
            $cursor->addDay();
        }
    }

    /**
     * @param  list<array{code: string, message: string}>  $reasons
     */
    private function pushSalonHoursReasons(Salon $salon, Carbon $localStart, Carbon $localEnd, array &$reasons): void
    {
        $dayKey = strtolower($localStart->copy()->locale('en')->format('l'));
        $day    = $salon->openingHoursForWeekdayKey($dayKey);

        if (! $day || empty($day['open'])) {
            $reasons[] = [
                'code' => 'salon_closed',
                'message' => 'The salon is closed on that day.',
            ];

            return;
        }

        $from = $day['from'] ?? $day['start'] ?? '09:00';
        $to   = $day['to'] ?? $day['end'] ?? '18:00';

        try {
            $open  = Carbon::createFromFormat('Y-m-d H:i', $localStart->format('Y-m-d') . ' ' . substr((string) $from, 0, 5), $localStart->getTimezone()->getName());
            $close = Carbon::createFromFormat('Y-m-d H:i', $localStart->format('Y-m-d') . ' ' . substr((string) $to, 0, 5), $localStart->getTimezone()->getName());
        } catch (\Throwable $e) {
            Log::warning('Invalid salon opening hours', ['salon_id' => $salon->id, 'error' => $e->getMessage()]);
            $reasons[] = [
                'code' => 'salon_hours_misconfigured',
                'message' => 'Opening hours could not be read. Check Settings → Hours.',
            ];

            return;
        }

        if ($open->gte($close)) {
            $reasons[] = [
                'code' => 'salon_hours_invalid',
                'message' => 'Opening hours for that day are invalid (close before open).',
            ];

            return;
        }

        if ($localStart->lt($open) || $localStart->gt($close)) {
            $reasons[] = [
                'code' => 'outside_salon_hours',
                'message' => 'That time is outside salon opening hours (' . $open->format('H:i') . '–' . $close->format('H:i') . ').',
            ];

            return;
        }

        // If the window crosses local midnight, $localEnd is on the next calendar day while $close
        // is still "today" — comparing them with > wrongly marks every slot as outside hours.
        if (! $localStart->isSameDay($localEnd)) {
            $reasons[] = [
                'code' => 'outside_salon_hours',
                'message' => 'That booking would run past closing time or into the next day. Choose an earlier slot or shorter services.',
            ];

            return;
        }

        if ($localEnd->gt($close)) {
            $reasons[] = [
                'code' => 'outside_salon_hours',
                'message' => 'That time is outside salon opening hours (' . $open->format('H:i') . '–' . $close->format('H:i') . ').',
            ];
        }
    }

    /**
     * @param  list<array{code: string, message: string}>  $reasons
     */
    private function pushStaffScheduleReasons(Staff $staff, Carbon $localStart, Carbon $localEnd, array &$reasons): void
    {
        $dow = $localStart->format('D'); // Mon, Tue, …
        $wd  = $staff->working_days;
        if (is_array($wd) && $wd !== [] && ! in_array($dow, $wd, true)) {
            $reasons[] = [
                'code' => 'staff_not_scheduled_day',
                'message' => 'This staff member does not work on that day of the week.',
            ];

            return;
        }

        $staffStart = substr((string) ($staff->start_time ?? '09:00'), 0, 5);
        $staffEnd   = substr((string) ($staff->end_time ?? '18:00'), 0, 5);

        try {
            $s = Carbon::createFromFormat('Y-m-d H:i', $localStart->format('Y-m-d') . ' ' . $staffStart, $localStart->getTimezone()->getName());
            $e = Carbon::createFromFormat('Y-m-d H:i', $localStart->format('Y-m-d') . ' ' . $staffEnd, $localStart->getTimezone()->getName());
        } catch (\Throwable) {
            $reasons[] = [
                'code' => 'staff_hours_invalid',
                'message' => 'Staff shift times are invalid.',
            ];

            return;
        }

        if ($localStart->lt($s) || $localStart->gt($e)) {
            $reasons[] = [
                'code' => 'outside_staff_shift',
                'message' => 'That time is outside this staff member\'s shift (' . $s->format('H:i') . '–' . $e->format('H:i') . ').',
            ];

            return;
        }

        if (! $localStart->isSameDay($localEnd)) {
            $reasons[] = [
                'code' => 'outside_staff_shift',
                'message' => 'That booking would extend past this staff member\'s shift or into the next day. Pick an earlier slot or shorter services.',
            ];

            return;
        }

        if ($localEnd->gt($e)) {
            $reasons[] = [
                'code' => 'outside_staff_shift',
                'message' => 'That time is outside this staff member\'s shift (' . $s->format('H:i') . '–' . $e->format('H:i') . ').',
            ];
        }
    }

    /**
     * @param  list<array{code: string, message: string}>  $reasons
     */
    private function pushOverlapReasons(
        int $salonId,
        int $staffId,
        Carbon $startsAt,
        Carbon $endsAt,
        ?int $excludeAppointmentId,
        array &$reasons,
    ): void {
        // Compare using UTC instants — matches how appointments are persisted and avoids
        // timezone / session mismatches that can miss overlaps.
        $startUtc = $startsAt->copy()->utc();
        $endUtc   = $endsAt->copy()->utc();

        $query = Appointment::where('salon_id', $salonId)
            ->where('staff_id', $staffId)
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->where('starts_at', '<', $endUtc)
            ->where('ends_at', '>', $startUtc);

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        $other = $query->first(['id', 'reference', 'starts_at']);
        if ($other) {
            $ref = $other->reference ?: '#' . $other->id;
            $reasons[] = [
                'code' => 'appointment_overlap',
                'message' => 'Overlaps an existing booking (' . $ref . ' at ' . $other->starts_at->format('H:i') . ').',
            ];
        }
    }
}
