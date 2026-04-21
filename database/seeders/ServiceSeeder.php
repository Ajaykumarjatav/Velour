<?php

namespace Database\Seeders;

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

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();
        $staff = Staff::where('salon_id', $salon->id)->get()->keyBy('initials');

        $cats = ServiceCategory::where('salon_id', $salon->id)->get()->keyBy('slug');

        $services = [
            // ── HAIR COLOUR ───────────────────────────────────────────────
            ['category' => 'hair-colour', 'name' => 'Full Head Highlights',       'duration' => 180, 'price' => 195.00, 'price_from' => 175.00, 'buffer' => 15],
            ['category' => 'hair-colour', 'name' => 'Half Head Highlights',        'duration' => 120, 'price' => 145.00, 'buffer' => 15],
            ['category' => 'hair-colour', 'name' => 'Balayage',                    'duration' => 210, 'price' => 220.00, 'price_from' => 180.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 20],
            ['category' => 'hair-colour', 'name' => 'Ombré / Sombré',              'duration' => 180, 'price' => 200.00, 'price_from' => 170.00, 'buffer' => 15],
            ['category' => 'hair-colour', 'name' => 'Babylights',                  'duration' => 240, 'price' => 250.00, 'price_from' => 220.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 20],
            ['category' => 'hair-colour', 'name' => 'Root Tint',                   'duration' => 90,  'price' => 85.00,  'buffer' => 10],
            ['category' => 'hair-colour', 'name' => 'Root Tint + Blow Dry',        'duration' => 120, 'price' => 110.00, 'buffer' => 10],
            ['category' => 'hair-colour', 'name' => 'Global Colour (Short)',       'duration' => 90,  'price' => 90.00,  'buffer' => 10],
            ['category' => 'hair-colour', 'name' => 'Global Colour (Long)',        'duration' => 120, 'price' => 125.00, 'buffer' => 10],
            ['category' => 'hair-colour', 'name' => 'Toner',                       'duration' => 45,  'price' => 45.00,  'buffer' => 5],
            ['category' => 'hair-colour', 'name' => 'Colour Correction',           'duration' => 300, 'price' => 0,      'price_on_consultation' => true, 'buffer' => 30, 'deposit_type' => 'fixed', 'deposit_value' => 50],
            ['category' => 'hair-colour', 'name' => 'Gloss Treatment',             'duration' => 60,  'price' => 55.00,  'buffer' => 5],
            ['category' => 'hair-colour', 'name' => 'Men\'s Colour',              'duration' => 60,  'price' => 65.00,  'buffer' => 10],
            ['category' => 'hair-colour', 'name' => 'Highlights + Toner',          'duration' => 210, 'price' => 225.00, 'price_from' => 195.00, 'buffer' => 15],
            ['category' => 'hair-colour', 'name' => 'Balayage + Toner + Blowdry', 'duration' => 270, 'price' => 275.00, 'price_from' => 245.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 25],

            // ── CUTS & STYLING ────────────────────────────────────────────
            ['category' => 'cuts-styling', 'name' => 'Women\'s Haircut',          'duration' => 45,  'price' => 75.00,  'buffer' => 10],
            ['category' => 'cuts-styling', 'name' => 'Women\'s Cut + Blowdry',    'duration' => 75,  'price' => 95.00,  'buffer' => 10],
            ['category' => 'cuts-styling', 'name' => 'Men\'s Cut',                'duration' => 30,  'price' => 45.00,  'buffer' => 10],
            ['category' => 'cuts-styling', 'name' => 'Men\'s Cut + Style',        'duration' => 45,  'price' => 60.00,  'buffer' => 10],
            ['category' => 'cuts-styling', 'name' => 'Children\'s Cut (Under 12)','duration' => 30,  'price' => 35.00,  'buffer' => 5],
            ['category' => 'cuts-styling', 'name' => 'Blowdry (Short)',            'duration' => 30,  'price' => 45.00,  'buffer' => 5],
            ['category' => 'cuts-styling', 'name' => 'Blowdry (Medium)',           'duration' => 45,  'price' => 55.00,  'buffer' => 5],
            ['category' => 'cuts-styling', 'name' => 'Blowdry (Long)',             'duration' => 60,  'price' => 65.00,  'buffer' => 5],
            ['category' => 'cuts-styling', 'name' => 'Bridal Blowdry',             'duration' => 60,  'price' => 85.00,  'buffer' => 10],
            ['category' => 'cuts-styling', 'name' => 'Bridal Up-Do',               'duration' => 90,  'price' => 150.00, 'buffer' => 10, 'deposit_type' => 'percentage', 'deposit_value' => 30],
            ['category' => 'cuts-styling', 'name' => 'Trial Up-Do',                'duration' => 90,  'price' => 120.00, 'buffer' => 10],
            ['category' => 'cuts-styling', 'name' => 'Keratin Smoothing',          'duration' => 180, 'price' => 250.00, 'price_from' => 200.00, 'buffer' => 15],
            ['category' => 'cuts-styling', 'name' => 'Fringe Trim',                'duration' => 15,  'price' => 15.00,  'buffer' => 0],
            ['category' => 'cuts-styling', 'name' => 'Dry Cut',                    'duration' => 30,  'price' => 50.00,  'buffer' => 5],

            // ── TREATMENTS ────────────────────────────────────────────────
            ['category' => 'treatments', 'name' => 'Olaplex Treatment (In-Salon)', 'duration' => 60,  'price' => 55.00,  'buffer' => 5],
            ['category' => 'treatments', 'name' => 'K18 Leave-In Treatment',       'duration' => 30,  'price' => 45.00,  'buffer' => 5],
            ['category' => 'treatments', 'name' => 'Deep Conditioning Masque',     'duration' => 45,  'price' => 40.00,  'buffer' => 5],
            ['category' => 'treatments', 'name' => 'Scalp Treatment',              'duration' => 45,  'price' => 50.00,  'buffer' => 5],
            ['category' => 'treatments', 'name' => 'Bond Builder Add-On',          'duration' => 15,  'price' => 25.00,  'buffer' => 0],
            ['category' => 'treatments', 'name' => 'Glossing Treatment',           'duration' => 45,  'price' => 55.00,  'buffer' => 5],
            ['category' => 'treatments', 'name' => 'Brazilian Blowout',            'duration' => 120, 'price' => 180.00, 'buffer' => 15],
            ['category' => 'treatments', 'name' => 'Scalp Massage',                'duration' => 30,  'price' => 35.00,  'buffer' => 5],

            // ── NAILS ─────────────────────────────────────────────────────
            ['category' => 'nails', 'name' => 'Gel Manicure',                  'duration' => 60,  'price' => 55.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Classic Manicure',              'duration' => 45,  'price' => 40.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Gel Pedicure',                  'duration' => 75,  'price' => 65.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Classic Pedicure',              'duration' => 60,  'price' => 50.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Gel Removal',                   'duration' => 30,  'price' => 20.00,  'buffer' => 5],
            ['category' => 'nails', 'name' => 'Gel Removal + Gel Manicure',    'duration' => 90,  'price' => 65.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Nail Art (per nail)',            'duration' => 15,  'price' => 5.00,   'buffer' => 0],
            ['category' => 'nails', 'name' => 'Acrylic Full Set',               'duration' => 90,  'price' => 70.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Acrylic Infill',                 'duration' => 60,  'price' => 50.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Builder Gel (BIAB)',             'duration' => 75,  'price' => 65.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Mani + Pedi Package',            'duration' => 120, 'price' => 95.00,  'buffer' => 10],
            ['category' => 'nails', 'name' => 'Express Polish Change',          'duration' => 20,  'price' => 18.00,  'buffer' => 5],

            // ── SKIN & FACIALS ────────────────────────────────────────────
            ['category' => 'skin-facials', 'name' => 'Signature Facial (60 min)',   'duration' => 60,  'price' => 85.00,  'buffer' => 10],
            ['category' => 'skin-facials', 'name' => 'Express Facial (30 min)',     'duration' => 30,  'price' => 50.00,  'buffer' => 5],
            ['category' => 'skin-facials', 'name' => 'Deep Cleanse Facial',         'duration' => 75,  'price' => 100.00, 'buffer' => 10],
            ['category' => 'skin-facials', 'name' => 'Anti-Ageing Facial',          'duration' => 90,  'price' => 120.00, 'buffer' => 10],
            ['category' => 'skin-facials', 'name' => 'Microdermabrasion',           'duration' => 60,  'price' => 90.00,  'buffer' => 10],
            ['category' => 'skin-facials', 'name' => 'Chemical Peel (Light)',       'duration' => 45,  'price' => 80.00,  'buffer' => 10],
            ['category' => 'skin-facials', 'name' => 'Chemical Peel (Medium)',      'duration' => 60,  'price' => 110.00, 'buffer' => 10, 'deposit_type' => 'percentage', 'deposit_value' => 20],
            ['category' => 'skin-facials', 'name' => 'LED Light Therapy',           'duration' => 30,  'price' => 55.00,  'buffer' => 5],
            ['category' => 'skin-facials', 'name' => 'Skin Consultation',           'duration' => 30,  'price' => 0.00,   'buffer' => 0, 'price_on_consultation' => false],
            ['category' => 'skin-facials', 'name' => 'Décolletage & Neck Facial',   'duration' => 45,  'price' => 70.00,  'buffer' => 10],

            // ── BROWS & LASHES ────────────────────────────────────────────
            ['category' => 'brows-lashes', 'name' => 'Brow Shape (Wax)',          'duration' => 20,  'price' => 25.00,  'buffer' => 5],
            ['category' => 'brows-lashes', 'name' => 'Brow Shape (Thread)',       'duration' => 20,  'price' => 22.00,  'buffer' => 5],
            ['category' => 'brows-lashes', 'name' => 'Brow Lamination',           'duration' => 60,  'price' => 65.00,  'buffer' => 10],
            ['category' => 'brows-lashes', 'name' => 'Brow Tint',                 'duration' => 20,  'price' => 20.00,  'buffer' => 5],
            ['category' => 'brows-lashes', 'name' => 'Brow Lamination + Tint',    'duration' => 75,  'price' => 80.00,  'buffer' => 10],
            ['category' => 'brows-lashes', 'name' => 'Lash Lift',                 'duration' => 60,  'price' => 75.00,  'buffer' => 10],
            ['category' => 'brows-lashes', 'name' => 'Lash Lift + Tint',          'duration' => 75,  'price' => 90.00,  'buffer' => 10],
            ['category' => 'brows-lashes', 'name' => 'Lash Tint',                 'duration' => 20,  'price' => 22.00,  'buffer' => 5],
            ['category' => 'brows-lashes', 'name' => 'Henna Brows',               'duration' => 45,  'price' => 55.00,  'buffer' => 10],
            ['category' => 'brows-lashes', 'name' => 'Upper Lip Wax',             'duration' => 10,  'price' => 12.00,  'buffer' => 0],
            ['category' => 'brows-lashes', 'name' => 'Full Face Wax',             'duration' => 30,  'price' => 40.00,  'buffer' => 5],

            // ── MASSAGE ───────────────────────────────────────────────────
            ['category' => 'massage', 'name' => 'Swedish Massage (60 min)',     'duration' => 60,  'price' => 90.00,  'buffer' => 10],
            ['category' => 'massage', 'name' => 'Swedish Massage (90 min)',     'duration' => 90,  'price' => 125.00, 'buffer' => 10],
            ['category' => 'massage', 'name' => 'Deep Tissue Massage (60 min)', 'duration' => 60,  'price' => 100.00, 'buffer' => 10],
            ['category' => 'massage', 'name' => 'Deep Tissue Massage (90 min)', 'duration' => 90,  'price' => 135.00, 'buffer' => 10],
            ['category' => 'massage', 'name' => 'Hot Stone Massage (75 min)',   'duration' => 75,  'price' => 115.00, 'buffer' => 10],
            ['category' => 'massage', 'name' => 'Back, Neck & Shoulder (30 min)','duration' => 30, 'price' => 55.00,  'buffer' => 5],
            ['category' => 'massage', 'name' => 'Aromatherapy Massage (60 min)','duration' => 60,  'price' => 95.00,  'buffer' => 10],
            ['category' => 'massage', 'name' => 'Indian Head Massage (30 min)', 'duration' => 30,  'price' => 50.00,  'buffer' => 5],
            ['category' => 'massage', 'name' => 'Pregnancy Massage (60 min)',   'duration' => 60,  'price' => 100.00, 'buffer' => 10],
            ['category' => 'massage', 'name' => 'Reflexology (45 min)',         'duration' => 45,  'price' => 70.00,  'buffer' => 10],
            ['category' => 'massage', 'name' => 'Couples Massage (60 min)',     'duration' => 60,  'price' => 185.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 25],

            // ── PACKAGES ─────────────────────────────────────────────────
            ['category' => 'packages', 'name' => 'The Maison Experience',       'duration' => 300, 'price' => 350.00, 'price_from' => 320.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 30],
            ['category' => 'packages', 'name' => 'Colour & Glow Package',       'duration' => 240, 'price' => 280.00, 'price_from' => 250.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 25],
            ['category' => 'packages', 'name' => 'Bridal Pamper Day',           'duration' => 360, 'price' => 450.00, 'buffer' => 30, 'deposit_type' => 'percentage', 'deposit_value' => 50],
            ['category' => 'packages', 'name' => 'Mani Pedi & Facial',          'duration' => 150, 'price' => 160.00, 'buffer' => 10],
            ['category' => 'packages', 'name' => 'Lashes & Brows Duo',          'duration' => 90,  'price' => 105.00, 'buffer' => 10],
            ['category' => 'packages', 'name' => 'Relax & Restore',             'duration' => 180, 'price' => 220.00, 'buffer' => 15, 'deposit_type' => 'percentage', 'deposit_value' => 20],
            ['category' => 'packages', 'name' => 'Cut, Colour & Blow',          'duration' => 210, 'price' => 240.00, 'price_from' => 200.00, 'buffer' => 15],
            ['category' => 'packages', 'name' => 'Teen Pamper Package',         'duration' => 120, 'price' => 120.00, 'buffer' => 10],
        ];

        $sortOrders = array_fill_keys($cats->keys()->toArray(), 0);

        foreach ($services as $svc) {
            $cat = $cats[$svc['category']] ?? null;
            if (! $cat) continue;

            Service::create([
                'salon_id'              => $salon->id,
                'business_type_id'      => (int) $salon->business_type_id,
                'category_id'           => $cat->id,
                'name'                  => $svc['name'],
                'duration_minutes'      => $svc['duration'],
                'buffer_minutes'        => $svc['buffer'] ?? 10,
                'price'                 => $svc['price'],
                'price_from'            => $svc['price_from'] ?? null,
                'price_on_consultation' => $svc['price_on_consultation'] ?? false,
                'deposit_type'          => $svc['deposit_type'] ?? 'none',
                'deposit_value'         => $svc['deposit_value'] ?? 0,
                'online_bookable'       => true,
                'show_in_menu'          => true,
                'status'                => 'active',
                'sort_order'            => ++$sortOrders[$svc['category']],
            ]);
        }

        $this->command->info('   ✓  ' . count($services) . ' services created across 8 categories.');
    }
}
