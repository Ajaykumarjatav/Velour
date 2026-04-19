@extends('layouts.app')
@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')
@section('content')

@php
    $tzAbbr = \App\Support\SalonTime::abbrev($salon);
@endphp

<div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-6">
    <form action="{{ route('reports.show', 'revenue') }}" method="GET" class="flex flex-wrap items-end gap-3">
        <div>
            <label class="form-label text-xs">From</label>
            <input type="date" name="from" value="{{ $from }}" class="form-input w-auto min-w-[160px]">
        </div>
        <div>
            <label class="form-label text-xs">To</label>
            <input type="date" name="to" value="{{ $to }}" class="form-input w-auto min-w-[160px]">
        </div>
        <div>
            <label class="form-label text-xs">Staff</label>
            <select name="staff_id" class="form-select w-auto min-w-[160px]">
                <option value="">All</option>
                @foreach($staffList as $st)
                <option value="{{ $st->id }}" {{ (string)($staffId ?? '') === (string) $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="form-label text-xs">Payment</label>
            <select name="payment_method" class="form-select w-auto min-w-[140px]">
                <option value="">All</option>
                @foreach(['cash','card','stripe','other'] as $pm)
                <option value="{{ $pm }}" {{ ($paymentMethod ?? '') === $pm ? 'selected' : '' }}>{{ ucfirst($pm) }}</option>
                @endforeach
            </select>
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer pb-2">
            <input type="checkbox" name="compare" value="1" class="rounded border-gray-300 text-velour-600" {{ !empty($compare) ? 'checked' : '' }}>
            Compare previous period
        </label>
        <button type="submit" class="btn-primary">Apply</button>
    </form>
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('reports.index') }}" class="text-sm text-link font-medium whitespace-nowrap">← All reports</a>
        <a href="{{ route('reports.revenue.export', array_filter(['from' => $from, 'to' => $to, 'staff_id' => $staffId ?? null, 'payment_method' => $paymentMethod ?? null])) }}"
           class="btn-outline btn-sm">Export CSV</a>
    </div>
</div>

<div class="alert-info mb-6 text-sm space-y-2">
    <p><strong>Recognition:</strong> totals include completed POS sales only, dated by <code class="text-xs bg-white/50 dark:bg-gray-800 px-1 rounded">completed_at</code> when present, otherwise <code class="text-xs bg-white/50 dark:bg-gray-800 px-1 rounded">created_at</code>. Date range is interpreted in your business timezone ({{ $tzAbbr }}).</p>
    <p><strong>Currency:</strong> all amounts are in <strong>@currencyLabel</strong>.</p>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Revenue</p>
        <p class="text-2xl font-bold text-heading">@money($totalRevenue)</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Transactions</p>
        <p class="text-2xl font-bold text-heading">{{ $totalTransactions }}</p>
    </div>
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Appointments (scheduled)</p>
        <p class="text-2xl font-bold text-heading">{{ $appointmentCountScheduled }}</p>
        <p class="text-xs text-muted mt-1">Starts in range · excl. cancelled / no-show</p>
    </div>
    @if(!empty($compare) && $prevTotalRevenue !== null)
    <div class="stat-card">
        <p class="text-xs font-semibold text-muted uppercase tracking-wider mb-2">Previous period</p>
        <p class="text-2xl font-bold text-heading">@money($prevTotalRevenue)</p>
        <p class="text-xs text-muted mt-1">{{ \Carbon\Carbon::parse($prevFrom)->format('d M') }} – {{ \Carbon\Carbon::parse($prevTo)->format('d M Y') }} · {{ $prevAppointmentCount }} appts</p>
    </div>
    @endif
</div>

@if(!empty($compare) && $prevTotalRevenue !== null && $prevTotalRevenue > 0)
@php $delta = round((($totalRevenue - $prevTotalRevenue) / $prevTotalRevenue) * 100, 1); @endphp
<p class="text-sm text-muted mb-6">vs previous period: <span class="font-semibold {{ $delta >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ $delta >= 0 ? '▲' : '▼' }} {{ abs($delta) }}%</span></p>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">By staff</h3>
        <table class="data-table">
            <thead>
            <tr>
                <th>Staff</th>
                <th class="text-right">Count</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($byStaff as $row)
            <tr>
                <td class="font-medium text-body">{{ $row->name }}</td>
                <td class="text-right text-muted">{{ $row->count }}</td>
                <td class="text-right font-bold text-heading">@money($row->total)</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-6 text-center text-sm text-muted">No data for filters</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Line items (services)</h3>
        <table class="data-table">
            <thead>
            <tr>
                <th>Service</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($byService as $row)
            <tr>
                <td class="font-medium text-body">{{ $row->name }}</td>
                <td class="text-right text-muted">{{ (int) $row->qty }}</td>
                <td class="text-right font-bold text-heading">@money($row->total)</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-5 py-6 text-center text-sm text-muted">No service lines in period</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="table-wrap mb-6">
    <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Daily breakdown (business days)</h3>
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
            <td class="font-medium text-body">{{ \Carbon\Carbon::parse($row->date)->format('D, d M Y') }}</td>
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
    <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">By payment method</h3>
    <table class="data-table">
        <thead>
        <tr>
            <th>Method</th>
            <th class="text-right">Count</th>
            <th class="text-right">Total</th>
        </tr>
        </thead>
        <tbody>
        @forelse($byMethod as $row)
        <tr>
            <td class="font-medium text-body capitalize">{{ str_replace('_',' ', $row->payment_method ?? '—') }}</td>
            <td class="text-right text-muted">{{ $row->count }}</td>
            <td class="text-right font-bold text-heading">@money($row->total)</td>
        </tr>
        @empty
        <tr><td colspan="3" class="px-5 py-6 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
