<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\DynamicPricingRule;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class ServiceController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index(Request $request)
    {
        $salon = $this->salon();

        $filterCategoryId = $request->get('category_id');
        $search           = $request->get('search');
        $statusFilter     = $request->get('status', '');
        $priceMin         = $request->filled('price_min') ? (float) $request->get('price_min') : null;
        $priceMax         = $request->filled('price_max') ? (float) $request->get('price_max') : null;
        $durationMin      = $request->filled('duration_min') ? (int) $request->get('duration_min') : null;
        $durationMax      = $request->filled('duration_max') ? (int) $request->get('duration_max') : null;

        $applyServiceConstraints = function ($q) use ($search, $statusFilter, $priceMin, $priceMax, $durationMin, $durationMax) {
            $q->orderBy('sort_order');
            if ($search) {
                $s = trim($search);
                $q->where(function ($sq) use ($s) {
                    $sq->where('name', 'like', "%{$s}%")
                        ->orWhere('description', 'like', "%{$s}%");
                });
            }
            if ($statusFilter === 'active') {
                $q->where('status', 'active');
            } elseif ($statusFilter === 'inactive') {
                $q->where('status', 'inactive');
            }
            if ($priceMin !== null) {
                $q->where('price', '>=', $priceMin);
            }
            if ($priceMax !== null) {
                $q->where('price', '<=', $priceMax);
            }
            if ($durationMin !== null) {
                $q->where('duration_minutes', '>=', $durationMin);
            }
            if ($durationMax !== null) {
                $q->where('duration_minutes', '<=', $durationMax);
            }
        };

        $categories = ServiceCategory::withoutGlobalScopes()
            ->where('service_categories.salon_id', $salon->id)
            ->when($filterCategoryId, fn ($q) => $q->where('service_categories.id', $filterCategoryId))
            ->with([
                'services' => function ($q) use ($applyServiceConstraints, $salon) {
                    $q->withoutTenantScope()->where('services.salon_id', $salon->id);
                    $applyServiceConstraints($q);
                },
                'businessType',
            ])
            ->join('business_types', 'business_types.id', '=', 'service_categories.business_type_id')
            ->orderBy('business_types.sort_order')
            ->orderBy('service_categories.sort_order')
            ->select('service_categories.*')
            ->get()
            ->filter(fn ($c) => $c->services->isNotEmpty())
            ->values();

        $uncategorisedQuery = Service::withoutTenantScope()
            ->where('salon_id', $salon->id)
            ->whereNull('category_id');
        $applyServiceConstraints($uncategorisedQuery);
        $uncategorised = $uncategorisedQuery->orderBy('sort_order')->get();

        $totalServices = Service::withoutTenantScope()->where('salon_id', $salon->id)->count();

        $accordionOpen = [];
        foreach ($categories as $i => $cat) {
            $accordionOpen[$cat->id] = $i === 0;
        }

        $categoryChips = ServiceCategory::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->with('businessType')
            ->get()
            ->sortBy(fn ($c) => [(int) ($c->businessType?->sort_order ?? 0), (int) $c->sort_order])
            ->values();

        $pricingRules = DynamicPricingRule::withoutTenantScope()
            ->where('salon_id', $salon->id)
            ->orderBy('sort_order')
            ->get();

        return view('services.index', compact(
            'salon',
            'categories',
            'uncategorised',
            'totalServices',
            'categoryChips',
            'filterCategoryId',
            'search',
            'pricingRules',
            'statusFilter',
            'accordionOpen'
        ));
    }

    public function create()
    {
        $salon      = $this->salon();
        $categories = ServiceCategory::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->with('businessType')
            ->orderBy('business_type_id')
            ->orderBy('sort_order')
            ->get();
        $assignedBusinessTypes = $salon->businessTypes()->orderBy('business_types.sort_order')->get();

        return view('services.create', compact('salon', 'categories', 'assignedBusinessTypes'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'description'              => ['nullable', 'string', 'max:1000'],
            'category_id'              => [
                'required',
                'integer',
                Rule::exists('service_categories', 'id')->where('salon_id', $salon->id),
            ],
            'duration_minutes'         => ['required', 'integer', 'min:5', 'max:480'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'is_active'                => ['boolean'],
            'online_booking'           => ['boolean'],
            'color'                    => ['nullable', 'string', 'max:7'],
            'variants'                 => ['nullable', 'array'],
            'variants.*.name'          => ['nullable', 'string', 'max:100'],
            'variants.*.price'         => ['nullable', 'numeric', 'min:0'],
            'addons'                   => ['nullable', 'array'],
            'addons.*.name'            => ['nullable', 'string', 'max:100'],
            'addons.*.price'           => ['nullable', 'numeric', 'min:0'],
            'addons_text'              => ['nullable', 'string', 'max:2000'],
            'dynamic_pricing_enabled'  => ['sometimes', 'boolean'],
            'staff_level'              => ['nullable', 'in:any,standard,senior,apprentice'],
            'allowed_roles'            => ['nullable', 'array'],
            'allowed_roles.*'          => ['string', Rule::in(Service::supportedStaffRoles())],
            'service_location'         => ['required', 'in:onsite,home'],
            'image'                    => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $imageFile = $request->file('image');
        unset($data['image']);

        $data['salon_id']                = $salon->id;
        $data['status']                  = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : 'active';
        $data['online_bookable']         = $data['online_booking'] ?? false;
        $data['dynamic_pricing_enabled'] = $request->boolean('dynamic_pricing_enabled');
        $data['variants']                = Service::normalizePriceRows($data['variants'] ?? null);
        $data['addons']                  = Service::mergeAddonsFromText(
            Service::normalizePriceRows($data['addons'] ?? null),
            $data['addons_text'] ?? null
        );
        $data['allowed_roles']           = $this->normalizeAllowedRoles($data['allowed_roles'] ?? null);
        unset($data['is_active'], $data['online_booking'], $data['addons_text']);

        $data['slug'] = $this->uniqueServiceSlug($salon->id, $data['name']);

        $service = Service::create($data);

        Cache::forget('plan_limit_services_'.$salon->id);

        if ($imageFile) {
            $service->update([
                'image' => $imageFile->store('salons/'.$salon->id.'/services', 'public'),
            ]);
        }

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $this->authorise($service);
        $service->loadMissing('category');
        $salon      = $this->salon();
        $categories = ServiceCategory::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->with('businessType')
            ->orderBy('business_type_id')
            ->orderBy('sort_order')
            ->get();
        $assignedBusinessTypes = $salon->businessTypes()->orderBy('business_types.sort_order')->get();

        return view('services.edit', compact('service', 'salon', 'categories', 'assignedBusinessTypes'));
    }

    public function update(Request $request, Service $service)
    {
        $this->authorise($service);
        $salon = $this->salon();

        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'description'              => ['nullable', 'string', 'max:1000'],
            'category_id'              => [
                'required',
                'integer',
                Rule::exists('service_categories', 'id')->where('salon_id', $salon->id),
            ],
            'duration_minutes'         => ['required', 'integer', 'min:5', 'max:480'],
            'price'                    => ['required', 'numeric', 'min:0'],
            'is_active'                => ['boolean'],
            'online_booking'           => ['boolean'],
            'color'                    => ['nullable', 'string', 'max:7'],
            'variants'                 => ['nullable', 'array'],
            'variants.*.name'          => ['nullable', 'string', 'max:100'],
            'variants.*.price'         => ['nullable', 'numeric', 'min:0'],
            'addons'                   => ['nullable', 'array'],
            'addons.*.name'            => ['nullable', 'string', 'max:100'],
            'addons.*.price'           => ['nullable', 'numeric', 'min:0'],
            'addons_text'              => ['nullable', 'string', 'max:2000'],
            'dynamic_pricing_enabled'  => ['sometimes', 'boolean'],
            'staff_level'              => ['nullable', 'in:any,standard,senior,apprentice'],
            'allowed_roles'            => ['nullable', 'array'],
            'allowed_roles.*'          => ['string', Rule::in(Service::supportedStaffRoles())],
            'service_location'         => ['required', 'in:onsite,home'],
            'image'                    => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        if (array_key_exists('is_active', $data)) {
            $data['status'] = $data['is_active'] ? 'active' : 'inactive';
            unset($data['is_active']);
        }

        if (array_key_exists('online_booking', $data)) {
            $data['online_bookable'] = $data['online_booking'];
            unset($data['online_booking']);
        }

        $data['dynamic_pricing_enabled'] = $request->boolean('dynamic_pricing_enabled');
        $data['variants']                = Service::normalizePriceRows($data['variants'] ?? null);
        $data['addons']                  = Service::mergeAddonsFromText(
            Service::normalizePriceRows($data['addons'] ?? null),
            $data['addons_text'] ?? null
        );
        $data['allowed_roles']           = $this->normalizeAllowedRoles($data['allowed_roles'] ?? null);
        unset($data['addons_text'], $data['image']);

        if (trim((string) $data['name']) !== trim((string) $service->name)) {
            $data['slug'] = $this->uniqueServiceSlug($service->salon_id, $data['name'], $service->id);
        }

        $service->update($data);
        Cache::forget('plan_limit_services_'.$service->salon_id);

        $this->syncServiceImage($request, $service);

        return redirect()->route('services.index')->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        $this->authorise($service);
        if ($service->image) {
            Storage::disk('public')->delete($service->image);
        }
        $salonId = (int) $service->salon_id;
        $service->delete();
        Cache::forget('plan_limit_services_'.$salonId);

        return redirect()->route('services.index')->with('success', 'Service deleted.');
    }

    public function updateVariants(Request $request, Service $service)
    {
        $this->authorise($service);

        $data = $request->validate([
            'variants'         => ['nullable', 'array'],
            'variants.*.name'  => ['nullable', 'string', 'max:100'],
            'variants.*.price' => ['nullable', 'numeric', 'min:0'],
            'addons'           => ['nullable', 'array'],
            'addons.*.name'    => ['nullable', 'string', 'max:100'],
            'addons.*.price'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $service->update([
            'variants' => Service::normalizePriceRows($data['variants'] ?? null),
            'addons'   => Service::normalizePriceRows($data['addons'] ?? null),
        ]);

        return redirect()->route('services.index')->with('success', 'Variants and add-ons saved.');
    }

    public function updatePricingRules(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'rules'                       => ['nullable', 'array'],
            'rules.*.title'               => ['nullable', 'string', 'max:150'],
            'rules.*.description'         => ['nullable', 'string', 'max:500'],
            'rules.*.adjustment_percent'  => ['nullable'],
            'rules.*.enabled'             => ['nullable'],
        ]);

        $rows = $data['rules'] ?? [];

        DB::transaction(function () use ($salon, $rows): void {
            DynamicPricingRule::withoutTenantScope()->where('salon_id', $salon->id)->delete();
            foreach ($rows as $i => $row) {
                if (trim((string) ($row['title'] ?? '')) === '') {
                    continue;
                }
                $pct = (int) ($row['adjustment_percent'] ?? 0);
                $pct = max(-100, min(500, $pct));
                DynamicPricingRule::create([
                    'salon_id'            => $salon->id,
                    'title'               => $row['title'],
                    'description'         => $row['description'] ?? null,
                    'adjustment_percent'  => $pct,
                    'enabled'             => filter_var($row['enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
                    'sort_order'          => $i,
                ]);
            }
        });

        return redirect()->route('services.index')->with('success', 'Dynamic pricing rules saved.');
    }

    private function authorise(Service $service): void
    {
        abort_unless($service->salon_id === $this->salon()->id, 403);
    }

    private function uniqueServiceSlug(int $salonId, string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'service';
        $slug = $base;
        $n    = 1;
        while ($this->serviceSlugTaken($salonId, $slug, $exceptId)) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function serviceSlugTaken(int $salonId, string $slug, ?int $exceptId): bool
    {
        $q = Service::withoutGlobalScopes()->where('salon_id', $salonId)->where('slug', $slug);
        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }

    private function syncServiceImage(Request $request, Service $service): void
    {
        if ($request->hasFile('image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $path = $request->file('image')->store('salons/'.$service->salon_id.'/services', 'public');
            $service->update(['image' => $path]);

            return;
        }

        if ($request->boolean('remove_image')) {
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            $service->update(['image' => null]);
        }
    }

    /** @param  array<int, mixed>|null  $roles */
    private function normalizeAllowedRoles(?array $roles): ?array
    {
        if ($roles === null || $roles === []) {
            return null;
        }

        $valid = array_flip(Service::supportedStaffRoles());
        $out = [];
        foreach ($roles as $role) {
            $key = strtolower(trim((string) $role));
            if ($key !== '' && isset($valid[$key])) {
                $out[$key] = true;
            }
        }

        $normalized = array_keys($out);

        return $normalized === [] ? null : $normalized;
    }
}
