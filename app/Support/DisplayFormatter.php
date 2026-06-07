<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Single place for salon-scoped money and date/time display in the tenant UI.
 */
final class DisplayFormatter
{
    public static function applyUserLocale(?User $user): void
    {
        if (! $user || ! $user->locale) {
            return;
        }

        try {
            $loc = str_replace('_', '-', $user->locale);
            Carbon::setLocale($loc);
        } catch (\Throwable) {
            // ignore invalid locale
        }
    }

    /** Options for profile "Display language" (Carbon month/day names). */
    public static function localeOptions(): array
    {
        return [
            'en'    => 'English',
            'en_GB' => 'English (UK)',
            'en_US' => 'English (US)',
            'fr'    => 'Français',
            'de'    => 'Deutsch',
            'es'    => 'Español',
            'hi'    => 'हिन्दी',
            'pt'    => 'Português',
            'it'    => 'Italiano',
            'nl'    => 'Nederlands',
            'pl'    => 'Polski',
        ];
    }

    public static function money(Salon $salon, float $amount): string
    {
        $code = $salon->currency ?? 'GBP';

        return \App\Helpers\CurrencyHelper::format($amount, $code);
    }

    /**
     * Operational appointment / calendar times: business timezone.
     */
    public static function businessDateTime(?Salon $salon, Carbon|string|null $at): string
    {
        if ($at === null || $at === '') {
            return '—';
        }

        $c = $at instanceof CarbonInterface ? $at->copy() : Carbon::parse($at);
        $tz = $salon ? SalonTime::timezone($salon) : (string) config('app.timezone', 'UTC');
        $local = $c->timezone($tz);

        return $local->format('D, j M Y, H:i') . ' ' . $local->format('T');
    }

    public static function businessDate(?Salon $salon, Carbon|string|null $at): string
    {
        if ($at === null || $at === '') {
            return '—';
        }

        $c = $at instanceof CarbonInterface ? $at->copy() : Carbon::parse($at);
        $tz = $salon ? SalonTime::timezone($salon) : (string) config('app.timezone', 'UTC');

        return $c->timezone($tz)->format('j M Y');
    }

    public static function businessTimeRange(?Salon $salon, Carbon|string|null $start, Carbon|string|null $end): string
    {
        if ($start === null || $start === '' || $end === null || $end === '') {
            return '—';
        }

        $a = self::businessClock($salon, $start);
        $b = self::businessClock($salon, $end);
        $abbr = $salon ? SalonTime::abbrev($salon) : 'UTC';

        return "{$a} – {$b} ({$abbr})";
    }

    /** Compact day + month (e.g. dashboard widgets). */
    public static function businessShortDate(?Salon $salon, Carbon|string|null $at): string
    {
        if ($at === null || $at === '') {
            return '—';
        }

        $c = $at instanceof CarbonInterface ? $at->copy() : Carbon::parse($at);
        $tz = $salon ? SalonTime::timezone($salon) : (string) config('app.timezone', 'UTC');

        return $c->timezone($tz)->format('j M');
    }

    public static function businessTime(?Salon $salon, Carbon|string|null $at): string
    {
        if ($at === null || $at === '') {
            return '—';
        }

        $c = $at instanceof CarbonInterface ? $at->copy() : Carbon::parse($at);
        $tz = $salon ? SalonTime::timezone($salon) : (string) config('app.timezone', 'UTC');
        $local = $c->timezone($tz);

        return $local->format('H:i') . ' ' . $local->format('T');
    }

    /** Time only (HH:mm) in business timezone — for compact lists. */
    public static function businessClock(?Salon $salon, Carbon|string|null $at): string
    {
        if ($at === null || $at === '') {
            return '—';
        }

        $c = $at instanceof CarbonInterface ? $at->copy() : Carbon::parse($at);
        $tz = $salon ? SalonTime::timezone($salon) : (string) config('app.timezone', 'UTC');

        return $c->timezone($tz)->format('H:i');
    }

    /**
     * User-facing timestamps (e.g. “last login”): viewer timezone when set, else business, else app.
     */
    public static function userDateTime(?User $user, ?Salon $salon, Carbon|string|null $at): string
    {
        if ($at === null || $at === '') {
            return '—';
        }

        $c = $at instanceof CarbonInterface ? $at->copy() : Carbon::parse($at);
        $tz = $user && $user->timezone
            ? $user->timezone
            : ($salon ? SalonTime::timezone($salon) : (string) config('app.timezone', 'UTC'));
        $local = $c->timezone($tz);

        return $local->format('j M Y, H:i') . ' ' . $local->format('T');
    }
}
