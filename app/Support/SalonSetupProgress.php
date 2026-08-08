<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;

class SalonSetupProgress
{
    /**
     * Single source of truth for sidebar setup %, Setup Progress page, and Go-Live readiness.
     *
     * @return array{
     *   items: list<array{key: string, label: string, done: bool, priority: string, link: string, tip: string}>,
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
                'tip' => 'Choose your business type so services and booking match your salon.',
            ],
            [
                'key' => 'service_categories',
                'label' => 'Service categories configured',
                'done' => $completion['has_service_categories'],
                'priority' => 'high',
                'link' => route('settings.index', ['tab' => 'services']),
                'tip' => 'Organise services into categories for easier booking.',
            ],
            [
                'key' => 'services',
                'label' => 'At least one service added',
                'done' => $completion['has_services'],
                'priority' => 'high',
                'link' => route('settings.index', ['tab' => 'services']),
                'tip' => 'Add the services clients can book.',
            ],
            [
                'key' => 'staff',
                'label' => 'At least one active team member',
                'done' => $completion['has_staff'],
                'priority' => 'medium',
                'link' => route('settings.index', ['tab' => 'profile']),
                'tip' => 'Add staff so appointments can be assigned.',
            ],
            [
                'key' => 'bookable_service',
                'label' => 'Online-bookable service enabled',
                'done' => Service::withoutGlobalScopes()->where('salon_id', $salonId)->where('status', 'active')->where('online_bookable', true)->exists(),
                'priority' => 'high',
                'link' => route('services.index'),
                'tip' => 'Enable online booking on at least one service.',
            ],
            [
                'key' => 'bookable_staff',
                'label' => 'Bookable staff available',
                'done' => Staff::withoutGlobalScopes()->where('salon_id', $salonId)->where('is_active', true)->where('bookable_online', true)->exists(),
                'priority' => 'medium',
                'link' => route('staff.index'),
                'tip' => 'Toggle bookable online in each staff profile.',
            ],
            [
                'key' => 'address',
                'label' => 'Address set',
                'done' => (bool) $salon->address_line1,
                'priority' => 'high',
                'link' => route('settings.index'),
                'tip' => 'Clients need to know where you are.',
            ],
            [
                'key' => 'phone',
                'label' => 'Phone number added',
                'done' => (bool) $salon->phone,
                'priority' => 'high',
                'link' => route('settings.index'),
                'tip' => 'Required for booking confirmations.',
            ],
            [
                'key' => 'hours',
                'label' => 'Opening hours set',
                'done' => ! empty($salon->opening_hours),
                'priority' => 'high',
                'link' => route('settings.index'),
                'tip' => 'Without hours, no booking slots appear.',
            ],
            [
                'key' => 'logo',
                'label' => 'Logo uploaded',
                'done' => (bool) $salon->logo,
                'priority' => 'medium',
                'link' => route('settings.index'),
                'tip' => 'Makes your booking page look professional.',
            ],
            [
                'key' => 'desc',
                'label' => 'Salon description added',
                'done' => (bool) $salon->description,
                'priority' => 'medium',
                'link' => route('settings.index'),
                'tip' => 'Helps new clients choose your salon.',
            ],
            [
                'key' => 'stripe',
                'label' => 'Online payments linked',
                'done' => (bool) $salon->stripe_account_id,
                'priority' => 'low',
                'link' => route('settings.index'),
                'tip' => 'Required to take deposits or online payments.',
            ],
        ];

        $completed = (int) collect($items)->where('done', true)->count();
        $total = count($items);
        $percent = (int) round(($completed / max(1, $total)) * 100);

        return compact('items', 'completed', 'total', 'percent');
    }

    /**
     * Shape used by Go Live page + share/checklist API.
     *
     * @return array{
     *   items: list<array{key: string, label: string, done: bool, priority: string, link: string, tip: string}>,
     *   done: int,
     *   total: int,
     *   score: int,
     *   ready: bool,
     *   booking_live: bool
     * }
     */
    public static function checklistForSalon(Salon $salon): array
    {
        $progress = self::forSalon($salon);
        $highDone = collect($progress['items'])
            ->where('priority', 'high')
            ->every(fn (array $item) => $item['done']);

        return [
            'items' => $progress['items'],
            'done' => $progress['completed'],
            'total' => $progress['total'],
            'score' => $progress['percent'],
            'ready' => $highDone || $progress['percent'] >= 75,
            'booking_live' => (bool) $salon->online_booking_enabled,
        ];
    }
}
