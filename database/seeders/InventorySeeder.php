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

class InventorySeeder extends Seeder
{
    public function run(): void
    {
        $salon = Salon::first();
        $cats  = InventoryCategory::where('salon_id', $salon->id)->get()->keyBy('slug');

        $products = [
            // ── HAIR COLOUR ────────────────────────────────────────────
            ['cat' => 'hair-colour', 'name' => 'Wella Koleston Perfect 6/0 Dark Blonde',   'sku' => 'WKP-060',  'supplier' => 'Wella Professional',  'unit' => '60ml tube', 'cost' => 5.20,  'retail' => 0,     'stock' => 24, 'min' => 10, 'reorder' => 12, 'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Wella Koleston Perfect 7/1 Medium Ash',     'sku' => 'WKP-071',  'supplier' => 'Wella Professional',  'unit' => '60ml tube', 'cost' => 5.20,  'retail' => 0,     'stock' => 18, 'min' => 10, 'reorder' => 12, 'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Wella Illumina Colour 8/1 Light Ash Blonde','sku' => 'WIC-081',  'supplier' => 'Wella Professional',  'unit' => '60ml tube', 'cost' => 6.50,  'retail' => 0,     'stock' => 20, 'min' => 8,  'reorder' => 10, 'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Goldwell Topchic 9N Extra Light Natural',   'sku' => 'GT-9N',    'supplier' => 'Goldwell',            'unit' => '60ml tube', 'cost' => 5.80,  'retail' => 0,     'stock' => 15, 'min' => 6,  'reorder' => 8,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'L\'Oréal INOA 6.0 Dark Blonde',             'sku' => 'LOI-060',  'supplier' => 'L\'Oréal Professional','unit' => '60g tube',  'cost' => 6.10,  'retail' => 0,     'stock' => 22, 'min' => 8,  'reorder' => 10, 'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Wella BlondorPlex Bleach 400g',              'sku' => 'WBP-400',  'supplier' => 'Wella Professional',  'unit' => '400g',      'cost' => 18.50, 'retail' => 0,     'stock' => 8,  'min' => 3,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Wella Blondor Freelights 400g',              'sku' => 'WBF-400',  'supplier' => 'Wella Professional',  'unit' => '400g',      'cost' => 19.00, 'retail' => 0,     'stock' => 6,  'min' => 3,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Wella Welloxon 6% Developer 1L',             'sku' => 'WOX-6-1L', 'supplier' => 'Wella Professional',  'unit' => '1 litre',   'cost' => 7.20,  'retail' => 0,     'stock' => 12, 'min' => 4,  'reorder' => 6,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Wella Welloxon 12% Developer 1L',            'sku' => 'WOX-12-1L','supplier' => 'Wella Professional',  'unit' => '1 litre',   'cost' => 7.20,  'retail' => 0,     'stock' => 10, 'min' => 4,  'reorder' => 6,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Goldwell ColorGlow Gloss Treatment',         'sku' => 'GCG-100',  'supplier' => 'Goldwell',            'unit' => '100ml',     'cost' => 12.00, 'retail' => 0,     'stock' => 9,  'min' => 3,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Kenra Platinum Silkening Mist Toner',        'sku' => 'KPT-SM',   'supplier' => 'Kenra Professional',  'unit' => '100ml',     'cost' => 9.50,  'retail' => 0,     'stock' => 7,  'min' => 3,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Colour Mixing Bowls (Pack of 10)',            'sku' => 'MXB-10',   'supplier' => 'Salon Supplies Ltd',  'unit' => 'Pack',      'cost' => 3.50,  'retail' => 0,     'stock' => 25, 'min' => 5,  'reorder' => 10, 'type' => 'professional'],
            ['cat' => 'hair-colour', 'name' => 'Colour Brushes (Pack of 5)',                  'sku' => 'CBR-5',    'supplier' => 'Salon Supplies Ltd',  'unit' => 'Pack',      'cost' => 4.20,  'retail' => 0,     'stock' => 14, 'min' => 4,  'reorder' => 5,  'type' => 'professional'],

            // ── HAIR CARE ──────────────────────────────────────────────
            ['cat' => 'hair-care', 'name' => 'Olaplex No.3 Hair Perfector 100ml',         'sku' => 'OL3-100',   'supplier' => 'Olaplex',             'unit' => '100ml',     'cost' => 16.00, 'retail' => 28.00, 'stock' => 20, 'min' => 6,  'reorder' => 8,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Olaplex No.4 Bond Maintenance Shampoo',     'sku' => 'OL4-250',   'supplier' => 'Olaplex',             'unit' => '250ml',     'cost' => 18.00, 'retail' => 30.00, 'stock' => 18, 'min' => 6,  'reorder' => 8,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Olaplex No.5 Bond Maintenance Conditioner', 'sku' => 'OL5-250',   'supplier' => 'Olaplex',             'unit' => '250ml',     'cost' => 18.00, 'retail' => 30.00, 'stock' => 16, 'min' => 6,  'reorder' => 8,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Olaplex No.7 Bonding Oil 30ml',             'sku' => 'OL7-30',    'supplier' => 'Olaplex',             'unit' => '30ml',      'cost' => 20.00, 'retail' => 32.00, 'stock' => 12, 'min' => 4,  'reorder' => 6,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Wella Professionals EIMI Thermal Image',    'sku' => 'WEITI-150', 'supplier' => 'Wella Professional',  'unit' => '150ml',     'cost' => 8.50,  'retail' => 17.50, 'stock' => 15, 'min' => 5,  'reorder' => 6,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'K18 Leave-In Molecular Repair Mask 50ml',   'sku' => 'K18-50',    'supplier' => 'K18',                 'unit' => '50ml',      'cost' => 38.00, 'retail' => 55.00, 'stock' => 10, 'min' => 3,  'reorder' => 4,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Goldwell Dualsenses Colour Shampoo 250ml',  'sku' => 'GDC-SH250', 'supplier' => 'Goldwell',            'unit' => '250ml',     'cost' => 9.00,  'retail' => 18.00, 'stock' => 22, 'min' => 6,  'reorder' => 8,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Goldwell Dualsenses Rich Repair Mask 200ml','sku' => 'GDR-MK200', 'supplier' => 'Goldwell',            'unit' => '200ml',     'cost' => 12.00, 'retail' => 22.00, 'stock' => 14, 'min' => 4,  'reorder' => 6,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Wella EIMI Extra Volume Mousse 300ml',      'sku' => 'WEVM-300',  'supplier' => 'Wella Professional',  'unit' => '300ml',     'cost' => 9.50,  'retail' => 18.00, 'stock' => 11, 'min' => 4,  'reorder' => 5,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Sebastian Professional Shaper Hairspray',   'sku' => 'SPS-400',   'supplier' => 'Sebastian',           'unit' => '400ml',     'cost' => 10.00, 'retail' => 20.00, 'stock' => 9,  'min' => 3,  'reorder' => 4,  'type' => 'both'],
            ['cat' => 'hair-care', 'name' => 'Moroccan Oil Treatment 100ml',              'sku' => 'MOT-100',   'supplier' => 'Moroccanoil',         'unit' => '100ml',     'cost' => 22.00, 'retail' => 38.00, 'stock' => 13, 'min' => 4,  'reorder' => 5,  'type' => 'both'],

            // ── NAIL PRODUCTS ──────────────────────────────────────────
            ['cat' => 'nail-products', 'name' => 'CND Shellac – Cashmere Wrap',      'sku' => 'CNDS-CW',  'supplier' => 'CND',              'unit' => '7.3ml',     'cost' => 8.50,  'retail' => 0,     'stock' => 6,  'min' => 2,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'CND Shellac – Glacial Mist',        'sku' => 'CNDS-GM',  'supplier' => 'CND',              'unit' => '7.3ml',     'cost' => 8.50,  'retail' => 0,     'stock' => 5,  'min' => 2,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'Gelish Top Coat 15ml',              'sku' => 'GLTC-15',  'supplier' => 'Gelish',           'unit' => '15ml',      'cost' => 11.00, 'retail' => 0,     'stock' => 8,  'min' => 3,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'Gelish Base Coat 15ml',             'sku' => 'GLBC-15',  'supplier' => 'Gelish',           'unit' => '15ml',      'cost' => 11.00, 'retail' => 0,     'stock' => 8,  'min' => 3,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'Akzentz Gel Polish – Nude Rose',    'sku' => 'AKZGP-NR', 'supplier' => 'Akzentz',          'unit' => '15ml',      'cost' => 9.00,  'retail' => 0,     'stock' => 4,  'min' => 2,  'reorder' => 3,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'Salon System Nail Wipes 200pk',     'sku' => 'SSNW-200', 'supplier' => 'Salon System',     'unit' => 'Pack',      'cost' => 5.00,  'retail' => 0,     'stock' => 10, 'min' => 3,  'reorder' => 5,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'Young Nails Acrylic Powder Clear',  'sku' => 'YNAP-C',   'supplier' => 'Young Nails',      'unit' => '85g',       'cost' => 14.00, 'retail' => 0,     'stock' => 5,  'min' => 2,  'reorder' => 3,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'Young Nails Acrylic Liquid 120ml',  'sku' => 'YNAL-120', 'supplier' => 'Young Nails',      'unit' => '120ml',     'cost' => 11.00, 'retail' => 0,     'stock' => 6,  'min' => 2,  'reorder' => 3,  'type' => 'professional'],
            ['cat' => 'nail-products', 'name' => 'IBX Nail Treatment System',         'sku' => 'IBX-KIT',  'supplier' => 'IBX',              'unit' => 'Kit',       'cost' => 45.00, 'retail' => 0,     'stock' => 3,  'min' => 1,  'reorder' => 2,  'type' => 'professional'],

            // ── SKINCARE ──────────────────────────────────────────────
            ['cat' => 'skincare', 'name' => 'Dermalogica Daily Microfoliant 74g',     'sku' => 'DDM-74',   'supplier' => 'Dermalogica',      'unit' => '74g',       'cost' => 28.00, 'retail' => 58.00, 'stock' => 10, 'min' => 3,  'reorder' => 4,  'type' => 'both'],
            ['cat' => 'skincare', 'name' => 'Dermalogica Skin Smoothing Cream 100ml', 'sku' => 'DSS-100',  'supplier' => 'Dermalogica',      'unit' => '100ml',     'cost' => 24.00, 'retail' => 52.00, 'stock' => 8,  'min' => 3,  'reorder' => 4,  'type' => 'both'],
            ['cat' => 'skincare', 'name' => 'Elemis Pro-Collagen Cleansing Balm',     'sku' => 'EPCB-100', 'supplier' => 'Elemis',           'unit' => '100g',      'cost' => 35.00, 'retail' => 55.00, 'stock' => 7,  'min' => 2,  'reorder' => 3,  'type' => 'both'],
            ['cat' => 'skincare', 'name' => 'Elemis Dynamic Resurfacing Serum 30ml',  'sku' => 'EDRS-30',  'supplier' => 'Elemis',           'unit' => '30ml',      'cost' => 40.00, 'retail' => 72.00, 'stock' => 6,  'min' => 2,  'reorder' => 3,  'type' => 'both'],
            ['cat' => 'skincare', 'name' => 'IMAGE MD Restoring Brightening Creme',   'sku' => 'IMRB-50',  'supplier' => 'IMAGE Skincare',   'unit' => '50g',       'cost' => 30.00, 'retail' => 65.00, 'stock' => 5,  'min' => 2,  'reorder' => 3,  'type' => 'both'],
            ['cat' => 'skincare', 'name' => 'Alumier EvenTone Brightening Serum',     'sku' => 'ABS-30',   'supplier' => 'AlumierMD',        'unit' => '30ml',      'cost' => 45.00, 'retail' => 88.00, 'stock' => 4,  'min' => 2,  'reorder' => 3,  'type' => 'both'],
            ['cat' => 'skincare', 'name' => 'Jan Marini SPF 33 Daily Face Protectant','sku' => 'JMSPF-57', 'supplier' => 'Jan Marini',       'unit' => '57g',       'cost' => 22.00, 'retail' => 45.00, 'stock' => 9,  'min' => 3,  'reorder' => 4,  'type' => 'both'],

            // ── CONSUMABLES ───────────────────────────────────────────
            ['cat' => 'consumables', 'name' => 'Foils (Pre-Cut) 500 pack',           'sku' => 'FOIL-500', 'supplier' => 'Salon Supplies Ltd', 'unit' => 'Pack',     'cost' => 6.50,  'retail' => 0,     'stock' => 15, 'min' => 4,  'reorder' => 6,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Latex-Free Gloves (Box of 100) – S', 'sku' => 'GLV-S100', 'supplier' => 'Salon Supplies Ltd', 'unit' => 'Box',      'cost' => 9.00,  'retail' => 0,     'stock' => 8,  'min' => 2,  'reorder' => 4,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Latex-Free Gloves (Box of 100) – M', 'sku' => 'GLV-M100', 'supplier' => 'Salon Supplies Ltd', 'unit' => 'Box',      'cost' => 9.00,  'retail' => 0,     'stock' => 10, 'min' => 3,  'reorder' => 5,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Cape (Black Nylon)',                  'sku' => 'CAPE-BLK', 'supplier' => 'Salon Supplies Ltd', 'unit' => 'Each',     'cost' => 4.00,  'retail' => 0,     'stock' => 20, 'min' => 5,  'reorder' => 5,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Neck Strips (Pack of 100)',           'sku' => 'NECK-100', 'supplier' => 'Salon Supplies Ltd', 'unit' => 'Pack',     'cost' => 3.50,  'retail' => 0,     'stock' => 18, 'min' => 5,  'reorder' => 6,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Cotton Wool Balls 150pk',             'sku' => 'COT-150',  'supplier' => 'Salon Supplies Ltd', 'unit' => 'Pack',     'cost' => 2.00,  'retail' => 0,     'stock' => 30, 'min' => 8,  'reorder' => 10, 'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Spatulas (Pack of 100)',              'sku' => 'SPAT-100', 'supplier' => 'Salon Supplies Ltd', 'unit' => 'Pack',     'cost' => 2.50,  'retail' => 0,     'stock' => 12, 'min' => 4,  'reorder' => 6,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Lint-Free Eye Pads (50pk)',           'sku' => 'EYEPAD-50','supplier' => 'Salon Supplies Ltd', 'unit' => 'Pack',     'cost' => 3.00,  'retail' => 0,     'stock' => 14, 'min' => 4,  'reorder' => 5,  'type' => 'professional'],
            ['cat' => 'consumables', 'name' => 'Velour Branded Tote Bags (pk 50)',    'sku' => 'VLR-BAG50','supplier' => 'Velour Internal',    'unit' => 'Pack',     'cost' => 22.00, 'retail' => 0,     'stock' => 4,  'min' => 1,  'reorder' => 2,  'type' => 'professional'],

            // ── RETAIL ────────────────────────────────────────────────
            ['cat' => 'retail', 'name' => 'Olaplex No.3 & No.4 Duo Gift Set',      'sku' => 'OL-DUO',   'supplier' => 'Olaplex',            'unit' => 'Set',      'cost' => 32.00, 'retail' => 55.00, 'stock' => 8,  'min' => 2,  'reorder' => 4,  'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Goldwell Hair Care Mini Set',            'sku' => 'GW-MINI',  'supplier' => 'Goldwell',           'unit' => 'Set',      'cost' => 20.00, 'retail' => 38.00, 'stock' => 6,  'min' => 2,  'reorder' => 3,  'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Velour Gift Voucher – £25',             'sku' => 'VGV-25',   'supplier' => 'Velour Internal',    'unit' => 'Each',     'cost' => 0,     'retail' => 25.00, 'stock' => 50, 'min' => 10, 'reorder' => 20, 'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Velour Gift Voucher – £50',             'sku' => 'VGV-50',   'supplier' => 'Velour Internal',    'unit' => 'Each',     'cost' => 0,     'retail' => 50.00, 'stock' => 50, 'min' => 10, 'reorder' => 20, 'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Velour Gift Voucher – £100',            'sku' => 'VGV-100',  'supplier' => 'Velour Internal',    'unit' => 'Each',     'cost' => 0,     'retail' => 100.00,'stock' => 30, 'min' => 5,  'reorder' => 10, 'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'K18 Molecular Repair Travel Kit',       'sku' => 'K18-TRVL', 'supplier' => 'K18',                'unit' => 'Kit',      'cost' => 25.00, 'retail' => 42.00, 'stock' => 7,  'min' => 2,  'reorder' => 3,  'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Elemis Pro-Collagen Marine Cream 50ml', 'sku' => 'EPC-50',   'supplier' => 'Elemis',             'unit' => '50ml',     'cost' => 42.00, 'retail' => 98.00, 'stock' => 5,  'min' => 2,  'reorder' => 3,  'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Moroccanoil Moisture Repair Shampoo',   'sku' => 'MOI-SH',   'supplier' => 'Moroccanoil',        'unit' => '200ml',    'cost' => 14.00, 'retail' => 26.00, 'stock' => 10, 'min' => 3,  'reorder' => 5,  'type' => 'retail'],
            ['cat' => 'retail', 'name' => 'Velour Silk Hair Wrap (Branded)',       'sku' => 'VLR-WRAP', 'supplier' => 'Velour Internal',    'unit' => 'Each',     'cost' => 8.00,  'retail' => 22.00, 'stock' => 15, 'min' => 4,  'reorder' => 6,  'type' => 'retail'],
        ];

        foreach ($products as $p) {
            $cat = $cats[$p['cat']] ?? null;
            if (! $cat) continue;

            InventoryItem::create([
                'salon_id'        => $salon->id,
                'category_id'     => $cat->id,
                'name'            => $p['name'],
                'sku'             => $p['sku'],
                'supplier'        => $p['supplier'],
                'unit'            => $p['unit'],
                'cost_price'      => $p['cost'],
                'retail_price'    => $p['retail'],
                'stock_quantity'  => $p['stock'],
                'min_stock_level' => $p['min'],
                'reorder_quantity'=> $p['reorder'],
                'type'            => $p['type'],
                'is_active'       => true,
            ]);
        }

        $this->command->info('   ✓  ' . count($products) . ' inventory products created.');
    }
}
