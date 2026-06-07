@extends('layouts.app')
@section('title', 'Inventory Report')
@section('page-title', 'Inventory Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">{{ $total_products }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Active SKUs</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-velour-600 dark:text-velour-400">@money($total_value_retail)</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Retail value</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">@money($total_value_cost)</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Cost value</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $low_stock_count }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Low stock</p>
    </div>
    <div class="stat-card text-center col-span-2 sm:col-span-1">
        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $out_of_stock }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Out of stock</p>
    </div>
</div>

@if($adjustments->isNotEmpty())
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    @foreach($adjustments as $row)
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">{{ $row->count }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">{{ ucfirst(str_replace('_', ' ', $row->type)) }} (period)</p>
    </div>
    @endforeach
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <div class="table-wrap">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
            <h3 class="font-semibold text-heading">Low stock items</h3>
            <a href="{{ route('inventory.index', ['low_stock' => 1]) }}" class="text-xs text-link font-medium">Manage inventory →</a>
        </div>
        <table class="data-table">
            <thead>
            <tr>
                <th>Product</th>
                <th class="text-right">On hand</th>
                <th class="text-right hidden sm:table-cell">Min</th>
            </tr>
            </thead>
            <tbody>
            @forelse($lowStockItems as $item)
            <tr>
                <td class="font-semibold text-heading">{{ $item->name }}</td>
                <td class="text-right text-amber-600 dark:text-amber-400 font-medium">{{ $item->stock_quantity }}</td>
                <td class="text-right text-muted hidden sm:table-cell">{{ $item->min_stock_level }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-muted">All items are above minimum levels</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Recent adjustments</h3>
        <table class="data-table">
            <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Change</th>
                <th class="hidden sm:table-cell">Type</th>
            </tr>
            </thead>
            <tbody>
            @forelse($recentAdjustments as $adj)
            <tr>
                <td class="font-medium text-heading">{{ $adj->item?->name ?? '—' }}</td>
                <td class="text-right font-mono {{ $adj->quantity_change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                    {{ $adj->quantity_change >= 0 ? '+' : '' }}{{ $adj->quantity_change }}
                </td>
                <td class="text-muted text-sm hidden sm:table-cell">{{ ucfirst(str_replace('_', ' ', $adj->type)) }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-muted">No adjustments in this period</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
