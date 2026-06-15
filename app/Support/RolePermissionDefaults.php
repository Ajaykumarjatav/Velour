<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Default Spatie permission sets for each store job role.
 */
final class RolePermissionDefaults
{
    /** @return list<string> */
    public static function forStoreRole(string $roleSlug): array
    {
        $roleSlug = StaffJobRoles::normalize($roleSlug) ?? $roleSlug;

        return match ($roleSlug) {
            'tenant_admin' => self::admin(),
            'salon_manager' => self::manager(),
            'receptionist' => self::receptionist(),
            default => self::serviceProvider(),
        };
    }

    /** @return list<string> */
    public static function admin(): array
    {
        return [
            'appointments.view', 'appointments.view-all', 'appointments.create',
            'appointments.edit', 'appointments.delete', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'clients.view-notes', 'clients.manage-notes',
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'inventory.delete', 'inventory.adjust-stock',
            'facilities.view', 'facilities.manage',
            'pos.view', 'pos.create', 'pos.refund',
            'marketing.view', 'marketing.create', 'marketing.edit',
            'marketing.delete', 'marketing.send',
            'reports.view', 'reports.export',
            'reviews.view', 'reviews.reply', 'reviews.delete',
            'website.view', 'website.edit', 'website.share',
            'multi-location.view', 'multi-location.edit', 'multi-location.switch',
            'settings.view', 'settings.edit',
            ...SettingsTabPermissions::permissionsForTabs(array_keys(SettingsTabPermissions::TABS)),
            'users.view', 'users.invite', 'users.edit', 'users.delete',
            'billing.view', 'billing.manage',
        ];
    }

    /** @return list<string> */
    public static function manager(): array
    {
        return [
            'appointments.view', 'appointments.view-all', 'appointments.create',
            'appointments.edit', 'appointments.delete', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit',
            'clients.view-notes', 'clients.manage-notes',
            'staff.view', 'staff.edit',
            'services.view', 'services.create', 'services.edit',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust-stock',
            'facilities.view', 'facilities.manage',
            'pos.view', 'pos.create',
            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.send',
            'reports.view', 'reports.export',
            'reviews.view', 'reviews.reply',
            'website.view', 'website.edit', 'website.share',
            'multi-location.view', 'multi-location.edit', 'multi-location.switch',
            'settings.view',
            ...SettingsTabPermissions::permissionsForTabs([
                'salon', 'booking', 'services', 'hours', 'social', 'notifications', 'team',
            ]),
        ];
    }

    /** @return list<string> */
    public static function receptionist(): array
    {
        return [
            'appointments.view', 'appointments.view-all', 'appointments.create',
            'appointments.edit', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit', 'clients.view-notes',
            'services.view',
            'inventory.view',
            'facilities.view',
            'pos.view', 'pos.create',
            'reports.view',
            'reviews.view',
            ...SettingsTabPermissions::permissionsForTabs(['booking', 'notifications'], includeEdit: false),
            ...SettingsTabPermissions::permissionsForTabs(['profile', 'security']),
        ];
    }

    /** Hair stylist, colorist, barber, therapists, etc. */
    /** @return list<string> */
    public static function serviceProvider(): array
    {
        return [
            'appointments.view', 'appointments.create',
            'appointments.edit', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit',
            'clients.view-notes', 'clients.manage-notes',
            'services.view',
            'facilities.view',
            'pos.view', 'pos.create',
            'reviews.view',
            ...SettingsTabPermissions::permissionsForTabs(['profile', 'security']),
            ...SettingsTabPermissions::permissionsForTabs(['team'], includeEdit: false),
        ];
    }
}
