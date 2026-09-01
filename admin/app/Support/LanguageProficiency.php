<?php

namespace App\Support;

/**
 * Spoken languages for staff / profile: ISO 639-1 codes, uniform storage as sorted CSV (e.g. "en,hi").
 */
final class LanguageProficiency
{
    /** @return array<string, string> code => English label */
    public static function options(): array
    {
        return [
            'en' => 'English',
            'hi' => 'Hindi',
            'es' => 'Spanish',
            'fr' => 'French',
            'de' => 'German',
            'pt' => 'Portuguese',
            'it' => 'Italian',
            'nl' => 'Dutch',
            'pl' => 'Polish',
            'ar' => 'Arabic',
            'zh' => 'Chinese',
            'ja' => 'Japanese',
            'ko' => 'Korean',
            'ru' => 'Russian',
            'tr' => 'Turkish',
            'vi' => 'Vietnamese',
            'th' => 'Thai',
            'id' => 'Indonesian',
            'ms' => 'Malay',
            'ta' => 'Tamil',
            'te' => 'Telugu',
            'bn' => 'Bengali',
            'mr' => 'Marathi',
            'gu' => 'Gujarati',
            'pa' => 'Punjabi',
            'ur' => 'Urdu',
        ];
    }

    /** @return list<string> */
    public static function allowedCodes(): array
    {
        return array_keys(self::options());
    }

    /**
     * Parse DB value or legacy comma-separated names into canonical codes.
     *
     * @return list<string>
     */
    public static function codesFromStored(?string $stored): array
    {
        if ($stored === null || trim($stored) === '') {
            return [];
        }

        $options = self::options();
        $valid = array_flip(array_keys($options));

        $labelToCode = [];
        foreach ($options as $code => $label) {
            $labelToCode[strtolower($label)] = $code;
        }

        $aliases = [
            'english' => 'en', 'en-us' => 'en', 'en-gb' => 'en', 'en_us' => 'en',
            'hindi' => 'hi', 'hinglish' => 'hi',
            'spanish' => 'es', 'french' => 'fr', 'german' => 'de',
            'portuguese' => 'pt', 'italian' => 'it', 'dutch' => 'nl', 'polish' => 'pl',
            'arabic' => 'ar', 'chinese' => 'zh', 'japanese' => 'ja', 'korean' => 'ko',
            'russian' => 'ru', 'turkish' => 'tr', 'vietnamese' => 'vi', 'thai' => 'th',
            'indonesian' => 'id', 'malay' => 'ms', 'tamil' => 'ta', 'telugu' => 'te',
            'bengali' => 'bn', 'marathi' => 'mr', 'gujarati' => 'gu', 'punjabi' => 'pa',
            'urdu' => 'ur',
        ];

        $parts = preg_split('/\s*,\s*/', trim($stored)) ?: [];
        $out = [];

        foreach ($parts as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            $low = strtolower($p);
            if (isset($aliases[$low])) {
                $out[] = $aliases[$low];
                continue;
            }
            if (isset($labelToCode[$low])) {
                $out[] = $labelToCode[$low];
                continue;
            }
            if (strlen($low) === 2 && isset($valid[$low])) {
                $out[] = $low;
                continue;
            }
        }

        $out = array_values(array_unique(array_intersect($out, array_keys($valid))));
        sort($out);

        return $out;
    }

    /**
     * @param  list<string>|null  $codes
     */
    public static function encode(?array $codes): ?string
    {
        if ($codes === null || $codes === []) {
            return null;
        }

        $allowed = array_flip(self::allowedCodes());
        $out = [];
        foreach ($codes as $c) {
            $c = strtolower(trim((string) $c));
            if ($c !== '' && isset($allowed[$c])) {
                $out[$c] = true;
            }
        }
        $list = array_keys($out);
        sort($list);
        if ($list === []) {
            return null;
        }

        while ($list !== [] && strlen(implode(',', $list)) > 120) {
            array_pop($list);
        }

        return $list === [] ? null : implode(',', $list);
    }
}
