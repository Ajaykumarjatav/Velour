<?php

declare(strict_types=1);

namespace App\Support;

final class SupportTicketHtml
{
    public static function sanitize(?string $html): string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return '';
        }

        $html = strip_tags($html, '<p><br><strong><b><em><i><ul><ol><li><a><code><pre><div>');

        $previous = libxml_use_internal_errors(true);
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML(
            '<?xml encoding="UTF-8"><div id="st-root">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        $root = $dom->getElementById('st-root');
        if (! $root) {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);

            return trim(strip_tags($html));
        }

        self::clean($root);
        $out = '';
        foreach ($root->childNodes as $child) {
            $out .= $dom->saveHTML($child);
        }
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return trim($out);
    }

    public static function plainLength(?string $html): int
    {
        return mb_strlen(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? ''));
    }

    public static function looksLikeHtml(?string $value): bool
    {
        return (bool) preg_match('/<(p|br|ul|ol|li|strong|b|em|i|a|code|pre|div)\b/i', (string) $value);
    }

    private static function clean(\DOMNode $node): void
    {
        if (! $node instanceof \DOMElement) {
            return;
        }

        if ($node->tagName === 'a') {
            $href = trim($node->getAttribute('href'));
            if ($href === '' || ! preg_match('#^https?://#i', $href)) {
                $node->removeAttribute('href');
            } else {
                $node->setAttribute('rel', 'noopener noreferrer');
                $node->setAttribute('target', '_blank');
            }
        }

        if ($node->hasAttributes()) {
            foreach (iterator_to_array($node->attributes) as $attr) {
                $name = strtolower($attr->name);
                if (str_starts_with($name, 'on') || in_array($name, ['style', 'class'], true)) {
                    $node->removeAttribute($attr->name);
                }
            }
        }

        foreach (iterator_to_array($node->childNodes) as $child) {
            self::clean($child);
        }
    }
}
