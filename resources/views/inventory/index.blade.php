@extends('layouts.app')
@section('title', 'Inventory & Retail')
@section('page-title', 'Inventory & Retail')

@section('content')
@php
    $currency = $currentSalon->currency ?? 'GBP';
    $chipBase = request()->except(['category_id', 'page']);
    $filterHidden = request()->except(['search', 'page', 'low_stock']);
@endphp

<div class="space-y-6"
     @if($errors->has('name')) x-init="addOpen = true" @endif
     x-data="{scanOpen:false,addOpen:false,reorderOpen:false,addStockOpen:false,reorderItem:{id:null,name:'',stock:0,min:0},addStockItem:{id:null,name:''},scanBarcode:'',openReorder(it){this.reorderItem=it;this.reorderOpen=true},openAddStock(it){this.addStockItem=it;this.addStockOpen=true},openScan(c){this.scanBarcode=c||'';this.scanOpen=true}}"
     x-on:keydown.escape.window="scanOpen=false;addOpen=false;reorderOpen=false;addStockOpen=false">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <p class="page-subtitle mt-1">
                {{ $totalSkus }} {{ Str::plural('product', $totalSkus) }}
                @if($alertCount > 0)
                    · <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $alertCount }} {{ Str::plural('alert', $alertCount) }}</span>
                @else
                    · <span class="text-emerald-600 dark:text-emerald-400">No stock alerts</span>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2 shrink-0">
            <button type="button" @click="openScan('')" class="btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"/></svg>
                Scan barcode
            </button>
            <a href="{{ route('inventory.export', request()->query()) }}" class="btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Export
            </a>
            <button type="button" @click="addOpen = true" class="btn-primary btn-sm">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add product
            </button>
            <a href="{{ route('inventory.create') }}" class="text-xs text-muted hover:text-body underline decoration-dotted">Full form page</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-velour-100 dark:bg-velour-900/40 p-2.5 text-velour-700 dark:text-velour-300">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-heading leading-tight">{{ $totalSkus }}</p>
                <p class="text-xs text-muted font-medium uppercase tracking-wide">Total SKUs</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-amber-100 dark:bg-amber-900/30 p-2.5 text-amber-700 dark:text-amber-300">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-heading leading-tight">{{ $lowStockSkus }}</p>
                <p class="text-xs text-muted font-medium uppercase tracking-wide">Low stock</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-red-100 dark:bg-red-900/30 p-2.5 text-red-700 dark:text-red-300">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div>
                <p class="text-2xl font-bold text-heading leading-tight">{{ $criticalSkus }}</p>
                <p class="text-xs text-muted font-medium uppercase tracking-wide">Critical</p>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold text-muted uppercase tracking-wider mr-1">Category</span>
        <a href="{{ route('inventory.index', $chipBase) }}"
           class="inline-flex items-center rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ !$categoryId ? 'bg-velour-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-body hover:bg-gray-50 dark:hover:bg-gray-800/80' }}">
            All
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('inventory.index', array_merge($chipBase, ['category_id' => $cat->id])) }}"
               class="inline-flex items-center rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ (string)$categoryId === (string)$cat->id ? 'bg-velour-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-body hover:bg-gray-50 dark:hover:bg-gray-800/80' }}">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end">
        @foreach($filterHidden as $k => $v)
            @if(is_array($v))
                @continue
            @endif
            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
        @endforeach
        <div class="flex-1 min-w-[200px]">
            <label class="form-label">Search</label>
            <input type="text" name="search" value="{{ $search }}" placeholder="Name, SKU, or barcode…" class="form-input w-full">
        </div>
        <label class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border cursor-pointer text-sm min-h-[42px] transition-colors
            {{ $lowStock ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 text-amber-800 dark:text-amber-300' : 'border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-body' }}">
            <input type="hidden" name="low_stock" value="0">
            <input type="checkbox" name="low_stock" value="1" {{ $lowStock ? 'checked' : '' }} class="rounded text-amber-500">
            Low stock only
            @if($lowStockCount > 0)<span class="bg-amber-500 text-white text-xs px-1.5 py-0.5 rounded-md">{{ $lowStockCount }}</span>@endif
        </label>
        <div class="flex gap-2">
            <button type="submit" class="btn-secondary">Apply</button>
            <a href="{{ route('inventory.index') }}" class="btn-outline">Clear</a>
        </div>
    </form>

    <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-x-auto shadow-sm">
        {{-- table-auto avoids collapsing the Product column; sticky Product keeps name visible when scrolling --}}
        <table class="data-table w-full text-sm table-auto [&_tbody_td]:py-2.5 [&_thead_th]:py-2.5">
            <thead>
            <tr>
                <th scope="col" class="sticky left-0 z-30 !text-left min-w-[10rem] max-w-[14rem] sm:max-w-[18rem] pr-2 bg-gray-50 dark:bg-gray-800/90 shadow-[4px_0_12px_-4px_rgba(0,0,0,0.15)] dark:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.4)]">Product</th>
                <th scope="col" class="hidden md:table-cell !text-left whitespace-nowrap">Category</th>
                <th scope="col" class="hidden sm:table-cell !text-left whitespace-nowrap">SKU</th>
                <th scope="col" class="!text-right whitespace-nowrap w-16 sm:w-20">Stock</th>
                <th scope="col" class="hidden lg:table-cell !text-right whitespace-nowrap w-14">Min</th>
                <th scope="col" class="hidden sm:table-cell !text-left whitespace-nowrap">Status</th>
                <th scope="col" class="!text-right align-top min-w-[11rem] sm:min-w-[13rem]">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($items as $item)
                @php
                    $level = $item->stockStatusLevel();
                    $rowTint = match ($level) {
                        'critical' => 'bg-red-50/40 dark:bg-red-950/20',
                        'low' => 'bg-amber-50/40 dark:bg-amber-950/15',
                        default => '',
                    };
                    $stockClass = match ($level) {
                        'critical' => 'text-red-600 dark:text-red-400',
                        'low' => 'text-amber-600 dark:text-amber-400',
                        default => 'text-emerald-600 dark:text-emerald-400',
                    };
                    $statusClass = match ($level) {
                        'critical' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                        'low' => 'bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-200',
                        default => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200',
                    };
                    $statusLabel = match ($level) {
                        'critical' => 'Critical',
                        'low' => 'Low stock',
                        default => 'In stock',
                    };
                @endphp
                @php
                    $stickyBg = match ($level) {
                        'critical' => 'bg-red-50 dark:bg-red-950/40',
                        'low' => 'bg-amber-50 dark:bg-amber-950/25',
                        default => 'bg-white dark:bg-gray-900',
                    };
                @endphp
                <tr class="{{ $rowTint }}">
                    <td class="sticky left-0 z-10 align-middle max-w-[14rem] sm:max-w-[18rem] pr-2 {{ $stickyBg }} shadow-[4px_0_12px_-4px_rgba(0,0,0,0.12)] dark:shadow-[4px_0_12px_-4px_rgba(0,0,0,0.35)] border-r border-gray-100/80 dark:border-gray-800/80">
                        <p class="font-semibold text-heading leading-snug break-words">{{ $item->name }}</p>
                        <p class="text-xs text-muted mt-0.5 leading-snug">
                            @if($item->retail_price)
                                {{ \App\Helpers\CurrencyHelper::format($item->retail_price, $currency) }}/unit
                            @else
                                —
                            @endif
                            @if($item->supplier)
                                <span class="text-muted">·</span> {{ $item->supplier }}
                            @endif
                        </p>
                    </td>
                    <td class="hidden md:table-cell align-middle text-sm {{ $level === 'critical' ? 'text-gray-700 dark:text-gray-200' : 'text-muted' }}">{{ $item->category?->name ?? '—' }}</td>
                    <td class="hidden sm:table-cell font-mono text-xs align-middle break-all sm:break-normal sm:whitespace-nowrap {{ $level === 'critical' ? 'text-red-100 dark:text-red-200/90' : 'text-muted' }}">{{ $item->sku ?? '—' }}</td>
                    <td class="align-middle text-right">
                        <span class="font-bold tabular-nums {{ $stockClass }}">{{ $item->stock_quantity }}</span>
                        <p class="text-[11px] text-muted lg:hidden mt-0.5">Min {{ $item->min_stock_level }}</p>
                    </td>
                    <td class="hidden lg:table-cell text-muted align-middle text-right tabular-nums">{{ $item->min_stock_level }}</td>
                    <td class="hidden sm:table-cell align-middle">
                        <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="relative z-30 align-top sm:align-middle text-right isolate">
                        <div class="flex flex-col items-stretch sm:items-end gap-1.5 pointer-events-auto">
                            @if($level !== 'in_stock')
                                <button type="button"
                                        class="btn-outline btn-sm py-1 w-full sm:w-auto text-center sm:text-left"
                                        x-on:click.stop="openReorder(@js(['id' => $item->id, 'name' => $item->name, 'stock' => (int) $item->stock_quantity, 'min' => (int) $item->min_stock_level]))">
                                    Reorder
                                </button>
                            @endif
                            <div class="flex flex-wrap justify-end gap-1">
                                <a href="{{ route('inventory.edit', $item) }}"
                                   class="btn-outline btn-sm py-1 px-2 relative z-30">Edit</a>
                                <button type="button"
                                        class="btn-outline btn-sm py-1 px-2"
                                        x-on:click.stop="openAddStock(@js(['id' => $item->id, 'name' => $item->name]))">
                                    Add stock
                                </button>
                                <button type="button"
                                        class="btn-outline btn-sm py-1 px-2"
                                        x-on:click.stop="openScan(@js($item->barcode ?? ''))">
                                    Scan
                                </button>
                                <form action="{{ route('inventory.destroy', $item) }}" method="POST" class="inline-flex shrink-0 relative z-30"
                                      onsubmit="return confirm('Remove this product from inventory?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-outline btn-sm py-1 px-2 text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-950/30">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-muted">No inventory items</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $items->links() }}</div>

    {{-- Barcode lookup --}}
    <div x-show="scanOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50" @click.self="scanOpen = false">
        <div class="card max-w-md w-full p-6 shadow-xl" @click.stop>
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-lg font-semibold text-heading">Barcode scanner</h2>
                <button type="button" class="text-muted hover:text-body p-1" @click="scanOpen = false" aria-label="Close">✕</button>
            </div>
            <div class="rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-muted mb-4">
                <p class="text-sm">Point a camera scanner at the barcode, or type the code below.</p>
                <p class="text-xs mt-2">In-browser camera scanning is not enabled here — manual entry always works.</p>
            </div>
            <form action="{{ route('inventory.barcode-lookup') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Barcode or SKU</label>
                    <input type="text" name="barcode" x-model="scanBarcode" placeholder="Enter barcode or SKU…" class="form-input" autocomplete="off">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-outline" @click="scanOpen = false">Cancel</button>
                    <button type="submit" class="btn-primary">Look up product</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add product (same validation as full create page) --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50 overflow-y-auto" @click.self="addOpen = false">
        <div class="card max-w-lg w-full p-6 shadow-xl my-8" @click.stop>
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-lg font-semibold text-heading">Add new product</h2>
                <button type="button" class="text-muted hover:text-body p-1" @click="addOpen = false">✕</button>
            </div>
            <form action="{{ route('inventory.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="form-label">Product name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required value="{{ old('name') }}" class="form-input @error('name') form-input-error @enderror" placeholder="e.g. Argan oil shampoo 250ml">
                    @error('name')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Category</label>
                        <select name="inventory_category_id" class="form-select">
                            <option value="">—</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" @selected(old('inventory_category_id') == $cat->id)>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="form-label">SKU</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" class="form-input font-mono">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Unit price ({{ \App\Helpers\CurrencyHelper::symbol($currency) }})</label>
                        <input type="number" name="retail_price" min="0" step="0.01" value="{{ old('retail_price') }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Cost price ({{ \App\Helpers\CurrencyHelper::symbol($currency) }})</label>
                        <input type="number" name="cost_price" min="0" step="0.01" value="{{ old('cost_price') }}" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Initial stock <span class="text-red-500">*</span></label>
                        <input type="number" name="quantity" min="0" required value="{{ old('quantity', 0) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Min stock alert <span class="text-red-500">*</span></label>
                        <input type="number" name="low_stock_threshold" min="0" required value="{{ old('low_stock_threshold', 5) }}" class="form-input">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label">Supplier</label>
                        <input type="text" name="supplier" value="{{ old('supplier') }}" class="form-input" placeholder="Supplier name">
                    </div>
                    <div>
                        <label class="form-label">Barcode / EAN</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}" class="form-input font-mono">
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline" @click="addOpen = false">Cancel</button>
                    <button type="submit" class="btn-primary">Add product</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Reorder --}}
    <div x-show="reorderOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50" @click.self="reorderOpen = false">
        <div class="card max-w-md w-full p-6 shadow-xl" @click.stop>
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-lg font-semibold text-heading">Reorder stock</h2>
                <button type="button" class="text-muted hover:text-body p-1" @click="reorderOpen = false">✕</button>
            </div>
            <div class="rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 p-3 text-sm text-body mb-4">
                <p class="font-semibold" x-text="reorderItem.name"></p>
                <p class="text-muted text-xs mt-1">
                    Current stock: <span x-text="reorderItem.stock"></span>
                    · Minimum: <span x-text="reorderItem.min"></span>
                </p>
            </div>
            <form action="{{ route('inventory.reorder') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="inventory_item_id" :value="reorderItem.id">
                <div>
                    <label class="form-label">Quantity to order</label>
                    <input type="number" name="order_quantity" min="1" value="20" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Supplier</label>
                    <input type="text" name="supplier" class="form-input" placeholder="Supplier or account name">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-outline" @click="reorderOpen = false">Cancel</button>
                    <button type="submit" class="btn-primary">Place order</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Add stock --}}
    <div x-show="addStockOpen" x-cloak class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50" @click.self="addStockOpen = false">
        <div class="card max-w-md w-full p-6 shadow-xl" @click.stop>
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-lg font-semibold text-heading">Add stock</h2>
                <button type="button" class="text-muted hover:text-body p-1" @click="addStockOpen = false">✕</button>
            </div>
            <p class="text-sm text-muted mb-4" x-text="addStockItem.name"></p>
            <form action="{{ route('inventory.adjust-hub') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="inventory_item_id" :value="addStockItem.id">
                <input type="hidden" name="type" value="add">
                <div>
                    <label class="form-label">Quantity to add</label>
                    <input type="number" name="amount" min="1" value="1" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Note (optional)</label>
                    <input type="text" name="reason" class="form-input" placeholder="Delivery ref, reason…">
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="btn-outline" @click="addStockOpen = false">Cancel</button>
                    <button type="submit" class="btn-primary">Update stock</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
