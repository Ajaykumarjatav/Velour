<?php

use App\Models\Staff;
use App\Support\RolePermissionDefaults;
use App\Support\SettingsTabPermissions;
use App\Support\StaffJobRoles;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->ensureDefaultPermissionsExist();

        $legacyToStore = [
            'stylist' => 'hair_stylist',
            'manager' => 'salon_manager',
        ];

        foreach (StaffJobRoles::permissionRoleSlugs() as $slug) {
            $role = Role::firstOrCreate(['name' => $slug, 'guard_name' => 'web']);
            if ($role->permissions()->count() === 0) {
                $role->syncPermissions(RolePermissionDefaults::forStoreRole($slug));
            }
        }

        Staff::withoutGlobalScopes()
            ->whereNotNull('user_id')
            ->with('user')
            ->chunkById(100, function ($staffRows) use ($legacyToStore) {
                foreach ($staffRows as $staff) {
                    $user = $staff->user;
                    if (! $user) {
                        continue;
                    }

                    $slug = StaffJobRoles::normalize($staff->role) ?? 'hair_stylist';
                    $user->syncRoles([$slug]);
                }
            });

        foreach ($legacyToStore as $legacy => $store) {
            $legacyRole = Role::where('name', $legacy)->where('guard_name', 'web')->first();
            if (! $legacyRole) {
                continue;
            }
            $users = $legacyRole->users;
            foreach ($users as $user) {
                if ($user->staffProfile) {
                    $slug = StaffJobRoles::normalize($user->staffProfile->role) ?? $store;
                    $user->syncRoles([$slug]);
                } else {
                    $user->syncRoles([$store]);
                }
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Roles are shared; do not remove on rollback.
    }

    /** Create permission rows before syncPermissions (settings tab keys run in a later migration too). */
    private function ensureDefaultPermissionsExist(): void
    {
        $keys = [];

        foreach (SettingsTabPermissions::allPermissionKeys() as $name) {
            $keys[$name] = true;
        }

        foreach (StaffJobRoles::permissionRoleSlugs() as $slug) {
            foreach (RolePermissionDefaults::forStoreRole($slug) as $name) {
                $keys[$name] = true;
            }
        }

        foreach (array_keys($keys) as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
};
