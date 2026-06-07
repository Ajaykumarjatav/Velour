<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Salon;
use App\Models\User;

/**
 * Maps Settings UI tabs to Spatie permissions (view tab / edit & save).
 *
 * General:
 *   settings.view  — show Settings in navigation and open the settings area
 *   settings.edit  — legacy: view + edit ALL tabs
 *
 * Per tab:
 *   settings.{tab}.view — see that tab
 *   settings.{tab}.edit — save that tab
 */
final class SettingsTabPermissions
{
    /** @var array<string, array{label: string, view: string, edit: string}> */
    public const TABS = [
        'salon' => [
            'label' => 'Business',
            'view' => 'settings.business.view',
            'edit' => 'settings.business.edit',
        ],
        'booking' => [
            'label' => 'Booking',
            'view' => 'settings.booking.view',
            'edit' => 'settings.booking.edit',
        ],
        'services' => [
            'label' => 'Service',
            'view' => 'settings.services.view',
            'edit' => 'settings.services.edit',
        ],
        'hours' => [
            'label' => 'Hours',
            'view' => 'settings.hours.view',
            'edit' => 'settings.hours.edit',
        ],
        'social' => [
            'label' => 'Social Links',
            'view' => 'settings.social.view',
            'edit' => 'settings.social.edit',
        ],
        'notifications' => [
            'label' => 'Notifications',
            'view' => 'settings.notifications.view',
            'edit' => 'settings.notifications.edit',
        ],
        'profile' => [
            'label' => 'Profile',
            'view' => 'settings.profile.view',
            'edit' => 'settings.profile.edit',
        ],
        'team' => [
            'label' => 'Team',
            'view' => 'settings.team.view',
            'edit' => 'settings.team.edit',
        ],
        'security' => [
            'label' => 'Security',
            'view' => 'settings.security.view',
            'edit' => 'settings.security.edit',
        ],
    ];

    public static function viewPermission(string $tab): ?string
    {
        return self::TABS[$tab]['view'] ?? null;
    }

    public static function editPermission(string $tab): ?string
    {
        return self::TABS[$tab]['edit'] ?? null;
    }

    /** Legacy: full edit (and view) on every settings tab. */
    public static function hasLegacyFullAccess(User $user): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return true;
        }

        return $user->hasExplicitPermission('settings.edit');
    }

    /** True when the user's roles grant any settings.* permission (view, edit, or tab-level). */
    public static function userHasAnySettingsPermission(User $user): bool
    {
        $user->loadMissing('roles.permissions');

        foreach ($user->roles as $role) {
            foreach ($role->permissions as $permission) {
                if (str_starts_with($permission->name, 'settings.')) {
                    return true;
                }
            }
        }

        foreach ($user->getDirectPermissions() as $permission) {
            if (str_starts_with($permission->name, 'settings.')) {
                return true;
            }
        }

        return false;
    }

    /** Show Settings link / allow GET settings.index */
    public static function canOpenSettings(User $user, ?Salon $salon = null): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($salon !== null && $user->ownsSalonId((int) $salon->id)) {
            return true;
        }

        if ($user->ownsCurrentSalon()) {
            return true;
        }

        if (self::userHasAnySettingsPermission($user)) {
            return true;
        }

        return false;
    }

    /** @return list<string> */
    public static function allPermissionKeys(): array
    {
        $keys = ['settings.view', 'settings.edit'];
        foreach (self::TABS as $meta) {
            $keys[] = $meta['view'];
            $keys[] = $meta['edit'];
        }

        return array_values(array_unique($keys));
    }

    /** @return list<string> */
    public static function allViewPermissions(): array
    {
        return array_map(fn (array $m) => $m['view'], self::TABS);
    }

    /** @return list<string> */
    public static function allEditPermissions(): array
    {
        return array_map(fn (array $m) => $m['edit'], self::TABS);
    }

    /**
     * Single Settings block for Team → Permissions (all tabs in one horizontal row).
     *
     * @return array{label: string, layout: string, tabs: list<array{key: string, label: string, permissions: list<array{key: string, label: string}>}>, general: list<array{key: string, label: string}>, permissions: list<array{key: string, label: string}>}
     */
    public static function unifiedPermissionCatalogGroup(): array
    {
        $tabs = [];
        $permissions = [
            ['key' => 'settings.view', 'label' => 'Open settings'],
            ['key' => 'settings.edit', 'label' => 'All tabs (full access)'],
        ];
        $general = $permissions;

        foreach (self::TABS as $tabKey => $meta) {
            $tabPerms = [
                ['key' => $meta['view'], 'label' => 'View'],
                ['key' => $meta['edit'], 'label' => 'Edit'],
            ];
            $tabs[] = [
                'key' => $tabKey,
                'label' => $meta['label'],
                'permissions' => $tabPerms,
            ];
            foreach ($tabPerms as $perm) {
                $permissions[] = $perm;
            }
        }

        return [
            'label' => 'Settings',
            'layout' => 'tabs_row',
            'tabs' => $tabs,
            'general' => $general,
            'permissions' => $permissions,
        ];
    }

    /** @return list<string> */
    public static function visibleTabsForUser(User $user): array
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return array_keys(self::TABS);
        }

        if (self::hasLegacyFullAccess($user)) {
            return array_keys(self::TABS);
        }

        $visible = [];
        foreach (self::TABS as $tabKey => $meta) {
            if ($user->hasExplicitPermission($meta['view'])) {
                $visible[] = $tabKey;
            }
        }

        return $visible;
    }

    /** @deprecated Use canOpenSettings() */
    public static function userCanAccessAnyTab(User $user): bool
    {
        return self::canOpenSettings($user);
    }

    public static function userCanViewTab(User $user, string $tab): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return isset(self::TABS[$tab]);
        }

        if (self::hasLegacyFullAccess($user)) {
            return isset(self::TABS[$tab]);
        }

        $view = self::viewPermission($tab);

        return $view !== null && $user->hasExplicitPermission($view);
    }

    public static function userCanEditTab(User $user, string $tab): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return isset(self::TABS[$tab]);
        }

        if (self::hasLegacyFullAccess($user)) {
            return isset(self::TABS[$tab]);
        }

        $edit = self::editPermission($tab);

        return $edit !== null && $user->hasExplicitPermission($edit);
    }

    /** Whether the user may hit a named settings route. */
    public static function userCanAccessRoute(User $user, string $routeName): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return true;
        }

        return match ($routeName) {
            'settings.index' => self::canOpenSettings($user),
            'settings.salon' => self::userCanEditTab($user, 'salon'),
            'settings.booking', 'settings.buffer-rules' => self::userCanEditTab($user, 'booking'),
            'settings.services' => self::userCanEditTab($user, 'services'),
            'settings.hours' => self::userCanEditTab($user, 'hours'),
            'settings.social-links' => self::userCanEditTab($user, 'social'),
            'settings.notifications' => self::userCanEditTab($user, 'notifications'),
            'settings.profile' => self::userCanEditTab($user, 'profile'),
            'settings.team-members' => self::userCanEditTab($user, 'team'),
            'settings.password' => self::userCanEditTab($user, 'security'),
            default => str_starts_with($routeName, 'settings.') ? false : true,
        };
    }

    /**
     * @param  list<string>  $tabs
     * @return list<string>
     */
    public static function permissionsForTabs(array $tabs, bool $includeEdit = true): array
    {
        $keys = [];
        foreach ($tabs as $tab) {
            if (! isset(self::TABS[$tab])) {
                continue;
            }
            $keys[] = self::TABS[$tab]['view'];
            if ($includeEdit) {
                $keys[] = self::TABS[$tab]['edit'];
            }
        }

        return $keys;
    }
}
