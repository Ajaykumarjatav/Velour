<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;
use App\Support\ProfileCompletion;

class SetupProgressController extends Controller
{
    use ResolvesActiveSalon;

    public function index()
    {
        $salon = $this->salon();
        $completion = ProfileCompletion::forSalon($salon);

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
                'done' => Service::withoutGlobalScopes()->where('salon_id', $salon->id)->where('status', 'active')->where('online_bookable', true)->exists(),
                'priority' => 'high',
                'link' => route('services.index'),
            ],
            [
                'key' => 'bookable_staff',
                'label' => 'Bookable staff available',
                'done' => Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->where('is_active', true)->where('bookable_online', true)->exists(),
                'priority' => 'medium',
                'link' => route('staff.index'),
            ],
        ];

        $completed = collect($items)->where('done', true)->count();
        $total = count($items);
        $percent = (int) round(($completed / max(1, $total)) * 100);

        return view('setup-progress.index', [
            'salon' => $salon,
            'items' => $items,
            'completed' => $completed,
            'total' => $total,
            'percent' => $percent,
        ]);
    }

    private function salon(): Salon
    {
        return $this->activeSalon();
    }
}

