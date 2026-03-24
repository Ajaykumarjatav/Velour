@extends('layouts.app')
@section('title', 'Appointments Report')
@section('page-title', 'Appointments Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-heading">{{ $total }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Total</p>
    </div>
    @foreach(['confirmed'=>'blue','completed'=>'green','cancelled'=>'red','no_show'=>'amber'] as $status => $color)
    <div class="stat-card text-center">
        <p class="text-2xl font-bold text-{{ $color }}-600">{{ $byStatus[$status] ?? 0 }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">{{ ucfirst(str_replace('_',' ',$status)) }}</p>
    </div>
    @endforeach
</div>

<div class="table-wrap">
    <h3 class="px-6 py-4 font-semibold text-gray-900 border-b border-gray-100">Daily Appointments</h3>
    <table class="data-table">
        <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase">Date</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Count</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($daily as $row)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <td class="px-5 py-3 font-medium text-body">{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
            <td class="px-5 py-3 text-right font-bold text-heading">{{ $row->count }}</td>
        </tr>
        @empty
        <tr><td colspan="2" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection

