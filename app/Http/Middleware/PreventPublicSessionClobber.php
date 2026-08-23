<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public storefront / booking surfaces must not mint or overwrite panel cookies.
 *
 * Admin sessions use Path=SESSION_PATH (APP_URL subdirectory). Preview opens
 * http://…/s/{slug} which does not send that cookie, so StartSession
 * would create a guest session and Set-Cookie would overwrite the logged-in
 * admin session — causing "Unauthenticated." on the next go-live AJAX call
 * (theme save, etc.).
 *
 * Even with the array driver, VerifyCsrfToken still writes a fresh XSRF-TOKEN
 * cookie on Path=SESSION_PATH, which desyncs CSRF for the panel tab.
 *
 * Fix: force array sessions on public surfaces and strip session/XSRF Set-Cookie
 * headers from the response (do not expire existing browser cookies).
 */
class PreventPublicSessionClobber
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->isPublicSurface($request)) {
            return $next($request);
        }

        config(['session.driver' => 'array']);

        $response = $next($request);

        return $this->stripSessionCookies($response);
    }

    private function stripSessionCookies(Response $response): Response
    {
        $sessionName = (string) config('session.cookie');
        $names = array_values(array_filter([
            $sessionName !== '' ? $sessionName : null,
            'XSRF-TOKEN',
        ]));

        foreach ($response->headers->getCookies() as $cookie) {
            if (! in_array($cookie->getName(), $names, true)) {
                continue;
            }

            $response->headers->removeCookie(
                $cookie->getName(),
                $cookie->getPath() ?: '/',
                $cookie->getDomain()
            );
        }

        return $response;
    }

    private function isPublicSurface(Request $request): bool
    {
        $path = trim($request->path(), '/');

        // Some hosts rewrite through /public — normalise before matching.
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }

        if ($path === 's' || $path === 'book') {
            return true;
        }

        // Public salon website JSON only (not authenticated /api/v1/salon/* panel APIs).
        if (preg_match('#^api/v1/salon/[^/]+/website#', $path)) {
            return true;
        }

        // /{store}/reviews/share/{token}
        if (preg_match('#^[a-z0-9]+(?:-[a-z0-9]+)*/reviews/share/#', $path)) {
            return true;
        }

        $prefixes = [
            's/',
            'book/',
            'website/',
            'widget/',
            'reviews/share/',
            'api/v1/book/',
            'api/v1/client/',
            'api/v1/track/',
            'api/v1/salons/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
