<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Models\Review;
use App\Models\Salon;
use App\Models\SalonPhoto;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServicePackage;
use App\Models\Staff;
use App\Scopes\TenantScope;
use App\Support\StaffJobRoles;
use App\Support\StorefrontTheme;
use App\Support\StorefrontUrl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SalonWebsitePayloadService
{
    public function build(Salon $salon): array
    {
        $salonId = $salon->id;
        $baseUrl = rtrim(config('app.url'), '/');

        $services = Service::withoutGlobalScope(TenantScope::class)
            ->with(['category:id,name,sort_order'])
            ->where('salon_id', $salonId)
            ->where('status', 'active')
            ->where('show_in_menu', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'category_id', 'name', 'description', 'duration_minutes', 'price', 'image', 'sort_order']);

        $categories = $this->groupServices($services, $salon->currency ?? 'GBP', $salonId);

        $staff = Staff::withoutGlobalScope(TenantScope::class)
            ->with(['services' => fn ($q) => $q->withoutGlobalScope(TenantScope::class)
                ->where('services.salon_id', $salonId)
                ->where('status', 'active')
                ->orderBy('sort_order')
                ->orderBy('name')])
            ->where('salon_id', $salonId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('first_name')
            ->get(['id', 'first_name', 'last_name', 'role', 'bio', 'specialisms', 'avatar', 'color', 'initials']);

        $reviews = Review::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->where('is_public', true)
            ->orderByDesc('created_at')
            ->limit(12)
            ->get(['rating', 'comment', 'reviewer_name', 'created_at']);

        $packages = ServicePackage::withoutGlobalScope(TenantScope::class)
            ->with(['services' => fn ($q) => $q->withoutGlobalScope(TenantScope::class)->where('services.salon_id', $salonId)])
            ->where('salon_id', $salonId)
            ->where('status', 'active')
            ->orderBy('sort_order')
            ->get();

        $photos = SalonPhoto::where('salon_id', $salonId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($p) => asset('storage/' . $p->path))
            ->values()
            ->all();

        $reviewCount = Review::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->where('is_public', true)
            ->count();
        $avgRating = (float) Review::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->where('is_public', true)
            ->avg('rating');

        $currency = $salon->currency ?? 'GBP';

        return [
            'salon' => [
                'id'                  => $salon->id,
                'name'                => $salon->name,
                'slug'                => $salon->slug,
                'description'         => $salon->description,
                'tagline'             => $salon->description
                    ? (strlen($salon->description) > 120 ? substr($salon->description, 0, 117) . '…' : $salon->description)
                    : 'Premium hair, skin, and grooming services tailored for all genders.',
                'phone'               => $salon->phone,
                'email'               => $salon->email,
                'address_line1'       => $salon->address_line1,
                'address_line2'       => $salon->address_line2,
                'city'                => $salon->city,
                'postcode'            => $salon->postcode,
                'full_address'        => trim(implode(', ', array_filter([
                    $salon->address_line1,
                    $salon->address_line2,
                    $salon->city,
                    $salon->postcode,
                ]))),
                'logo_url'            => $this->resolveLogoUrl($salon),
                'cover_image_url'     => $salon->cover_image ? asset('storage/' . $salon->cover_image) : null,
                'currency'            => $currency,
                'currency_symbol'     => CurrencyHelper::symbol($currency),
                'website_url'         => StorefrontUrl::website($salon),
                'booking_url'         => StorefrontUrl::booking($salon),
                'website_theme'       => StorefrontTheme::forSalon($salon),
                'website_theme_label' => StorefrontTheme::label(StorefrontTheme::forSalon($salon)),
                'whatsapp_url'        => $this->whatsappUrl($salon->phone),
                'opening_hours'       => $salon->opening_hours,
                'opening_hours_lines' => $this->openingHoursLines($salon->opening_hours),
                'social_links'        => $salon->social_links ?? [],
                'awards_accolades'    => $salon->awards_accolades,
                'avg_rating'          => $avgRating > 0 ? round($avgRating, 1) : null,
                'review_count'        => $reviewCount,
                'service_count'       => $services->count(),
                'staff_count'         => $staff->count(),
                'online_booking_enabled' => (bool) $salon->online_booking_enabled,
            ],
            'service_categories' => $categories,
            'staff'              => $staff->map(fn (Staff $s) => [
                'id'           => $s->id,
                'name'         => trim($s->first_name . ' ' . $s->last_name),
                'role'         => $s->role,
                'role_label'   => StaffJobRoles::label($s->role),
                'bio'          => $s->bio,
                'specialisms'  => is_array($s->specialisms)
                    ? implode(' | ', $s->specialisms)
                    : (is_string($s->specialisms) ? $s->specialisms : null),
                'service_labels' => $s->services->pluck('name')->values()->all(),
                'avatar_url'   => \App\Models\Staff::resolvePublicAvatarUrl($s->avatar),
                'initials'     => $this->staffInitials($s),
                'color'        => $s->color ?? '#7c3aed',
            ])->values()->all(),
            'packages' => $packages->map(function (ServicePackage $pkg) use ($currency) {
                $components = round((float) $pkg->services->sum(fn ($s) => (float) $s->price), 2);
                $price = (float) $pkg->price;
                $savings = $components > $price ? round((($components - $price) / $components) * 100) : 0;

                return [
                    'id'               => $pkg->id,
                    'name'             => $pkg->name,
                    'description'      => $pkg->description,
                    'price'            => $price,
                    'price_formatted'  => CurrencyHelper::format($price, $currency),
                    'components_total' => $components,
                    'components_formatted' => CurrencyHelper::format($components, $currency),
                    'discount_percent' => $savings > 0 ? $savings . '% OFF' : null,
                    'items'            => $pkg->services->map(fn ($s) => [
                        'name'  => $s->name,
                        'price' => CurrencyHelper::format((float) $s->price, $currency),
                    ])->values()->all(),
                ];
            })->values()->all(),
            'reviews' => $reviews->map(fn (Review $r) => [
                'rating' => (int) $r->rating,
                'title'  => $this->reviewTitle($r),
                'text'   => (string) $r->comment,
                'author' => $r->reviewer_name ?: 'Guest',
            ])->values()->all(),
            'photos' => $photos,
            'locations'          => $this->resolveLocations($salon),
        ];
    }

    /** @return list<array<string, mixed>> */
    private function resolveLocations(Salon $current): array
    {
        $query = Salon::withoutGlobalScope(TenantScope::class)
            ->where('is_active', true);

        if ($current->owner_id) {
            $query->where('owner_id', $current->owner_id);
        } else {
            $query->where('id', $current->id);
        }

        $rows = $query
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$current->id])
            ->orderBy('name')
            ->get([
                'id', 'name', 'slug', 'address_line1', 'address_line2',
                'city', 'postcode', 'country', 'latitude', 'longitude', 'opening_hours',
            ]);

        return $rows->map(fn (Salon $s) => $this->mapLocation($s, $current))->values()->all();
    }

    /** @return array<string, mixed> */
    private function mapLocation(Salon $salon, Salon $current): array
    {
        $photos = SalonPhoto::where('salon_id', $salon->id)
            ->orderBy('sort_order')
            ->limit(4)
            ->get()
            ->map(fn ($p) => asset('storage/' . $p->path))
            ->values()
            ->all();

        return [
            'id'                  => $salon->id,
            'name'                => $salon->name,
            'slug'                => $salon->slug,
            'address'             => trim(implode(', ', array_filter([
                $salon->address_line1,
                $salon->address_line2,
                $salon->city,
                $salon->postcode,
            ]))),
            'is_current'          => (int) $salon->id === (int) $current->id,
            'map_embed_url'       => $this->mapEmbedUrl($salon),
            'opening_hours_lines' => $this->openingHoursLines($salon->opening_hours),
            'photos'              => $photos,
        ];
    }

    private function mapEmbedUrl(Salon $salon): ?string
    {
        if ($salon->latitude !== null && $salon->longitude !== null) {
            $lat = (float) $salon->latitude;
            $lng = (float) $salon->longitude;

            return 'https://www.google.com/maps?q=' . $lat . ',' . $lng . '&z=15&output=embed';
        }

        $query = trim(implode(', ', array_filter([
            $salon->address_line1,
            $salon->address_line2,
            $salon->city,
            $salon->postcode,
            $salon->country,
        ])));

        if ($query === '') {
            return null;
        }

        return 'https://www.google.com/maps?q=' . rawurlencode($query) . '&z=15&output=embed';
    }

    /** @param  Collection<int, Service>  $services */
    private function groupServices(Collection $services, string $currency, int $salonId): array
    {
        $mapService = fn (Service $s) => [
            'id'                 => $s->id,
            'name'               => $s->name,
            'description'        => $s->description ?: '',
            'duration_minutes'   => (int) $s->duration_minutes,
            'price'              => (float) $s->price,
            'price_formatted'    => CurrencyHelper::format((float) $s->price, $currency),
            'image_url'          => $s->image ? asset('storage/' . $s->image) : null,
        ];

        $grouped = $services->groupBy(fn (Service $s) => (int) ($s->category_id ?: 0));

        $storeCategories = ServiceCategory::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salonId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'sort_order']);

        $out = [];
        $usedCategoryIds = [];

        foreach ($storeCategories as $category) {
            $rows = $grouped->get((int) $category->id, collect());
            if ($rows->isEmpty()) {
                continue;
            }

            $usedCategoryIds[] = (int) $category->id;
            $out[] = [
                'id'       => (int) $category->id,
                'name'     => $category->name,
                'services' => $rows->map($mapService)->values()->all(),
            ];
        }

        // Services linked to a category row outside this salon's active list (legacy / orphaned).
        foreach ($grouped as $categoryId => $rows) {
            $categoryId = (int) $categoryId;
            if ($categoryId === 0 || in_array($categoryId, $usedCategoryIds, true) || $rows->isEmpty()) {
                continue;
            }

            $first = $rows->first();
            $out[] = [
                'id'       => $categoryId,
                'name'     => $first?->category?->name ?? 'Services',
                'services' => $rows->map($mapService)->values()->all(),
            ];
        }

        if ($grouped->has(0) && $grouped->get(0)->isNotEmpty()) {
            $out[] = [
                'id'       => 0,
                'name'     => 'Services',
                'services' => $grouped->get(0)->map($mapService)->values()->all(),
            ];
        }

        return $out;
    }

    /** @var list<string> */
    private const WEEKDAY_ORDER = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
    ];

    /** @return list<string> */
    private function openingHoursLines(?array $hours): array
    {
        if (! is_array($hours) || $hours === []) {
            return [
                'Monday to Friday — 9 AM to 6 PM',
                'Saturday — 10 AM to 5 PM',
                'Sunday — Closed',
            ];
        }

        $schedules = [];
        foreach (self::WEEKDAY_ORDER as $day) {
            $config = $this->hoursConfigForDay($hours, $day);
            if ($config === null || ! $this->isDayOpen($config)) {
                $schedules[] = ['day' => $day, 'closed' => true];
                continue;
            }

            $times = $this->dayTimes($config);
            if ($times === null) {
                $schedules[] = ['day' => $day, 'closed' => true];
                continue;
            }

            $schedules[] = [
                'day' => $day,
                'closed' => false,
                'start' => $times[0],
                'end' => $times[1],
            ];
        }

        $lines = $this->groupOpeningHourSchedules($schedules);

        return $lines !== [] ? $lines : ['See salon for opening hours'];
    }

    /** @return array<string, mixed>|null */
    private function hoursConfigForDay(array $hours, string $day): ?array
    {
        if (isset($hours[$day]) && is_array($hours[$day])) {
            return $hours[$day];
        }

        $ucfirst = ucfirst($day);
        if (isset($hours[$ucfirst]) && is_array($hours[$ucfirst])) {
            return $hours[$ucfirst];
        }

        foreach ($hours as $key => $config) {
            if (is_string($key) && strtolower($key) === $day && is_array($config)) {
                return $config;
            }
        }

        return null;
    }

    /** @param  array<string, mixed>  $config */
    private function isDayOpen(array $config): bool
    {
        if (($config['closed'] ?? false) === true || ($config['closed'] ?? false) === '1') {
            return false;
        }

        if (isset($config['is_open']) && $config['is_open'] === false) {
            return false;
        }

        if (array_key_exists('open', $config)) {
            $open = $config['open'];
            if (is_bool($open)) {
                return $open;
            }

            return in_array((string) $open, ['1', 'true', 'yes', 'on'], true);
        }

        return $this->dayTimes($config) !== null;
    }

    /** @param  array<string, mixed>  $config
     * @return array{0: string, 1: string}|null
     */
    private function dayTimes(array $config): ?array
    {
        $start = $config['from'] ?? $config['start'] ?? $config['open_time'] ?? null;
        $end = $config['to'] ?? $config['end'] ?? $config['close_time'] ?? $config['close'] ?? null;

        if (! is_string($start) || ! is_string($end)) {
            return null;
        }

        $start = substr($start, 0, 5);
        $end = substr($end, 0, 5);

        if ($start === '00:00' && $end === '00:00') {
            return null;
        }

        return [$start, $end];
    }

    /**
     * @param  list<array{day: string, closed: bool, start?: string, end?: string}>  $schedules
     * @return list<string>
     */
    private function groupOpeningHourSchedules(array $schedules): array
    {
        $groups = [];
        $current = null;

        foreach ($schedules as $item) {
            $key = $item['closed']
                ? 'closed'
                : ($item['start'] . '|' . $item['end']);

            if ($current === null
                || $current['key'] !== $key
                || ! $this->areConsecutiveWeekdays($current['endDay'], $item['day'])) {
                if ($current !== null) {
                    $groups[] = $current;
                }

                $current = [
                    'key' => $key,
                    'startDay' => $item['day'],
                    'endDay' => $item['day'],
                    'closed' => $item['closed'],
                    'start' => $item['start'] ?? null,
                    'end' => $item['end'] ?? null,
                    'count' => 1,
                ];
            } else {
                $current['endDay'] = $item['day'];
                $current['count']++;
            }
        }

        if ($current !== null) {
            $groups[] = $current;
        }

        $lines = [];
        foreach ($groups as $group) {
            $label = $this->formatWeekdayRange($group['startDay'], $group['endDay'], $group['count']);
            if ($group['closed']) {
                $lines[] = $label . ' — Closed';
                continue;
            }

            $lines[] = $label . ' — ' . $this->formatTime12((string) $group['start'])
                . ' to ' . $this->formatTime12((string) $group['end']);
        }

        return $lines;
    }

    private function areConsecutiveWeekdays(string $first, string $second): bool
    {
        $index = array_flip(self::WEEKDAY_ORDER);
        if (! isset($index[$first], $index[$second])) {
            return false;
        }

        return $index[$second] === $index[$first] + 1;
    }

    private function formatWeekdayRange(string $startDay, string $endDay, int $count): string
    {
        if ($count === 1) {
            return ucfirst($startDay);
        }

        if ($count === 2 && $startDay === 'saturday' && $endDay === 'sunday') {
            return 'Saturday & Sunday';
        }

        return ucfirst($startDay) . ' to ' . ucfirst($endDay);
    }

    private function formatTime12(string $hhmm): string
    {
        try {
            $dt = \Carbon\Carbon::createFromFormat('H:i', substr($hhmm, 0, 5));

            return $dt->format((int) $dt->format('i') === 0 ? 'g A' : 'g:i A');
        } catch (\Throwable) {
            return $hhmm;
        }
    }

    private function reviewTitle(Review $review): string
    {
        $rating = (int) $review->rating;
        if ($rating >= 5) {
            return 'Excellent experience';
        }
        if ($rating >= 4) {
            return 'Great visit';
        }

        return 'Client review';
    }

    private function staffInitials(Staff $staff): string
    {
        $stored = $staff->getAttribute('initials');
        if (is_string($stored) && trim($stored) !== '') {
            return strtoupper(trim($stored));
        }

        $first = strtoupper(substr((string) ($staff->first_name ?? ''), 0, 1));
        $last  = strtoupper(substr((string) ($staff->last_name ?? ''), 0, 1));

        return $first . $last ?: '?';
    }

    private function resolveLogoUrl(Salon $salon): ?string
    {
        $path = $salon->logo;
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! Storage::disk('public')->exists($path)) {
            return null;
        }

        return asset('storage/' . $path);
    }

    private function whatsappUrl(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? 'https://wa.me/' . $digits : null;
    }
}
