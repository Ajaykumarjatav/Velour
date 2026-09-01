<?php

use App\Models\Staff;
use App\Support\StaffJobRoles;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Staff::withoutGlobalScopes()
            ->whereNotNull('user_id')
            ->whereNull('deleted_at')
            ->with('user')
            ->chunkById(100, function ($rows) {
                foreach ($rows as $staff) {
                    $user = $staff->user;
                    if ($user === null || $user->isSuperAdmin()) {
                        continue;
                    }

                    if ($user->salons()->where('id', $staff->salon_id)->exists()) {
                        continue;
                    }

                    $slug = StaffJobRoles::spatieRoleForJob($staff->role);
                    $user->syncRoles([$slug]);
                }
            });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function down(): void
    {
        // Role assignments are data; do not revert.
    }
};
