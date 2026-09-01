<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\SalonSetting;

final class StorefrontAbout
{
    public const SETTING_KEY = 'website_about_json';

    public const GALLERY_SLOTS = 6;

    /**
     * @return list<string>
     */
    public static function fieldKeys(): array
    {
        return [
            'eyebrow',
            'heading_prefix',
            'heading_highlight',
            'heading_suffix',
            'body',
            'stat_one_value',
            'stat_one_label',
            'stat_two_value',
            'stat_two_label',
        ];
    }

    /**
     * Unique About copy per storefront theme. Empty tenant fields fall back here.
     *
     * @return array<string, string>
     */
    public static function themeDefaults(string $theme): array
    {
        return match (StorefrontTheme::normalizeSlug($theme)) {
            'beauty' => [
                'eyebrow' => 'The studio',
                'heading_prefix' => 'Crafting confidence through',
                'heading_highlight' => 'beauty',
                'heading_suffix' => '',
                'body' => 'This is a beauty house for skin, hair, and occasion glam — not a quick trim shop. Our artists listen first, then build a look that feels like you: polished everyday makeup, restorative facials, and bridal parties that stay camera-ready from vows to last dance.',
                'stat_one_value' => '2016',
                'stat_one_label' => 'Doors opened',
                'stat_two_value' => '1,200+',
                'stat_two_label' => 'Makeovers',
            ],
            'nail' => [
                'eyebrow' => 'The atelier',
                'heading_prefix' => 'When colour becomes',
                'heading_highlight' => 'jewellery',
                'heading_suffix' => 'for your hands.',
                'body' => 'We treat nails as wearable art. Soft gels, precise cuticle work, and custom nail illustrations are paced so the set lasts — from a barely-there French to sculpted chrome — in a quiet studio built for detail, not rush-hour polish.',
                'stat_one_value' => '2017',
                'stat_one_label' => 'First set',
                'stat_two_value' => '4,000+',
                'stat_two_label' => 'Manicures',
            ],
            'spa' => [
                'eyebrow' => 'The sanctuary',
                'heading_prefix' => 'Crafted for pure',
                'heading_highlight' => 'stillness',
                'heading_suffix' => '',
                'body' => 'This spa is a pause, not a salon add-on. Warm stone, slow massage, and thermal rituals are sequenced to quiet the nervous system. You leave with softer muscle, clearer breath, and a body that remembers what rest feels like.',
                'stat_one_value' => '2010',
                'stat_one_label' => 'First ritual',
                'stat_two_value' => '10k+',
                'stat_two_label' => 'Sessions',
            ],
            'pet-grooming' => [
                'eyebrow' => 'For good dogs',
                'heading_prefix' => 'Gentle baths,',
                'heading_highlight' => 'happy',
                'heading_suffix' => 'paws.',
                'body' => 'Every visit is paced for the animal, not the clock: calm intro, breed-right clip, and a dryer that does not frighten. Cats and dogs leave smelling clean, moving freely, and looking like themselves — fluff included.',
                'stat_one_value' => '2019',
                'stat_one_label' => 'Wagging since',
                'stat_two_value' => '2,500+',
                'stat_two_label' => 'Pets groomed',
            ],
            'mackup' => [
                'eyebrow' => 'The glam room',
                'heading_prefix' => 'Light, pigment, and',
                'heading_highlight' => 'you',
                'heading_suffix' => '',
                'body' => 'Makeup here is architecture for the face: skin that photographs true, eyes that last through humidity, and lips that do not migrate. Editorial, bridal, and everyday lessons share one rule — enhance, never mask.',
                'stat_one_value' => '2020',
                'stat_one_label' => 'First booking',
                'stat_two_value' => '900+',
                'stat_two_label' => 'Looks painted',
            ],
            'tattoo' => [
                'eyebrow' => 'The shop',
                'heading_prefix' => 'Where art meets',
                'heading_highlight' => 'skin',
                'heading_suffix' => '',
                'body' => 'We draw custom tattoos in a sterile, well-lit shop — fine line, blackwork, and colour that is meant to age with you. Consultations are honest about placement, pain, and aftercare so the piece stays yours for decades.',
                'stat_one_value' => '2012',
                'stat_one_label' => 'First needle',
                'stat_two_value' => '6,000+',
                'stat_two_label' => 'Pieces inked',
            ],
            default => [
                'eyebrow' => 'The chair',
                'heading_prefix' => 'Where',
                'heading_highlight' => 'craft',
                'heading_suffix' => "meets\nthe cut.",
                'body' => 'Glow Rose is a grooming floor for sharp fades, lived-in colour, and skin that can take a close shave. Consult, cut, finish — no assembly-line styling. You leave with a shape that still looks intentional a week later.',
                'stat_one_value' => '2014',
                'stat_one_label' => 'First fade',
                'stat_two_value' => '500+',
                'stat_two_label' => 'Regulars',
            ],
        };
    }

    /**
     * @return array<string, mixed>
     */
    public static function saved(Salon $salon): array
    {
        $raw = SalonSetting::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('key', self::SETTING_KEY)
            ->value('value');

        $decoded = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * Tenant edits for one theme only. Legacy flat JSON applies only to the salon’s current theme.
     *
     * @return array<string, mixed>
     */
    public static function savedForTheme(Salon $salon, string $theme): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $root = self::saved($salon);

        if (self::isThemeMap($root)) {
            $slice = $root[$theme] ?? [];

            return is_array($slice) ? $slice : [];
        }

        if ($root === []) {
            return [];
        }

        $guess = self::guessLegacyTheme($root);
        if ($guess === $theme || ($guess === null && $theme === StorefrontTheme::forSalon($salon))) {
            return $root;
        }

        return [];
    }

    public static function persistTheme(Salon $salon, string $theme, array $themePayload): void
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $root = self::saved($salon);

        if (! self::isThemeMap($root)) {
            $legacy = $root;
            $root = [];
            if ($legacy !== []) {
                $bucket = self::guessLegacyTheme($legacy) ?? StorefrontTheme::forSalon($salon);
                $root[$bucket] = $legacy;
            }
        }

        $root[$theme] = $themePayload;

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => self::SETTING_KEY],
            ['value' => json_encode($root, JSON_UNESCAPED_UNICODE), 'type' => 'json']
        );
    }

    public static function isBlank(string $value, bool $html = false): bool
    {
        $value = trim($value);
        if ($value === '') {
            return true;
        }

        if ($html) {
            return trim(html_entity_decode(strip_tags($value), ENT_QUOTES, 'UTF-8')) === '';
        }

        return false;
    }

    /**
     * Saved copy, with theme defaults whenever a field is blank.
     *
     * @return array<string, string>
     */
    public static function resolve(Salon $salon, ?string $theme = null): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme ?? StorefrontTheme::forSalon($salon));
        $defaults = self::themeDefaults($theme);
        $saved = self::savedForTheme($salon, $theme);
        $out = [];

        foreach (self::fieldKeys() as $key) {
            $value = (string) ($saved[$key] ?? '');
            $html = $key === 'body';
            $out[$key] = self::isBlank($value, $html)
                ? (string) ($defaults[$key] ?? '')
                : $value;
        }

        return $out;
    }

    /**
     * Theme fallback files for the About gallery row (must match each theme's about partial).
     *
     * @return list<string>
     */
    public static function defaultGalleryFiles(string $theme): array
    {
        $files = match (StorefrontTheme::normalizeSlug($theme)) {
            'glow-rose' => ['Rectangle 31.png', 'Rectangle 27.png', 'Rectangle 28.png', 'Rectangle 29.png', 'Rectangle 30.png', 'Rectangle 32.png'],
            'beauty' => ['Rectangle 31.png', 'Rectangle 27.png', 'Rectangle 28.png', 'Rectangle 29.png', 'Rectangle 31.png', 'Rectangle 32.png'],
            default => ['Rectangle 31.png', 'Rectangle 27.png', 'Rectangle 28.png', 'Rectangle 30.png', 'Rectangle 32.png', 'Rectangle 29.png'],
        };

        return array_slice($files, 0, self::GALLERY_SLOTS);
    }

    /**
     * @return list<string>
     */
    public static function savedGallery(Salon $salon, ?string $theme = null): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme ?? StorefrontTheme::forSalon($salon));
        $saved = self::savedForTheme($salon, $theme);
        $raw = $saved['gallery'] ?? [];
        if (! is_array($raw)) {
            $raw = [];
        }

        $out = [];
        for ($i = 0; $i < self::GALLERY_SLOTS; $i++) {
            $out[$i] = trim((string) ($raw[$i] ?? ''));
        }

        return $out;
    }

    /**
     * Custom gallery URLs by slot index; null means use the theme default image.
     *
     * @return list<string|null>
     */
    public static function galleryUrls(Salon $salon, ?string $theme = null): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme ?? StorefrontTheme::forSalon($salon));
        $urls = [];
        foreach (self::savedGallery($salon, $theme) as $path) {
            $urls[] = $path !== ''
                ? (PublicStorage::url($path) ?? url('media/'.ltrim($path, '/')))
                : null;
        }

        return $urls;
    }

    /**
     * @return list<array{index: int, preview_url: string, is_custom: bool}>
     */
    public static function gallerySlots(Salon $salon, string $theme): array
    {
        $theme = StorefrontTheme::normalizeSlug($theme);
        $defaults = self::defaultGalleryFiles($theme);
        $saved = self::savedGallery($salon, $theme);
        $slots = [];

        for ($i = 0; $i < self::GALLERY_SLOTS; $i++) {
            $path = $saved[$i];
            $customUrl = $path !== ''
                ? (PublicStorage::url($path) ?? url('media/'.ltrim($path, '/')))
                : null;
            $preview = $customUrl ?: StorefrontAssets::assetUrl($theme, $defaults[$i] ?? $defaults[0]);
            if ($customUrl) {
                $preview .= (str_contains($preview, '?') ? '&' : '?').'v='.rawurlencode(basename($path));
            }
            $slots[] = [
                'index' => $i,
                'preview_url' => $preview,
                'is_custom' => $path !== '',
            ];
        }

        return $slots;
    }

    /**
     * @param  array<string, mixed>  $root
     */
    private static function isThemeMap(array $root): bool
    {
        foreach (array_keys(StorefrontTheme::all()) as $slug) {
            if (isset($root[$slug]) && is_array($root[$slug])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $legacy
     */
    private static function guessLegacyTheme(array $legacy): ?string
    {
        $highlight = strtolower(trim((string) ($legacy['heading_highlight'] ?? '')));
        $suffix = strtolower(trim((string) ($legacy['heading_suffix'] ?? '')));
        $prefix = strtolower(trim((string) ($legacy['heading_prefix'] ?? '')));

        return match (true) {
            $highlight === 'beauty' => 'beauty',
            $highlight === 'relaxation' => 'spa',
            str_contains($suffix, 'paws') || $highlight === 'happy' || str_contains($highlight, 'happy') => 'pet-grooming',
            $highlight === 'meets' || str_contains($prefix, 'where art') => 'tattoo',
            str_contains($suffix, 'precision') => 'mackup',
            $highlight === 'elegance' => 'nail',
            $highlight === 'artistry' => 'glow-rose',
            default => null,
        };
    }
}
