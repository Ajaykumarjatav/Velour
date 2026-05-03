<?php

namespace App\Http\Controllers\Web;

use App\Helpers\CurrencyHelper;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServicePackageController extends Controller
{
    use ResolvesActiveSalon;

    private function salon()
    {
        return $this->activeSalon();
    }

    public function index()
    {
        Gate::authorize('viewAny', ServicePackage::class);

        $salon = $this->salon();
        $packages = ServicePackage::withoutTenantScope()
            ->where('salon_id', $salon->id)
            ->with([
                'services' => function ($q) use ($salon) {
                    $q->withoutTenantScope()
                        ->where('services.salon_id', $salon->id)
                        ->orderByPivot('sort_order');
                },
            ])
            ->withCount(['services' => fn ($q) => $q->withoutTenantScope()->where('services.salon_id', $salon->id)])
            ->withSum(['services' => fn ($q) => $q->withoutTenantScope()->where('services.salon_id', $salon->id)], 'price')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('service-packages.index', compact('salon', 'packages'));
    }

    public function create()
    {
        Gate::authorize('create', ServicePackage::class);

        $salon = $this->salon();
        $services = $this->servicesForPackageCatalog($salon->id);
        $servicesPayload = $this->servicesPayload($services, $salon->currency ?? 'GBP');
        $initialSelectedIds = array_values(array_unique(array_map('intval', (array) old('service_ids', []))));

        return view('service-packages.create', compact('salon', 'services', 'servicesPayload', 'initialSelectedIds'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', ServicePackage::class);

        $salon = $this->salon();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'online_bookable' => ['boolean'],
            'is_active' => ['boolean'],
            'service_ids' => ['required', 'array', 'min:2'],
            'service_ids.*' => ['integer', Rule::exists('services', 'id')->where('salon_id', $salon->id)],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string', Rule::in(Service::supportedStaffRoles())],
        ], [
            'service_ids.required' => 'Add at least two services to the package using Add in the catalog above.',
            'service_ids.min' => 'A package must include at least two services. Use Add to move services into the package.',
        ]);

        $serviceIds = array_values(array_unique(array_map('intval', $data['service_ids'])));

        $package = ServicePackage::create([
            'salon_id' => $salon->id,
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($salon->id, $data['name']),
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'online_bookable' => $request->boolean('online_bookable', true),
            'status' => $request->boolean('is_active', true) ? 'active' : 'inactive',
            'sort_order' => (int) ServicePackage::withoutTenantScope()->where('salon_id', $salon->id)->max('sort_order') + 1,
            'allowed_roles' => $this->normalizeAllowedRoles($data['allowed_roles'] ?? null),
        ]);

        $this->syncPackageServices($package, $serviceIds);

        return redirect()->route('service-packages.index')->with('success', 'Service package created.');
    }

    public function edit(ServicePackage $servicePackage)
    {
        $this->authorise($servicePackage);
        Gate::authorize('update', $servicePackage);

        $salon = $this->salon();
        $attachedIds = $servicePackage->services()
            ->withoutTenantScope()
            ->where('services.salon_id', $salon->id)
            ->orderByPivot('sort_order')
            ->get()
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
        $services = $this->servicesForPackageCatalog($salon->id, $attachedIds);
        $servicesPayload = $this->servicesPayload($services, $salon->currency ?? 'GBP');
        $initialSelectedIds = old('service_ids') !== null
            ? array_values(array_unique(array_map('intval', (array) old('service_ids'))))
            : $attachedIds;

        return view('service-packages.edit', compact('salon', 'services', 'servicesPayload', 'initialSelectedIds', 'servicePackage'));
    }

    public function update(Request $request, ServicePackage $servicePackage)
    {
        $this->authorise($servicePackage);
        Gate::authorize('update', $servicePackage);

        $salon = $this->salon();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price' => ['required', 'numeric', 'min:0'],
            'online_bookable' => ['boolean'],
            'is_active' => ['boolean'],
            'service_ids' => ['required', 'array', 'min:2'],
            'service_ids.*' => ['integer', Rule::exists('services', 'id')->where('salon_id', $salon->id)],
            'allowed_roles' => ['nullable', 'array'],
            'allowed_roles.*' => ['string', Rule::in(Service::supportedStaffRoles())],
        ], [
            'service_ids.required' => 'Add at least two services to the package using Add in the catalog above.',
            'service_ids.min' => 'A package must include at least two services. Use Add to move services into the package.',
        ]);

        $serviceIds = array_values(array_unique(array_map('intval', $data['service_ids'])));

        $update = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'],
            'online_bookable' => $request->boolean('online_bookable', true),
            'status' => $request->boolean('is_active', true) ? 'active' : 'inactive',
            'allowed_roles' => $this->normalizeAllowedRoles($data['allowed_roles'] ?? null),
        ];

        if (trim((string) $data['name']) !== trim((string) $servicePackage->name)) {
            $update['slug'] = $this->uniqueSlug($salon->id, $data['name'], $servicePackage->id);
        }

        $servicePackage->update($update);
        $this->syncPackageServices($servicePackage, $serviceIds);

        return redirect()->route('service-packages.index')->with('success', 'Service package updated.');
    }

    public function destroy(ServicePackage $servicePackage)
    {
        $this->authorise($servicePackage);
        Gate::authorize('delete', $servicePackage);

        $servicePackage->delete();

        return redirect()->route('service-packages.index')->with('success', 'Service package removed.');
    }

    private function authorise(ServicePackage $package): void
    {
        abort_unless($package->salon_id === $this->salon()->id, 403);
    }

    private function uniqueSlug(int $salonId, string $name, ?int $exceptId = null): string
    {
        $base = Str::slug($name) ?: 'package';
        $slug = $base;
        $n = 1;
        while ($this->slugTaken($salonId, $slug, $exceptId)) {
            $slug = $base.'-'.(++$n);
        }

        return $slug;
    }

    private function slugTaken(int $salonId, string $slug, ?int $exceptId): bool
    {
        $q = ServicePackage::withoutGlobalScopes()->where('salon_id', $salonId)->where('slug', $slug);
        if ($exceptId !== null) {
            $q->where('id', '!=', $exceptId);
        }

        return $q->exists();
    }

    /**
     * @param  list<int>  $selectedIdsInOrder  Order matches the package builder (first = first in bundle).
     */
    private function syncPackageServices(ServicePackage $package, array $selectedIdsInOrder): void
    {
        $ids = array_values(array_unique(array_map('intval', $selectedIdsInOrder)));
        $sync = [];
        foreach ($ids as $i => $sid) {
            $sync[$sid] = ['sort_order' => $i];
        }
        $package->services()->sync($sync);
    }

    /**
     * Active + inactive services (not archived); optionally include IDs already on the package so they stay visible if status changed.
     *
     * @param  list<int>  $alwaysIncludeIds
     */
    private function servicesForPackageCatalog(int $salonId, array $alwaysIncludeIds = [])
    {
        return Service::withoutTenantScope()
            ->where('salon_id', $salonId)
            ->where(function ($q) use ($alwaysIncludeIds): void {
                $q->whereIn('status', ['active', 'inactive']);
                if ($alwaysIncludeIds !== []) {
                    $q->orWhereIn('id', $alwaysIncludeIds);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'duration_minutes', 'status']);
    }

    /** @param  \Illuminate\Support\Collection<int, \App\Models\Service>  $services */
    private function servicesPayload($services, string $currency): array
    {
        return $services->map(fn (Service $s) => [
            'id' => (int) $s->id,
            'name' => $s->name,
            'priceLabel' => CurrencyHelper::format((float) $s->price, $currency),
            'status' => (string) $s->status,
        ])->values()->all();
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
