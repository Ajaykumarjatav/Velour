@extends('layouts.app')
@section('title', 'Services Report')
@section('page-title', 'Services Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase">Service</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Bookings</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Revenue</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase hidden sm:table-cell">Avg Price</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($services as $svc)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <td class="px-5 py-3.5 font-semibold text-gray-800">{{ $svc->name }}</td>
            <td class="px-5 py-3.5 text-right text-gray-700">{{ $svc->booking_count }}</td>
            <td class="px-5 py-3.5 text-right font-bold text-heading">Â£{{ number_format($svc->total_revenue ?? 0, 2) }}</td>
            <td class="px-5 py-3.5 text-right text-muted hidden sm:table-cell">
                {{ $svc->booking_count > 0 ? 'Â£'.number_format($svc->total_revenue / $svc->booking_count, 2) : 'â€”' }}
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection

