<?php

use App\Support\SettingsTabPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (SettingsTabPermissions::allPermissionKeys() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $this->grantTabPermissionsToRolesWith('settings.view', SettingsTabPermissions::allViewPermissions());
        $this->grantTabPermissionsToRolesWith('settings.edit', SettingsTabPermissions::allEditPermissions());

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /** @param list<string> $tabPermissions */
    private function grantTabPermissionsToRolesWith(string $legacyPermission, array $tabPermissions): void
    {
        $legacyId = Permission::query()->where('name', $legacyPermission)->value('id');
        if (! $legacyId) {
            return;
        }

        $roleIds = DB::table('role_has_permissions')
            ->where('permission_id', $legacyId)
            ->pluck('role_id');

        $permIds = Permission::query()
            ->whereIn('name', $tabPermissions)
            ->pluck('id');

        foreach ($roleIds as $roleId) {
            foreach ($permIds as $permId) {
                DB::table('role_has_permissions')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permId,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Permissions are shared; keep on rollback.
    }
};
