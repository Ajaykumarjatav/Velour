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

class ServiceCategorySeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();

        $categories = [
            ['name' => 'Hair Colour',    'slug' => 'hair-colour',   'icon' => '🎨', 'color' => 'rgba(184,148,58,0.15)',  'text_color' => '#B8943A', 'sort_order' => 1],
            ['name' => 'Cuts & Styling', 'slug' => 'cuts-styling',  'icon' => '✂️', 'color' => 'rgba(196,85,107,0.15)', 'text_color' => '#C4556B', 'sort_order' => 2],
            ['name' => 'Treatments',     'slug' => 'treatments',    'icon' => '💆', 'color' => 'rgba(90,138,114,0.15)', 'text_color' => '#5A8A72', 'sort_order' => 3],
            ['name' => 'Nails',          'slug' => 'nails',         'icon' => '💅', 'color' => 'rgba(124,107,158,0.15)','text_color' => '#7C6B9E', 'sort_order' => 4],
            ['name' => 'Skin & Facials', 'slug' => 'skin-facials',  'icon' => '🌿', 'color' => 'rgba(217,119,6,0.15)',  'text_color' => '#D97706', 'sort_order' => 5],
            ['name' => 'Brows & Lashes', 'slug' => 'brows-lashes',  'icon' => '👁️', 'color' => 'rgba(5,150,105,0.15)',  'text_color' => '#059669', 'sort_order' => 6],
            ['name' => 'Massage',        'slug' => 'massage',       'icon' => '🕯️', 'color' => 'rgba(59,130,246,0.15)', 'text_color' => '#3B82F6', 'sort_order' => 7],
            ['name' => 'Packages',       'slug' => 'packages',      'icon' => '🎁', 'color' => 'rgba(236,72,153,0.15)', 'text_color' => '#EC4899', 'sort_order' => 8],
        ];

        foreach ($categories as $cat) {
            ServiceCategory::create(array_merge($cat, ['salon_id' => $salon->id]));
        }

        $this->command->info('   ✓  8 service categories created.');
    }
}
