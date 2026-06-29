<?php

namespace App\Support;

use App\Models\Salon;

final class StorefrontUrl
{
    /** Public site root (APP_URL without trailing /admin). */
    public static function publicAppUrl(): string
    {
        $url = self::laravelBaseUrl();
        if (str_ends_with($url, '/admin')) {
            return substr($url, 0, -strlen('/admin'));
        }

        return $url;
    }

    /**
     * Laravel app URL (includes /admin) — API and built theme assets live here.
     */
    public static function laravelBaseUrl(): string
    {
        $url = rtrim((string) config('app.url'), '/');
        if ($url === '') {
            return '';
        }

        if (! str_ends_with(strtolower($url), '/admin')) {
            $url .= '/admin';
        }

        return $url;
    }

    /** Absolute URL to built theme assets (admin/public/website/{theme}/). */
    public static function themeAssetBase(string $theme): string
    {
        $theme = StorefrontTheme::normalizeSlug($theme);

        return self::laravelBaseUrl().'/website/'.$theme.'/';
    }

    public static function website(Salon $salon): string
    {
        $dev = StorefrontTheme::devUrl($salon);
        if ($dev) {
            return rtrim($dev, '/') . '/s/' . $salon->slug;
        }

        return self::publicAppUrl() . '/s/' . $salon->slug;
    }

    /** In-site booking on the React storefront (#book), not the legacy /book/ blade. */
    public static function booking(Salon $salon): string
    {
        return self::website($salon) . '#book';
    }
}
