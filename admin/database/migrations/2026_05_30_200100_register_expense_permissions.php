<?php

use App\Support\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        PermissionCatalog::ensurePermissionsRegistered();
    }

    public function down(): void
    {
        // Permissions are shared; leave registered on rollback.
    }
};
