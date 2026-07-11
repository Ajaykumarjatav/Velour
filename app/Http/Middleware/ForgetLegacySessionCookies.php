<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

/**
 * Expire legacy session cookies left over from earlier local installs.
 */
class ForgetLegacySessionCookies
{
    /** @var list<string> */
    private const LEGACY_COOKIES = [
        'easygrox_session',
    ];

    /** @var list<string> */
    private const PATHS = [
        '/vellor/admin',
        '/vellor',
        '/',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        foreach (self::LEGACY_COOKIES as $name) {
            foreach (self::PATHS as $path) {
                $response->headers->setCookie(new Cookie(
                    $name,
                    null,
                    1,
                    $path,
                    config('session.domain'),
                    config('session.secure'),
                    true,
                    false,
                    config('session.same_site')
                ));
            }
        }

        return $response;
    }
}
