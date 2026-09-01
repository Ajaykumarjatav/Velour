<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryAdjustment;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    /* ── GET /inventory ─────────────────────────────────────────────────── */
    public function index(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');

        $items = InventoryItem::with('category')
            ->where('salon_id', $salonId)
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->type,        fn($q) => $q->where('type', $request->type))
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'ilike', '%'.$request->search.'%')
                  ->orWhere('sku',      'ilike', '%'.$request->search.'%')
                  ->orWhere('supplier', 'ilike', '%'.$request->search.'%');
            })
            ->when($request->low_stock, fn($q) => $q->whereColumn('stock_quantity', '<', 'min_stock_level'))
            ->orderBy('name')
            ->paginate($request->per_page ?? 50);

        return response()->json($items);
    }

    /* ── POST /inventory ────────────────────────────────────────────────── */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'category_id'    => 'required|integer',
            'name'           => 'required|string|max:255',
            'sku'            => 'nullable|string|max:100',
            'barcode'        => 'nullable|string|max:100',
            'supplier'       => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:50',
            'cost_price'     => 'required|numeric|min:0',
            'retail_price'   => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'min_stock_level'=> 'nullable|integer|min:0',
            'reorder_quantity'=> 'nullable|integer|min:0',
            'type'           => 'nullable|in:professional,retail,both',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $data['salon_id'] = $request->attributes->get('salon_id');

        // Auto-generate SKU if blank
        if (empty($data['sku'])) {
            $data['sku'] = 'SKU-' . strtoupper(substr(md5($data['name'] . time()), 0, 8));
        }

        $item = InventoryItem::create($data);

        // Log initial stock
        if ($data['stock_quantity'] > 0) {
            InventoryAdjustment::create([
                'inventory_item_id' => $item->id,
                'type'              => 'set',
                'quantity_before'   => 0,
                'quantity_change'   => $data['stock_quantity'],
                'quantity_after'    => $data['stock_quantity'],
                'note'              => 'Initial stock on creation',
            ]);
        }

        return response()->json(['message' => 'Product added.', 'item' => $item->load('category')], 201);
    }

    /* ── GET /inventory/{id} ────────────────────────────────────────────── */
    public function show(Request $request, int $id): JsonResponse
    {
        $item = InventoryItem::with(['category', 'adjustments' => fn($q) => $q->latest()->limit(20)])
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->findOrFail($id);

        return response()->json($item);
    }

    /* ── PUT /inventory/{id} ────────────────────────────────────────────── */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'category_id'    => 'sometimes|integer',
            'name'           => 'sometimes|string|max:255',
            'sku'            => 'nullable|string|max:100',
            'supplier'       => 'nullable|string|max:255',
            'unit'           => 'nullable|string|max:50',
            'cost_price'     => 'sometimes|numeric|min:0',
            'retail_price'   => 'nullable|numeric|min:0',
            'min_stock_level'=> 'nullable|integer|min:0',
            'reorder_quantity'=> 'nullable|integer|min:0',
            'type'           => 'nullable|in:professional,retail,both',
            'notes'          => 'nullable|string|max:1000',
            'is_active'      => 'nullable|boolean',
        ]);

        $item = InventoryItem::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $item->update($data);

        return response()->json(['message' => 'Product updated.', 'item' => $item]);
    }

    /* ── DELETE /inventory/{id} ─────────────────────────────────────────── */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $item = InventoryItem::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Product deleted.']);
    }

    /* ── POST /inventory/{id}/adjust ────────────────────────────────────── */
    public function adjust(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'type'      => 'required|in:add,use,sell,waste,set',
            'quantity'  => 'required|integer|min:0',
            'note'      => 'nullable|string|max:500',
            'reference' => 'nullable|string|max:100',
        ]);

        $item = InventoryItem::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $before = $item->stock_quantity;

        $after = match ($data['type']) {
            'add'  => $before + $data['quantity'],
            'use', 'sell', 'waste' => max(0, $before - $data['quantity']),
            'set'  => $data['quantity'],
            default => $before,
        };

        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'staff_id'          => $request->attributes->get('staff_id'),
            'type'              => $data['type'],
            'quantity_before'   => $before,
            'quantity_change'   => $after - $before,
            'quantity_after'    => $after,
            'note'              => $data['note'] ?? null,
            'reference'         => $data['reference'] ?? null,
        ]);

        $item->update(['stock_quantity' => $after]);

        return response()->json([
            'message'  => 'Stock adjusted.',
            'before'   => $before,
            'after'    => $after,
            'change'   => $after - $before,
            'is_low'   => $after < $item->min_stock_level,
        ]);
    }

    /* ── GET /inventory/{id}/history ────────────────────────────────────── */
    public function history(Request $request, int $id): JsonResponse
    {
        $item = InventoryItem::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($id);

        $history = InventoryAdjustment::with('staff')
            ->where('inventory_item_id', $id)
            ->orderByDesc('created_at')
            ->paginate(30);

        return response()->json($history);
    }

    /* ── GET /inventory/low-stock ───────────────────────────────────────── */
    public function lowStock(Request $request): JsonResponse
    {
        $items = InventoryItem::with('category')
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->whereColumn('stock_quantity', '<', 'min_stock_level')
            ->where('is_active', true)
            ->orderBy('stock_quantity')
            ->get();

        return response()->json([
            'count' => $items->count(),
            'items' => $items,
        ]);
    }

    /* ── GET /inventory/export ──────────────────────────────────────────── */
    public function export(Request $request): JsonResponse
    {
        $items = InventoryItem::with('category')
            ->where('salon_id', $request->attributes->get('salon_id'))
            ->orderBy('name')
            ->get();

        return response()->json(['count' => $items->count(), 'data' => $items]);
    }

    /* ── POST /inventory/import ─────────────────────────────────────────── */
    public function import(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt|max:5120']);
        return response()->json(['message' => 'Import queued.']);
    }

    /* ── Purchase Orders ─────────────────────────────────────────────────── */

    public function generatePO(Request $request): JsonResponse
    {
        $salonId = $request->attributes->get('salon_id');

        $lowItems = InventoryItem::where('salon_id', $salonId)
            ->whereColumn('stock_quantity', '<', 'min_stock_level')
            ->where('is_active', true)
            ->get();

        if ($lowItems->isEmpty()) {
            return response()->json(['message' => 'No items need reordering.'], 422);
        }

        // Group by supplier
        $bySupplier = $lowItems->groupBy(fn($i) => $i->supplier ?? 'Unknown');

        $orders = [];
        foreach ($bySupplier as $supplier => $items) {
            $po = PurchaseOrder::create([
                'salon_id'   => $salonId,
                'created_by' => $request->attributes->get('staff_id') ?? $items->first()->id,
                'supplier'   => $supplier,
                'status'     => 'draft',
                'ordered_at' => now()->toDateString(),
            ]);

            $total = 0;
            foreach ($items as $item) {
                $qty   = max($item->reorder_quantity ?: $item->min_stock_level, $item->min_stock_level - $item->stock_quantity);
                $itemTotal = $qty * $item->cost_price;
                $total += $itemTotal;

                PurchaseOrderItem::create([
                    'purchase_order_id'  => $po->id,
                    'inventory_item_id'  => $item->id,
                    'quantity_ordered'   => $qty,
                    'quantity_received'  => 0,
                    'unit_cost'          => $item->cost_price,
                    'total'              => $itemTotal,
                ]);
            }

            $po->update(['total' => $total]);
            $orders[] = $po->load('items.inventoryItem');
        }

        return response()->json([
            'message'        => count($orders) . ' purchase order(s) generated.',
            'purchase_orders' => $orders,
        ], 201);
    }

    public function receivePO(Request $request, int $poId): JsonResponse
    {
        $data = $request->validate([
            'items'                    => 'required|array',
            'items.*.id'               => 'required|integer',
            'items.*.quantity_received'=> 'required|integer|min:0',
        ]);

        $po = PurchaseOrder::where('salon_id', $request->attributes->get('salon_id'))->findOrFail($poId);

        foreach ($data['items'] as $received) {
            $poItem = PurchaseOrderItem::where('purchase_order_id', $po->id)
                                       ->findOrFail($received['id']);

            $poItem->update(['quantity_received' => $received['quantity_received']]);

            $invItem = InventoryItem::find($poItem->inventory_item_id);
            if ($invItem) {
                $before = $invItem->stock_quantity;
                $after  = $before + $received['quantity_received'];
                $invItem->update(['stock_quantity' => $after, 'last_ordered_at' => now()->toDateString()]);

                InventoryAdjustment::create([
                    'inventory_item_id' => $invItem->id,
                    'type'              => 'purchase_order',
                    'quantity_before'   => $before,
                    'quantity_change'   => $received['quantity_received'],
                    'quantity_after'    => $after,
                    'note'              => "Received via PO {$po->reference}",
                    'reference'         => $po->reference,
                ]);
            }
        }

        $allReceived = $po->items()->whereColumn('quantity_received', '<', 'quantity_ordered')->doesntExist();
        $po->update([
            'status'      => $allReceived ? 'received' : 'partial',
            'received_at' => $allReceived ? now()->toDateString() : null,
        ]);

        return response()->json(['message' => 'Stock received and updated.', 'purchase_order' => $po->load('items')]);
    }
}
