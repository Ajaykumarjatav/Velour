@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

@php
    $tzAbbr = $tzAbbr ?? '';
    $todayLabel = $todayLabel ?? today()->format('d M Y');
    $revenueChange = $revenueChange ?? null;
    $profileCompletion = $profileCompletion ?? ['percentage' => 100];
@endphp

@if(!empty($stylistDashboardScoped))
<div class="mb-4 rounded-xl border border-velour-200 dark:border-velour-800 bg-velour-50 dark:bg-velour-950/40 px-4 py-3 text-sm text-velour-900 dark:text-velour-100">
    <p class="font-medium">Your personal dashboard</p>
    <p class="mt-1 text-velour-800/90 dark:text-velour-200/90">Numbers and lists here include only <strong>your</strong> appointments, POS sales credited to you, and clients you have booked.</p>
</div>
@endif

<div class="alert-info mb-7 text-sm flex flex-wrap items-start justify-between gap-3">
    <p>Figures use your salon timezone (<abbr title="{{ $salon->timezone ?? 'UTC' }}">{{ $tzAbbr }}</abbr>). Revenue is counted when a POS sale is completed (see <a href="{{ route('reports.show', ['type' => 'revenue', 'from' => \App\Support\SalonTime::monthStartDateString($salon), 'to' => \App\Support\SalonTime::todayDateString($salon)]) }}" class="underline font-semibold">Revenue report</a>).</p>
</div>

{{-- KPI cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5 mb-7">
    <div class="stat-card">
        <p class="stat-label">Today's Revenue</p>
        <p class="stat-value">@money($todayRevenue)</p>
        <p class="stat-sub">{{ $todayLabel }} &middot; {{ $completedVisitsToday ?? 0 }} completed visit{{ ($completedVisitsToday ?? 0) === 1 ? '' : 's' }}</p>
        @if(($todayRevenue ?? 0) <= 0 && ($completedVisitsToday ?? 0) === 0)
            <p class="text-xs text-muted mt-2">No completed POS sales yet today — <a href="{{ route('pos.index') }}" class="text-link font-medium">open Point of Sale</a></p>
        @endif
    </div>
    <div class="stat-card">
        <p class="stat-label">Month Revenue</p>
        <p class="stat-value">@money($monthRevenue)</p>
        @if($revenueChange !== null && $lastMonthRevenue > 0)
            <p class="text-xs mt-1 {{ $revenueChange >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $revenueChange >= 0 ? '▲' : '▼' }} {{ abs($revenueChange) }}% vs last month
            </p>
            <p class="stat-sub">Last month total @money($lastMonthRevenue)</p>
        @elseif($lastMonthRevenue > 0)
            <p class="text-xs mt-1 text-muted">Last month @money($lastMonthRevenue)</p>
        @else
            <p class="text-xs mt-1 text-muted">No prior month to compare</p>
        @endif
    </div>
    <div class="stat-card">
        <p class="stat-label">Today's Appointments</p>
        <p class="stat-value">{{ $todayAppointments }}</p>
        <p class="stat-sub">Scheduled today (excl. cancelled / no-show)</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Clients</p>
        <p class="stat-value">{{ number_format($totalClients) }}</p>
        <p class="text-xs text-green-500 mt-1">+{{ $newClientsThisMonth }} this month</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-7">

    {{-- Upcoming appointments --}}
    <div class="lg:col-span-2 card">
        <div class="card-header flex items-center justify-between">
            <h2 class="section-title">Upcoming Appointments</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('calendar') }}" class="text-link text-xs font-medium">Calendar</a>
                <a href="{{ route('appointments.index') }}" class="text-link text-sm font-medium">View all →</a>
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($upcomingAppointments as $apt)
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
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
                    <p class="text-sm font-semibold text-body">@bizclock($apt->starts_at)</p>
                    <p class="text-xs text-muted">@bizshortdate($apt->starts_at)</p>
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
                <p class="empty-state-sub mt-1 max-w-sm">The <a href="{{ route('calendar') }}" class="text-link">calendar</a> and this list use the same schedule.</p>
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
        <div class="card p-6">
            <div class="flex items-center justify-between gap-2 mb-5">
                <h2 class="section-title">Revenue (7 days)</h2>
                <a href="{{ route('revenue.index') }}" class="text-link text-xs font-medium">Details</a>
            </div>
            <div class="flex items-end gap-1.5 h-24">
                @php $maxRev = max(collect($weeklyRevenue)->pluck('revenue')->max(), 1); @endphp
                @foreach($weeklyRevenue as $day)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full rounded-t-md bg-velour-200 dark:bg-velour-800 hover:bg-velour-400 dark:hover:bg-velour-600 transition-colors"
                         style="height: {{ max(4, ($day['revenue'] / $maxRev) * 80) }}px"
                         title="@money($day['revenue'])"></div>
                    <span class="text-xs text-muted">{{ $day['date'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent sales --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="section-title">Today's sales</h2>
                <a href="{{ route('pos.index') }}" class="text-link text-xs font-medium">POS</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentSales as $sale)
                <div class="flex items-center justify-between px-6 py-3.5">
                    <div>
                        <p class="text-sm font-medium text-body">
                            {{ $sale->client?->first_name ?? 'Walk-in' }} {{ $sale->client?->last_name }}
                        </p>
                        <p class="text-xs text-muted">{{ ($sale->completed_at ?? $sale->created_at)->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-bold text-heading">@money($sale->total)</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-sm text-muted">No completed POS sales yet today.</p>
                    <p class="text-xs text-muted mt-2">Record a sale in <a href="{{ route('pos.index') }}" class="text-link font-medium">Point of Sale</a> — revenue appears after checkout completes.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
