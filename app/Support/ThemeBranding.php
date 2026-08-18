<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\SalonThemeAsset;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves the logo, banner image and banner text a storefront should show.
 *
 * Every element answers one question: has this salon uploaded it for THIS
 * theme (salon_theme_assets)? If not, the theme's own default from
 * config/storefront-themes.php is used. The salon's site-wide logo and cover
 * image deliberately play no part, so switching themes never drags branding
 * the salon did not pick for that theme into the storefront.
 *
 * Uploaded files are checked on disk first, so a file removed behind the app's
 * back degrades to the theme default instead of a broken image.
 */
final class ThemeBranding
{
    public const ELEMENTS = ['logo', 'banner', 'heading', 'subheading'];

    /**
     * Effective branding for a salon on one theme.
     *
     * Everything is keyed on the salon row, which is one store/location, so two
     * branches of the same owner never see each other's uploads.
     *
     * `source` says which step won: `theme` (this theme's upload), `default`
     * (the theme's own asset) or `none` (the theme ships no default either, so
     * the logo falls back to the EasyGrox mark).
     *
     * @return array{
     *     theme: string,
     *     logo_url: ?string,
     *     banner_url: ?string,
     *     heading: string,
     *     subheading: string,
     *     custom: array<string, bool>,
     *     source: array<string, string>,
     *     updated_at: ?string
     * }
     */
    public static function resolve(Salon $salon, ?string $theme = null): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme ?? StorefrontTheme::forSalon($salon));
        $override = SalonThemeAsset::lookup($salon->id, $theme);
        $defaults = self::defaults($theme);

        $logo = self::resolveImage(
            self::uploadedUrl($override?->logo_path),
            self::themeAssetUrl($theme, $defaults['logo'])
        );

        $banner = self::resolveImage(
            self::uploadedUrl($override?->banner_path),
            self::themeAssetUrl($theme, $defaults['banner'])
        );

        $heading    = self::text($override?->banner_heading);
        $subheading = self::text($override?->banner_subheading);

        return [
            'theme'      => $theme,
            'logo_url'   => $logo['url'],
            'banner_url' => $banner['url'],
            'heading'    => $heading ?? $defaults['heading'],
            'subheading' => $subheading ?? $defaults['subheading'],
            'custom'     => [
                'logo'       => $logo['source'] === 'theme',
                'banner'     => $banner['source'] === 'theme',
                'heading'    => $heading !== null,
                'subheading' => $subheading !== null,
            ],
            'source'     => [
                'logo'       => $logo['source'],
                'banner'     => $banner['source'],
                'heading'    => $heading !== null ? 'theme' : 'default',
                'subheading' => $subheading !== null ? 'theme' : 'default',
            ],
            'updated_at' => $override?->updated_at?->toIso8601String(),
        ];
    }

    /** @return array{url: ?string, source: string} */
    private static function resolveImage(?string $themeUpload, ?string $themeDefault): array
    {
        return match (true) {
            $themeUpload !== null  => ['url' => $themeUpload, 'source' => 'theme'],
            $themeDefault !== null => ['url' => $themeDefault, 'source' => 'default'],
            default => ['url' => null, 'source' => 'none'],
        };
    }

    /**
     * The theme's shipped values, used to seed placeholders in the tenant panel
     * and as the final fallback when nothing has been uploaded.
     *
     * @return array{logo: ?string, banner: ?string, heading: string, subheading: string}
     */
    public static function defaults(string $theme): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $branding = config('storefront-themes.themes.'.$theme.'.branding', []);

        return [
            'logo'       => $branding['logo'] ?? null,
            'banner'     => $branding['banner'] ?? null,
            'heading'    => (string) ($branding['heading'] ?? ''),
            'subheading' => (string) ($branding['subheading'] ?? ''),
        ];
    }

    /** Default banner as a URL, for previewing "what you get if you upload nothing". */
    public static function defaultBannerUrl(string $theme): ?string
    {
        return self::themeAssetUrl($theme, self::defaults($theme)['banner']);
    }

    public static function defaultLogoUrl(string $theme): ?string
    {
        return self::themeAssetUrl($theme, self::defaults($theme)['logo']);
    }

    private static function uploadedUrl(?string $path): ?string
    {
        if (! is_string($path) || trim($path) === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/'.$path).'?v='.Storage::disk('public')->lastModified($path);
    }

    private static function themeAssetUrl(string $theme, ?string $file): ?string
    {
        if (! is_string($file) || trim($file) === '') {
            return null;
        }

        return StorefrontAssets::assetUrl($theme, $file);
    }

    /** Blank input means "use the default", not "render an empty hero". */
    private static function text(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
