<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Picks a contextual SVG glyph when a service has no photo (salon catalog list, edit preview).
 *
 * @phpstan-type GlyphKey 'scissors'|'paint_brush'|'heart'|'sun'|'eye'|'sparkles'|'star'
 */
final class ServicePlaceholderGlyph
{
    /** @var array<string, list<string>> */
    private const GLYPH_PATHS = [
        'scissors' => [
            'M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z',
        ],
        'paint_brush' => [
            'M9.53 16.122a3 3 0 00-5.061 4.043 4.5 4.5 0 01-2.133-2.133 3 3 0 004.043-5.06L15.364 4.364a2.121 2.121 0 113 3L9.53 16.122z',
        ],
        'heart' => [
            'M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z',
        ],
        'sun' => [
            'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.227 9.477M12 12a3 3 0 100-6 3 3 0 000 6z',
        ],
        'eye' => [
            'M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z',
            'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        ],
        'sparkles' => [
            'M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z',
        ],
        'star' => [
            'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z',
        ],
    ];

    /** @var list<array{0: list<string>, 1: string}> */
    private const RULES = [
        [['haircut', 'hair cut', 'blow dry', 'blowdry', 'hair styling', 'hair style', 'kids haircut', 'kid haircut', 'trim', 'barber', 'fade', 'perm', 'extensions', 'braid'], 'scissors'],
        [['colour', 'color', 'dye', 'highlight', 'balayage', 'tint', 'bleach', 'toner'], 'paint_brush'],
        [['nail', 'manicure', 'pedicure', 'gel polish', 'acrylic'], 'sparkles'],
        [['massage', 'spa', 'therapy', 'reflexology', 'aromatherapy'], 'heart'],
        [['facial', 'skin care', 'skincare', 'peel', 'derma', 'microblading', 'cleansing'], 'sun'],
        [['wax', 'threading', 'brow', 'lash', 'eyebrow', 'eyelash'], 'eye'],
        [['beard', 'shave', 'shaving', 'mustache', 'moustache'], 'scissors'],
        [['makeup', 'make-up', 'bridal', 'cosmetic'], 'sparkles'],
        [['tan', 'tanning'], 'sun'],
        [['tattoo', 'piercing', 'pierce'], 'star'],
        [['kids', 'child', 'children'], 'sparkles'],
    ];

    /**
     * @return list<string> SVG path `d` attributes (outline strokes).
     */
    public static function pathDs(string $serviceName, ?string $categoryName = null, ?string $businessTypeName = null): array
    {
        $haystack = mb_strtolower(trim($serviceName).' '.trim((string) $categoryName).' '.trim((string) $businessTypeName));

        foreach (self::RULES as [$keywords, $glyph]) {
            foreach ($keywords as $kw) {
                if ($kw !== '' && str_contains($haystack, mb_strtolower($kw))) {
                    return self::pathsForGlyph($glyph);
                }
            }
        }

        if (str_contains($haystack, 'hair')) {
            return self::pathsForGlyph('scissors');
        }
        if (str_contains($haystack, 'nail')) {
            return self::pathsForGlyph('sparkles');
        }
        if (str_contains($haystack, 'skin')) {
            return self::pathsForGlyph('sun');
        }

        return self::pathsForGlyph('star');
    }

    /**
     * @return list<string>
     */
    private static function pathsForGlyph(string $glyph): array
    {
        return self::GLYPH_PATHS[$glyph] ?? self::GLYPH_PATHS['star'];
    }
}
