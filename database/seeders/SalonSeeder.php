<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use App\Models\Appointment;
use App\Models\AppointmentService;
use App\Models\Client;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\MarketingCampaign;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonNotification;
use App\Models\SalonSetting;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/* ═══════════════════════════════════════════════════════════════════════════
 * SalonSeeder
 * Creates: 1 owner user + Maison Lumière salon (matching frontend mock data)
 * ═══════════════════════════════════════════════════════════════════════════ */
class SalonSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::create([
            'name'     => 'Isabelle Laurent',
            'email'    => 'demo@velour.app',
            'password' => Hash::make('password'),
            'phone'    => '+44 7700 900456',
            'plan'     => 'pro',
            'is_active'=> true,
        ]);

        Salon::create([
            'owner_id'               => $owner->id,
            'business_type_id'       => BusinessType::defaultId(),
            'name'                   => 'Maison Lumière',
            'slug'                   => 'maison-lumiere',
            'description'            => 'A luxury boutique salon in the heart of Chelsea. Specialising in colour, balayage, and advanced treatments.',
            'phone'                  => '+44 20 7946 0123',
            'email'                  => 'hello@maisonlumiere.co.uk',
            'website'                => 'https://maisonlumiere.co.uk',
            'address_line1'          => '24 Beauchamp Place',
            'city'                   => 'London',
            'county'                 => 'Chelsea',
            'postcode'               => 'SW3 1NJ',
            'country'                => 'GB',
            'latitude'               => 51.4958,
            'longitude'              => -0.1643,
            'timezone'               => 'Europe/London',
            'currency'               => 'GBP',
            'locale'                 => 'en-GB',
            'booking_url'            => 'velour.app/maison-lumiere',
            'online_booking_enabled' => true,
            'new_client_booking_enabled' => true,
            'deposit_required'       => true,
            'deposit_percentage'     => 20.00,
            'instant_confirmation'   => false,
            'booking_advance_days'   => 60,
            'cancellation_hours'     => 24,
            'is_active'              => true,
            'opening_hours'          => [
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
        ]);

        $this->command->info('Salon created: Maison Lumière (slug: maison-lumiere)');
    }
}
