<?php

declare(strict_types=1);

namespace App\Support;

final class SocialLinkPlatforms
{
    /**
     * @return array<string, array{label: string, prefix: string}>
     */
    public static function all(): array
    {
        return [
            'instagram' => ['label' => 'Instagram', 'prefix' => 'https://instagram.com/'],
            'facebook' => ['label' => 'Facebook', 'prefix' => 'https://facebook.com/'],
            'tiktok' => ['label' => 'TikTok', 'prefix' => 'https://tiktok.com/@'],
            'whatsapp' => ['label' => 'WhatsApp', 'prefix' => 'https://wa.me/'],
            'google' => ['label' => 'Google Business', 'prefix' => 'https://g.page/'],
            'twitter' => ['label' => 'X / Twitter', 'prefix' => 'https://x.com/'],
            'youtube' => ['label' => 'YouTube', 'prefix' => 'https://youtube.com/@'],
            'linkedin' => ['label' => 'LinkedIn', 'prefix' => 'https://linkedin.com/company/'],
            'pinterest' => ['label' => 'Pinterest', 'prefix' => 'https://pinterest.com/'],
        ];
    }

    public static function prefix(string $platform): string
    {
        return self::all()[$platform]['prefix'] ?? '';
    }

    public static function isPrefixOnly(?string $url, string $platform): bool
    {
        $url = trim((string) $url);
        if ($url === '') {
            return true;
        }

        $prefix = self::prefix($platform);
        if ($prefix === '') {
            return false;
        }

        $normalized = rtrim($url, '/');
        $normalizedPrefix = rtrim($prefix, '/');

        return strcasecmp($normalized, $normalizedPrefix) === 0
            || strcasecmp($url, $prefix) === 0;
    }
}
