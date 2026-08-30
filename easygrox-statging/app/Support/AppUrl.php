<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Build absolute URLs that include the APP_URL subdirectory when the app
 * runs under a path prefix (e.g. /easygrox/admin or the APP_URL path on XAMPP).
 */
final class AppUrl
{
    public static function absolute(string $pathOrUrl): string
    {
        $pathOrUrl = trim($pathOrUrl);

        if ($pathOrUrl === '') {
            return url('/');
        }

        $appPath = self::basePath();
        $path = $pathOrUrl;
        $query = null;

        if (str_contains($pathOrUrl, '://') || str_starts_with($pathOrUrl, '//')) {
            $path = parse_url($pathOrUrl, PHP_URL_PATH) ?? '/';
            $query = parse_url($pathOrUrl, PHP_URL_QUERY) ?: null;
        } elseif (! str_starts_with($pathOrUrl, '/')) {
            $path = '/'.$pathOrUrl;
        }

        if ($appPath !== '' && (str_starts_with($path, $appPath.'/') || $path === $appPath)) {
            $path = substr($path, strlen($appPath)) ?: '/';
        }

        $url = url($path);

        if ($query !== null && $query !== '') {
            $url .= '?'.$query;
        }

        return $url;
    }

    /**
     * Same-origin path for fetch/form actions.
     * Always prefixes the live request subdirectory so forms never POST to
     * /admin/... or /login at the Apache document root.
     */
    public static function path(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $relative = route($name, $parameters, false);
        $query = '';
        if (str_contains($relative, '?')) {
            [$relative, $qs] = explode('?', $relative, 2);
            $query = '?'.$qs;
        }

        $rel = '/'.ltrim($relative, '/');
        $base = self::requestBasePath();

        if ($base !== '' && ($rel === $base || str_starts_with($rel, $base.'/'))) {
            return $rel.$query;
        }

        return ($base === '' ? $rel : $base.$rel).$query;
    }

    /**
     * Absolute login URL for the current request (subdirectory-safe).
     * Never return host-root /login — Apache 404s that on XAMPP.
     */
    public static function login(): string
    {
        $base = self::requestBasePath();
        $host = '';
        try {
            $host = request()->getSchemeAndHttpHost();
        } catch (\Throwable) {
            $host = '';
        }

        if ($host !== '' && $base !== '') {
            return $host.$base.'/login';
        }

        if ($base !== '') {
            return $base.'/login';
        }

        try {
            $url = route('login');
            $path = parse_url($url, PHP_URL_PATH) ?: '/login';
            if ($path === '/login' && $host !== '') {
                $fallback = self::basePath();

                return $fallback !== '' ? $host.$fallback.'/login' : $host.'/login';
            }

            return $url;
        } catch (\Throwable) {
            return ($host !== '' ? $host : '').'/login';
        }
    }

    /**
     * Subdirectory the browser used, e.g. "/easygrox/admin" from APP_URL.
     * Never keep "/public" in the path — that produces .../public/login.
     */
    public static function requestBasePath(): string
    {
        $configured = self::basePath();

        $detected = '';
        try {
            $request = request();
            $detected = rtrim((string) $request->getBasePath(), '/');
            $detected = self::stripPublicDir($detected);

            if ($detected === '' || $detected === '/') {
                $script = str_replace('\\', '/', (string) $request->server->get('SCRIPT_NAME', ''));
                if (str_ends_with($script, '/index.php')) {
                    $detected = self::stripPublicDir(
                        rtrim(substr($script, 0, -strlen('/index.php')), '/')
                    );
                }
            }
        } catch (\Throwable) {
            $detected = '';
        }

        if ($configured !== '') {
            return $configured;
        }

        if ($detected !== '' && $detected !== '/') {
            return $detected;
        }

        return $configured;
    }

    private static function stripPublicDir(string $path): string
    {
        $path = rtrim($path, '/');
        if (str_ends_with($path, '/public')) {
            $path = rtrim(substr($path, 0, -strlen('/public')), '/');
        }

        return $path;
    }

    /**
     * App subdirectory from APP_URL (e.g. "/easygrox/admin" or "/admin" or "").
     */
    public static function basePath(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        return rtrim(parse_url($appUrl, PHP_URL_PATH) ?: '', '/');
    }

    public static function intendedFromRequest(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');
        $query = $request->getQueryString();

        if ($query !== null && $query !== '') {
            $path .= '?'.$query;
        }

        return self::absolute($path);
    }

    /**
     * Root-relative favicon path (avoids APP_URL host mismatch on localhost/XAMPP).
     */
    public static function faviconHref(): string
    {
        $base = self::requestBasePath();
        if ($base === '') {
            $base = rtrim((string) request()->getBasePath(), '/');
        }

        return ($base === '' ? '' : $base).'/favicon.png?v=5';
    }
}
