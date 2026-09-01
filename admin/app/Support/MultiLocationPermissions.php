<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Multi-store / branch management.
 */
final class MultiLocationPermissions
{
    public static function canView(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return true;
        }

        if ($user->hasExplicitPermission('multi-location.view') || $user->hasExplicitPermission('multi-location.edit')) {
            return true;
        }

        return SettingsTabPermissions::hasLegacyFullAccess($user)
            || $user->hasExplicitPermission('settings.business.view')
            || $user->hasExplicitPermission('settings.business.edit');
    }

    public static function canEdit(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return true;
        }

        if ($user->hasExplicitPermission('multi-location.edit')) {
            return true;
        }

        return SettingsTabPermissions::hasLegacyFullAccess($user)
            || $user->hasExplicitPermission('settings.business.edit');
    }
}
