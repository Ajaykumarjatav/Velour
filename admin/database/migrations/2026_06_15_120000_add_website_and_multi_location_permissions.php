<?php

use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    private const NEW_KEYS = [
        'website.view',
        'website.edit',
        'website.share',
        'multi-location.view',
        'multi-location.edit',
        'multi-location.switch',
    ];

    public function up(): void
    {
        PermissionCatalog::ensurePermissionsRegistered();

        $permissions = collect(self::NEW_KEYS)
            ->map(fn (string $name) => Permission::findByName($name, 'web'));

        $tenantAdmin = Role::query()->where('name', 'tenant_admin')->where('guard_name', 'web')->first();
        if ($tenantAdmin) {
            $tenantAdmin->givePermissionTo($permissions);
        }

        $legacyBusinessEdit = Permission::query()
            ->where('name', 'settings.business.edit')
            ->where('guard_name', 'web')
            ->first();

        if ($legacyBusinessEdit) {
            Role::query()
                ->where('guard_name', 'web')
                ->whereHas('permissions', fn ($q) => $q->where('name', 'settings.business.edit'))
                ->with('permissions')
                ->get()
                ->each(function (Role $role) use ($permissions): void {
                    $role->givePermissionTo($permissions);
                });
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
