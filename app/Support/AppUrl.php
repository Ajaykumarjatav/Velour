<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Build absolute URLs that include the APP_URL subdirectory when the app
 * runs under a path prefix (e.g. /vellor on XAMPP).
 */
final class AppUrl
{
    public static function absolute(string $pathOrUrl): string
    {
        $pathOrUrl = trim($pathOrUrl);

        if ($pathOrUrl === '') {
            return url('/');
        }

        $appPath = self::appBasePath();
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

    public static function intendedFromRequest(Request $request): string
    {
        $path = '/'.ltrim($request->path(), '/');
        $query = $request->getQueryString();

        if ($query !== null && $query !== '') {
            $path .= '?'.$query;
        }

        return self::absolute($path);
    }

    private static function appBasePath(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');

        return rtrim(parse_url($appUrl, PHP_URL_PATH) ?: '', '/');
    }
}
