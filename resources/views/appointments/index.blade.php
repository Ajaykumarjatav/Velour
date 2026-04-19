@extends('layouts.app')
@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('content')

<div class="flex flex-col lg:flex-row lg:items-center gap-3 mb-4">
    <form action="{{ route('appointments.index') }}" method="GET"
          class="flex flex-1 flex-wrap items-center gap-2 min-w-0">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search client or reference…"
               class="form-input w-full min-w-0 sm:flex-1 sm:min-w-[200px] lg:max-w-xs">
        <input type="date" name="date" value="{{ $date }}"
               class="form-input w-full min-w-[140px] sm:w-auto sm:flex-initial">
        <select name="status" class="form-select w-full min-w-0 sm:w-auto sm:min-w-[140px]">
            <option value="">All statuses</option>
            @foreach(['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'] as $s)
            <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <select name="staff_id" class="form-select w-full min-w-0 sm:w-auto sm:min-w-[140px]">
            <option value="">All staff</option>
            @foreach($staff as $s)
            <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-secondary shrink-0 w-full sm:w-auto">Filter</button>
        <a href="{{ route('appointments.index') }}" class="btn-outline shrink-0 w-full sm:w-auto text-center">Clear</a>
    </form>
    <a href="{{ route('appointments.create') }}"
       class="btn-primary shrink-0 w-full lg:w-auto text-center whitespace-nowrap">
        + New Appointment
    </a>
</div>

<div class="table-wrap [&_thead_th]:py-2.5 [&_tbody_td]:py-2">
    <table class="data-table">
        <thead>
        <tr>
            <th>Client</th>
            <th class="hidden md:table-cell">Service</th>
            <th class="hidden sm:table-cell">Staff</th>
            <th>Date &amp; time</th>
            <th class="hidden lg:table-cell text-right"><abbr title="@currencyLabel">Amount</abbr></th>
            <th class="text-center">Status</th>
            <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($appointments as $apt)
        <tr>
            <td>
                <div class="flex flex-col gap-0 leading-snug">
                    <p class="font-semibold text-heading">{{ $apt->client?->first_name }} {{ $apt->client?->last_name }}</p>
                    <p class="text-xs text-muted">{{ $apt->reference }}</p>
                </div>
            </td>
            <td class="hidden md:table-cell text-body max-w-[150px]">
                <div class="flex flex-col gap-0 leading-snug max-w-full">
                    <span class="truncate">{{ $apt->services->first()?->service?->name ?? '—' }}</span>
                    @if($apt->services->count() > 1)<span class="text-xs text-muted">+{{ $apt->services->count() - 1 }}</span>@endif
                </div>
            </td>
            <td class="hidden sm:table-cell text-body">{{ $apt->staff?->name ?? '—' }}</td>
            <td>
                <div class="flex flex-col gap-0 leading-snug">
                    <p class="font-medium text-body">@bizclock($apt->starts_at)</p>
                    <p class="text-xs text-muted">@bizdate($apt->starts_at)</p>
                </div>
            </td>
            <td class="hidden lg:table-cell font-semibold text-heading text-right">@money($apt->total_price)</td>
            <td class="text-center">
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
            <td class="text-right">
                <a href="{{ route('appointments.show', $apt->id) }}" class="text-link text-xs font-medium whitespace-nowrap">View</a>
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
