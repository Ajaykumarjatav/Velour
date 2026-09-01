<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cookie;

/**
 * Signed device cookie for "Stay signed in on this device" with 2FA enabled.
 * When present and valid, the user skips the 2FA challenge on return visits.
 */
final class TrustedDevice
{
    public const COOKIE = 'velour_trusted_device';

    private const TTL_DAYS = 30;

    public static function issue(User $user): void
    {
        $expires = now()->addDays(self::TTL_DAYS)->getTimestamp();
        $payload = self::payload($user, (int) $expires);
        $signature = self::sign($payload);

        Cookie::queue(self::cookie($payload.'|'.$signature, self::TTL_DAYS * 24 * 60));
    }

    public static function matches(User $user, ?string $value): bool
    {
        if (! is_string($value) || $value === '') {
            return false;
        }

        $parts = explode('|', $value, 4);
        if (count($parts) !== 4) {
            return false;
        }

        [$id, $expires, $passwordMarker, $signature] = $parts;

        if ((string) $user->getAuthIdentifier() !== (string) $id) {
            return false;
        }

        if (! is_numeric($expires) || (int) $expires < now()->getTimestamp()) {
            return false;
        }

        if ($passwordMarker !== self::passwordMarker($user)) {
            return false;
        }

        $payload = self::payload($user, (int) $expires);

        return hash_equals(self::sign($payload), $signature);
    }

    public static function forget(): void
    {
        Cookie::queue(Cookie::forget(
            self::COOKIE,
            config('session.path', '/'),
            config('session.domain')
        ));
    }

    private static function payload(User $user, int $expires): string
    {
        return $user->getAuthIdentifier().'|'.$expires.'|'.self::passwordMarker($user);
    }

    private static function passwordMarker(User $user): string
    {
        $hash = (string) $user->getAuthPassword();

        return $hash === '' ? '' : substr($hash, -12);
    }

    private static function sign(string $payload): string
    {
        return hash_hmac('sha256', $payload, (string) config('app.key'));
    }

    private static function cookie(string $value, int $minutes): \Symfony\Component\HttpFoundation\Cookie
    {
        return cookie(
            self::COOKIE,
            $value,
            $minutes,
            config('session.path', '/'),
            config('session.domain'),
            config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        );
    }
}
