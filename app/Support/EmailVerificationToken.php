<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Cache-backed email verification tokens (avoids signed-URL / APP_URL / proxy 403s).
 */
final class EmailVerificationToken
{
    public static function issue(User $user, int $minutes = 60): string
    {
        $token = Str::random(64);
        Cache::put(self::cacheKey($user->id), hash('sha256', $token), now()->addMinutes($minutes));

        return $token;
    }

    public static function assertValid(User $user, string $token): bool
    {
        $token = trim($token);
        if ($token === '') {
            return false;
        }

        $stored = Cache::get(self::cacheKey($user->id));
        if (! is_string($stored) || $stored === '') {
            return false;
        }

        return hash_equals($stored, hash('sha256', $token));
    }

    public static function forget(User $user): void
    {
        Cache::forget(self::cacheKey($user->id));
    }

    public static function url(User $user, string $token): string
    {
        $path = route('verification.verify', [
            'id' => $user->getKey(),
            'hash' => sha1($user->getEmailForVerification()),
        ], absolute: false);

        $query = http_build_query(['token' => $token]);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path.(str_contains($path, '?') ? '&' : '?').$query;
        }

        $root = rtrim((string) config('app.url'), '/');
        $urlPath = '/'.ltrim($path, '/');
        $rootPath = rtrim((string) (parse_url($root, PHP_URL_PATH) ?: ''), '/');

        if ($rootPath !== '' && ($urlPath === $rootPath || str_starts_with($urlPath, $rootPath.'/'))) {
            return $root.substr($urlPath, strlen($rootPath)).'?'.$query;
        }

        return $root.$urlPath.'?'.$query;
    }

    private static function cacheKey(int|string $userId): string
    {
        return 'email_verification_token:'.$userId;
    }
}
