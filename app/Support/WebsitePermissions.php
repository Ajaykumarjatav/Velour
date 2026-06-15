<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Go Live, website SEO, customization / branding.
 */
final class WebsitePermissions
{
    public static function canView(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return true;
        }

        if ($user->hasExplicitPermission('website.view') || $user->hasExplicitPermission('website.edit')) {
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

        if ($user->hasExplicitPermission('website.edit')) {
            return true;
        }

        return SettingsTabPermissions::hasLegacyFullAccess($user)
            || $user->hasExplicitPermission('settings.business.edit');
    }
}
