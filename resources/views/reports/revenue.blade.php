@extends('layouts.app')
@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Total Revenue</p>
        <p class="text-3xl font-bold text-heading">@money($totalRevenue)</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Transactions</p>
        <p class="text-3xl font-bold text-heading">{{ $totalTransactions }}</p>
    </div>
</div>

<div class="table-wrap mb-6">
    <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Daily breakdown</h3>
    <table class="data-table">
        <thead>
        <tr>
            <th>Date</th>
            <th class="text-right">Transactions</th>
            <th class="text-right">Revenue</th>
        </tr>
        </thead>
        <tbody>
        @forelse($daily as $row)
        <tr>
            <td class="font-medium text-body">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
            <td class="text-right text-muted">{{ $row->transactions }}</td>
            <td class="text-right font-bold text-heading">@money($row->revenue)</td>
        </tr>
        @empty
        <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-muted">No data for this period</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="table-wrap">
    <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">By Payment Method</h3>
    <table class="data-table">
        <thead>
        <tr>
            <th>Method</th>
            <th class="text-right">Count</th>
            <th class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($byMethod as $row)
        <tr>
            <td class="font-medium text-body capitalize">{{ str_replace('_',' ',$row->payment_method) }}</td>
            <td class="text-right text-muted">{{ $row->count }}</td>
            <td class="text-right font-bold text-heading">@money($row->total)</td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
