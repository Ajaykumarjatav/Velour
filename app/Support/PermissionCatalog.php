<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Module ↔ permission mapping for navigation, route guards, and the Team permissions UI.
 */
final class PermissionCatalog
{
    /**
     * All store roles configurable in Team → permissions (Admin + every job title).
     *
     * @return array<string, string>
     */
    public static function permissionRoles(): array
    {
        return StaffJobRoles::permissionRoles();
    }

    /** @deprecated Use permissionRoles() */
    public const LOGIN_ROLES = []; // filled via permissionRoles() for BC

    /**
     * Sidebar / feature modules → minimum permission to see the module.
     * null = any authenticated salon user.
     *
     * @var array<string, string|null>
     */
    public const MODULE_VIEW_PERMISSION = [
        'dashboard' => null,
        'action_center' => 'appointments.view',
        'tasks' => 'appointments.view',
        'calendar' => 'appointments.view',
        'appointments' => 'appointments.view',
        'clients' => 'clients.view',
        'staff' => 'staff.view',
        'services' => 'services.view',
        'service_packages' => 'services.view',
        'multi_location' => 'settings.edit',
        'availability' => 'staff.view',
        'inventory' => 'inventory.view',
        'pos' => 'pos.view',
        'go_live' => 'settings.edit',
        'website_seo' => 'settings.edit',
        'customization' => 'settings.edit',
        'marketing' => 'marketing.view',
        'reviews' => 'reviews.view',
        'analytics' => 'reports.view',
        'reports_menu' => 'reports.view',
        'revenue' => 'reports.view',
        'growth_tips' => 'reports.view',
        'billing' => 'billing.view',
        'settings' => 'settings.view',
        'team' => 'users.view',
        'guide' => null,
        'notifications' => null,
        'security_support' => null,
        'support' => null,
        'deleted_items' => null,
    ];

    /**
     * Modules displayed in the Role permissions matrix (Team page).
     *
     * @var array<string, string>
     */
    public const FEATURE_MODULES = [
        'appointments' => 'Appointments',
        'clients' => 'Clients',
        'staff' => 'Staff',
        'services' => 'Services',
        'inventory' => 'Inventory',
        'pos' => 'POS / Sales',
        'marketing' => 'Marketing',
        'reports' => 'Reports',
        'reviews' => 'Reviews',
        'settings' => 'Settings',
        'users' => 'User Management',
        'billing' => 'Billing',
    ];

    /**
     * Which permission grants “view” access for each feature module in the matrix.
     *
     * @var array<string, string>
     */
    public const FEATURE_VIEW_PERMISSION = [
        'appointments' => 'appointments.view',
        'clients' => 'clients.view',
        'staff' => 'staff.view',
        'services' => 'services.view',
        'inventory' => 'inventory.view',
        'pos' => 'pos.view',
        'marketing' => 'marketing.view',
        'reports' => 'reports.view',
        'reviews' => 'reviews.view',
        'settings' => 'settings.view',
        'users' => 'users.view',
        'billing' => 'billing.view',
    ];

    /**
     * Permissions grouped by module for the editable matrix (action-level).
     *
     * @return array<string, array{label: string, permissions: list<array{key: string, label: string}>}>
     */
    public static function permissionGroups(): array
    {
        return [
            'appointments' => [
                'label' => 'Appointments',
                'permissions' => [
                    ['key' => 'appointments.view', 'label' => 'View'],
                    ['key' => 'appointments.view-all', 'label' => 'View all staff'],
                    ['key' => 'appointments.create', 'label' => 'Create'],
                    ['key' => 'appointments.edit', 'label' => 'Edit'],
                    ['key' => 'appointments.delete', 'label' => 'Delete'],
                    ['key' => 'appointments.update-status', 'label' => 'Update status'],
                ],
            ],
            'clients' => [
                'label' => 'Clients',
                'permissions' => [
                    ['key' => 'clients.view', 'label' => 'View'],
                    ['key' => 'clients.create', 'label' => 'Create'],
                    ['key' => 'clients.edit', 'label' => 'Edit'],
                    ['key' => 'clients.delete', 'label' => 'Delete'],
                    ['key' => 'clients.view-notes', 'label' => 'View notes'],
                    ['key' => 'clients.manage-notes', 'label' => 'Manage notes'],
                ],
            ],
            'staff' => [
                'label' => 'Staff',
                'permissions' => [
                    ['key' => 'staff.view', 'label' => 'View'],
                    ['key' => 'staff.create', 'label' => 'Create'],
                    ['key' => 'staff.edit', 'label' => 'Edit'],
                    ['key' => 'staff.delete', 'label' => 'Delete'],
                ],
            ],
            'services' => [
                'label' => 'Services',
                'permissions' => [
                    ['key' => 'services.view', 'label' => 'View'],
                    ['key' => 'services.create', 'label' => 'Create'],
                    ['key' => 'services.edit', 'label' => 'Edit'],
                    ['key' => 'services.delete', 'label' => 'Delete'],
                ],
            ],
            'inventory' => [
                'label' => 'Inventory',
                'permissions' => [
                    ['key' => 'inventory.view', 'label' => 'View'],
                    ['key' => 'inventory.create', 'label' => 'Create'],
                    ['key' => 'inventory.edit', 'label' => 'Edit'],
                    ['key' => 'inventory.delete', 'label' => 'Delete'],
                    ['key' => 'inventory.adjust-stock', 'label' => 'Adjust stock'],
                ],
            ],
            'pos' => [
                'label' => 'POS / Sales',
                'permissions' => [
                    ['key' => 'pos.view', 'label' => 'View'],
                    ['key' => 'pos.create', 'label' => 'Create'],
                    ['key' => 'pos.refund', 'label' => 'Refund'],
                ],
            ],
            'marketing' => [
                'label' => 'Marketing',
                'permissions' => [
                    ['key' => 'marketing.view', 'label' => 'View'],
                    ['key' => 'marketing.create', 'label' => 'Create'],
                    ['key' => 'marketing.edit', 'label' => 'Edit'],
                    ['key' => 'marketing.delete', 'label' => 'Delete'],
                    ['key' => 'marketing.send', 'label' => 'Send'],
                ],
            ],
            'reports' => [
                'label' => 'Reports',
                'permissions' => [
                    ['key' => 'reports.view', 'label' => 'View'],
                    ['key' => 'reports.export', 'label' => 'Export'],
                ],
            ],
            'reviews' => [
                'label' => 'Reviews',
                'permissions' => [
                    ['key' => 'reviews.view', 'label' => 'View'],
                    ['key' => 'reviews.reply', 'label' => 'Reply'],
                    ['key' => 'reviews.delete', 'label' => 'Delete'],
                ],
            ],
            'settings' => SettingsTabPermissions::unifiedPermissionCatalogGroup(),
            'users' => [
                'label' => 'User Management',
                'permissions' => [
                    ['key' => 'users.view', 'label' => 'View'],
                    ['key' => 'users.invite', 'label' => 'Invite'],
                    ['key' => 'users.edit', 'label' => 'Edit'],
                    ['key' => 'users.delete', 'label' => 'Delete'],
                ],
            ],
            'billing' => [
                'label' => 'Billing',
                'permissions' => [
                    ['key' => 'billing.view', 'label' => 'View'],
                    ['key' => 'billing.manage', 'label' => 'Manage'],
                ],
            ],
        ];
    }

    /** @return list<string> */
    public static function allPermissionKeys(): array
    {
        $keys = [];
        foreach (self::permissionGroups() as $group) {
            foreach ($group['permissions'] as $perm) {
                $keys[] = $perm['key'];
            }
        }

        return array_values(array_unique($keys));
    }

    /** Ensure every catalog permission exists in the database (Spatie requires rows before assign). */
    public static function ensurePermissionsRegistered(): void
    {
        foreach (self::allPermissionKeys() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public static function userCanAccessModule(User $user, string $moduleKey): bool
    {
        if ($user->isSuperAdmin() || $user->ownsCurrentSalon()) {
            return true;
        }

        if ($moduleKey === 'settings') {
            return SettingsTabPermissions::canOpenSettings($user);
        }

        $permission = self::MODULE_VIEW_PERMISSION[$moduleKey] ?? null;
        if ($permission === null) {
            return true;
        }

        return $user->hasPermissionTo($permission, 'web');
    }

    /**
     * Feature matrix for Team page: module × role (view access).
     *
     * @return array<string, array<string, bool>>
     */
    /**
     * @param  array<string, string>|null  $roleMap  slug => label; defaults to all store roles
     */
    public static function featureMatrixForRoles(?array $roleMap = null): array
    {
        $matrix = [];
        $roleMap = $roleMap ?? self::permissionRoles();
        $roles = Role::whereIn('name', array_keys($roleMap))->get()->keyBy('name');

        foreach (self::FEATURE_MODULES as $moduleKey => $label) {
            $viewPerm = self::FEATURE_VIEW_PERMISSION[$moduleKey] ?? null;
            $row = ['label' => $label];
            foreach ($roleMap as $roleName => $roleLabel) {
                $role = $roles->get($roleName);
                $row[$roleName] = $viewPerm && $role
                    ? $role->hasPermissionTo($viewPerm)
                    : false;
            }
            $matrix[$moduleKey] = $row;
        }

        return $matrix;
    }

    /** @return Collection<int, Permission> */
    public static function allPermissions(): Collection
    {
        return Permission::query()->where('guard_name', 'web')->orderBy('name')->get();
    }

    /** @return list<string> */
    public static function permissionKeysForRole(string $roleName): array
    {
        $role = Role::findByName($roleName, 'web');

        return $role->permissions->pluck('name')->sort()->values()->all();
    }
}
