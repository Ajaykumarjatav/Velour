<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Build absolute URLs that include the APP_URL subdirectory when the app
 * runs under a path prefix (e.g. /vellor/admin on XAMPP).
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
     * Same-origin path for fetch/form actions (keeps subdirectory, drops host).
     * Avoids CSRF failures when APP_URL host differs from the browser host.
     */
    public static function path(string $name, mixed $parameters = [], bool $absolute = true): string
    {
        $url = route($name, $parameters, $absolute);
        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $query = parse_url($url, PHP_URL_QUERY);

        return $query ? $path.'?'.$query : $path;
    }

    /**
     * App subdirectory from APP_URL (e.g. "/vellor/admin" or "/admin" or "").
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
        $base = self::basePath();
        if ($base === '') {
            $base = rtrim((string) request()->getBasePath(), '/');
        }

        return ($base === '' ? '' : $base).'/favicon.png?v=5';
    }
}
