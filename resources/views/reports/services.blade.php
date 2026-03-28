@extends('layouts.app')
@section('title', 'Services Report')
@section('page-title', 'Services Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th class="text-left">Service</th>
            <th class="text-right">Bookings</th>
            <th class="text-right">Revenue</th>
            <th class="text-right hidden sm:table-cell">Avg Price</th>
        </tr>
        </thead>
        <tbody>
        @forelse($services as $svc)
        <tr>
            <td class="font-semibold text-heading">{{ $svc->name }}</td>
            <td class="text-right text-muted">{{ $svc->booking_count }}</td>
            <td class="text-right font-bold text-heading">@money($svc->total_revenue ?? 0)</td>
            <td class="text-right text-muted hidden sm:table-cell">
                {{ $svc->booking_count > 0 ? \App\Helpers\CurrencyHelper::format($svc->total_revenue / $svc->booking_count, $currentSalon->currency ?? 'GBP') : '—' }}
            </td>
        </tr>
        @empty
        <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
