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
use App\Support\StorefrontUrl;
use Illuminate\Support\Collection;

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

        $categories = $this->groupServices($services, $salon->currency ?? 'GBP');

        $staff = Staff::withoutGlobalScope(TenantScope::class)
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
                'logo_url'            => $salon->logo ? asset('storage/' . $salon->logo) : null,
                'cover_image_url'     => $salon->cover_image ? asset('storage/' . $salon->cover_image) : null,
                'currency'            => $currency,
                'currency_symbol'     => CurrencyHelper::symbol($currency),
                'website_url'         => StorefrontUrl::website($salon),
                'booking_url'         => StorefrontUrl::booking($salon),
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
                'bio'          => $s->bio,
                'specialisms'  => is_array($s->specialisms)
                    ? implode(' | ', $s->specialisms)
                    : (is_string($s->specialisms) ? $s->specialisms : null),
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
        ];
    }

    /** @param  Collection<int, Service>  $services */
    private function groupServices(Collection $services, string $currency): array
    {
        $grouped = $services->groupBy(fn (Service $s) => $s->category_id ?: 0);

        $out = [];
        foreach ($grouped as $categoryId => $rows) {
            $first = $rows->first();
            $name = $first?->category?->name ?? 'Services';

            $out[] = [
                'id'       => (int) $categoryId,
                'name'     => $name,
                'services' => $rows->map(fn (Service $s) => [
                    'id'                 => $s->id,
                    'name'               => $s->name,
                    'description'        => $s->description ?: '',
                    'duration_minutes'   => (int) $s->duration_minutes,
                    'price'              => (float) $s->price,
                    'price_formatted'    => CurrencyHelper::format((float) $s->price, $currency),
                    'image_url'          => $s->image ? asset('storage/' . $s->image) : null,
                ])->values()->all(),
            ];
        }

        usort($out, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return $out;
    }

    /** @return list<string> */
    private function openingHoursLines(?array $hours): array
    {
        if (! is_array($hours) || $hours === []) {
            return [
                'Monday to Friday — 9:00 AM to 6:00 PM',
                'Saturday — 10:00 AM to 5:00 PM',
                'Sunday — Closed',
            ];
        }

        $lines = [];
        foreach ($hours as $day => $config) {
            if (! is_array($config)) {
                continue;
            }
            if (array_key_exists('open', $config) && is_bool($config['open']) && $config['open'] === false) {
                $lines[] = ucfirst((string) $day) . ' — Closed';
                continue;
            }
            if (($config['is_open'] ?? true) === false) {
                $lines[] = ucfirst((string) $day) . ' — Closed';
                continue;
            }
            $start = $config['start'] ?? $config['open_time'] ?? null;
            $end   = $config['end'] ?? $config['close_time'] ?? $config['close'] ?? null;
            if (! is_string($start) || ! is_string($end) || $start === '00:00' && $end === '00:00') {
                continue;
            }
            $lines[] = ucfirst((string) $day) . ' — ' . $this->formatTime12($start) . ' to ' . $this->formatTime12($end);
        }

        return $lines !== [] ? $lines : ['See salon for opening hours'];
    }

    private function formatTime12(string $hhmm): string
    {
        try {
            $dt = \Carbon\Carbon::createFromFormat('H:i', substr($hhmm, 0, 5));

            return $dt->format('g:i A');
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

    private function whatsappUrl(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $digits = preg_replace('/\D+/', '', $phone);

        return $digits !== '' ? 'https://wa.me/' . $digits : null;
    }
}
