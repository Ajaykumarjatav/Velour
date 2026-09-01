<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Salon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class SocialShareClicks
{
    /**
     * @return list<string>
     */
    public static function platforms(): array
    {
        return array_merge(array_keys(SocialLinkPlatforms::all()), ['email']);
    }

    public static function destination(Salon $salon, string $platform): ?string
    {
        $platform = strtolower($platform);

        if ($platform === 'email') {
            $email = trim((string) $salon->email);
            return $email !== '' ? 'mailto:'.$email : null;
        }

        if ($platform === 'whatsapp') {
            $fromLinks = self::linkUrl($salon, 'whatsapp');
            if ($fromLinks) {
                return $fromLinks;
            }
            $digits = preg_replace('/\D+/', '', (string) $salon->whatsappNumberForSite());

            return $digits !== '' ? 'https://wa.me/'.$digits : null;
        }

        return self::linkUrl($salon, $platform);
    }

    public static function record(int $salonId, string $platform, Request $request, ?int $userId = null): void
    {
        DB::table('social_share_clicks')->insert([
            'salon_id'   => $salonId,
            'user_id'    => $userId,
            'platform'   => $platform,
            'ip_address' => $request->ip(),
            'device'     => str_contains((string) $request->userAgent(), 'Mobile') ? 'mobile' : 'desktop',
            'clicked_at' => now(),
        ]);
    }

    /**
     * @return array<string, string>
     */
    public static function outUrls(Salon $salon): array
    {
        $urls = [];
        foreach (self::platforms() as $platform) {
            if (self::destination($salon, $platform)) {
                $urls[$platform] = route('storefront.social.out', [
                    'slug'     => $salon->slug,
                    'platform' => $platform,
                ]);
            }
        }

        return $urls;
    }

    public static function isSafeRedirect(string $url): bool
    {
        if (str_starts_with(strtolower($url), 'mailto:')) {
            return true;
        }

        $scheme = strtolower((string) (parse_url($url, PHP_URL_SCHEME) ?? ''));

        return in_array($scheme, ['http', 'https'], true);
    }

    private static function linkUrl(Salon $salon, string $platform): ?string
    {
        $links = is_array($salon->social_links) ? $salon->social_links : [];
        $url = trim((string) ($links[$platform] ?? ''));
        if ($url === '' || SocialLinkPlatforms::isPrefixOnly($url, $platform)) {
            return null;
        }
        if (! self::isSafeRedirect($url)) {
            return null;
        }

        return $url;
    }
}
