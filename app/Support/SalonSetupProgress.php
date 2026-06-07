<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;

class SalonSetupProgress
{
    /**
     * @return array{
     *   items: list<array{key: string, label: string, done: bool, priority: string, link: string}>,
     *   completed: int,
     *   total: int,
     *   percent: int
     * }
     */
    public static function forSalon(Salon $salon): array
    {
        $completion = ProfileCompletion::forSalon($salon);
        $salonId = (int) $salon->id;

        $items = [
            [
                'key' => 'business_type',
                'label' => 'Business type selected',
                'done' => $completion['has_business_type'],
                'priority' => 'high',
                'link' => route('settings.index', ['tab' => 'services']),
            ],
            [
                'key' => 'service_categories',
                'label' => 'Service categories configured',
                'done' => $completion['has_service_categories'],
                'priority' => 'high',
                'link' => route('settings.index', ['tab' => 'services']),
            ],
            [
                'key' => 'services',
                'label' => 'At least one service added',
                'done' => $completion['has_services'],
                'priority' => 'high',
                'link' => route('settings.index', ['tab' => 'services']),
            ],
            [
                'key' => 'staff',
                'label' => 'At least one active team member',
                'done' => $completion['has_staff'],
                'priority' => 'medium',
                'link' => route('settings.index', ['tab' => 'profile']),
            ],
            [
                'key' => 'bookable_service',
                'label' => 'Online-bookable service enabled',
                'done' => Service::withoutGlobalScopes()->where('salon_id', $salonId)->where('status', 'active')->where('online_bookable', true)->exists(),
                'priority' => 'high',
                'link' => route('services.index'),
            ],
            [
                'key' => 'bookable_staff',
                'label' => 'Bookable staff available',
                'done' => Staff::withoutGlobalScopes()->where('salon_id', $salonId)->where('is_active', true)->where('bookable_online', true)->exists(),
                'priority' => 'medium',
                'link' => route('staff.index'),
            ],
        ];

        $completed = (int) collect($items)->where('done', true)->count();
        $total = count($items);
        $percent = (int) round(($completed / max(1, $total)) * 100);

        return compact('items', 'completed', 'total', 'percent');
    }
}
