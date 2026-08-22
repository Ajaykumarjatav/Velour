<?php

namespace App\Support;

class AwardsHtml
{
    public static function looksLikeHtml(?string $value): bool
    {
        return (bool) preg_match('/<(p|br|img|ul|ol|li|strong|b|em|i|div)\b/i', (string) $value);
    }

    public static function sanitize(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><div><img>');

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="awards-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $root = $dom->getElementById('awards-root');
        if (! $root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return null;
        }

        self::cleanElement($root);

        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $out = trim(str_replace('&#13;', '', $out));
        if ($out === '' || (! str_contains(strtolower($out), '<img') && trim(html_entity_decode(strip_tags($out), ENT_QUOTES, 'UTF-8')) === '')) {
            return null;
        }

        return $out;
    }

    public static function forEditor(?string $value): string
    {
        $value = (string) $value;
        if (trim($value) === '') {
            return '';
        }
        if (self::looksLikeHtml($value)) {
            return self::sanitize($value) ?? '';
        }

        return '<p>'.nl2br(e($value), false).'</p>';
    }

    public static function safe(?string $value): string
    {
        if ($value === null || trim($value) === '') {
            return '';
        }
        if (! self::looksLikeHtml($value)) {
            return nl2br(e($value), false);
        }

        return self::sanitize($value) ?? '';
    }

    public static function isEmpty(?string $value): bool
    {
        if ($value === null || trim($value) === '') {
            return true;
        }
        if (self::looksLikeHtml($value)) {
            return self::sanitize($value) === null;
        }

        return trim($value) === '';
    }

    /**
     * @return list<string>
     */
    public static function imagePaths(?string $html, int $salonId): array
    {
        $html = self::sanitize($html) ?? '';
        if ($html === '' || ! preg_match_all('/src="([^"]+)"/i', $html, $matches)) {
            return [];
        }

        $paths = [];
        $needle = 'salons/'.$salonId.'/awards/';
        foreach ($matches[1] as $src) {
            $decoded = html_entity_decode(urldecode($src), ENT_QUOTES, 'UTF-8');
            $pos = strpos($decoded, $needle);
            if ($pos === false) {
                continue;
            }
            $path = substr($decoded, $pos);
            $path = preg_replace('/[?#].*$/', '', $path) ?: $path;
            if (preg_match('#^salons/\d+/awards/[A-Za-z0-9._-]+$#', $path)) {
                $paths[] = $path;
            }
        }

        return array_values(array_unique($paths));
    }

    private static function cleanElement(\DOMElement $el): void
    {
        $allowed = ['p', 'br', 'strong', 'b', 'em', 'i', 'ul', 'ol', 'li', 'div', 'img'];
        $toProcess = [];
        foreach ($el->childNodes as $child) {
            $toProcess[] = $child;
        }

        foreach ($toProcess as $child) {
            if ($child instanceof \DOMComment) {
                $child->parentNode?->removeChild($child);
                continue;
            }
            if (! $child instanceof \DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);
            if (! in_array($tag, $allowed, true)) {
                $parent = $child->parentNode;
                if ($parent) {
                    while ($child->firstChild) {
                        $parent->insertBefore($child->firstChild, $child);
                    }
                    $parent->removeChild($child);
                }
                continue;
            }

            self::stripAttributes($child, $tag);
            self::cleanElement($child);
        }
    }

    private static function stripAttributes(\DOMElement $el, string $tag): void
    {
        $keep = [];
        if ($tag === 'img') {
            $src = trim($el->getAttribute('src'));
            $alt = trim($el->getAttribute('alt'));
            if (self::isAllowedImageSrc($src)) {
                $keep['src'] = $src;
                $keep['alt'] = $alt !== '' ? $alt : 'Award';
            }
        }

        $names = [];
        foreach ($el->attributes ?? [] as $attr) {
            $names[] = $attr->name;
        }
        foreach ($names as $name) {
            $el->removeAttribute($name);
        }
        foreach ($keep as $name => $value) {
            $el->setAttribute($name, $value);
        }

        if ($tag === 'img' && ! $el->hasAttribute('src')) {
            $el->parentNode?->removeChild($el);
        }
    }

    private static function isAllowedImageSrc(string $src): bool
    {
        if ($src === '' || preg_match('/^\s*(javascript|data|vbscript):/i', $src)) {
            return false;
        }

        if (str_starts_with($src, '/storage/') || str_starts_with($src, 'storage/')) {
            return true;
        }

        $parts = parse_url($src);
        $scheme = strtolower((string) ($parts['scheme'] ?? ''));

        return in_array($scheme, ['http', 'https'], true);
    }
}
