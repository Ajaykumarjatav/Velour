@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

{{-- KPI cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="stat-card">
        <p class="stat-label">Today's Revenue</p>
        <p class="stat-value">£{{ number_format($todayRevenue, 2) }}</p>
        <p class="stat-sub">{{ today()->format('d M Y') }}</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Month Revenue</p>
        <p class="stat-value">£{{ number_format($monthRevenue, 2) }}</p>
        <p class="text-xs mt-1 {{ $revenueChange >= 0 ? 'text-green-500' : 'text-red-500' }}">
            {{ $revenueChange >= 0 ? '▲' : '▼' }} {{ abs($revenueChange) }}% vs last month
        </p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Today's Appointments</p>
        <p class="stat-value">{{ $todayAppointments }}</p>
        <p class="stat-sub">Scheduled today</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Clients</p>
        <p class="stat-value">{{ number_format($totalClients) }}</p>
        <p class="text-xs text-green-500 mt-1">+{{ $newClientsThisMonth }} this month</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Upcoming appointments --}}
    <div class="lg:col-span-2 card">
        <div class="card-header flex items-center justify-between">
            <h2 class="section-title">Upcoming Appointments</h2>
            <a href="{{ route('appointments.index') }}" class="text-link text-sm font-medium">View all →</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($upcomingAppointments as $apt)
            <div class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background-color: {{ $apt->staff?->color ?? '#7C3AED' }}">
                    {{ strtoupper(substr($apt->client?->first_name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-heading">
                        {{ $apt->client?->first_name }} {{ $apt->client?->last_name }}
                    </p>
                    <p class="text-xs text-muted truncate">
                        {{ $apt->services->pluck('service.name')->filter()->join(', ') ?: 'Appointment' }}
                        &middot; {{ $apt->staff?->name }}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-sm font-semibold text-body">{{ $apt->starts_at->format('H:i') }}</p>
                    <p class="text-xs text-muted">{{ $apt->starts_at->format('d M') }}</p>
                </div>
                <a href="{{ route('appointments.show', $apt->id) }}"
                   class="flex-shrink-0 p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/30 text-muted hover:text-velour-600 dark:hover:text-velour-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @empty
            <div class="empty-state">
                <p class="empty-state-title">No upcoming appointments</p>
                <a href="{{ route('appointments.create') }}" class="mt-3 text-link text-sm font-medium">
                    Book an appointment →
                </a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Right column --}}
    <div class="space-y-6">

        {{-- Weekly revenue chart --}}
        <div class="card p-5">
            <h2 class="section-title mb-4">Revenue (7 days)</h2>
            <div class="flex items-end gap-1.5 h-24">
                @php $maxRev = max(collect($weeklyRevenue)->pluck('revenue')->max(), 1); @endphp
                @foreach($weeklyRevenue as $day)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full rounded-t-md bg-velour-200 dark:bg-velour-800 hover:bg-velour-400 dark:hover:bg-velour-600 transition-colors"
                         style="height: {{ max(4, ($day['revenue'] / $maxRev) * 80) }}px"
                         title="£{{ $day['revenue'] }}"></div>
                    <span class="text-xs text-muted">{{ $day['date'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent sales --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="section-title">Recent Sales</h2>
                <a href="{{ route('pos.index') }}" class="text-link text-xs font-medium">View all</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentSales as $sale)
                <div class="flex items-center justify-between px-5 py-3">
                    <div>
                        <p class="text-sm font-medium text-body">
                            {{ $sale->client?->first_name ?? 'Walk-in' }} {{ $sale->client?->last_name }}
                        </p>
                        <p class="text-xs text-muted">{{ $sale->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-bold text-heading">£{{ number_format($sale->total, 2) }}</span>
                </div>
                @empty
                <p class="px-5 py-6 text-center text-sm text-muted">No sales yet today</p>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
