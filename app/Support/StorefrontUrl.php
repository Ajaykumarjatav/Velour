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

    public static function legal(Salon $salon, string $page = 'terms'): string
    {
        $page = trim($page, '/');

        return rtrim(self::website($salon), '/').'/'.$page;
    }

    /** In-site booking on the React storefront (#book), not the legacy /book/ blade. */
    public static function booking(Salon $salon): string
    {
        return self::website($salon) . '#book';
    }

    /**
     * Customer-facing WhatsApp message for sharing the booking page
     * (business name, contact, location + link — not URL alone).
     */
    public static function whatsappBookingShareText(Salon $salon): string
    {
        $name = trim((string) $salon->name);
        $lines = [];

        if ($name !== '') {
            $lines[] = $name;
            $lines[] = '';
        }

        $lines[] = 'Book your next appointment with us online!';
        $lines[] = '';

        $address = self::formatShareAddress($salon);
        if ($address !== '') {
            $lines[] = 'Location: '.$address;
        }

        $phone = trim((string) ($salon->whatsappNumberForSite() ?: $salon->phone ?: ''));
        if ($phone !== '') {
            $lines[] = 'Phone: '.$phone;
        }

        $email = trim((string) ($salon->email ?? ''));
        if ($email !== '') {
            $lines[] = 'Email: '.$email;
        }

        if ($address !== '' || $phone !== '' || $email !== '') {
            $lines[] = '';
        }

        $lines[] = 'Book here:';
        $lines[] = self::booking($salon);

        return implode("\n", $lines);
    }

    public static function whatsappBookingShareUrl(Salon $salon): string
    {
        return 'https://wa.me/?text='.rawurlencode(self::whatsappBookingShareText($salon));
    }

    private static function formatShareAddress(Salon $salon): string
    {
        $parts = array_values(array_filter([
            trim((string) ($salon->address_line1 ?? '')),
            trim((string) ($salon->address_line2 ?? '')),
            trim((string) ($salon->city ?? '')),
            trim((string) ($salon->county ?? '')),
            trim((string) ($salon->postcode ?? '')),
        ], fn (string $p) => $p !== ''));

        return implode(', ', $parts);
    }
}
