@extends('layouts.app')
@section('title', $staff->name)
@section('page-title', 'Staff Profile')
@section('content')

<div class="max-w-3xl space-y-6">
    <div class="card p-6 flex items-start gap-5">
        <div class="w-16 h-16 rounded-2xl flex items-center justify-center text-white font-bold text-2xl flex-shrink-0"
             style="background-color: {{ $staff->color ?? '#7C3AED' }}">
            {{ strtoupper(substr($staff->name, 0, 1)) }}
        </div>
        <div class="flex-1">
            <h2 class="text-xl font-bold text-heading">{{ $staff->name }}</h2>
            <p class="text-sm text-muted capitalize mt-0.5">{{ str_replace('_',' ',$staff->role) }}</p>
            <div class="flex gap-4 mt-2 text-sm text-muted">
                @if($staff->email)<span>✉ {{ $staff->email }}</span>@endif
                @if($staff->phone)<span>📞 {{ $staff->phone }}</span>@endif
            </div>
        </div>
        <a href="{{ route('staff.edit', $staff->id) }}" class="flex-shrink-0 btn-outline">Edit</a>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="stat-card text-center">
            <p class="stat-value">@money($totalRevenue)</p>
            <p class="stat-label mt-1">Revenue</p>
        </div>
        <div class="stat-card text-center">
            <p class="stat-value">{{ $completedAppointments->total() }}</p>
            <p class="stat-label mt-1">Completed</p>
        </div>
        <div class="stat-card text-center">
            <p class="stat-value">{{ $upcomingCount }}</p>
            <p class="stat-label mt-1">Upcoming</p>
        </div>
    </div>

    <div class="table-wrap">
        <h3 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Recent Appointments</h3>
        <table class="data-table">
            <tbody>
            @forelse($completedAppointments as $apt)
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
                <td class="font-semibold text-heading">@money($apt->total_price)</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-5 py-8 text-center text-sm text-muted">No appointments</td></tr>
            @endforelse
            </tbody>
        </table>
        @if($completedAppointments->hasPages())
        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800">{{ $completedAppointments->links() }}</div>
        @endif
    </div>
</div>

@endsection
