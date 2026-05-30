@extends('layouts.app')
@section('title', $staff->name)
@section('page-title', 'Staff Profile')
@section('content')

<div class="max-w-3xl space-y-6">
    <div class="card p-6 flex items-start gap-5">
        <x-staff-avatar :staff="$staff" size="lg" rounded="2xl" />
        <div class="flex-1">
            <h2 class="text-xl font-bold text-heading">{{ $staff->name }}</h2>
            <p class="text-sm text-muted capitalize mt-0.5">{{ str_replace('_',' ',$staff->role) }}</p>
            <div class="flex flex-wrap gap-x-4 gap-y-1 mt-2 text-sm">
                @if($staff->email)
                    <a href="mailto:{{ $staff->email }}" class="text-link inline-flex items-center gap-1.5 hover:underline">
                        <span class="text-muted" aria-hidden="true">✉</span>
                        <span>{{ $staff->email }}</span>
                    </a>
                @endif
                @if($staff->phone)
                    @php $phoneHref = preg_replace('/[^\d+]/', '', $staff->phone) ?: $staff->phone; @endphp
                    <a href="tel:{{ $phoneHref }}" class="text-link inline-flex items-center gap-1.5 hover:underline">
                        <span class="text-muted" aria-hidden="true">📞</span>
                        <span>{{ $staff->phone }}</span>
                    </a>
                @endif
            </div>
        </div>
        <a href="{{ route('staff.edit', $staff->id) }}" class="flex-shrink-0 btn-outline">Edit</a>
    </div>

    @if(trim((string) ($staff->bio ?? '')) !== '')
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-heading mb-2">Bio</h3>
        <p class="text-sm text-body whitespace-pre-line">{{ $staff->bio }}</p>
    </div>
    @endif

    @if(trim((string) ($staff->awards_accolades ?? '')) !== '')
    <div class="card p-6">
        <h3 class="text-sm font-semibold text-heading mb-2">Awards &amp; accolades</h3>
        <p class="text-sm text-body whitespace-pre-line">{{ $staff->awards_accolades }}</p>
    </div>
    @endif

    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card text-center">
            <p class="stat-value">@money($totalRevenue)</p>
            <p class="stat-label mt-1">Revenue</p>
        </div>
        <div class="stat-card text-center">
            <p class="stat-value">{{ $completedCount }}</p>
            <p class="stat-label mt-1">Completed</p>
        </div>
        <div class="stat-card text-center">
            <p class="stat-value">{{ $upcomingCount }}</p>
            <p class="stat-label mt-1">Upcoming</p>
        </div>
    </div>

    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Recent appointments</h3>
        <table class="data-table">
            <thead>
            <tr>
                <th>Client</th>
                <th class="hidden sm:table-cell">Services</th>
                <th>Date</th>
                <th class="text-right">Total</th>
            </tr>
            </thead>
            <tbody>
            @forelse($recentAppointments as $apt)
            <tr>
                <td>
                    <a href="{{ route('appointments.show', $apt->id) }}" class="font-medium text-link">
                        {{ $apt->client?->first_name }} {{ $apt->client?->last_name }}
                    </a>
                </td>
                <td class="text-muted text-xs hidden sm:table-cell">
                    {{ $apt->services->pluck('service.name')->filter()->join(', ') ?: '—' }}
                </td>
                <td class="text-muted">{{ $apt->starts_at->format('d M Y') }}</td>
                <td class="font-semibold text-heading text-right">@money($apt->total_price)</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-muted">No appointments</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($recentAppointments->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">{{ $recentAppointments->links() }}</div>
        @endif
    </div>
</div>

@endsection
