<?php

namespace Database\Seeders;

use App\Models\BusinessType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessTypeSeeder extends Seeder
{
    /**
     * Seed canonical business types (salon verticals).
     */
    public function run(): void
    {
        $types = [
            ['name' => "Women's", 'slug' => 'womens'],
            ['name' => "Man's", 'slug' => 'mans'],
            ['name' => 'Unisex', 'slug' => 'unisex'],
            ['name' => 'Pet', 'slug' => 'pet'],
        ];

        foreach ($types as $order => $row) {
            BusinessType::updateOrCreate(
                ['slug' => $row['slug']],
                ['name' => $row['name'], 'sort_order' => $order]
            );
        }

        $desiredSlugs = array_column($types, 'slug');
        $fallbackId = (int) BusinessType::query()->where('slug', 'unisex')->value('id');
        $legacyIds = BusinessType::query()
            ->whereNotIn('slug', $desiredSlugs)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($fallbackId > 0 && $legacyIds !== []) {
            DB::table('salons')
                ->whereIn('business_type_id', $legacyIds)
                ->update(['business_type_id' => $fallbackId]);

            DB::table('service_categories')
                ->whereIn('business_type_id', $legacyIds)
                ->update(['business_type_id' => $fallbackId]);

            DB::table('services')
                ->whereIn('business_type_id', $legacyIds)
                ->update(['business_type_id' => $fallbackId]);

            $salonIds = DB::table('salon_business_types')
                ->whereIn('business_type_id', $legacyIds)
                ->pluck('salon_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($salonIds as $salonId) {
                DB::table('salon_business_types')->updateOrInsert(
                    ['salon_id' => $salonId, 'business_type_id' => $fallbackId],
                    ['created_at' => now(), 'updated_at' => now()]
                );
            }

            DB::table('salon_business_types')
                ->whereIn('business_type_id', $legacyIds)
                ->delete();

            BusinessType::query()->whereIn('id', $legacyIds)->delete();
        }
    }
}
