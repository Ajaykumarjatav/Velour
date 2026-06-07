@extends('layouts.app')
@section('title', 'Appointments Report')
@section('page-title', 'Appointments Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<p class="text-sm text-muted mb-6 max-w-3xl">
    Totals use each appointment’s <strong>scheduled start</strong> in this date range ({{ \App\Support\SalonTime::abbrev($salon) }}).
    If a booking is missing, widen the end date so it includes that day.
</p>

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">{{ $total }}</p>
        <p class="stat-label mt-1">Total</p>
    </div>
    @foreach([
        'confirmed' => 'text-blue-600 dark:text-blue-400',
        'completed' => 'text-green-600 dark:text-green-400',
        'cancelled' => 'text-red-600 dark:text-red-400',
        'no_show' => 'text-amber-600 dark:text-amber-400',
    ] as $status => $cls)
    <div class="stat-card text-center">
        <p class="text-2xl font-bold {{ $cls }}">{{ $byStatus[$status] ?? 0 }}</p>
        <p class="stat-label mt-1">{{ ucfirst(str_replace('_',' ',$status)) }}</p>
    </div>
    @endforeach
</div>

<div class="table-wrap">
    <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Daily appointments</h3>
    <table class="data-table">
        <thead>
        <tr>
            <th>Date</th>
            <th class="text-right">Count</th>
        </tr>
        </thead>
        <tbody>
        @forelse($daily as $row)
        <tr>
            <td class="font-medium text-body">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
            <td class="text-right font-bold text-heading">{{ $row->count }}</td>
        </tr>
        @empty
        <tr><td colspan="2" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
