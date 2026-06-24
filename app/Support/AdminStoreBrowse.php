<?php

declare(strict_types=1);

namespace App\Support;

final class AdminStoreBrowse
{
    public const SESSION_KEY = 'admin_store_browse';

    /** @return array{salon_id: int, owner_id: int, salon_name: string}|null */
    public static function session(): ?array
    {
        $data = session(self::SESSION_KEY);

        if (! is_array($data) || empty($data['salon_id'])) {
            return null;
        }

        return [
            'salon_id'   => (int) $data['salon_id'],
            'owner_id'   => (int) ($data['owner_id'] ?? 0),
            'salon_name' => (string) ($data['salon_name'] ?? ''),
        ];
    }

    public static function isActive(): bool
    {
        return self::session() !== null;
    }

    public static function salonId(): ?int
    {
        return self::session()['salon_id'] ?? null;
    }

    public static function start(int $salonId, int $ownerId, string $salonName): void
    {
        session([
            self::SESSION_KEY => [
                'salon_id'   => $salonId,
                'owner_id'   => $ownerId,
                'salon_name' => $salonName,
            ],
            'active_salon_id' => $salonId,
        ]);
    }

    public static function clear(): void
    {
        session()->forget([self::SESSION_KEY, 'active_salon_id', 'switched_location_name']);
    }
}
