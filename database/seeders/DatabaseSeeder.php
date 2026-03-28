<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the Velour demo data.
     *
     * Run:  php artisan db:seed
     * Or:   php artisan migrate:fresh --seed
     *
     * Demo login: demo@velour.app / password
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class, // 0a. RBAC roles + permissions
            SuperAdminSeeder::class,          // 0b. Platform super-admin account
            SalonSeeder::class,              // 1. Salon + owner user + settings + notifications
            StaffSeeder::class,              // 2. 6 staff members with schedules & commission rates
            ServiceCategorySeeder::class,    // 3. 8 service categories
            ServiceSeeder::class,            // 4. ~90 services with real UK pricing
            InventoryCategorySeeder::class,  // 5. 6 inventory categories
            InventorySeeder::class,          // 6. ~60 products with SKUs, suppliers & stock
            ClientSeeder::class,             // 7. 150 demo clients (20 VIP, with notes & formulas)
            AppointmentSeeder::class,        // 8. ~700 appointments (90 days past + 14 future)
            PosSeeder::class,                // 9. POS transactions for all completed appointments
            MarketingSeeder::class,          // 10. 8 demo campaigns in various states
            ReviewSeeder::class,             // 11. 60 reviews with owner replies
            HelpArticleSeeder::class,        // 12. AUDIT FIX: Help centre seed articles
        ]);

        $this->command->info('');
        $this->command->info('✅  Velour demo data seeded successfully!');
        $this->command->info('');
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Salon',          '1 (Maison Lumière)'],
                ['Staff',          '6 (with schedules & commission)'],
                ['Service Categories', '8'],
                ['Services',       '~90 (with real UK pricing)'],
                ['Inventory Products', '~60'],
                ['Clients',        '150 (20 VIP)'],
                ['Appointments',   '~700 (90 days past + 14 future)'],
                ['POS Transactions','Matching completed appointments'],
                ['Campaigns',      '8 (in various states)'],
                ['Reviews',        '60 (with replies)'],
            ]
        );
        $this->command->info('');
        $this->command->info('🔑  Login: demo@velour.app / password');
    }
}
