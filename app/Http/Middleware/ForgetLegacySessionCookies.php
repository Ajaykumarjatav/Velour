<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Expire legacy session cookies left over from earlier installs / cookie renames.
 *
 * Never expire the active SESSION_COOKIE name — that caused 419 CSRF errors when
 * production still used easygrox_session (the same name listed as "legacy").
 */
class ForgetLegacySessionCookies
{
    /** @var list<string> */
    private const LEGACY_COOKIES = [
        'easygrox_session',
        'easygrox_vellor_local_session',
        'laravel_session',
        'velour_session',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $current = (string) config('session.cookie');
        $paths = $this->paths();

        foreach (self::LEGACY_COOKIES as $name) {
            if ($name === '' || ($current !== '' && hash_equals($current, $name))) {
                continue;
            }

            foreach ($paths as $path) {
                $response->headers->setCookie(new Cookie(
                    $name,
                    null,
                    1,
                    $path,
                    config('session.domain'),
                    (bool) config('session.secure'),
                    true,
                    false,
                    config('session.same_site') ?: 'lax'
                ));
            }
        }

        return $response;
    }

    /**
     * @return list<string>
     */
    private function paths(): array
    {
        $sessionPath = rtrim((string) config('session.path', '/'), '/');
        if ($sessionPath === '') {
            $sessionPath = '/';
        }

        return array_values(array_unique(array_filter([
            '/vellor/admin',
            '/vellor',
            '/admin',
            '/',
            $sessionPath,
        ])));
    }
}
