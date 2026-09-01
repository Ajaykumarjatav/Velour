<?php

namespace App\Support;

/**
 * Absolute URLs for assets embedded in outbound email (clients cannot load localhost).
 */
final class MailAssets
{
    public static function logoUrl(): string
    {
        $configured = trim((string) config('mail.logo_url', ''));
        if ($configured !== '') {
            return $configured;
        }

        // Light wordmark for the purple email header.
        $path = 'images/easygrox-logo-light.png';
        $local = asset($path);
        $host = strtolower((string) (parse_url($local, PHP_URL_HOST) ?: ''));

        if ($host === 'localhost' || $host === '127.0.0.1' || str_ends_with($host, '.local')) {
            return 'https://easygrox.com/'.$path;
        }

        return $local;
    }
}
