@extends('layouts.app')
@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

{{-- Summary --}}
<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Total Revenue</p>
        <p class="text-3xl font-bold text-heading">Â£{{ number_format($totalRevenue, 2) }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Transactions</p>
        <p class="text-3xl font-bold text-heading">{{ $totalTransactions }}</p>
    </div>
</div>

{{-- Daily table --}}
<div class="table-wrap mb-6">
    <h3 class="px-6 py-4 font-semibold text-gray-900 border-b border-gray-100">Daily Breakdown</h3>
    <table class="data-table">
        <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase">Date</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Transactions</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Revenue</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($daily as $row)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <td class="px-5 py-3 font-medium text-body">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
            <td class="px-5 py-3 text-right text-muted">{{ $row->transactions }}</td>
            <td class="px-5 py-3 text-right font-bold text-heading">Â£{{ number_format($row->revenue, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-muted">No data for this period</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

{{-- Payment methods --}}
<div class="table-wrap">
    <h3 class="px-6 py-4 font-semibold text-gray-900 border-b border-gray-100">By Payment Method</h3>
    <table class="data-table">
        <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase">Method</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Count</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Total</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @foreach($byMethod as $row)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <td class="px-5 py-3 font-medium text-body capitalize">{{ str_replace('_',' ',$row->payment_method) }}</td>
            <td class="px-5 py-3 text-right text-muted">{{ $row->count }}</td>
            <td class="px-5 py-3 text-right font-bold text-heading">Â£{{ number_format($row->total, 2) }}</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection

