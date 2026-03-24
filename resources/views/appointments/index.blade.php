@extends('layouts.app')
@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('content')

<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form action="{{ route('appointments.index') }}" method="GET" class="flex flex-1 gap-2 flex-wrap">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search client or reference…" class="form-input flex-1 min-w-[180px]">
        <input type="date" name="date" value="{{ $date }}" class="form-input w-auto">
        <select name="status" class="form-select w-auto">
            <option value="">All statuses</option>
            @foreach(['confirmed','completed','cancelled','no_show'] as $s)
            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="staff_id" class="form-select w-auto">
            <option value="">All staff</option>
            @foreach($staff as $s)
            <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary">Filter</button>
        <a href="{{ route('appointments.index') }}" class="btn-outline">Clear</a>
    </form>
    <a href="{{ route('appointments.create') }}" class="btn-primary flex-shrink-0">+ New Appointment</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Client</th>
            <th class="hidden md:table-cell">Service</th>
            <th class="hidden sm:table-cell">Staff</th>
            <th>Date & Time</th>
            <th class="hidden lg:table-cell">Amount</th>
            <th>Status</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($appointments as $apt)
        <tr>
            <td class="px-5 py-3.5">
                <p class="font-semibold text-heading">{{ $apt->client?->first_name }} {{ $apt->client?->last_name }}</p>
                <p class="text-xs text-muted">{{ $apt->reference }}</p>
            </td>
            <td class="px-4 py-3.5 hidden md:table-cell text-body max-w-[150px] truncate">
                {{ $apt->services->first()?->service?->name ?? '—' }}
                @if($apt->services->count() > 1)<span class="text-xs text-muted">+{{ $apt->services->count() - 1 }}</span>@endif
            </td>
            <td class="px-4 py-3.5 hidden sm:table-cell text-body">{{ $apt->staff?->name ?? '—' }}</td>
            <td class="px-4 py-3.5">
                <p class="font-medium text-body">{{ $apt->starts_at->format('H:i') }}</p>
                <p class="text-xs text-muted">{{ $apt->starts_at->format('d M Y') }}</p>
            </td>
            <td class="px-4 py-3.5 hidden lg:table-cell font-semibold text-heading">£{{ number_format($apt->total_price, 2) }}</td>
            <td class="px-4 py-3.5">
                @php
                    $colors = [
                        'confirmed' => 'badge-blue',
                        'completed' => 'badge-green',
                        'cancelled' => 'badge-red',
                        'no_show'   => 'badge-yellow',
                        'pending'   => 'badge-gray',
                    ];
                    $cls = $colors[$apt->status] ?? 'badge-gray';
                @endphp
                <span class="{{ $cls }}">{{ ucfirst(str_replace('_',' ',$apt->status)) }}</span>
            </td>
            <td class="px-4 py-3.5">
                <a href="{{ route('appointments.show', $apt->id) }}" class="text-link text-xs font-medium">View</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-5 py-12 text-center text-sm text-muted">No appointments found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $appointments->links() }}</div>
@endsection
