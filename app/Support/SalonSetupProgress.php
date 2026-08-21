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
     *   items: list<array{key: string, label: string, done: bool, priority: string, link: string, tip: string, focus: string}>,
     *   completed: int,
     *   total: int,
     *   percent: int
     * }
     */
    public static function forSalon(Salon $salon): array
    {
        $completion = ProfileCompletion::forSalon($salon);
        $salonId = (int) $salon->id;
        $store = SalonUrl::key($salon);
        $settings = fn (array $extra = []) => route('settings.index', array_merge(['store' => $store], $extra));

        $items = [
            [
                'key' => 'business_type',
                'label' => 'Business type selected',
                'done' => $completion['has_business_type'],
                'priority' => 'high',
                'link' => self::urlWithFocus($settings(['tab' => 'services']), 'settings-business-types-list'),
                'focus' => 'settings-business-types-list',
                'tip' => 'Choose your business type so services and booking match your salon.',
            ],
            [
                'key' => 'service_categories',
                'label' => 'Service categories configured',
                'done' => $completion['has_service_categories'],
                'priority' => 'high',
                'link' => self::urlWithFocus($settings(['tab' => 'services']), 'settings-service-categories-list'),
                'focus' => 'settings-service-categories-list',
                'tip' => 'Organise services into categories for easier booking.',
            ],
            [
                'key' => 'services',
                'label' => 'At least one service added',
                'done' => $completion['has_services'],
                'priority' => 'high',
                'link' => self::urlWithFocus($settings(['tab' => 'services']), 'settings-service-offers-list'),
                'focus' => 'settings-service-offers-list',
                'tip' => 'Add the services clients can book.',
            ],
            [
                'key' => 'staff',
                'label' => 'At least one active team member',
                'done' => $completion['has_staff'],
                'priority' => 'medium',
                'link' => self::urlWithFocus($settings(['tab' => 'team']), 'settings-staff-rows'),
                'focus' => 'settings-staff-rows',
                'tip' => 'Add staff so appointments can be assigned.',
            ],
            [
                'key' => 'bookable_service',
                'label' => 'Online-bookable service enabled',
                'done' => Service::withoutGlobalScopes()->where('salon_id', $salonId)->where('status', 'active')->where('online_bookable', true)->exists(),
                'priority' => 'high',
                'link' => self::urlWithFocus(route('services.create', ['store' => $store]), 'service-online-booking'),
                'focus' => 'service-online-booking',
                'tip' => 'Enable online booking on at least one service.',
            ],
            [
                'key' => 'bookable_staff',
                'label' => 'Bookable staff available',
                'done' => Staff::withoutGlobalScopes()->where('salon_id', $salonId)->where('is_active', true)->where('bookable_online', true)->exists(),
                'priority' => 'medium',
                'link' => self::urlWithFocus($settings(['tab' => 'team']), 'settings-staff-rows'),
                'focus' => 'settings-staff-rows',
                'tip' => 'Add a team member (bookable online is enabled automatically from Settings → Team).',
            ],
            [
                'key' => 'address',
                'label' => 'Address set',
                'done' => (bool) $salon->address_line1,
                'priority' => 'high',
                'link' => self::urlWithFocus($settings(['tab' => 'salon']), 'settings-salon-address'),
                'focus' => 'settings-salon-address',
                'tip' => 'Clients need to know where you are.',
            ],
            [
                'key' => 'phone',
                'label' => 'Phone number added',
                'done' => (bool) $salon->phone,
                'priority' => 'high',
                'link' => self::urlWithFocus($settings(['tab' => 'salon']), 'settings-salon-phone'),
                'focus' => 'settings-salon-phone',
                'tip' => 'Required for booking confirmations.',
            ],
            [
                'key' => 'hours',
                'label' => 'Opening hours set',
                'done' => ! empty($salon->opening_hours),
                'priority' => 'high',
                'link' => self::urlWithFocus($settings(['tab' => 'hours']), 'settings-hours-form'),
                'focus' => 'settings-hours-form',
                'tip' => 'Without hours, no booking slots appear.',
            ],
        ];

        $completed = (int) collect($items)->where('done', true)->count();
        $total = count($items);
        $percent = (int) round(($completed / max(1, $total)) * 100);

        return compact('items', 'completed', 'total', 'percent');
    }

    /**
     * First incomplete checklist item in setup order, or null when setup is complete.
     *
     * @return array{key: string, label: string, done: bool, priority: string, link: string, tip: string, focus: string}|null
     */
    public static function nextIncomplete(Salon $salon): ?array
    {
        foreach (self::forSalon($salon)['items'] as $item) {
            if (! $item['done']) {
                return $item;
            }
        }

        return null;
    }

    /**
     * Redirect URL for the next incomplete step, with setup_focus for the blink cue.
     */
    public static function nextIncompleteUrl(Salon $salon): ?string
    {
        $item = self::nextIncomplete($salon);
        if (! $item) {
            return null;
        }

        return self::urlWithFocus($item['link'], $item['focus'] ?? $item['key']);
    }

    public static function urlWithFocus(string $url, string $focus): string
    {
        $focus = trim($focus);
        if ($focus === '') {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $query['setup_focus'] = $focus;

        $rebuilt = '';
        if (isset($parts['scheme'], $parts['host'])) {
            $rebuilt .= $parts['scheme'].'://'.$parts['host'];
            if (isset($parts['port'])) {
                $rebuilt .= ':'.$parts['port'];
            }
        }
        $rebuilt .= $parts['path'] ?? '';
        $qs = http_build_query($query);
        if ($qs !== '') {
            $rebuilt .= '?'.$qs;
        }
        if (! empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt !== '' ? $rebuilt : $url;
    }

    /**
     * Shape used by Go Live page + share/checklist API.
     *
     * @return array{
     *   items: list<array{key: string, label: string, done: bool, priority: string, link: string, tip: string, focus: string}>,
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
