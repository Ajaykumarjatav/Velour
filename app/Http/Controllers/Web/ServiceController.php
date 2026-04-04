<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\DynamicPricingRule;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon = $this->salon();

        $filterCategoryId = $request->get('category_id');
        $search           = $request->get('search');

        $categoriesQuery = ServiceCategory::where('salon_id', $salon->id)
            ->when($filterCategoryId, fn ($q) => $q->where('id', $filterCategoryId))
            ->with(['services' => function ($q) use ($search) {
                $q->orderBy('sort_order');
                if ($search) {
                    $s = trim($search);
                    $q->where(function ($sq) use ($s) {
                        $sq->where('name', 'like', "%{$s}%")
                            ->orWhere('description', 'like', "%{$s}%");
                    });
                }
            }])
            ->orderBy('sort_order');

        $categories = $categoriesQuery->get();
        if ($search) {
            $categories = $categories->filter(fn ($c) => $c->services->isNotEmpty())->values();
        }

        $uncategorisedQuery = Service::where('salon_id', $salon->id)
            ->whereNull('category_id')
            ->active();
        if ($search) {
            $s = trim($search);
            $uncategorisedQuery->where(function ($sq) use ($s) {
                $sq->where('name', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%");
            });
        }
        $uncategorised = $uncategorisedQuery->orderBy('sort_order')->get();

        $totalServices = Service::where('salon_id', $salon->id)->count();

        $categoryChips = ServiceCategory::where('salon_id', $salon->id)
            ->orderBy('sort_order')
            ->get(['id', 'name']);

        $pricingRules = DynamicPricingRule::where('salon_id', $salon->id)
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
            'pricingRules'
        ));
    }

    public function create()
    {
        $salon      = $this->salon();
        $categories = ServiceCategory::where('salon_id', $salon->id)->orderBy('sort_order')->get(['id','name']);

        return view('services.create', compact('salon', 'categories'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'description'              => ['nullable', 'string', 'max:1000'],
            'category_id'              => ['nullable', 'exists:service_categories,id'],
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
        ]);

        $data['salon_id']                = $salon->id;
        $data['status']                  = isset($data['is_active']) ? ($data['is_active'] ? 'active' : 'inactive') : 'active';
        $data['online_bookable']         = $data['online_booking'] ?? false;
        $data['dynamic_pricing_enabled'] = $request->boolean('dynamic_pricing_enabled');
        $data['variants']                = Service::normalizePriceRows($data['variants'] ?? null);
        $data['addons']                  = Service::mergeAddonsFromText(
            Service::normalizePriceRows($data['addons'] ?? null),
            $data['addons_text'] ?? null
        );
        unset($data['is_active'], $data['online_booking'], $data['addons_text']);

        Service::create($data);

        return redirect()->route('services.index')->with('success', 'Service created successfully.');
    }

    public function edit(Service $service)
    {
        $this->authorise($service);
        $salon      = $this->salon();
        $categories = ServiceCategory::where('salon_id', $salon->id)->orderBy('sort_order')->get(['id','name']);

        return view('services.edit', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $this->authorise($service);

        $data = $request->validate([
            'name'                     => ['required', 'string', 'max:150'],
            'description'              => ['nullable', 'string', 'max:1000'],
            'category_id'              => ['nullable', 'exists:service_categories,id'],
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
        unset($data['addons_text']);

        $service->update($data);

        return redirect()->route('services.index')->with('success', 'Service updated.');
    }

    public function destroy(Service $service)
    {
        $this->authorise($service);
        $service->delete();

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
            DynamicPricingRule::where('salon_id', $salon->id)->delete();
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
}
