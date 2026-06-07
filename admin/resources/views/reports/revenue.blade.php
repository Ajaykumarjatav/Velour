@extends('layouts.app')
@section('title', 'Revenue Report')
@section('page-title', 'Revenue Report')
@section('content')

@php
    $tzAbbr = \App\Support\SalonTime::abbrev($salon);
    $salonToday = \App\Support\SalonTime::todayDateString($salon);
@endphp

<div class="mb-4 space-y-0.5 text-[11px] leading-snug text-gray-400 dark:text-gray-500">
    <p><strong class="font-medium text-gray-500 dark:text-gray-400">Recognition:</strong> totals include completed POS sales only, dated by <code class="text-[10px] bg-gray-100/80 dark:bg-gray-800/80 px-1 rounded">completed_at</code> when present, otherwise <code class="text-[10px] bg-gray-100/80 dark:bg-gray-800/80 px-1 rounded">created_at</code>. Date range is interpreted in your business timezone ({{ $tzAbbr }}).</p>
    <p><strong class="font-medium text-gray-500 dark:text-gray-400">Currency:</strong> all amounts are in <strong class="font-medium text-gray-500 dark:text-gray-400">@currencyLabel</strong>.</p>
</div>

<div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/40 p-4 sm:p-5 mb-6 overflow-visible">
    <form action="{{ route('reports.show', 'revenue') }}" method="GET">
        <div class="flex flex-wrap xl:flex-nowrap items-end gap-3 sm:gap-4">
            <div class="flex flex-col gap-1.5 w-full sm:w-auto sm:min-w-[14rem] sm:max-w-xs xl:flex-1 xl:max-w-sm shrink-0">
                <label class="form-label text-xs mb-0">Date range</label>
                <x-date-range-picker
                    :from-value="$from"
                    :to-value="$to"
                    :salon-today="$salonToday"
                    class="w-full" />
            </div>
            <div class="flex flex-col gap-1.5 w-[calc(50%-0.375rem)] sm:w-36 shrink-0">
                <label class="form-label text-xs mb-0" for="rev-report-staff-trigger">Staff</label>
                <x-searchable-select
                    id="rev-report-staff"
                    name="staff_id"
                    wrapper-class="w-full"
                    :search-url="route('lookup.staff', ['compact' => 1])"
                    :hide-search="true"
                    trigger-class="form-select w-full">
                    <option value="">All</option>
                    @foreach($staffList as $st)
                    <option value="{{ $st->id }}" {{ (string)($staffId ?? '') === (string) $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </x-searchable-select>
            </div>
            <div class="flex flex-col gap-1.5 w-[calc(50%-0.375rem)] sm:w-36 shrink-0">
                <label class="form-label text-xs mb-0" for="rev-report-pay-trigger">Payment</label>
                <x-searchable-select
                    id="rev-report-pay"
                    name="payment_method"
                    wrapper-class="w-full"
                    :search-url="null"
                    :hide-search="true"
                    trigger-class="form-select w-full">
                    <option value="">All</option>
                    @foreach(['cash','card','stripe','other'] as $pm)
                    <option value="{{ $pm }}" {{ ($paymentMethod ?? '') === $pm ? 'selected' : '' }}>{{ ucfirst($pm) }}</option>
                    @endforeach
                </x-searchable-select>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer shrink-0 pb-2.5 whitespace-nowrap">
                <input type="checkbox" name="compare" value="1" class="rounded border-gray-300 text-velour-600" {{ !empty($compare) ? 'checked' : '' }}>
                Compare previous period
            </label>
            <button type="submit" class="btn-primary shrink-0">Apply</button>
            <div class="flex items-center gap-3 shrink-0 xl:ml-auto w-full sm:w-auto justify-end sm:justify-start pb-0.5">
                <a href="{{ route('reports.index') }}" class="text-sm text-link font-medium whitespace-nowrap">← All reports</a>
                <a href="{{ route('reports.revenue.export', array_filter(['from' => $from, 'to' => $to, 'staff_id' => $staffId ?? null, 'payment_method' => $paymentMethod ?? null])) }}"
                   class="btn-outline btn-sm whitespace-nowrap">Export CSV</a>
            </div>
        </div>
    </form>
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
