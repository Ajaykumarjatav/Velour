@extends('layouts.app')
@section('title', 'Clients Report')
@section('page-title', 'Clients Report')
@section('content')

@include('reports._filter', ['type' => $type, 'from' => $from, 'to' => $to])

<div class="grid grid-cols-2 gap-4 mb-6">
    <div class="stat-card text-center">
        <p class="text-3xl font-bold text-velour-600">{{ $newClients }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">New clients</p>
    </div>
    <div class="stat-card text-center">
        <p class="text-3xl font-bold text-green-600">{{ $returningClients }}</p>
        <p class="text-xs text-muted mt-1 uppercase tracking-wide">Returning clients</p>
    </div>
</div>

<div class="table-wrap">
    <h3 class="px-6 py-4 font-semibold text-gray-900 border-b border-gray-100">Top Spenders</h3>
    <table class="data-table">
        <thead>
        <tr class="bg-gray-50 border-b border-gray-100">
            <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase">#</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-muted uppercase">Client</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-muted uppercase">Spent</th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
        @forelse($topClients as $i => $client)
        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40">
            <td class="px-5 py-3 text-muted font-mono">{{ $i + 1 }}</td>
            <td class="px-5 py-3">
                <a href="{{ route('clients.show', $client->id) }}" class="font-semibold text-velour-600 hover:underline">
                    {{ $client->first_name }} {{ $client->last_name }}
                </a>
            </td>
            <td class="px-5 py-3 text-right font-bold text-heading">Â£{{ number_format($client->total_spent, 2) }}</td>
        </tr>
        @empty
        <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection

