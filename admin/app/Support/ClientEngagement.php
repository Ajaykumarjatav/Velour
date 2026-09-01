<?php

namespace App\Support;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

/**
 * Client engagement (Active / Inactive) based on visit recency.
 *
 * Active  = visited within the window, OR has an upcoming non-cancelled appointment.
 * Inactive = no visit in the window and no upcoming appointment.
 */
final class ClientEngagement
{
    public const ACTIVE_WINDOW_DAYS = 90;

    public static function activeWindowDays(): int
    {
        return self::ACTIVE_WINDOW_DAYS;
    }

    public static function cutoff(?Carbon $asOf = null): Carbon
    {
        return ($asOf ?? now())->copy()->subDays(self::activeWindowDays());
    }

    public static function isActive(Client $client, ?Carbon $asOf = null): bool
    {
        if ($client->last_visit_at !== null && $client->last_visit_at->gte(self::cutoff($asOf))) {
            return true;
        }

        return $client->appointments()
            ->where('starts_at', '>=', ($asOf ?? now()))
            ->whereNotIn('status', ['cancelled', 'no_show'])
            ->exists();
    }

    public static function label(Client $client, ?Carbon $asOf = null): string
    {
        return self::isActive($client, $asOf) ? 'Active' : 'Inactive';
    }

    public static function hint(Client $client, ?Carbon $asOf = null): string
    {
        $days = self::activeWindowDays();

        if ($client->last_visit_at !== null && $client->last_visit_at->gte(self::cutoff($asOf))) {
            return 'Visited within the last '.$days.' days';
        }

        if (self::isActive($client, $asOf)) {
            return 'Has an upcoming appointment';
        }

        if ($client->last_visit_at === null) {
            return 'No completed visit yet (outside '.$days.'-day window)';
        }

        return 'Last visit more than '.$days.' days ago';
    }

    public static function scopeActive(Builder $query, ?Carbon $asOf = null): Builder
    {
        $cutoff = self::cutoff($asOf);
        $asOf = $asOf ?? now();

        return $query->where(function (Builder $q) use ($cutoff, $asOf) {
            $q->where(function (Builder $inner) use ($cutoff) {
                $inner->whereNotNull('last_visit_at')
                    ->where('last_visit_at', '>=', $cutoff);
            })->orWhereHas('appointments', function (Builder $apt) use ($asOf) {
                $apt->where('starts_at', '>=', $asOf)
                    ->whereNotIn('status', ['cancelled', 'no_show']);
            });
        });
    }

    public static function scopeInactive(Builder $query, ?Carbon $asOf = null): Builder
    {
        $cutoff = self::cutoff($asOf);
        $asOf = $asOf ?? now();

        return $query->where(function (Builder $q) use ($cutoff, $asOf) {
            $q->where(function (Builder $inner) use ($cutoff) {
                $inner->whereNull('last_visit_at')
                    ->orWhere('last_visit_at', '<', $cutoff);
            })->whereDoesntHave('appointments', function (Builder $apt) use ($asOf) {
                $apt->where('starts_at', '>=', $asOf)
                    ->whereNotIn('status', ['cancelled', 'no_show']);
            });
        });
    }
}
