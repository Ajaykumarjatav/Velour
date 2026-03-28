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

class InventoryCategorySeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();

        $categories = [
            ['name' => 'Hair Colour',    'slug' => 'hair-colour',    'color' => 'rgba(184,148,58,0.15)',  'text_color' => '#B8943A'],
            ['name' => 'Hair Care',      'slug' => 'hair-care',      'color' => 'rgba(196,85,107,0.15)', 'text_color' => '#C4556B'],
            ['name' => 'Nail Products',  'slug' => 'nail-products',  'color' => 'rgba(124,107,158,0.15)','text_color' => '#7C6B9E'],
            ['name' => 'Skincare',       'slug' => 'skincare',       'color' => 'rgba(90,138,114,0.15)', 'text_color' => '#5A8A72'],
            ['name' => 'Consumables',    'slug' => 'consumables',    'color' => 'rgba(217,119,6,0.15)',  'text_color' => '#D97706'],
            ['name' => 'Retail',         'slug' => 'retail',         'color' => 'rgba(5,150,105,0.15)',  'text_color' => '#059669'],
        ];

        foreach ($categories as $i => $cat) {
            InventoryCategory::create(array_merge($cat, ['salon_id' => $salon->id, 'sort_order' => $i + 1]));
        }

        $this->command->info('   ✓  6 inventory categories created.');
    }
}
