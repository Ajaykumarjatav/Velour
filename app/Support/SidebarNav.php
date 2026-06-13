<?php

namespace App\Support;

use App\Models\User;

/**
 * Which sidebar links are shown for the current user (Spatie permissions only).
 */
final class SidebarNav
{
    public static function show(User $user, string $item): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return PermissionCatalog::userCanAccessModule($user, $item);
    }

    public static function showManageHeading(User $user): bool
    {
        foreach (['staff', 'services', 'service_packages', 'multi_location', 'availability', 'inventory'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return false;
    }

    public static function showGrowHeading(User $user): bool
    {
        foreach (['go_live', 'website_seo', 'customization', 'marketing', 'reviews', 'analytics', 'reports_menu'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return false;
    }

    public static function showAccountTeam(User $user): bool
    {
        return self::show($user, 'team')
            && ($user->ownsCurrentSalon() || $user->hasRole('tenant_admin'));
    }

    public static function showDeletedItems(User $user): bool
    {
        return \App\Support\DeletedItemsRegistry::userCanAccessTrash($user);
    }
}
