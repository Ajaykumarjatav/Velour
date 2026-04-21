<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Seed canonical business types (salon verticals).
     */
    public function run(): void
    {
        $types = [
            'Salon',
            'Spa',
            'Nail Studio',
            'Wellness Center',
            'Barbershop',
            'Massage Therapy Center',
            'Ayurvedic/Alternative Medicine Clinic',
            'Fitness & Yoga Studio',
            'Cosmetic & Aesthetic Clinic',
            'Tattoo & Piercing Studio',
        ];

        foreach ($types as $order => $name) {
            $slug = Str::slug($name);
            BusinessType::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'sort_order' => $order]
            );
        }
    }
}
