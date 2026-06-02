<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $source = Role::query()->where('name', 'hair_stylist')->where('guard_name', 'web')->first();
        $target = Role::query()->where('name', 'salon_manager')->where('guard_name', 'web')->first();

        if (! $source || ! $target) {
            return;
        }

        $settingsPermissions = $source->permissions
            ->filter(fn ($p) => str_starts_with($p->name, 'settings.'))
            ->pluck('name')
            ->all();

        if ($settingsPermissions !== []) {
            $target->givePermissionTo($settingsPermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Intentionally leave salon_manager settings as configured.
    }
};
