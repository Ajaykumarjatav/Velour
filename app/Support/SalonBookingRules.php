<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\SalonBufferRule;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Live booking policy from Settings → Buffer time & booking rules.
 * Max daily bookings per staff is intentionally not enforced.
 */
class SalonBookingRules
{
    public function __construct(
        public readonly Salon $salon,
        public readonly SalonBufferRule $rule,
    ) {}

    public static function forSalon(Salon|int $salon): self
    {
        $model = $salon instanceof Salon
            ? $salon
            : Salon::query()->findOrFail($salon);

        $rule = SalonBufferRule::withoutGlobalScopes()->firstOrCreate(
            ['salon_id' => $model->id],
            SalonBufferRule::defaultsForNewSalon()
        );

        return new self($model, $rule);
    }

    public function bufferBeforeMinutes(): int
    {
        return max(0, (int) $this->rule->buffer_before_minutes);
    }

    public function bufferAfterMinutes(): int
    {
        return max(0, (int) $this->rule->buffer_after_minutes);
    }

    public function salonPaddingMinutes(): int
    {
        return $this->bufferBeforeMinutes() + $this->bufferAfterMinutes();
    }

    /**
     * Service span (duration + per-service buffers) + salon before/after once per appointment.
     */
    public function appointmentSpanMinutes(int $serviceSpanMinutes): int
    {
        return max(0, $serviceSpanMinutes) + $this->salonPaddingMinutes();
    }

    public function advanceBookingDays(): int
    {
        $fromRule = (int) $this->rule->advance_booking_days;
        if ($fromRule >= 1) {
            return $fromRule;
        }

        return max(1, (int) ($this->salon->booking_advance_days ?? 60));
    }

    public function lastMinuteCutoffHours(): int
    {
        return max(0, (int) $this->rule->last_minute_cutoff_hours);
    }

    public function overbookingPercent(): int
    {
        return max(0, min(100, (int) $this->rule->overbooking_percent));
    }

    /**
     * Earliest allowed appointment start in salon timezone (now + last-minute cut-off).
     */
    public function earliestBookableAt(): CarbonInterface
    {
        return SalonTime::now($this->salon)->copy()->addHours($this->lastMinuteCutoffHours());
    }

    public function latestBookableDate(): CarbonInterface
    {
        $tz = SalonTime::timezone($this->salon);
        $todayYmd = SalonTime::todayDateString($this->salon);

        return Carbon::createFromFormat('Y-m-d', $todayYmd, $tz)->startOfDay()->addDays($this->advanceBookingDays());
    }

    /**
     * @return list<array{code: string, message: string}>
     */
    public function windowPolicyReasons(CarbonInterface $startsAt, ?CarbonInterface $endsAt = null): array
    {
        $reasons = [];
        $tz = SalonTime::timezone($this->salon);
        $localStart = $startsAt->copy()->timezone($tz);
        $earliest = $this->earliestBookableAt();
        $todayYmd = SalonTime::todayDateString($this->salon);
        $today = Carbon::createFromFormat('Y-m-d', $todayYmd, $tz)->startOfDay();
        $latest = $this->latestBookableDate();

        if ($localStart->lt($today)) {
            $reasons[] = [
                'code' => 'date_in_past',
                'message' => 'Please select today or a future date.',
            ];
        }

        if ($localStart->copy()->startOfDay()->gt($latest)) {
            $days = $this->advanceBookingDays();
            $reasons[] = [
                'code' => 'outside_advance_window',
                'message' => "Bookings can only be made up to {$days} days in advance.",
            ];
        }

        if ($localStart->lt($earliest)) {
            $hours = $this->lastMinuteCutoffHours();
            $reasons[] = [
                'code' => 'last_minute_cutoff',
                'message' => $hours > 0
                    ? "Bookings require at least {$hours} hours' notice before the start time."
                    : 'Please choose a time later than the current time.',
            ];
        }

        return $reasons;
    }

    public function assertStartsAtAllowed(CarbonInterface $startsAt): void
    {
        $reasons = $this->windowPolicyReasons($startsAt);
        if ($reasons !== []) {
            throw new \InvalidArgumentException($reasons[0]['message']);
        }
    }

    /**
     * Overbooking % > 0 is reserved; 0 keeps hard no-overlap (current engine).
     */
    public function allowsSoftOverbooking(): bool
    {
        return $this->overbookingPercent() > 0;
    }
}
