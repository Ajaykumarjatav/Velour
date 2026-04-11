<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InventoryCategory;
use App\Models\InventoryItem;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\Tenant;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class RelationQuickCreateController extends Controller
{
    private function currentSalon(): Salon
    {
        $user = Auth::user();
        $activeSalonId = (int) session('active_salon_id', 0);
        $salon = $activeSalonId > 0
            ? $user->salons()->where('id', $activeSalonId)->first()
            : null;

        return $salon ?: $user->salons()->firstOrFail();
    }

    public function storeClient(Request $request): JsonResponse
    {
        Gate::authorize('create', Client::class);

        $salon = $this->currentSalon();

        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name'  => ['required', 'string', 'max:100'],
            'email'      => ['nullable', 'email', 'max:150'],
            'phone'      => ['nullable', 'string', 'max:20'],
        ]);

        $data['salon_id'] = $salon->id;
        $data['marketing_consent'] = false;

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

        $salon = $this->currentSalon();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'role'  => ['required', 'in:owner,manager,stylist,therapist,receptionist,junior'],
            'email' => ['nullable', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $nameParts = explode(' ', trim($data['name']), 2);
        $staff = Staff::create([
            'salon_id'        => $salon->id,
            'first_name'      => $nameParts[0],
            'last_name'       => $nameParts[1] ?? '',
            'email'           => $data['email'] ?? null,
            'phone'           => $data['phone'] ?? null,
            'role'            => $data['role'],
            'color'           => '#7C3AED',
            'commission_rate' => 0,
            'is_active'       => true,
        ]);

        return response()->json([
            'id'    => $staff->id,
            'label' => $staff->name,
        ]);
    }

    /**
     * Same salon resolution as {@see \App\Http\Controllers\Web\InventoryController::salon()}.
     */
    private function inventorySalon(): Salon
    {
        if (Tenant::checkCurrent()) {
            return Salon::query()->findOrFail((int) Tenant::current()->getKey());
        }

        $user = Auth::user();
        if ($user->salons()->exists()) {
            return $user->salons()->firstOrFail();
        }
        if ($user->staffProfile?->salon_id) {
            return Salon::query()->findOrFail($user->staffProfile->salon_id);
        }

        abort(403, 'No salon associated with this account.');
    }

    public function storeInventoryCategory(Request $request): JsonResponse
    {
        Gate::authorize('create', InventoryItem::class);

        $salon = $this->inventorySalon();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
        ]);

        $base = Str::slug($data['name']);
        if ($base === '') {
            $base = 'category';
        }
        $slug = $base;
        $n = 0;
        while (InventoryCategory::query()
            ->where('salon_id', $salon->id)
            ->where('slug', $slug)
            ->exists()) {
            $n++;
            $slug = $base.'-'.$n;
        }

        $sortOrder = (int) InventoryCategory::query()
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
}
