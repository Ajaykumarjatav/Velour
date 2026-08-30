<?php

namespace App\Support;

final class StorefrontAssets
{
    public static function cssUrl(string $theme): string
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $path = public_path('storefront/'.$theme.'/theme.css');

        if (is_file($path)) {
            return asset('storefront/'.$theme.'/theme.css');
        }

        // Fallback to legacy Vite build while migrating
        $legacy = glob(public_path('website/'.$theme.'/assets/index-*.css')) ?: [];

        return $legacy !== []
            ? asset('website/'.$theme.'/assets/'.basename($legacy[0]))
            : asset('storefront/'.$theme.'/theme.css');
    }

    public static function assetUrl(string $theme, string $file): string
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $file = ltrim(str_replace(['..', '\\'], '', $file), '/');

        $storefrontPath = public_path('storefront/'.$theme.'/assets/'.$file);
        if (is_file($storefrontPath)) {
            return route('storefront.theme.asset', ['theme' => $theme, 'asset' => $file]);
        }

        $legacyPath = base_path('salon-website/themes/'.$theme.'/public/assets/'.$file);
        if (is_file($legacyPath)) {
            return route('storefront.theme.asset', ['theme' => $theme, 'asset' => $file]);
        }

        return route('storefront.theme.asset', ['theme' => $theme, 'asset' => $file]);
    }

    /** @return array<string, string> */
    public static function tokens(string $theme): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $tokens = config('storefront-themes.themes.'.$theme.'.tokens', []);

        return is_array($tokens) ? $tokens : [];
    }

    /** @return array<string, mixed> */
    public static function assets(string $theme): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $assets = config('storefront-themes.themes.'.$theme.'.assets', []);

        return is_array($assets) ? $assets : [];
    }

    public static function cssVariables(string $theme): string
    {
        $tokens = self::tokens($theme);
        if ($tokens === []) {
            return '';
        }

        $lines = [];
        foreach ($tokens as $key => $value) {
            $var = '--'.str_replace('_', '-', preg_replace('/([a-z])([A-Z])/', '$1-$2', $key) ?? $key);
            $lines[] = $var.': '.$value.';';
        }

        return implode("\n    ", $lines);
    }

    public static function staticAssetPath(string $theme, string $file): ?string
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $file = ltrim(str_replace(['..', '\\'], '', $file), '/');

        $candidates = [
            public_path('storefront/'.$theme.'/assets/'.$file),
            base_path('salon-website/themes/'.$theme.'/public/assets/'.$file),
            public_path('website/'.$theme.'/assets/'.$file),
        ];

        foreach ($candidates as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
