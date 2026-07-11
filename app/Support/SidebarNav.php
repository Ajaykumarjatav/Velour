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

    public static function showGrowHeading(User $user): bool
    {
        return self::showGrowthGroup($user);
    }

    public static function showBusinessGroup(User $user): bool
    {
        foreach (['staff', 'services', 'service_packages', 'multi_location', 'availability', 'inventory', 'expenses', 'pos'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return false;
    }

    public static function showGrowthGroup(User $user): bool
    {
        foreach (['go_live', 'website_seo', 'customization', 'marketing', 'reviews', 'analytics', 'reports_menu', 'growth_tips'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return false;
    }

    public static function showAccountGroup(User $user): bool
    {
        if (config('billing.subscriptions_enabled') && self::show($user, 'billing')) {
            return true;
        }

        foreach (['settings', 'security_support', 'notifications', 'support', 'guide'] as $key) {
            if (self::show($user, $key)) {
                return true;
            }
        }

        return self::showDeletedItems($user);
    }

    public static function showAdminGroup(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if (self::showAccountTeam($user)) {
            return true;
        }

        return config('billing.subscriptions_enabled')
            && self::show($user, 'billing')
            && $user->salons()->exists();
    }

    public static function showManageHeading(User $user): bool
    {
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
