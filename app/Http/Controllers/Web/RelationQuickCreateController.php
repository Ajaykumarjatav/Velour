<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\Concerns\ResolvesActiveSalon;
use App\Models\Client;
use App\Models\LoyaltyTier;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Salon;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Staff;
use App\Services\NotificationService;
use App\Support\StaffServiceEligibility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RelationQuickCreateController extends Controller
{
    use ResolvesActiveSalon;

    public function storeClient(Request $request): JsonResponse
    {
        Gate::authorize('create', Client::class);

        $salon = $this->activeSalon();

        $request->merge([
            'date_of_birth'   => $request->filled('date_of_birth') ? $request->input('date_of_birth') : null,
            'gender'          => $request->filled('gender') ? $request->input('gender') : null,
            'loyalty_tier_id' => $request->filled('loyalty_tier_id') ? $request->input('loyalty_tier_id') : null,
        ]);

        $data = $request->validate([
            'first_name'        => ['required', 'string', 'max:100'],
            'last_name'         => ['required', 'string', 'max:100'],
            'email'             => ['nullable', 'email', 'max:150'],
            'phone'             => ['nullable', 'string', 'max:20'],
            'date_of_birth'     => ['nullable', 'date'],
            'gender'            => ['nullable', 'in:female,male,non_binary,prefer_not_to_say'],
            'address'           => ['nullable', 'string', 'max:500'],
            'notes'             => ['nullable', 'string', 'max:2000'],
            'loyalty_tier_id'   => ['nullable', 'integer', 'exists:loyalty_tiers,id'],
        ]);

        if (! empty($data['loyalty_tier_id'])) {
            abort_unless(
                LoyaltyTier::where('id', $data['loyalty_tier_id'])->where('salon_id', $salon->id)->exists(),
                422
            );
        }

        $data['salon_id'] = $salon->id;
        $data['marketing_consent'] = $request->boolean('marketing_consent');

        $client = Client::create($data);
        app(NotificationService::class)->notifyTenantNewClientRegistered($salon, $client);

        $label = trim($client->first_name.' '.$client->last_name);
        if ($client->phone) {
            $label .= ' — '.$client->phone;
        }

        return response()->json([
            'id'    => $client->id,
            'label' => $label,
        ]);
    }

    public function storeStaff(Request $request): JsonResponse
    {
        Gate::authorize('create', Staff::class);

        $salon = $this->activeSalon();

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['nullable', 'email', 'max:150'],
            'phone'           => ['nullable', 'string', 'max:20'],
            'role'            => ['required', 'in:owner,manager,stylist,therapist,receptionist,junior'],
            'bio'             => ['nullable', 'string', 'max:1000'],
            'color'           => ['nullable', 'string', 'max:7'],
            'commission_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'services'        => ['nullable', 'array'],
            'services.*'      => [Rule::exists('services', 'id')->where('salon_id', $salon->id)],
            'avatar'          => ['required', 'file', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        StaffServiceEligibility::assertEligibleForRole($salon->id, (string) $data['role'], $data['services'] ?? []);

        $nameParts = explode(' ', trim($data['name']), 2);
        $avatarFile = $request->file('avatar');
        unset($data['avatar']);

        $staff = Staff::create([
            'salon_id'         => $salon->id,
            'first_name'       => $nameParts[0],
            'last_name'        => $nameParts[1] ?? '',
            'email'            => $data['email'] ?? null,
            'phone'            => $data['phone'] ?? null,
            'role'             => $data['role'],
            'bio'              => $data['bio'] ?? null,
            'color'            => $data['color'] ?? '#7C3AED',
            'commission_rate'  => $data['commission_rate'] ?? 0,
            'is_active'        => true,
        ]);

        $staff->update([
            'avatar' => $avatarFile->store('salons/'.$salon->id.'/staff', 'public'),
        ]);

        $serviceIds = array_values(array_map('intval', (array) ($data['services'] ?? [])));
        if ($serviceIds !== []) {
            $staff->services()->withoutTenantScope()->sync($serviceIds);
        }

        return response()->json([
            'id'    => $staff->id,
            'label' => $staff->name,
        ]);
    }

    public function storeInventoryCategory(Request $request): JsonResponse
    {
        Gate::authorize('create', InventoryItem::class);

        $salon = $this->activeSalon();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $base = Str::slug($data['name']);
        if ($base === '') {
            $base = 'category';
        }
        $slug = $base;
        $n = 0;
        while (InventoryCategory::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('slug', $slug)
            ->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        $sortOrder = (int) InventoryCategory::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->max('sort_order');
        $category = InventoryCategory::create([
            'salon_id'   => $salon->id,
            'name'       => $data['name'],
            'slug'       => $slug,
            'sort_order' => $sortOrder + 1,
        ]);

        return response()->json([
            'id'    => $category->id,
            'label' => $category->name,
        ]);
    }

    /**
     * Minimal service for manual booking — full editing remains under Services.
     */
    public function storeService(Request $request): JsonResponse
    {
        Gate::authorize('create', Service::class);

        $salon = $this->activeSalon();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:480'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $categoryId = $this->defaultServiceCategoryId($salon);

        $base = Str::slug($data['name']);
        if ($base === '') {
            $base = 'service';
        }
        $slug = $base;
        $n = 0;
        while (Service::withoutGlobalScopes()->where('salon_id', $salon->id)->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        $sortOrder = (int) Service::withoutGlobalScopes()->where('salon_id', $salon->id)->max('sort_order');

        $businessTypeId = (int) ($salon->businessTypes()->orderBy('business_types.sort_order')->value('business_types.id') ?? $salon->business_type_id);

        $service = Service::create([
            'salon_id' => $salon->id,
            'business_type_id' => $businessTypeId,
            'category_id' => $categoryId,
            'name' => $data['name'],
            'slug' => $slug,
            'duration_minutes' => $data['duration_minutes'],
            'buffer_minutes' => 10,
            'price' => $data['price'],
            'status' => 'active',
            'online_bookable' => true,
            'show_in_menu' => true,
            'sort_order' => $sortOrder + 1,
        ]);

        $currency = $salon->currency ?? 'GBP';

        return response()->json([
            'id' => $service->id,
            'name' => $service->name,
            'duration_minutes' => (int) $service->duration_minutes,
            'price_formatted' => \App\Helpers\CurrencyHelper::format((float) $service->price, $currency),
        ]);
    }

    private function defaultServiceCategoryId(Salon $salon): int
    {
        $businessTypeId = (int) ($salon->businessTypes()->orderBy('business_types.sort_order')->value('business_types.id') ?? $salon->business_type_id);

        $existing = ServiceCategory::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->where('business_type_id', $businessTypeId)
            ->orderBy('sort_order')
            ->first();
        if ($existing) {
            return (int) $existing->id;
        }

        $base = 'general';
        $slug = $base;
        $n = 0;
        while (ServiceCategory::withoutGlobalScopes()->where('salon_id', $salon->id)->where('business_type_id', $businessTypeId)->where('slug', $slug)->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        $sortOrder = (int) ServiceCategory::withoutGlobalScopes()->where('salon_id', $salon->id)
            ->where('business_type_id', $businessTypeId)
            ->max('sort_order');
        $category = ServiceCategory::create([
            'salon_id'         => $salon->id,
            'business_type_id' => $businessTypeId,
            'name'             => 'General',
            'slug'             => $slug,
            'sort_order'       => $sortOrder + 1,
            'is_active'        => true,
        ]);

        return (int) $category->id;
    }
}
