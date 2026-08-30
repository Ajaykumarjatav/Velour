<?php

namespace App\Support;

use Illuminate\Support\Facades\URL;

/**
 * Signed URLs that work under subdirectory installs (/admin) and reverse proxies.
 * Signatures are relative (path + query) so host/scheme mismatches do not 403.
 */
final class SignedUrl
{
    /**
     * Temporary signed route as an absolute URL for emails / WhatsApp.
     *
     * @param  array<string, mixed>  $parameters
     */
    public static function temporaryRoute(string $name, $expiration, array $parameters = []): string
    {
        $relative = URL::temporarySignedRoute($name, $expiration, $parameters, absolute: false);

        if (str_starts_with($relative, 'http://') || str_starts_with($relative, 'https://')) {
            return $relative;
        }

        $root = rtrim((string) config('app.url'), '/');
        $path = '/' . ltrim($relative, '/');
        $rootPath = rtrim((string) (parse_url($root, PHP_URL_PATH) ?: ''), '/');

        // APP_URL already includes /admin; relative path may also start with /admin/...
        if ($rootPath !== '' && ($path === $rootPath || str_starts_with($path, $rootPath . '/'))) {
            return $root . substr($path, strlen($rootPath));
        }

        return $root . $path;
    }
}
