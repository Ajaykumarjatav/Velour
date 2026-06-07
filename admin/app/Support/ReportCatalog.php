<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth for salon report types (sidebar, index, routing).
 */
class ReportCatalog
{
    /**
     * @return list<array{
     *     key: string,
     *     label: string,
     *     description: string,
     *     icon: string,
     *     permission: string|null,
     *     date_filter: bool
     * }>
     */
    public static function definitions(): array
    {
        return [
            [
                'key' => 'revenue',
                'label' => 'Revenue',
                'description' => 'Track daily, weekly and monthly income',
                'icon' => '💰',
                'permission' => null,
                'date_filter' => true,
            ],
            [
                'key' => 'appointments',
                'label' => 'Appointments',
                'description' => 'Booking volumes and completion rates',
                'icon' => '📅',
                'permission' => null,
                'date_filter' => true,
            ],
            [
                'key' => 'staff',
                'label' => 'Staff',
                'description' => 'Performance, revenue and bookings per team member',
                'icon' => '👤',
                'permission' => null,
                'date_filter' => true,
            ],
            [
                'key' => 'clients',
                'label' => 'Clients',
                'description' => 'New vs returning, top spenders',
                'icon' => '🧑',
                'permission' => null,
                'date_filter' => true,
            ],
            [
                'key' => 'services',
                'label' => 'Services',
                'description' => 'Most popular and highest earning services',
                'icon' => '✂️',
                'permission' => null,
                'date_filter' => true,
            ],
            [
                'key' => 'inventory',
                'label' => 'Inventory',
                'description' => 'Stock levels, low-stock alerts and adjustments',
                'icon' => '📦',
                'permission' => 'inventory.view',
                'date_filter' => true,
            ],
            [
                'key' => 'marketing',
                'label' => 'Marketing',
                'description' => 'Campaign performance, opens, clicks and attributed revenue',
                'icon' => '📣',
                'permission' => 'marketing.view',
                'date_filter' => true,
            ],
        ];
    }

    /** @return list<string> */
    public static function keys(): array
    {
        return array_column(self::definitions(), 'key');
    }

    public static function exists(string $key): bool
    {
        return in_array($key, self::keys(), true);
    }

    /**
     * @return array{key: string, label: string, description: string, icon: string, permission: string|null, date_filter: bool}|null
     */
    public static function find(string $key): ?array
    {
        foreach (self::definitions() as $def) {
            if ($def['key'] === $key) {
                return $def;
            }
        }

        return null;
    }

    public static function visibleTo(User $user, array $def): bool
    {
        if (! $user->can('reports.view')) {
            return false;
        }

        $permission = $def['permission'] ?? null;

        return $permission === null || $user->can($permission);
    }

    /**
     * Reports the user may open (requires reports.view plus any module permission).
     *
     * @return list<array{key: string, label: string, description: string, icon: string, permission: string|null, date_filter: bool}>
     */
    public static function forUser(User $user): array
    {
        return array_values(array_filter(
            self::definitions(),
            fn (array $def) => self::visibleTo($user, $def)
        ));
    }
}
