<?php

namespace App\Support;

use App\Models\Salon;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon as SupportCarbon;

/**
 * Date/time helpers so "today", reports, and calendar days follow the salon's timezone.
 */
final class SalonTime
{
    public static function timezone(Salon $salon): string
    {
        return $salon->timezone ?: (string) config('app.timezone', 'UTC');
    }

    public static function now(Salon $salon): CarbonInterface
    {
        return Carbon::now(self::timezone($salon));
    }

    public static function todayDateString(Salon $salon): string
    {
        return self::now($salon)->toDateString();
    }

    public static function monthStartDateString(Salon $salon): string
    {
        return self::now($salon)->copy()->startOfMonth()->toDateString();
    }

    /**
     * @return array{0: Carbon, 1: Carbon} UTC instants for [start, end] inclusive of that local calendar day.
     */
    public static function dayRangeUtcFromYmd(Salon $salon, string $ymd): array
    {
        $tz = self::timezone($salon);
        $localStart = Carbon::createFromFormat('Y-m-d', $ymd, $tz)->startOfDay();

        return [$localStart->copy()->utc(), $localStart->copy()->endOfDay()->utc()];
    }

    /**
     * @return array{0: Carbon, 1: Carbon} UTC range for the salon-local month containing $ymd.
     */
    public static function monthRangeUtcContaining(Salon $salon, string $ymd): array
    {
        $tz = self::timezone($salon);
        $local = Carbon::createFromFormat('Y-m-d', $ymd, $tz)->startOfMonth();
        $start = $local->copy()->startOfDay();
        $end = $local->copy()->endOfMonth()->endOfDay();

        return [$start->utc(), $end->utc()];
    }

    /**
     * Parse a calendar date from the URL as start-of-day in the salon timezone.
     */
    public static function parseLocalDate(Salon $salon, string $ymd): Carbon
    {
        $tz = self::timezone($salon);

        return Carbon::createFromFormat('Y-m-d', $ymd, $tz)->startOfDay();
    }

    /**
     * Parse a booking datetime from forms/API. Naive "Y-m-d H:i:s" strings are interpreted as
     * salon-local wall time (matching slot generation and public booking). Strings that already
     * include a timezone (ISO-8601 with T, Z, or ±offset) are parsed as absolute instants.
     *
     * Returns {@see SupportCarbon} so values match Laravel type hints (`Illuminate\Support\Carbon`).
     */
    public static function parseAppointmentStartsAt(Salon $salon, string $value): SupportCarbon
    {
        $v = trim($value);
        if ($v === '') {
            throw new \InvalidArgumentException('Empty appointment start time.');
        }

        if (self::isAbsoluteIsoDatetime($v)) {
            return SupportCarbon::parse($v);
        }

        $tz = self::timezone($salon);

        return SupportCarbon::parse($v, $tz);
    }

    private static function isAbsoluteIsoDatetime(string $v): bool
    {
        if (preg_match('/^\d{4}-\d{2}-\d{2}T/', $v)) {
            return true;
        }
        if (preg_match('/Z$/i', $v)) {
            return true;
        }
        // e.g. ...+03:00 or ...-04:00 at end
        if (preg_match('/[+\-]\d{2}:\d{2}$/', $v)) {
            return true;
        }

        return false;
    }

    public static function abbrev(Salon $salon): string
    {
        try {
            return Carbon::now(self::timezone($salon))->format('T');
        } catch (\Throwable) {
            return self::timezone($salon);
        }
    }
}
