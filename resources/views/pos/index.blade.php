@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')
@section('content')

@php
    $salonToday = \App\Support\SalonTime::todayDateString($salon);
    $fromDt = \Carbon\Carbon::createFromFormat('Y-m-d', $from);
    $toDt = \Carbon\Carbon::createFromFormat('Y-m-d', $to);
    $rangeTriggerLabel = $from === $to
        ? $fromDt->format('d M Y')
        : $fromDt->format('d M') . ' – ' . $toDt->format('d M Y');
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 max-w-3xl">
    <div class="stat-card">
        <p class="stat-label">Today's Revenue</p>
        <p class="stat-value">@money($todayRevenue)</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Today's Transactions</p>
        <p class="stat-value">{{ $todayCount }}</p>
    </div>
</div>

<div class="flex flex-col sm:flex-row gap-3 mb-5 items-stretch sm:items-center">
    <form action="{{ route('pos.index') }}" method="GET"
          x-data="{ openRangePicker: false }"
          class="flex flex-1 min-w-0">
        <div class="flex flex-wrap items-center gap-2 w-full">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search reference or client…" class="form-input w-full sm:flex-1 sm:min-w-[10rem] min-w-0">
            <div class="relative w-full sm:w-auto shrink-0">
                <button type="button"
                        @click="openRangePicker = !openRangePicker"
                        @keydown.escape.window="openRangePicker = false"
                        class="form-select w-full sm:w-auto sm:min-w-[9.5rem] !flex items-center justify-between gap-1.5 text-left tabular-nums text-[13px]"
                        :class="openRangePicker ? '!ring-2 !ring-velour-500 !border-transparent' : ''"
                        :aria-expanded="openRangePicker">
                    <span class="truncate min-w-0 text-heading">{{ $rangeTriggerLabel }}</span>
                    <svg class="w-3.5 h-3.5 shrink-0 text-gray-400 dark:text-gray-500 transition-transform"
                         :class="openRangePicker ? 'rotate-180' : ''"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openRangePicker"
                     x-cloak
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 -translate-y-0.5"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="openRangePicker = false"
                     class="absolute left-0 right-0 sm:left-auto sm:right-0 top-full mt-1.5 w-[min(100vw-1rem,36rem)] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 p-2.5 shadow-lg ring-1 ring-black/5 dark:ring-white/10 z-[80]">
                    <x-date-range-picker
                        :inline="true"
                        :compact="true"
                        :from-value="$from"
                        :to-value="$to"
                        :salon-today="$salonToday"
                        class="relative z-10" />
                    <div class="flex justify-end gap-1.5 pt-2 mt-2 border-t border-gray-100 dark:border-gray-800">
                        <button type="button" class="btn-outline btn-sm !py-1 !px-2.5 !text-xs" @click="openRangePicker = false">Cancel</button>
                        <button type="submit" class="btn-primary btn-sm !py-1 !px-2.5 !text-xs" @click="openRangePicker = false">Apply</button>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn-secondary btn-sm shrink-0">Filter</button>
            <a href="{{ route('pos.index') }}" class="btn-outline btn-sm shrink-0">Clear</a>
        </div>
    </form>
    @unless(\App\Support\AuthPanel::isAdminStoreBrowse())
    <a href="{{ route('pos.create') }}" class="btn-primary btn-sm flex-shrink-0 w-full sm:w-auto text-center whitespace-nowrap">
        + New Sale
    </a>
    @endunless
</div>

<div class="table-wrap">
    <table class="data-table data-table-fixed">
        <colgroup>
            <col class="w-[12%]">
            <col class="w-[18%]">
            <col class="w-[20%]">
            <col class="w-[14%]">
            <col class="w-[12%]">
            <col class="w-[12%]">
            <col class="w-[12%]">
        </colgroup>
        <thead>
        <tr>
            <th>Reference</th>
            <th class="hidden sm:table-cell">Client</th>
            <th>Date</th>
            <th class="hidden md:table-cell">Method</th>
            <th class="text-right"><abbr title="@currencyLabel">Total</abbr></th>
            <th class="text-center">Status</th>
            <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($transactions as $txn)
        <tr>
            <td class="font-mono text-xs text-muted truncate max-w-0">{{ $txn->reference }}</td>
            <td class="hidden sm:table-cell text-body truncate max-w-0">
                {{ $txn->client ? $txn->client->full_name : 'Walk-in' }}
            </td>
            <td class="text-muted text-xs whitespace-nowrap">@bizdatetime($txn->completed_at ?? $txn->created_at)</td>
            <td class="hidden md:table-cell">
                <span class="badge-gray capitalize">{{ str_replace('_',' ',$txn->payment_method) }}</span>
            </td>
            <td class="font-bold text-heading text-right whitespace-nowrap">@money($txn->total)</td>
            <td class="text-center">
                @php $colors = ['completed'=>'badge-green','refunded'=>'badge-yellow','voided'=>'badge-red']; @endphp
                <span class="{{ $colors[$txn->status] ?? 'badge-gray' }}">{{ ucfirst($txn->status) }}</span>
            </td>
            <td class="text-right">
                <a href="{{ route('pos.show', $txn->id) }}" class="text-xs text-link font-medium whitespace-nowrap">View</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-muted">No transactions for the selected date range.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $transactions->links() }}</div>

@endsection
