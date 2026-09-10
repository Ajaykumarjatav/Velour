<?php

namespace App\Support;

use App\Models\LinkVisit;
use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Records public website visits / button clicks and classifies source + human vs bot.
 */
final class WebsiteTraffic
{
    public const KIND_VISIT = 'visit';
    public const KIND_CLICK = 'click';

    /** @var list<string> */
    private const BOT_NEEDLES = [
        'bot', 'spider', 'crawler', 'crawl', 'slurp', 'scanner',
        'googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck', 'sogou',
        'facebookexternalhit', 'facebot', 'twitterbot', 'linkedinbot',
        'pinterestbot', 'slackbot', 'telegrambot', 'discordbot',
        'semrush', 'ahrefs', 'mj12', 'dotbot', 'majestic', 'screaming frog',
        'gptbot', 'chatgpt', 'claudebot', 'anthropic', 'bytespider', 'ccbot',
        'amazonbot', 'applebot', 'petalbot', 'dataforseo',
        'curl/', 'wget', 'python-requests', 'python-urllib', 'go-http',
        'libwww', 'httpclient', 'okhttp', 'postman',
        'insomnia', 'headlesschrome', 'phantomjs', 'puppeteer', 'playwright',
    ];

    /** @var array<string, string> */
    private const HOST_SOURCES = [
        'google.' => 'google',
        'bing.' => 'bing',
        'yahoo.' => 'yahoo',
        'duckduckgo.' => 'duckduckgo',
        'instagram.' => 'instagram',
        'facebook.' => 'facebook',
        'fb.com' => 'facebook',
        'fb.me' => 'facebook',
        't.co' => 'twitter',
        'twitter.' => 'twitter',
        'x.com' => 'twitter',
        'whatsapp.' => 'whatsapp',
        'wa.me' => 'whatsapp',
        'youtube.' => 'youtube',
        'youtu.be' => 'youtube',
        'linkedin.' => 'linkedin',
        'tiktok.' => 'tiktok',
        'pinterest.' => 'pinterest',
    ];

    public static function isBot(?string $userAgent): bool
    {
        $ua = strtolower(trim((string) $userAgent));
        if ($ua === '') {
            return true;
        }

        foreach (self::BOT_NEEDLES as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        // WhatsApp link-unfurl crawler has no Mozilla token; in-app browsers do.
        if (str_contains($ua, 'whatsapp') && ! str_contains($ua, 'mozilla')) {
            return true;
        }

        return false;
    }

    public static function classifySource(?string $utmSource, ?string $explicitSource, ?string $referrer, ?string $requestHost): string
    {
        $utm = strtolower(trim((string) $utmSource));
        if ($utm !== '' && $utm !== 'direct') {
            return self::normalizeSourceKey($utm);
        }

        $explicit = strtolower(trim((string) $explicitSource));
        if ($explicit !== '' && $explicit !== 'direct' && $explicit !== 'unknown') {
            return self::normalizeSourceKey($explicit);
        }

        $referrer = trim((string) $referrer);
        if ($referrer === '') {
            return 'direct';
        }

        $host = strtolower((string) (parse_url($referrer, PHP_URL_HOST) ?: ''));
        $host = preg_replace('/^www\./', '', $host) ?? $host;
        if ($host === '') {
            return 'direct';
        }

        $own = strtolower(trim((string) $requestHost));
        $own = preg_replace('/^www\./', '', $own) ?? $own;
        if ($own !== '' && ($host === $own || str_ends_with($host, '.'.$own))) {
            return 'direct';
        }

        foreach (self::HOST_SOURCES as $needle => $label) {
            if (str_contains($host, $needle)) {
                return $label;
            }
        }

        return 'referral';
    }

    public static function deviceFrom(?string $userAgent): string
    {
        $ua = strtolower((string) $userAgent);
        if ($ua === '') {
            return 'unknown';
        }
        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }
        if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) {
            return 'mobile';
        }

        return 'desktop';
    }

    public static function sourceLabel(string $key): string
    {
        $key = strtolower(trim($key));

        return match ($key) {
            'direct' => 'Direct',
            'google' => 'Google',
            'bing' => 'Bing',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'twitter' => 'X / Twitter',
            'whatsapp' => 'WhatsApp',
            'youtube' => 'YouTube',
            'linkedin' => 'LinkedIn',
            'tiktok' => 'TikTok',
            'pinterest' => 'Pinterest',
            'qr' => 'QR code',
            'embed' => 'Website embed',
            'email' => 'Email',
            'referral' => 'Other website',
            default => $key !== '' ? ucfirst(str_replace(['_', '-'], ' ', $key)) : 'Unknown',
        };
    }

    public static function clickLabel(string $key): string
    {
        $key = strtolower(trim($key));

        return match ($key) {
            'book' => 'Book now',
            'call' => 'Call',
            'whatsapp' => 'WhatsApp',
            'package' => 'Package',
            'service' => 'Service',
            'continue_booking' => 'Continue to booking',
            default => $key !== '' ? ucfirst(str_replace(['_', '-'], ' ', $key)) : 'Button',
        };
    }

    public static function normalizeClickName(string $raw): string
    {
        $key = strtolower(trim($raw));
        $key = preg_replace('/[^a-z0-9_-]+/', '', $key) ?? '';

        return substr($key, 0, 40);
    }

    public static function recordClick(Request $request, Salon $salon, string $name, array $overrides = []): void
    {
        $name = self::normalizeClickName($name);
        if ($name === '') {
            return;
        }

        $overrides['kind'] = self::KIND_CLICK;
        self::record($request, $salon, $name, $overrides);
    }

    public static function record(Request $request, Salon $salon, string $page, array $overrides = []): void
    {
        if ($request->isMethod('HEAD')) {
            return;
        }

        $ua = substr((string) ($overrides['user_agent'] ?? $request->userAgent() ?? ''), 0, 2000);
        $ip = (string) ($overrides['ip_address'] ?? $request->ip() ?? '');
        $isBot = array_key_exists('is_bot', $overrides)
            ? (bool) $overrides['is_bot']
            : self::isBot($ua);

        $kind = ($overrides['kind'] ?? self::KIND_VISIT) === self::KIND_CLICK
            ? self::KIND_CLICK
            : self::KIND_VISIT;
        $ttl = $kind === self::KIND_CLICK ? 5 : 30;

        $dedupeKey = 'web-traffic:'.$salon->id.':'.md5($kind.'|'.$ip.'|'.$page.'|'.($isBot ? '1' : '0'));
        if (! Cache::add($dedupeKey, 1, $ttl)) {
            return;
        }

        $referrer = (string) ($overrides['referrer'] ?? $request->header('Referer') ?? '');
        $utmSource = $overrides['utm_source'] ?? $request->query('utm_source');
        $explicit = $overrides['source'] ?? $request->query('src');
        $host = $request->getHost();

        $source = self::classifySource(
            is_string($utmSource) ? $utmSource : null,
            is_string($explicit) ? $explicit : null,
            $referrer !== '' ? $referrer : null,
            $host
        );

        try {
            LinkVisit::withoutGlobalScopes()->create([
                'salon_id' => $salon->id,
                'source' => $source,
                'page' => substr($page, 0, 100),
                'kind' => $kind,
                'ip_address' => $ip !== '' ? substr($ip, 0, 45) : null,
                'device' => self::deviceFrom($ua),
                'is_bot' => $isBot,
                'user_agent' => $ua !== '' ? $ua : null,
                'converted' => false,
                'utm_source' => is_string($utmSource) && $utmSource !== '' ? substr($utmSource, 0, 100) : null,
                'utm_medium' => self::nullableQuery($overrides['utm_medium'] ?? $request->query('utm_medium')),
                'utm_campaign' => self::nullableQuery($overrides['utm_campaign'] ?? $request->query('utm_campaign')),
                'referrer' => $referrer !== '' ? substr($referrer, 0, 500) : null,
            ]);
        } catch (Throwable) {
            // Tracking must never break the public website.
        }
    }

    private static function normalizeSourceKey(string $raw): string
    {
        $raw = strtolower(trim($raw));
        $raw = str_replace(['www.', 'http://', 'https://'], '', $raw);
        if (str_contains($raw, 'instagram')) {
            return 'instagram';
        }
        if (str_contains($raw, 'facebook') || $raw === 'fb') {
            return 'facebook';
        }
        if (str_contains($raw, 'whatsapp') || $raw === 'wa') {
            return 'whatsapp';
        }
        if ($raw === 'x' || str_contains($raw, 'twitter')) {
            return 'twitter';
        }

        return preg_replace('/[^a-z0-9_-]+/', '', $raw) ?: 'referral';
    }

    private static function nullableQuery(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }
        $value = trim($value);

        return $value !== '' ? substr($value, 0, 100) : null;
    }
}
