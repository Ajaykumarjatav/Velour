<?php

namespace App\Support;

use App\Models\Salon;

class SalonBusinessStatus
{
    /**
     * @return array{
     *   name: string,
     *   is_live: bool,
     *   setup_percent: int,
     *   copy_url: string,
     *   setup_url: string
     * }
     */
    public static function forSalon(Salon $salon): array
    {
        $setup = SalonSetupProgress::forSalon($salon);

        return [
            'name'          => (string) $salon->name,
            'is_live'       => (bool) $salon->online_booking_enabled,
            'setup_percent' => $setup['percent'],
            'copy_url'      => StorefrontUrl::booking($salon),
            'setup_url'     => route('setup-progress'),
        ];
    }
}
