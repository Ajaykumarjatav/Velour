<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\InventoryAdjustment;
use App\Models\Salon;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryController extends Controller
{
    /**
     * Active salon for this request — must match {@see TenantScope} / route binding (owner or staff).
     */
    private function salon(): Salon
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

    public function index(Request $request)
    {
        $salon      = $this->salon();
        $search     = $request->get('search');
        $categoryId = $request->get('category_id');
        $lowStock   = $request->boolean('low_stock');

        $query = $this->filteredInventoryQuery($salon, $request);

        $items      = $query->orderBy('name')->paginate(25)->withQueryString();
        $categories = InventoryCategory::where('salon_id', $salon->id)->orderBy('name')->get(['id','name']);
        $lowStockCount = InventoryItem::where('salon_id', $salon->id)->whereColumn('stock_quantity', '<', 'min_stock_level')->count();

        $stats = $this->inventoryStats($salon->id);

        return view('inventory.index', array_merge(
            compact('salon', 'items', 'categories', 'search', 'categoryId', 'lowStock', 'lowStockCount'),
            $stats
        ));
    }

    /**
     * @return array{totalSkus: int, lowStockSkus: int, criticalSkus: int, alertCount: int}
     */
    private function inventoryStats(int $salonId): array
    {
        $lowStockSkus = 0;
        $criticalSkus = 0;

        InventoryItem::where('salon_id', $salonId)
            ->select(['id', 'stock_quantity', 'min_stock_level'])
            ->chunkById(500, function ($chunk) use (&$lowStockSkus, &$criticalSkus) {
                foreach ($chunk as $row) {
                    $lvl = InventoryItem::stockStatusLevelFrom((int) $row->stock_quantity, (int) $row->min_stock_level);
                    if ($lvl === 'low') {
                        $lowStockSkus++;
                    }
                    if ($lvl === 'critical') {
                        $criticalSkus++;
                    }
                }
            });

        $totalSkus = InventoryItem::where('salon_id', $salonId)->count();
        $alertCount = $lowStockSkus + $criticalSkus;

        return compact('totalSkus', 'lowStockSkus', 'criticalSkus', 'alertCount');
    }

    private function filteredInventoryQuery(Salon $salon, Request $request)
    {
        $search     = $request->get('search');
        $categoryId = $request->get('category_id');
        $lowStock   = $request->boolean('low_stock');

        $query = InventoryItem::where('salon_id', $salon->id)->with('category');

        if ($search) {
            $query->where(fn($q) =>
                $q->where('name', 'like', "%$search%")
                  ->orWhere('sku', 'like', "%$search%")
                  ->orWhere('barcode', 'like', "%$search%")
            );
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($lowStock) {
            $query->whereColumn('stock_quantity', '<', 'min_stock_level');
        }

        return $query;
    }

    public function export(Request $request): StreamedResponse
    {
        $salon = $this->salon();
        $items = $this->filteredInventoryQuery($salon, $request)->orderBy('name')->get();

        $filename = 'inventory-export-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($items, $salon) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['Name', 'Category', 'SKU', 'Barcode', 'Stock', 'Min', 'Status', 'Retail price', 'Supplier', 'Currency']);

            foreach ($items as $item) {
                $lvl = $item->stockStatusLevel();
                $statusLabel = match ($lvl) {
                    'critical' => 'Critical',
                    'low' => 'Low stock',
                    default => 'In stock',
                };
                fputcsv($out, [
                    $item->name,
                    $item->category?->name ?? '',
                    $item->sku ?? '',
                    $item->barcode ?? '',
                    $item->stock_quantity,
                    $item->min_stock_level,
                    $statusLabel,
                    $item->retail_price,
                    $item->supplier ?? '',
                    $salon->currency ?? 'GBP',
                ]);
            }

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function barcodeLookup(Request $request)
    {
        $salon = $this->salon();
        $code  = trim((string) $request->input('barcode', ''));

        if ($code === '') {
            return redirect()->route('inventory.index')->with('error', 'Enter a barcode or SKU to look up.');
        }

        $item = InventoryItem::where('salon_id', $salon->id)
            ->where(function ($q) use ($code) {
                $q->where('barcode', $code)->orWhere('sku', $code);
            })
            ->first();

        if (!$item) {
            return redirect()->route('inventory.index', $request->except('barcode'))
                ->with('error', 'No product found for that code.');
        }

        return redirect()->route('inventory.edit', $item)->with('success', 'Product matched — you can update details or stock.');
    }

    public function reorder(Request $request)
    {
        $salon = $this->salon();

        $data = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'order_quantity'    => ['required', 'integer', 'min:1', 'max:999999'],
            'supplier'          => ['nullable', 'string', 'max:150'],
        ]);

        $item = InventoryItem::where('salon_id', $salon->id)->findOrFail($data['inventory_item_id']);
        $this->authorise($item);

        $note = 'Reorder requested: '.$data['order_quantity'].' units.';
        if (!empty($data['supplier'])) {
            $note .= ' Supplier: '.$data['supplier'];
        }

        InventoryAdjustment::create([
            'inventory_item_id' => $item->id,
            'staff_id'          => $request->user()->staffProfile?->id,
            'type'              => 'purchase_order',
            'quantity_before'   => $item->stock_quantity,
            'quantity_change'   => 0,
            'quantity_after'    => $item->stock_quantity,
            'note'              => $note,
        ]);

        return back()->with('success', 'Reorder logged. Stock unchanged until goods are received.');
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
        $data['category_id'] = $data['inventory_category_id'] ?? null;
        $data['cost_price']   = $data['cost_price']   ?? 0;
        $data['retail_price'] = $data['retail_price'] ?? 0;
        unset($data['quantity'], $data['low_stock_threshold'], $data['inventory_category_id']);
        InventoryItem::create($data);

        return redirect()->route('inventory.index')->with('success', 'Item added to inventory.');
    }

    public function edit(InventoryItem $inventory)
    {
        $this->authorise($inventory);
        $salon      = $this->salon();
        $categories = InventoryCategory::where('salon_id', $salon->id)->orderBy('name')->get(['id','name']);

        return view('inventory.edit', ['item' => $inventory, 'categories' => $categories]);
    }

    public function update(Request $request, InventoryItem $inventory)
    {
        $this->authorise($inventory);

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
        $data['category_id']  = $data['inventory_category_id'] ?? null;
        $data['cost_price']   = $data['cost_price']   ?? 0;
        $data['retail_price'] = $data['retail_price'] ?? 0;
        unset($data['low_stock_threshold'], $data['inventory_category_id']);
        $inventory->update($data);

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
            'staff_id'          => $request->user()->staffProfile?->id,
            'type'              => $data['type'],
            'quantity_before'   => $before,
            'quantity_after'    => $after,
            'quantity_change'   => $after - $before,
            'note'              => $data['reason'] ?? null,
        ]);

        return back()->with('success', 'Stock adjusted successfully.');
    }

    /** Same rules as {@see adjust} but accepts `inventory_item_id` for hub modals (no URL model binding). */
    public function adjustHub(Request $request)
    {
        $salon = $this->salon();
        $data  = $request->validate([
            'inventory_item_id' => ['required', 'integer', 'exists:inventory_items,id'],
            'type'              => ['required', 'in:add,remove,set'],
            'amount'            => ['required', 'integer', 'min:0'],
            'reason'            => ['nullable', 'string', 'max:255'],
        ]);

        $item = InventoryItem::where('salon_id', $salon->id)->findOrFail($data['inventory_item_id']);

        $inner = Request::create('/', 'POST', [
            'type'   => $data['type'],
            'amount' => $data['amount'],
            'reason' => $data['reason'] ?? null,
        ]);
        $inner->setUserResolver($request->getUserResolver());

        return $this->adjust($inner, $item);
    }

    public function destroy(InventoryItem $inventory)
    {
        $this->authorise($inventory);
        $inventory->delete();

        return redirect()->route('inventory.index')->with('success', 'Item removed.');
    }

    private function authorise(InventoryItem $item): void
    {
        if (Tenant::checkCurrent()) {
            abort_unless((int) $item->salon_id === (int) Tenant::current()->getKey(), 403);

            return;
        }

        abort_unless((int) $item->salon_id === (int) $this->salon()->id, 403);
    }
}
