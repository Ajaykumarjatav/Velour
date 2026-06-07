<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\Salon;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/* ═══════════════════════════════════════════════════════════════════════════
 * SalonSeeder
 * Creates: 1 owner user + Maison Lumière salon (matching frontend mock data)
 * Safe to re-run: uses firstOrCreate — never deletes existing rows.
 * ═══════════════════════════════════════════════════════════════════════════ */
class SalonSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'demo@velour.app'],
            [
                'name'      => 'Isabelle Laurent',
                'password'  => Hash::make('password'),
                'phone'     => '+44 7700 900456',
                'plan'      => 'pro',
                'is_active' => true,
            ]
        );

        if (! $owner->hasRole('tenant_admin')) {
            $owner->assignRole('tenant_admin');
        }

        $salon = Salon::withoutGlobalScopes()->firstOrCreate(
            ['slug' => 'maison-lumiere'],
            [
                'owner_id'                   => $owner->id,
                'business_type_id'           => BusinessType::defaultId(),
                'name'                       => 'Maison Lumière',
                'description'                => 'A luxury boutique salon in the heart of Chelsea. Specialising in colour, balayage, and advanced treatments.',
                'phone'                      => '+44 20 7946 0123',
                'email'                      => 'hello@maisonlumiere.co.uk',
                'website'                    => 'https://maisonlumiere.co.uk',
                'address_line1'              => '24 Beauchamp Place',
                'city'                       => 'London',
                'county'                     => 'Chelsea',
                'postcode'                   => 'SW3 1NJ',
                'country'                    => 'GB',
                'latitude'                   => 51.4958,
                'longitude'                  => -0.1643,
                'timezone'                   => 'Europe/London',
                'currency'                   => 'GBP',
                'locale'                     => 'en-GB',
                'booking_url'                => 'velour.app/maison-lumiere',
                'online_booking_enabled'     => true,
                'new_client_booking_enabled' => true,
                'deposit_required'           => true,
                'deposit_percentage'         => 20.00,
                'instant_confirmation'       => false,
                'booking_advance_days'       => 60,
                'cancellation_hours'         => 24,
                'is_active'                  => true,
                'opening_hours'              => [
                    'Monday'    => ['open' => true,  'start' => '09:00', 'end' => '19:00'],
                    'Tuesday'   => ['open' => true,  'start' => '09:00', 'end' => '19:00'],
                    'Wednesday' => ['open' => true,  'start' => '09:00', 'end' => '20:00'],
                    'Thursday'  => ['open' => true,  'start' => '09:00', 'end' => '20:00'],
                    'Friday'    => ['open' => true,  'start' => '09:00', 'end' => '19:00'],
                    'Saturday'  => ['open' => true,  'start' => '09:00', 'end' => '18:00'],
                    'Sunday'    => ['open' => false, 'start' => '10:00', 'end' => '16:00'],
                ],
                'social_links' => [
                    'instagram' => 'https://instagram.com/maisonlumiere',
                    'facebook'  => 'https://facebook.com/maisonlumiere',
                    'tiktok'    => 'https://tiktok.com/@maisonlumiere',
                ],
            ]
        );

        if (! $salon->owner_id) {
            $salon->update(['owner_id' => $owner->id]);
        }

        $this->command->info(
            $salon->wasRecentlyCreated
                ? 'Salon created: Maison Lumière (slug: maison-lumiere)'
                : '   ↷ Demo salon already exists: Maison Lumière (slug: maison-lumiere)'
        );
    }
}
