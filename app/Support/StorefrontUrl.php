<?php

namespace App\Support;

use App\Models\Salon;

final class StorefrontUrl
{
    public static function website(Salon $salon): string
    {
        $dev = config('storefront.dev_url');
        if ($dev && app()->environment('local')) {
            return rtrim((string) $dev, '/') . '/s/' . $salon->slug;
        }

        return rtrim(config('app.url'), '/') . '/s/' . $salon->slug;
    }

    /** In-site booking on the React storefront (#book), not the legacy /book/ blade. */
    public static function booking(Salon $salon): string
    {
        return self::website($salon) . '#book';
    }
}
