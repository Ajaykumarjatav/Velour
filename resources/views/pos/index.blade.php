@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')
@section('content')

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

<div class="flex flex-col lg:flex-row gap-4 mb-6 items-start">
    <form action="{{ route('pos.index') }}" method="GET" class="flex flex-1 flex-col gap-3 min-w-0 w-full">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search reference or client…" class="form-input w-full min-w-0">
            <input type="date" name="from" value="{{ $from }}" class="form-input w-full min-w-0">
            <input type="date" name="to" value="{{ $to }}" class="form-input w-full min-w-0">
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="btn-secondary">Filter</button>
            <a href="{{ route('pos.index') }}" class="btn-outline">Clear</a>
        </div>
    </form>
    <a href="{{ route('pos.create') }}" class="btn-primary flex-shrink-0 w-full sm:w-auto text-center">
        + New Sale
    </a>
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
            <th class="text-right">Total</th>
            <th class="text-center">Status</th>
            <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($transactions as $txn)
        <tr>
            <td class="font-mono text-xs text-muted truncate max-w-0">{{ $txn->reference }}</td>
            <td class="hidden sm:table-cell text-body truncate max-w-0">
                {{ $txn->client ? $txn->client->first_name.' '.$txn->client->last_name : 'Walk-in' }}
            </td>
            <td class="text-muted text-xs whitespace-nowrap">{{ $txn->created_at->format('d M Y H:i') }}</td>
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
        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-muted">No transactions</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $transactions->links() }}</div>

@endsection
