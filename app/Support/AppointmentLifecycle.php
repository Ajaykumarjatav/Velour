<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Salon;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Detects appointments whose slot has passed without a terminal status update.
 */
final class AppointmentLifecycle
{
    public const DISPLAY_MISSED = 'missed';

    /** @return list<string> */
    public static function unresolvedStatuses(): array
    {
        return ['pending', 'confirmed', 'checked_in', 'in_progress', 'hold'];
    }

    /** @return list<string> */
    public static function terminalStatuses(): array
    {
        return ['completed', 'cancelled', 'no_show'];
    }

    public static function slotEndsAt(Appointment $appointment): Carbon
    {
        if ($appointment->ends_at) {
            return $appointment->ends_at->copy();
        }

        $mins = max(1, (int) $appointment->duration_minutes);

        return $appointment->starts_at->copy()->addMinutes($mins);
    }

    public static function isPastUnresolved(Appointment $appointment, ?Salon $salon = null): bool
    {
        if (in_array($appointment->status, self::terminalStatuses(), true)) {
            return false;
        }

        if (! in_array($appointment->status, self::unresolvedStatuses(), true)) {
            return false;
        }

        $salon = $salon ?? $appointment->salon;
        $compare = $salon ? SalonTime::now($salon)->utc() : now()->utc();

        return self::slotEndsAt($appointment)->lt($compare);
    }

    public static function displayStatusKey(Appointment $appointment, ?Salon $salon = null): string
    {
        if (self::isPastUnresolved($appointment, $salon)) {
            return self::DISPLAY_MISSED;
        }

        return (string) $appointment->status;
    }

    public static function displayStatusLabel(Appointment $appointment, ?Salon $salon = null): string
    {
        if (self::isPastUnresolved($appointment, $salon)) {
            return 'Missed';
        }

        return ucfirst(str_replace('_', ' ', (string) $appointment->status));
    }

    public static function scopeMissedUnresolved(Builder $query, Salon $salon): Builder
    {
        $now = SalonTime::now($salon)->utc()->toDateTimeString();

        return $query
            ->whereIn('status', self::unresolvedStatuses())
            ->where(function (Builder $q) use ($now) {
                $q->where('ends_at', '<', $now)
                    ->orWhere(function (Builder $inner) use ($now) {
                        $inner->whereNull('ends_at')
                            ->whereRaw(
                                'DATE_ADD(starts_at, INTERVAL COALESCE(duration_minutes, 30) MINUTE) < ?',
                                [$now]
                            );
                    });
            });
    }
}
