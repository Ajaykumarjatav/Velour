<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public storefront / booking surfaces must not mint a new session cookie.
 *
 * Admin sessions use Path=/vellor/admin. Visiting /vellor/s/{slug} (or Vite
 * proxied /api) does not send that cookie, so StartSession would create a
 * guest session and Set-Cookie would overwrite the logged-in admin session —
 * causing "Unauthenticated." on the next admin AJAX call (e.g. theme save).
 *
 * When no session cookie is present on these public routes, use the array
 * driver so nothing is written back to the browser.
 */
class PreventPublicSessionClobber
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->isPublicSurface($request) && ! $this->hasSessionCookie($request)) {
            config(['session.driver' => 'array']);
        }

        return $next($request);
    }

    private function hasSessionCookie(Request $request): bool
    {
        $name = (string) config('session.cookie');

        return $name !== '' && $request->cookies->has($name);
    }

    private function isPublicSurface(Request $request): bool
    {
        $path = trim($request->path(), '/');

        $prefixes = [
            's/',
            'book/',
            'website/',
            'widget/',
            'reviews/share/',
            'api/v1/book/',
            'api/v1/client/',
            'api/v1/track/',
        ];

        if ($path === 's' || $path === 'book') {
            return true;
        }

        // /{store}/reviews/share/{token}
        if (preg_match('#^[a-z0-9]+(?:-[a-z0-9]+)*/reviews/share/#', $path)) {
            return true;
        }

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
