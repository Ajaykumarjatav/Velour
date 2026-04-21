<?php

namespace App\Support;

use App\Models\BusinessType;
use App\Models\Salon;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Support\Str;

class RegistrationStarterServices
{
    /**
     * @param  list<int>  $typeIds
     * @return list<string>  composite keys "slug:category_slug"
     */
    public static function allowedCategoryKeysForTypeIds(array $typeIds): array
    {
        if ($typeIds === []) {
            return [];
        }

        $types = BusinessType::query()->whereIn('id', $typeIds)->get();
        $keys  = [];

        foreach ($types as $t) {
            $seen = [];
            foreach (config('registration_starter_services.' . $t->slug, []) as $row) {
                $catSlug = trim((string) ($row['category_slug'] ?? Str::slug((string) ($row['category'] ?? 'General'))));
                if ($catSlug === '') {
                    $catSlug = 'general';
                }
                $composite = $t->slug . ':' . $catSlug;
                if (isset($seen[$composite])) {
                    continue;
                }
                $seen[$composite] = true;
                $keys[]           = $composite;
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Create service categories from registration picks (no services required).
     *
     * @param  list<string>  $categoryKeys  e.g. "salon:haircuts-styling"
     */
    public static function seedStarterCategories(Salon $salon, array $categoryKeys): void
    {
        if ($categoryKeys === []) {
            return;
        }

        foreach ($categoryKeys as $composite) {
            if (! is_string($composite) || ! str_contains($composite, ':')) {
                continue;
            }

            [$slug, $catSlug] = explode(':', $composite, 2);
            $rows             = config('registration_starter_services.' . $slug, []);
            $catName          = null;

            foreach ($rows as $row) {
                $cs = trim((string) ($row['category_slug'] ?? Str::slug((string) ($row['category'] ?? 'General'))));
                if ($cs === '') {
                    $cs = 'general';
                }
                if ($cs === $catSlug) {
                    $catName = trim((string) ($row['category'] ?? 'General'));
                    break;
                }
            }

            if ($catName === null || $catName === '') {
                $catName = 'General';
            }

            $typeId = (int) (BusinessType::query()->where('slug', $slug)->value('id') ?? 0);
            if ($typeId < 1) {
                continue;
            }

            if (! $salon->businessTypes()->where('business_types.id', $typeId)->exists()) {
                continue;
            }

            $sortOrder = (int) ServiceCategory::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('business_type_id', $typeId)
                ->max('sort_order');

            ServiceCategory::withoutGlobalScopes()->firstOrCreate(
                [
                    'salon_id'         => $salon->id,
                    'business_type_id' => $typeId,
                    'slug'             => $catSlug,
                ],
                [
                    'name'       => $catName,
                    'sort_order' => $sortOrder + 1,
                    'is_active'  => true,
                ]
            );
        }
    }

    /**
     * @param  list<int>  $typeIds
     * @return list<string>  composite keys "slug:service_key"
     */
    public static function allowedKeysForTypeIds(array $typeIds): array
    {
        if ($typeIds === []) {
            return [];
        }

        $types = BusinessType::query()->whereIn('id', $typeIds)->get();
        $keys    = [];

        foreach ($types as $t) {
            foreach (config('registration_starter_services.' . $t->slug, []) as $row) {
                if (! empty($row['key'])) {
                    $keys[] = $t->slug . ':' . $row['key'];
                }
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * @param  list<string>  $compositeKeys  e.g. "salon:cut-blowdry"
     */
    public static function seedSalon(Salon $salon, array $compositeKeys): void
    {
        if ($compositeKeys === []) {
            return;
        }

        $sort = (int) Service::withoutGlobalScopes()->where('salon_id', $salon->id)->max('sort_order');

        foreach ($compositeKeys as $composite) {
            if (! is_string($composite) || ! str_contains($composite, ':')) {
                continue;
            }

            [$slug, $svcKey] = explode(':', $composite, 2);
            $rows = config('registration_starter_services.' . $slug, []);
            $def  = collect($rows)->firstWhere('key', $svcKey);
            if (! is_array($def)) {
                continue;
            }

            $typeId = (int) (BusinessType::query()->where('slug', $slug)->value('id') ?? 0);
            if ($typeId < 1) {
                continue;
            }

            if (! $salon->businessTypes()->where('business_types.id', $typeId)->exists()) {
                continue;
            }

            $categoryName = trim((string) ($def['category'] ?? 'General'));
            if ($categoryName === '') {
                $categoryName = 'General';
            }
            $categorySlug = trim((string) ($def['category_slug'] ?? Str::slug($categoryName)));
            if ($categorySlug === '') {
                $categorySlug = 'general';
            }
            $sortOrder = (int) ServiceCategory::withoutGlobalScopes()
                ->where('salon_id', $salon->id)
                ->where('business_type_id', $typeId)
                ->max('sort_order');

            $defaultCategory = ServiceCategory::withoutGlobalScopes()->firstOrCreate(
                [
                    'salon_id'         => $salon->id,
                    'business_type_id' => $typeId,
                    'slug'             => $categorySlug,
                ],
                [
                    'name'       => $categoryName,
                    'sort_order' => $sortOrder + 1,
                    'is_active'  => true,
                ]
            );

            $name = (string) $def['name'];
            $base = Str::slug($name) ?: 'service';
            $slugOut = $base;
            $n = 0;
            while (Service::withoutGlobalScopes()->where('salon_id', $salon->id)->where('slug', $slugOut)->exists()) {
                $slugOut = $base . '-' . (++$n);
            }

            Service::create([
                'salon_id'           => $salon->id,
                'business_type_id'   => $typeId,
                'category_id'        => $defaultCategory->id,
                'name'               => $name,
                'slug'               => $slugOut,
                'duration_minutes'   => (int) $def['duration_minutes'],
                'buffer_minutes'     => (int) ($def['buffer_minutes'] ?? 10),
                'price'              => (float) $def['price'],
                'deposit_type'       => 'none',
                'deposit_value'      => 0,
                'online_bookable'    => true,
                'show_in_menu'       => true,
                'status'             => 'active',
                'sort_order'         => ++$sort,
            ]);
        }
    }
}
