<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\SalonSetting;

final class StorefrontTheme
{
    public static function all(): array
    {
        return config('storefront-themes.themes', []);
    }

    public static function default(): string
    {
        return (string) config('storefront-themes.default', 'glow-rose');
    }

    public static function forSalon(Salon $salon): string
    {
        $raw = SalonSetting::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('key', 'website_theme')
            ->value('value');

        return self::normalizeSlug($raw);
    }

    public static function normalizeSlug(?string $raw): string
    {
        $slug = trim((string) $raw);
        if ($slug === '') {
            return self::default();
        }

        $legacy = config('storefront-themes.legacy_labels', []);
        if (isset($legacy[$slug])) {
            return $legacy[$slug];
        }

        $themes = self::all();
        if (isset($themes[$slug])) {
            return $slug;
        }

        $byLabel = collect($themes)->search(
            fn (array $theme) => strcasecmp($theme['label'] ?? '', $slug) === 0
        );
        if (is_string($byLabel)) {
            return $byLabel;
        }

        return self::default();
    }

    public static function label(string $slug): string
    {
        $themes = self::all();

        return $themes[$slug]['label'] ?? ucfirst(str_replace('-', ' ', $slug));
    }

    public static function devPort(string $slug): int
    {
        $themes = self::all();
        $slug = self::normalizeSlug($slug);

        return (int) ($themes[$slug]['dev_port'] ?? $themes[self::default()]['dev_port'] ?? 5173);
    }

    public static function assetBase(string $slug): string
    {
        $slug = self::normalizeSlug($slug);
        $configured = rtrim((string) config('storefront.asset_base', '/website/'), '/');

        if ($configured === '/website' || $configured === '') {
            return '/website/' . $slug . '/';
        }

        return $configured . '/' . $slug . '/';
    }

    public static function buildPath(string $slug): string
    {
        $slug = self::normalizeSlug($slug);

        return public_path('website/' . $slug . '/index.html');
    }

    public static function previewImagePath(string $slug): ?string
    {
        $slug = self::normalizeSlug($slug);
        $preview = self::all()[$slug]['preview'] ?? null;
        if (! $preview) {
            return null;
        }

        $path = base_path('salon-website/themes/' . $slug . '/public/assets/' . $preview);

        return is_file($path) ? $path : null;
    }

    public static function previewImageUrl(string $slug): ?string
    {
        if (! self::previewImagePath($slug)) {
            return null;
        }

        return route('storefront.theme-preview', ['slug' => self::normalizeSlug($slug)]);
    }

    public static function accentColor(string $slug): string
    {
        $slug = self::normalizeSlug($slug);

        return (string) (self::all()[$slug]['accent'] ?? '#D14D41');
    }

    public static function devUrl(Salon $salon): ?string
    {
        $devRoot = config('storefront.dev_url');
        if (! $devRoot || ! app()->environment('local')) {
            return null;
        }

        return rtrim((string) $devRoot, '/');
    }
}
