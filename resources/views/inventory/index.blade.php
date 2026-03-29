@extends('layouts.app')
@section('title', 'Inventory')
@section('page-title', 'Inventory')
@section('content')

<div class="flex flex-col lg:flex-row gap-4 mb-6 items-start">
    <form action="{{ route('inventory.index') }}" method="GET" class="flex flex-1 flex-col gap-3 min-w-0 w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search name or SKU…" class="form-input w-full min-w-0">
            <select name="category_id" class="form-select w-full min-w-0">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 px-3 py-2 rounded-xl border cursor-pointer text-sm transition-colors min-h-[42px]
                          {{ $lowStock ? 'bg-amber-50 dark:bg-amber-900/20 border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400' : 'border-gray-300 dark:border-gray-700 text-body bg-white dark:bg-gray-800 hover:bg-amber-50 dark:hover:bg-amber-900/20' }}">
                <input type="checkbox" name="low_stock" value="1" {{ $lowStock ? 'checked' : '' }} onchange="this.form.submit()" class="rounded text-amber-500">
                Low stock
                @if($lowStockCount > 0)<span class="bg-amber-400 text-white text-xs px-1.5 py-0.5 rounded-md">{{ $lowStockCount }}</span>@endif
            </label>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('inventory.index') }}" class="btn-outline">Clear</a>
        </div>
    </form>
    <a href="{{ route('inventory.create') }}" class="btn-primary flex-shrink-0 w-full sm:w-auto text-center">+ Add Item</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Item</th>
            <th class="hidden sm:table-cell">SKU</th>
            <th class="hidden md:table-cell">Category</th>
            <th>Stock</th>
            <th class="hidden lg:table-cell text-right">Retail</th>
            <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($items as $item)
        @php $isLow = $item->quantity <= $item->low_stock_threshold; @endphp
        <tr class="{{ $isLow ? 'bg-amber-50/30 dark:bg-amber-900/10' : '' }}">
            <td>
                <p class="font-semibold text-heading">{{ $item->name }}</p>
                @if($item->supplier)<p class="text-xs text-muted">{{ $item->supplier }}</p>@endif
            </td>
            <td class="hidden sm:table-cell text-muted font-mono text-xs">{{ $item->sku ?? '—' }}</td>
            <td class="hidden md:table-cell text-muted">{{ $item->category?->name ?? '—' }}</td>
            <td>
                <span class="font-bold {{ $isLow ? 'text-amber-600 dark:text-amber-400' : 'text-heading' }}">{{ $item->quantity }}</span>
                @if($isLow)<span class="ml-1 text-xs text-amber-500">⚠ Low</span>@endif
                <p class="text-xs text-muted">Min: {{ $item->low_stock_threshold }}</p>
            </td>
            <td class="hidden lg:table-cell font-semibold text-heading">
                {{ $item->retail_price ? \App\Helpers\CurrencyHelper::format($item->retail_price, $currentSalon->currency ?? 'GBP') : '—' }}
            </td>
            <td>
                <div class="flex justify-end gap-2">
                    <button onclick="document.getElementById('adjust-{{ $item->id }}').classList.remove('hidden')"
                            class="text-xs text-link font-medium">Adjust</button>
                    <a href="{{ route('inventory.edit', $item->id) }}" class="text-xs text-muted hover:text-body font-medium">Edit</a>
                </div>
                <div id="adjust-{{ $item->id }}" class="hidden mt-2">
                    <form action="{{ route('inventory.adjust', $item->id) }}" method="POST" class="flex gap-1 items-center">
                        @csrf
                        <select name="type" class="text-xs px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-body">
                            <option value="add">Add</option>
                            <option value="remove">Remove</option>
                            <option value="set">Set to</option>
                        </select>
                        <input type="number" name="amount" min="0" value="1"
                               class="w-16 text-xs px-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-body">
                        <button type="submit" class="text-xs px-2 py-1.5 bg-velour-600 text-white rounded-lg font-medium">OK</button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-muted">No inventory items</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $items->links() }}</div>

@endsection
