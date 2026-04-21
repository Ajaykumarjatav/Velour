<?php

namespace Database\Seeders;

use App\Models\Salon;
use App\Models\Staff;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class TestBookingSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Fix the salon ──────────────────────────────────────────────
        $salon = Salon::where('slug', 'ak-salon')->first();

        if (! $salon) {
            $this->command->error('Salon "ak-salon" not found. Please create it first.');
            return;
        }

        $salon->update([
            'online_booking_enabled'     => true,
            'new_client_booking_enabled' => true,
            'booking_advance_days'       => 90,
            'instant_confirmation'       => true,
            'deposit_required'           => false,
            'cancellation_hours'         => 24,
            'opening_hours' => [
                'Monday'    => ['open' => true,  'start' => '09:00', 'end' => '18:00'],
                'Tuesday'   => ['open' => true,  'start' => '09:00', 'end' => '18:00'],
                'Wednesday' => ['open' => true,  'start' => '09:00', 'end' => '18:00'],
                'Thursday'  => ['open' => true,  'start' => '09:00', 'end' => '18:00'],
                'Friday'    => ['open' => true,  'start' => '09:00', 'end' => '19:00'],
                'Saturday'  => ['open' => true,  'start' => '10:00', 'end' => '17:00'],
                'Sunday'    => ['open' => false, 'start' => '00:00', 'end' => '00:00'],
            ],
        ]);

        $this->command->info("✓ Salon '{$salon->name}' updated with opening hours.");

        // ── 2. Ensure service category exists ────────────────────────────
        $category = ServiceCategory::firstOrCreate(
            [
                'salon_id'         => $salon->id,
                'business_type_id' => $salon->business_type_id,
                'slug'             => 'hair-services',
            ],
            ['name' => 'Hair Services', 'sort_order' => 1, 'is_active' => true]
        );

        // ── 3. Ensure services exist and are bookable ─────────────────────
        $serviceData = [
            ['name' => 'Haircut & Styling',   'duration_minutes' => 45,  'price' => 40],
            ['name' => 'Hair Color',           'duration_minutes' => 90,  'price' => 80],
            ['name' => 'Balayage Highlights',  'duration_minutes' => 120, 'price' => 120],
            ['name' => 'Blow Dry & Waves',     'duration_minutes' => 30,  'price' => 25],
            ['name' => 'Keratin Treatment',    'duration_minutes' => 60,  'price' => 65],
        ];

        // Also fix the existing "Cutting" service if present
        Service::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->update([
                'status'          => 'active',
                'online_bookable' => true,
                'show_in_menu'    => true,
            ]);

        $serviceIds = [];
        foreach ($serviceData as $i => $svc) {
            $service = Service::withoutGlobalScopes()->firstOrCreate(
                ['salon_id' => $salon->id, 'name' => $svc['name']],
                [
                    'category_id'     => $category->id,
                    'description'     => 'Professional ' . $svc['name'] . ' service',
                    'duration_minutes'=> $svc['duration_minutes'],
                    'buffer_minutes'  => 10,
                    'price'           => $svc['price'],
                    'status'          => 'active',
                    'online_bookable' => true,
                    'show_in_menu'    => true,
                    'sort_order'      => $i + 1,
                ]
            );
            $serviceIds[] = $service->id;
        }

        // Include any existing services
        $allServiceIds = Service::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('status', 'active')
            ->pluck('id')
            ->toArray();

        $this->command->info("✓ " . count($allServiceIds) . " services ready.");

        // ── 4. Fix ALL existing staff + add new ones if needed ────────────
        $workingDays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        // Fix existing staff
        $existingStaff = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->get();

        foreach ($existingStaff as $staff) {
            $staff->update([
                'bookable_online' => true,
                'is_active'       => true,
                'working_days'    => $workingDays,
                'start_time'      => '09:00:00',
                'end_time'        => '18:00:00',
            ]);
            $staff->services()->sync($allServiceIds);
            $this->command->info("✓ Fixed existing staff: {$staff->first_name} {$staff->last_name}");
        }

        // Add demo staff if fewer than 2 bookable staff exist
        if ($existingStaff->count() < 2) {
            $newStaff = [
                ['first_name' => 'Priya', 'last_name' => 'Sharma', 'role' => 'stylist',   'color' => '#EC4899', 'end_time' => '18:00:00'],
                ['first_name' => 'Anika', 'last_name' => 'Reddy',  'role' => 'therapist', 'color' => '#8B5CF6', 'end_time' => '17:00:00'],
            ];

            foreach ($newStaff as $i => $member) {
                $email = strtolower($member['first_name'] . '.' . $member['last_name'] . '@salon.local');
                $staff = Staff::withoutGlobalScopes()->firstOrCreate(
                    ['salon_id' => $salon->id, 'email' => $email],
                    [
                        'first_name'      => $member['first_name'],
                        'last_name'       => $member['last_name'],
                        'role'            => $member['role'],
                        'initials'        => strtoupper($member['first_name'][0] . $member['last_name'][0]),
                        'color'           => $member['color'],
                        'is_active'       => true,
                        'bookable_online' => true,
                        'working_days'    => $workingDays,
                        'start_time'      => '09:00:00',
                        'end_time'        => $member['end_time'],
                        'sort_order'      => $existingStaff->count() + $i + 1,
                    ]
                );
                $staff->services()->sync($allServiceIds);
                $this->command->info("✓ Added staff: {$staff->first_name} {$staff->last_name}");
            }
        }

        $totalStaff = Staff::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('bookable_online', true)
            ->count();

        $this->command->newLine();
        $this->command->info('✅  Booking data seeded successfully!');
        $this->command->table(
            ['Item', 'Status'],
            [
                ['Salon opening hours',  'Set (Mon–Sat)'],
                ['Bookable services',    count($allServiceIds) . ' services'],
                ['Bookable staff',       $totalStaff . ' members'],
                ['Staff working days',   'Mon–Sat'],
                ['Service-staff links',  'Synced'],
            ]
        );
        $this->command->newLine();
        $this->command->info('Run: php artisan cache:clear');
    }
}
