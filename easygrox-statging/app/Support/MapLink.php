<?php

declare(strict_types=1);

namespace App\Support;

/** Normalize map links, Plus Codes, and plain addresses for storage / storefront. */
final class MapLink
{
    public static function normalize(?string $value): ?string
    {
        $v = trim((string) $value);
        if ($v === '') {
            return null;
        }

        if (filter_var($v, FILTER_VALIDATE_URL)) {
            return $v;
        }

        if (preg_match('#^maps\.google\.#i', $v) || preg_match('#^google\.com/maps#i', $v)) {
            return 'https://'.ltrim($v, '/');
        }

        if (str_starts_with($v, '//')) {
            return 'https:'.$v;
        }

        if (str_starts_with($v, 'www.')) {
            return 'https://'.$v;
        }

        return 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($v);
    }

    /** Google Maps iframe embed URL from a saved map link, Plus Code, or address. */
    public static function embedUrl(?string $mapUrl): ?string
    {
        $url = trim((string) $mapUrl);
        if ($url === '') {
            return null;
        }

        if (str_contains($url, 'output=embed')) {
            return $url;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return 'https://www.google.com/maps?q='.rawurlencode($url).'&z=15&output=embed';
        }

        $parsed = parse_url($url);
        if (! is_array($parsed)) {
            return 'https://www.google.com/maps?q='.rawurlencode($url).'&z=15&output=embed';
        }

        parse_str($parsed['query'] ?? '', $query);

        if (! empty($query['query'])) {
            return 'https://www.google.com/maps?q='.rawurlencode((string) $query['query']).'&z=15&output=embed';
        }

        if (! empty($query['q'])) {
            $z = isset($query['z']) ? (string) $query['z'] : '15';

            return 'https://www.google.com/maps?q='.rawurlencode((string) $query['q']).'&z='.$z.'&output=embed';
        }

        if (preg_match('#/@(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?)#', $url, $coords)) {
            return 'https://www.google.com/maps?q='.$coords[1].','.$coords[2].'&z=15&output=embed';
        }

        if (str_contains($parsed['path'] ?? '', '/place/')) {
            return 'https://www.google.com/maps?q='.rawurlencode($url).'&output=embed';
        }

        return 'https://www.google.com/maps?q='.rawurlencode($url).'&z=15&output=embed';
    }
}
