<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    private function salon()
    {
        return Auth::user()->salons()->firstOrFail();
    }

    public function index(Request $request)
    {
        $salon      = $this->salon();
        $search     = $request->get('search');
        $categoryId = $request->get('category_id');
        $lowStock   = $request->boolean('low_stock');

        $query = InventoryItem::where('salon_id', $salon->id)
            ->with('category');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%")
                  ->orWhere('barcode', 'like', "%$search%")
            );
        }

        if ($categoryId) {
            $query->where('inventory_category_id', $categoryId);
        }

        if ($lowStock) {
            $query->whereColumn('stock_quantity', '<=', 'min_stock_level');
        }

        $items      = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = InventoryCategory::where('salon_id', $salon->id)->orderBy('name')->get(['id','name']);
        $lowStockCount = InventoryItem::where('salon_id', $salon->id)->whereColumn('stock_quantity', '<=', 'min_stock_level')->count();

        return view('inventory.index', compact('salon', 'items', 'categories', 'search', 'categoryId', 'lowStock', 'lowStockCount'));
    }

    public function create()
    {
        $salon      = $this->salon();
        $categories = InventoryCategory::where('salon_id', $salon->id)->orderBy('name')->get(['id','name']);

        return view('inventory.create', compact('salon', 'categories'));
    }

    public function store(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'name'                   => ['required', 'string', 'max:150'],
            'sku'                    => ['nullable', 'string', 'max:50'],
            'barcode'                => ['nullable', 'string', 'max:50'],
            'inventory_category_id'  => ['nullable', 'exists:inventory_categories,id'],
            'quantity'               => ['required', 'integer', 'min:0'],
            'low_stock_threshold'    => ['required', 'integer', 'min:0'],
            'cost_price'             => ['nullable', 'numeric', 'min:0'],
            'retail_price'           => ['nullable', 'numeric', 'min:0'],
            'supplier'               => ['nullable', 'string', 'max:150'],
        ]);

        $data['salon_id'] = $salon->id;
        $data['stock_quantity'] = $data['quantity'];
        $data['min_stock_level'] = $data['low_stock_threshold'];
        unset($data['quantity'], $data['low_stock_threshold']);
        InventoryItem::create($data);

        return redirect()->route('inventory.index')->with('success', 'Item added to inventory.');
    }

    public function edit(InventoryItem $item)
    {
        $this->authorise($item);
        $salon      = $this->salon();
        $categories = InventoryCategory::where('salon_id', $salon->id)->orderBy('name')->get(['id','name']);

        return view('inventory.edit', compact('item', 'categories'));
    }

    public function update(Request $request, InventoryItem $item)
    {
        $this->authorise($item);

        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'sku'                   => ['nullable', 'string', 'max:50'],
            'barcode'               => ['nullable', 'string', 'max:50'],
            'inventory_category_id' => ['nullable', 'exists:inventory_categories,id'],
            'low_stock_threshold'   => ['required', 'integer', 'min:0'],
            'cost_price'            => ['nullable', 'numeric', 'min:0'],
            'retail_price'          => ['nullable', 'numeric', 'min:0'],
            'supplier'              => ['nullable', 'string', 'max:150'],
        ]);

        $data['min_stock_level'] = $data['low_stock_threshold'];
        unset($data['low_stock_threshold']);
        $item->update($data);

        return redirect()->route('inventory.index')->with('success', 'Item updated.');
    }

    public function adjust(Request $request, InventoryItem $item)
    {
        $this->authorise($item);

        $data = $request->validate([
            'type'   => ['required', 'in:add,remove,set'],
            'amount' => ['required', 'integer', 'min:0'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $before = $item->stock_quantity;
        $after  = match ($data['type']) {
            'add'    => $before + $data['amount'],
            'remove' => max(0, $before - $data['amount']),
            'set'    => $data['amount'],
        };

        $item->update(['stock_quantity' => $after]);

        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'salon_id'          => $item->salon_id,
            'type'              => $data['type'],
            'quantity_before'   => $before,
            'quantity_after'    => $after,
            'quantity_change'   => $after - $before,
            'reason'            => $data['reason'] ?? null,
            'adjusted_by'       => Auth::id(),
        ]);

        return back()->with('success', 'Stock adjusted successfully.');
    }

    public function destroy(InventoryItem $item)
    {
        $this->authorise($item);
        $item->delete();

        return redirect()->route('inventory.index')->with('success', 'Item removed.');
    }

    private function authorise(InventoryItem $item): void
    {
        abort_unless($item->salon_id === $this->salon()->id, 403);
    }
}
