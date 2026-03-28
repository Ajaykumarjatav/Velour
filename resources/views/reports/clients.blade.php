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
    <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Top Spenders</h3>
    <table class="data-table">
        <thead>
        <tr>
            <th class="text-left">#</th>
            <th class="text-left">Client</th>
            <th class="text-right">Spent</th>
        </tr>
        </thead>
        <tbody>
        @forelse($topClients as $i => $client)
        <tr>
            <td class="text-muted font-mono">{{ $i + 1 }}</td>
            <td>
                <a href="{{ route('clients.show', $client->id) }}" class="font-semibold text-velour-600 hover:underline">
                    {{ $client->first_name }} {{ $client->last_name }}
                </a>
            </td>
            <td class="text-right font-bold text-heading">@money($client->total_spent)</td>
        </tr>
        @empty
        <tr><td colspan="3" class="px-5 py-8 text-center text-sm text-muted">No data</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@endsection
