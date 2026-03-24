<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Models\User;

/**
 * RolesAndPermissionsSeeder
 *
 * Creates the full RBAC hierarchy for Velour.
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

            // Settings
            'settings.view',
            'settings.edit',

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
        $tenantAdmin->syncPermissions([
            'appointments.view', 'appointments.view-all', 'appointments.create',
            'appointments.edit', 'appointments.delete', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit', 'clients.delete',
            'clients.view-notes', 'clients.manage-notes',
            'staff.view', 'staff.create', 'staff.edit', 'staff.delete',
            'services.view', 'services.create', 'services.edit', 'services.delete',
            'inventory.view', 'inventory.create', 'inventory.edit',
            'inventory.delete', 'inventory.adjust-stock',
            'pos.view', 'pos.create', 'pos.refund',
            'marketing.view', 'marketing.create', 'marketing.edit',
            'marketing.delete', 'marketing.send',
            'reports.view', 'reports.export',
            'reviews.view', 'reviews.reply', 'reviews.delete',
            'settings.view', 'settings.edit',
            'users.view', 'users.invite', 'users.edit', 'users.delete',
            'billing.view', 'billing.manage',
        ]);

        // Manager — full operations but no billing/user management
        $manager = Role::firstOrCreate(['name' => 'manager', 'guard_name' => 'web']);
        $manager->syncPermissions([
            'appointments.view', 'appointments.view-all', 'appointments.create',
            'appointments.edit', 'appointments.delete', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit',
            'clients.view-notes', 'clients.manage-notes',
            'staff.view', 'staff.edit',
            'services.view', 'services.create', 'services.edit',
            'inventory.view', 'inventory.create', 'inventory.edit', 'inventory.adjust-stock',
            'pos.view', 'pos.create',
            'marketing.view', 'marketing.create', 'marketing.edit', 'marketing.send',
            'reports.view', 'reports.export',
            'reviews.view', 'reviews.reply',
            'settings.view',
        ]);

        // Stylist — own appointments and clients only
        $stylist = Role::firstOrCreate(['name' => 'stylist', 'guard_name' => 'web']);
        $stylist->syncPermissions([
            'appointments.view', 'appointments.create',
            'appointments.edit', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit',
            'clients.view-notes', 'clients.manage-notes',
            'services.view',
            'pos.view', 'pos.create',
            'reviews.view',
        ]);

        // Receptionist — front-desk focus
        $receptionist = Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web']);
        $receptionist->syncPermissions([
            'appointments.view', 'appointments.view-all', 'appointments.create',
            'appointments.edit', 'appointments.update-status',
            'clients.view', 'clients.create', 'clients.edit', 'clients.view-notes',
            'services.view',
            'inventory.view',
            'pos.view', 'pos.create',
            'reports.view',
            'reviews.view',
        ]);

        $this->command->info('✓ Roles and permissions seeded.');
        $this->command->table(
            ['Role', 'Permissions'],
            [
                ['super_admin',   Permission::count()],
                ['tenant_admin',  $tenantAdmin->permissions()->count()],
                ['manager',       $manager->permissions()->count()],
                ['stylist',        $stylist->permissions()->count()],
                ['receptionist',  $receptionist->permissions()->count()],
            ]
        );
    }
}
