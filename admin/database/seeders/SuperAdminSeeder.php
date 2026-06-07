<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * SuperAdminSeeder
 *
 * Creates the initial super-admin account if it doesn't exist.
 * Credentials are pulled from environment variables — never hard-coded.
 *
 * Usage:
 *   php artisan db:seed --class=SuperAdminSeeder
 *
 * Env variables (add to .env):
 *   SUPER_ADMIN_NAME="Velour Admin"
 *   SUPER_ADMIN_EMAIL="admin@velour.app"
 *   SUPER_ADMIN_PASSWORD="change-me-immediately"
 */
class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SUPER_ADMIN_EMAIL', 'admin@velour.app');

        $user = User::withTrashed()->firstOrCreate(
            ['email' => $email],
            [
                'name'              => env('SUPER_ADMIN_NAME', 'Velour Admin'),
                'password'          => Hash::make(env('SUPER_ADMIN_PASSWORD', Str::random(32))),
                'system_role'       => 'super_admin',
                'is_active'         => true,
                'email_verified_at' => now(),
            ]
        );

        // Restore if soft-deleted
        if ($user->trashed()) {
            $user->restore();
        }

        // Ensure system_role is set even if record already existed
        $user->update(['system_role' => 'super_admin', 'is_active' => true]);

        // Assign Spatie super_admin role (skipped: permission package not compatible with L11)
        // $user->assignRole('super_admin');

        $this->command->info("✓ Super admin ready: {$user->email}");

        if (! env('SUPER_ADMIN_PASSWORD')) {
            $this->command->warn('  ⚠ No SUPER_ADMIN_PASSWORD set in .env — a random password was generated.');
            $this->command->warn('    Set SUPER_ADMIN_PASSWORD in .env and re-run this seeder.');
        }
    }
}
