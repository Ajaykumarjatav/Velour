@extends('layouts.app')
@section('title', $client->first_name.' '.$client->last_name)
@section('page-title', 'Client Profile')
@section('content')

<div class="max-w-4xl space-y-6">

    <div class="card p-6 flex items-start gap-5">
        <div class="w-16 h-16 rounded-2xl bg-velour-100 dark:bg-velour-900/30 flex items-center justify-center text-velour-700 dark:text-velour-400 font-bold text-2xl flex-shrink-0">
            {{ strtoupper(substr($client->first_name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-heading">{{ $client->first_name }} {{ $client->last_name }}</h2>
            <div class="flex flex-wrap gap-4 mt-2 text-sm text-muted">
                @if($client->email)<span>✉ {{ $client->email }}</span>@endif
                @if($client->phone)<span>📞 {{ $client->phone }}</span>@endif
                @if($client->date_of_birth)<span>🎂 {{ \Carbon\Carbon::parse($client->date_of_birth)->format('d M Y') }}</span>@endif
            </div>
        </div>
        <a href="{{ route('clients.edit', $client->id) }}" class="flex-shrink-0 btn-outline">Edit</a>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card text-center">
            <p class="stat-value">{{ $visitCount }}</p>
            <p class="stat-label mt-1">Visits</p>
        </div>
        <div class="stat-card text-center">
            <p class="stat-value">@money($totalSpent)</p>
            <p class="stat-label mt-1">Total Spent</p>
        </div>
        <div class="stat-card text-center">
            <p class="stat-value">{{ $lastVisit ? $lastVisit->starts_at->format('d M') : '—' }}</p>
            <p class="stat-label mt-1">Last Visit</p>
        </div>
    </div>

    @if($client->notes)
    <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-5">
        <p class="text-xs font-semibold text-amber-600 dark:text-amber-400 uppercase tracking-wide mb-2">Notes</p>
        <p class="text-sm text-body">{{ $client->notes }}</p>
    </div>
    @endif

    <div class="table-wrap">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <h3 class="font-semibold text-heading">Appointments</h3>
            <a href="{{ route('appointments.create') }}?client_id={{ $client->id }}" class="text-sm text-link font-medium">+ Book</a>
        </div>
        <table class="data-table">
            <thead>
            <tr>
                <th>Date</th>
                <th class="hidden sm:table-cell">Services</th>
                <th>Staff</th>
                <th class="text-right">Amount</th>
                <th class="text-center">Status</th>
            </tr>
            </thead>
            <tbody>
            @forelse($appointments as $apt)
            <tr>
                <td>
                    <a href="{{ route('appointments.show', $apt->id) }}" class="font-medium text-link">
                        {{ $apt->starts_at->format('d M Y') }}
                    </a>
                    <p class="text-xs text-muted">{{ $apt->starts_at->format('H:i') }}</p>
                </td>
                <td class="hidden sm:table-cell text-muted text-xs">
                    {{ $apt->services->pluck('service.name')->filter()->join(', ') ?: '—' }}
                </td>
                <td class="text-body">{{ $apt->staff?->name ?? '—' }}</td>
                <td class="font-semibold text-heading text-right">@money($apt->total_price)</td>
                <td class="text-center">
                    @php $colors = ['confirmed'=>'badge-blue','completed'=>'badge-green','cancelled'=>'badge-red','no_show'=>'badge-yellow']; @endphp
                    <span class="{{ $colors[$apt->status] ?? 'badge-gray' }}">
                        {{ ucfirst(str_replace('_',' ',$apt->status)) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-sm text-muted">No appointments yet</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($appointments->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">{{ $appointments->links() }}</div>
        @endif
    </div>

</div>

@endsection
