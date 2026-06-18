<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use Illuminate\Support\Str;

class ExpenseCategoryDefaults
{
    /** @var list<array{name: string, slug: string}> */
    public const DEFAULTS = [
        ['name' => 'Staff Salary', 'slug' => 'salary'],
        ['name' => 'Inventory & Stock', 'slug' => 'inventory'],
        ['name' => 'Rent', 'slug' => 'rent'],
        ['name' => 'Utilities', 'slug' => 'utilities'],
        ['name' => 'Marketing', 'slug' => 'marketing'],
        ['name' => 'Equipment', 'slug' => 'equipment'],
        ['name' => 'Maintenance & Repairs', 'slug' => 'maintenance'],
        ['name' => 'Other', 'slug' => 'other'],
    ];

    public static function ensureForSalon(int $salonId): void
    {
        $existing = ExpenseCategory::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->count();

        if ($existing > 0) {
            return;
        }

        foreach (self::DEFAULTS as $i => $row) {
            ExpenseCategory::withoutGlobalScopes()->create([
                'salon_id' => $salonId,
                'name' => $row['name'],
                'slug' => $row['slug'],
                'sort_order' => $i,
                'is_system' => true,
            ]);
        }
    }

    public static function createCustom(int $salonId, string $name): ExpenseCategory
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $n = 0;
        while (ExpenseCategory::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->where('slug', $slug)
            ->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        $sortOrder = (int) ExpenseCategory::withoutGlobalScopes()
            ->where('salon_id', $salonId)
            ->max('sort_order') + 1;

        return ExpenseCategory::withoutGlobalScopes()->create([
            'salon_id' => $salonId,
            'name' => $name,
            'slug' => $slug,
            'sort_order' => $sortOrder,
            'is_system' => false,
        ]);
    }
}
