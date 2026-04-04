<?php
// ════════════════════════════════════════════════════════════════════════════
// ServiceController
// ════════════════════════════════════════════════════════════════════════════
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    /* ── GET /services ───────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');

        $services = Service::with(['category', 'staff'])
            ->where('salon_id', $salonId)
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->status,      fn($q) => $q->where('status', $request->status))
            ->when($request->search,      function ($q) use ($request) {
                $q->where('name', 'ilike', '%' . $request->search . '%')
                  ->orWhere('description', 'ilike', '%' . $request->search . '%');
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->groupBy('category_id');

        return response()->json([
            'services' => $services,
            'total'    => $services->flatten()->count(),
        ]);
    }

    /* ── POST /services ─────────────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id'              => 'required|integer',
            'name'                     => 'required|string|max:255',
            'description'              => 'nullable|string|max:1000',
            'duration_minutes'         => 'required|integer|min:5|max:480',
            'buffer_minutes'           => 'nullable|integer|min:0|max:60',
            'price'                    => 'required|numeric|min:0',
            'price_from'               => 'nullable|numeric|min:0',
            'price_on_consultation'    => 'nullable|boolean',
            'deposit_type'             => 'nullable|in:none,percentage,fixed,full',
            'deposit_value'            => 'nullable|numeric|min:0',
            'online_bookable'          => 'nullable|boolean',
            'show_in_menu'             => 'nullable|boolean',
            'status'                   => 'nullable|in:active,inactive',
            'staff_ids'                => 'nullable|array',
            'staff_ids.*'              => 'integer',
            'color'                    => 'nullable|string|max:7',
            'variants'                 => 'nullable|array',
            'variants.*.name'          => 'nullable|string|max:100',
            'variants.*.price'         => 'nullable|numeric|min:0',
            'addons'                   => 'nullable|array',
            'addons.*.name'            => 'nullable|string|max:100',
            'addons.*.price'           => 'nullable|numeric|min:0',
            'addons_text'              => 'nullable|string|max:2000',
            'dynamic_pricing_enabled'  => 'nullable|boolean',
            'staff_level'              => 'nullable|in:any,standard,senior,apprentice',
        ]);

        $data['dynamic_pricing_enabled'] = $request->boolean('dynamic_pricing_enabled');
        $data['variants']                = Service::normalizePriceRows($data['variants'] ?? null);
        $data['addons']                  = Service::mergeAddonsFromText(
            Service::normalizePriceRows($data['addons'] ?? null),
            $data['addons_text'] ?? null
        );
        unset($data['addons_text']);

        $service = Service::create([...$data, 'salon_id' => $request->attributes->get('salon_id')]);

        if (!empty($data['staff_ids'])) {
            $service->staff()->sync($data['staff_ids']);
        }

        return response()->json(['message' => 'Service created.', 'service' => $service->load(['category','staff'])], 201);
    }

    /* ── GET /services/{id} ─────────────────────────────────────────────── */
    public function show(Request $request, int $id): JsonResponse
    {
        $service = Service::with(['category', 'staff'])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);
        return response()->json($service);
    }

    /* ── PUT /services/{id} ─────────────────────────────────────────────── */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'category_id'              => 'sometimes|integer',
            'name'                     => 'sometimes|string|max:255',
            'description'              => 'nullable|string|max:1000',
            'duration_minutes'         => 'sometimes|integer|min:5|max:480',
            'buffer_minutes'           => 'nullable|integer|min:0|max:60',
            'price'                    => 'sometimes|numeric|min:0',
            'price_from'               => 'nullable|numeric|min:0',
            'price_on_consultation'    => 'nullable|boolean',
            'deposit_type'             => 'nullable|in:none,percentage,fixed,full',
            'deposit_value'            => 'nullable|numeric|min:0',
            'online_bookable'          => 'nullable|boolean',
            'show_in_menu'             => 'nullable|boolean',
            'status'                   => 'nullable|in:active,inactive,archived',
            'staff_ids'                => 'nullable|array',
            'staff_ids.*'              => 'integer',
            'color'                    => 'nullable|string|max:7',
            'variants'                 => 'sometimes|nullable|array',
            'variants.*.name'          => 'nullable|string|max:100',
            'variants.*.price'         => 'nullable|numeric|min:0',
            'addons'                   => 'sometimes|nullable|array',
            'addons.*.name'            => 'nullable|string|max:100',
            'addons.*.price'           => 'nullable|numeric|min:0',
            'addons_text'              => 'sometimes|nullable|string|max:2000',
            'dynamic_pricing_enabled'  => 'sometimes|boolean',
            'staff_level'              => 'nullable|in:any,standard,senior,apprentice',
        ]);

        $service = Service::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        if (array_key_exists('variants', $data)) {
            $data['variants'] = Service::normalizePriceRows($data['variants'] ?? null);
        }
        if (array_key_exists('addons', $data) || array_key_exists('addons_text', $data)) {
            $fromRows = array_key_exists('addons', $data)
                ? ($data['addons'] ?? null)
                : $service->addons;
            $data['addons'] = Service::mergeAddonsFromText(
                Service::normalizePriceRows(is_array($fromRows) ? $fromRows : null),
                $data['addons_text'] ?? null
            );
            unset($data['addons_text']);
        }
        if (array_key_exists('dynamic_pricing_enabled', $data)) {
            $data['dynamic_pricing_enabled'] = $request->boolean('dynamic_pricing_enabled');
        }

        $service->update($data);

        if (array_key_exists('staff_ids', $data)) {
            $service->staff()->sync($data['staff_ids'] ?? []);
        }

        return response()->json(['message' => 'Service updated.', 'service' => $service->load(['category','staff'])]);
    }

    /* ── DELETE /services/{id} ──────────────────────────────────────────── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $service = Service::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $service->update(['status' => 'archived']);
        return response()->json(['message' => 'Service archived.']);
    }

    /* ── POST /services/{id}/duplicate ─────────────────────────────────── */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $service = Service::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $copy = $service->replicate();
        $copy->name   = $service->name . ' (Copy)';
        $copy->status = 'inactive';
        $copy->save();
        $copy->staff()->sync($service->staff->pluck('id'));
        return response()->json(['message' => 'Service duplicated.', 'service' => $copy], 201);
    }

    /* ── PUT /services/reorder ──────────────────────────────────────────── */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer']);
        foreach ($request->ids as $order => $id) {
            Service::where('salon_id', $request->attributes->get('salon_id'))
                   ->where('id', $id)
                   ->update(['sort_order' => $order]);
        }
        return response()->json(['message' => 'Order saved.']);
    }

    /* ── PUT /services/{id}/toggle ──────────────────────────────────────── */
    public function toggleStatus(Request $request, int $id): JsonResponse
    {
        $service = Service::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $service->update(['status' => $service->status === 'active' ? 'inactive' : 'active']);
        return response()->json(['status' => $service->status]);
    }

    /* ── PUT /services/{id}/staff ───────────────────────────────────────── */
    public function assignStaff(Request $request, int $id): JsonResponse
    {
        $request->validate(['staff_ids' => 'required|array', 'staff_ids.*' => 'integer']);
        $service = Service::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $service->staff()->sync($request->staff_ids);
        return response()->json(['message' => 'Staff assigned.']);
    }

    /* ── Category CRUD ─────────────────────────────────────────────────── */
    public function reorderCategories(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        foreach ($request->ids as $order => $id) {
            ServiceCategory::where('salon_id', $request->attributes->get('salon_id'))
                           ->where('id', $id)
                           ->update(['sort_order' => $order]);
        }
        return response()->json(['message' => 'Categories reordered.']);
    }
}
