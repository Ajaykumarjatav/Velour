<?php

namespace App\Support;

use App\Models\InventoryCategory;

/**
 * Ensures every salon / store has the standard retail & stock category set.
 * Safe to call multiple times (no-op when categories already exist).
 */
final class DefaultInventoryCatalog
{
    /**
     * @return list<array{name: string, slug: string, color: string, text_color: string}>
     */
    public static function categoryDefinitions(): array
    {
        return [
            ['name' => 'Hair Colour', 'slug' => 'hair-colour', 'color' => 'rgba(184,148,58,0.15)', 'text_color' => '#B8943A'],
            ['name' => 'Hair Care', 'slug' => 'hair-care', 'color' => 'rgba(196,85,107,0.15)', 'text_color' => '#C4556B'],
            ['name' => 'Nail Products', 'slug' => 'nail-products', 'color' => 'rgba(124,107,158,0.15)', 'text_color' => '#7C6B9E'],
            ['name' => 'Skincare', 'slug' => 'skincare', 'color' => 'rgba(90,138,114,0.15)', 'text_color' => '#5A8A72'],
            ['name' => 'Consumables', 'slug' => 'consumables', 'color' => 'rgba(217,119,6,0.15)', 'text_color' => '#D97706'],
            ['name' => 'Retail', 'slug' => 'retail', 'color' => 'rgba(5,150,105,0.15)', 'text_color' => '#059669'],
        ];
    }

    public static function ensureCategoriesForSalon(int $salonId): void
    {
        if ($salonId <= 0) {
            return;
        }

        $exists = InventoryCategory::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->exists();

        if ($exists) {
            return;
        }

        foreach (self::categoryDefinitions() as $i => $cat) {
            InventoryCategory::withoutGlobalScopes()->create(array_merge($cat, [
                'salon_id' => $salonId,
                'sort_order' => $i + 1,
            ]));
        }
    }
}
