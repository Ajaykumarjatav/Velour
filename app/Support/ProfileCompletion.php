<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;

class ProfileCompletion
{
    /**
     * @return array{
     *   percentage:int,
     *   has_business_type:bool,
     *   has_service_categories:bool,
     *   has_services:bool,
     *   has_staff:bool
     * }
     */
    public static function forSalon(Salon $salon): array
    {
        $hasBusinessType = $salon->businessTypes()->exists() || $salon->business_type_id !== null;
        $salonId = (int) $salon->id;
        $hasServiceCategories = ServiceCategory::withoutGlobalScopes()->where('salon_id', $salonId)->exists();
        $hasServices = Service::withoutGlobalScopes()->where('salon_id', $salonId)->exists();
        $hasStaff = Staff::withoutGlobalScopes()->where('salon_id', $salonId)->where('is_active', true)->exists();

        $completed = 0;
        foreach ([$hasBusinessType, $hasServiceCategories, $hasServices, $hasStaff] as $flag) {
            if ($flag) {
                $completed++;
            }
        }

        return [
            'percentage' => (int) round(($completed / 4) * 100),
            'has_business_type' => $hasBusinessType,
            'has_service_categories' => $hasServiceCategories,
            'has_services' => $hasServices,
            'has_staff' => $hasStaff,
        ];
    }
}

