<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Support\RolePermissionDefaults;
use App\Support\SettingsTabPermissions;
use App\Support\StaffJobRoles;

/**
 * RolesAndPermissionsSeeder
 *
 * Creates the full RBAC hierarchy for EasyGrox.
 *
 * ┌─────────────────────────────────────────────────────────────────────────┐
 * │  Role          │  Guard  │  Scope         │  Description                │
 * ├─────────────────────────────────────────────────────────────────────────┤
 * │  super_admin   │  web    │  Platform-wide  │  Full access to everything  │
 * │  tenant_admin  │  web    │  Own salon      │  Owner-level salon access   │
 * │  manager       │  web    │  Own salon      │  Ops management, no billing │
 * │  stylist        │  web    │  Own salon      │  Own appts + clients only   │
 * │  receptionist  │  web    │  Own salon      │  Calendar + client bookings │
 * └─────────────────────────────────────────────────────────────────────────┘
 *
 * Permissions are grouped by resource:
 *   appointments.*  clients.*  staff.*  services.*
 *   inventory.*     pos.*      marketing.*  reports.*
 *   reviews.*       settings.* users.*
 */
class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles & permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Define all permissions ────────────────────────────────────────
        $permissions = [

            // Appointments
            'appointments.view',
            'appointments.view-all',        // view all staff's appointments
            'appointments.create',
            'appointments.edit',
            'appointments.delete',
            'appointments.update-status',

            // Clients
            'clients.view',
            'clients.create',
            'clients.edit',
            'clients.delete',
            'clients.view-notes',
            'clients.manage-notes',

            // Staff
            'staff.view',
            'staff.create',
            'staff.edit',
            'staff.delete',

            // Services
            'services.view',
            'services.create',
            'services.edit',
            'services.delete',

            // Inventory
            'inventory.view',
            'inventory.create',
            'inventory.edit',
            'inventory.delete',
            'inventory.adjust-stock',

            // Facilities (rooms, stations, areas)
            'facilities.view',
            'facilities.manage',

            // POS / Transactions
            'pos.view',
            'pos.create',
            'pos.refund',

            // Marketing
            'marketing.view',
            'marketing.create',
            'marketing.edit',
            'marketing.delete',
            'marketing.send',

            // Reports
            'reports.view',
            'reports.export',

            // Reviews
            'reviews.view',
            'reviews.reply',
            'reviews.delete',

            // Settings (general + per-tab)
            ...SettingsTabPermissions::allPermissionKeys(),

            // Users (user management within a salon)
            'users.view',
            'users.invite',
            'users.edit',
            'users.delete',

            // Billing (plan management)
            'billing.view',
            'billing.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // ── Create roles and assign permissions ───────────────────────────

        // Super Admin — gets everything (permissions are additive with wildcard)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());

        // Tenant Admin — full access within their own salon
        $tenantAdmin = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
        $tenantAdmin->syncPermissions(RolePermissionDefaults::admin());

        $rows = [['super_admin', Permission::count()], ['tenant_admin', $tenantAdmin->permissions()->count()]];

        foreach (StaffJobRoles::permissionRoleSlugs() as $slug) {
            if ($slug === 'tenant_admin') {
                continue;
            }
            $role = Role::firstOrCreate(['name' => $slug, 'guard_name' => 'web']);
            $role->syncPermissions(RolePermissionDefaults::forStoreRole($slug));
            $rows[] = [StaffJobRoles::label($slug), $role->permissions()->count()];
        }

        $this->command->info('✓ Roles and permissions seeded (all store job titles).');
        $this->command->table(['Role', 'Permissions'], $rows);
    }
}
