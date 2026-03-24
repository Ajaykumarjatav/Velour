@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')
@section('content')

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="stat-card">
        <p class="stat-label">Today's Revenue</p>
        <p class="stat-value">£{{ number_format($todayRevenue, 2) }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Today's Transactions</p>
        <p class="stat-value">{{ $todayCount }}</p>
    </div>
</div>

<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form action="{{ route('pos.index') }}" method="GET" class="flex flex-1 gap-2 flex-wrap">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search reference or client…" class="form-input flex-1 min-w-[180px]">
        <input type="date" name="from" value="{{ $from }}" class="form-input w-auto">
        <input type="date" name="to" value="{{ $to }}" class="form-input w-auto">
        <button type="submit" class="btn-secondary">Filter</button>
    </form>
    <a href="{{ route('pos.create') }}" class="flex-shrink-0 btn-primary">+ New Sale</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Reference</th>
            <th class="hidden sm:table-cell">Client</th>
            <th>Date</th>
            <th class="hidden md:table-cell">Method</th>
            <th>Total</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($transactions as $txn)
        <tr>
            <td class="font-mono text-xs text-muted">{{ $txn->reference }}</td>
            <td class="hidden sm:table-cell text-body">
                {{ $txn->client ? $txn->client->first_name.' '.$txn->client->last_name : 'Walk-in' }}
            </td>
            <td class="text-muted text-xs">{{ $txn->created_at->format('d M Y H:i') }}</td>
            <td class="hidden md:table-cell">
                <span class="badge-gray capitalize">{{ str_replace('_',' ',$txn->payment_method) }}</span>
            </td>
            <td class="font-bold text-heading">£{{ number_format($txn->total, 2) }}</td>
            <td>
                @php $colors = ['completed'=>'badge-green','refunded'=>'badge-yellow','voided'=>'badge-red']; @endphp
                <span class="{{ $colors[$txn->status] ?? 'badge-gray' }}">{{ ucfirst($txn->status) }}</span>
            </td>
            <td>
                <a href="{{ route('pos.show', $txn->id) }}" class="text-xs text-link font-medium">View</a>
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
