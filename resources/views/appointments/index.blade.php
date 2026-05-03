@extends('layouts.app')
@section('title', 'Appointments')
@section('page-title', 'Appointments')
@section('content')

<div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 px-4 py-3.5 sm:px-5 sm:py-4 mb-7 shadow-sm dark:shadow-none">
    <div class="flex flex-col lg:flex-row lg:items-center gap-4 lg:gap-5">
        <form action="{{ route('appointments.index') }}" method="GET"
              class="flex flex-1 flex-wrap items-center gap-2.5 sm:gap-3 min-w-0">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search client or reference…"
                   class="form-input w-full min-w-0 sm:flex-1 sm:min-w-[12rem] lg:max-w-md">
            <input type="date" name="date" value="{{ $date }}"
                   title="Filter by date"
                   class="form-input w-full min-w-[10.5rem] sm:w-[11.5rem] sm:flex-initial shrink-0">
            <select name="status" class="form-select w-full min-w-0 sm:w-[10.5rem] shrink-0">
                <option value="">All statuses</option>
                @foreach(['pending','confirmed','checked_in','in_progress','completed','cancelled','no_show'] as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
            <select name="staff_id" class="form-select w-full min-w-0 sm:min-w-[10.5rem] sm:max-w-[14rem] shrink-0">
                <option value="">All staff</option>
                @foreach($staff as $s)
                <option value="{{ $s->id }}" {{ $staffId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <div class="flex w-full sm:w-auto gap-2 shrink-0">
                <button type="submit" class="btn-secondary flex-1 sm:flex-initial min-w-0">Filter</button>
                <a href="{{ route('appointments.index') }}" class="btn-outline flex-1 sm:flex-initial min-w-0 text-center">Clear</a>
            </div>
        </form>
        <a href="{{ route('appointments.create') }}"
           class="btn-primary shrink-0 w-full lg:w-auto text-center whitespace-nowrap lg:min-w-[11rem]">
            + New Appointment
        </a>
    </div>
</div>

<div class="table-wrap [&_thead_th]:py-3 [&_thead_th]:px-5 [&_tbody_td]:py-2.5 [&_tbody_td]:px-5">
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
            <td class="hidden lg:table-cell font-semibold text-heading text-right tabular-nums">@money($apt->total_price)</td>
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
                <a href="{{ route('appointments.show', $apt->id) }}"
                   class="text-link text-sm font-medium whitespace-nowrap inline-flex items-center rounded-lg px-2.5 py-1.5 -mr-1 hover:bg-velour-50 dark:hover:bg-velour-900/25 transition-colors">
                    View
                </a>
            </td>
        </tr>
        @empty
        <tr><td colspan="7" class="px-6 py-14 text-center text-sm text-muted">No appointments found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6 flex justify-center sm:justify-end">{{ $appointments->links() }}</div>
@endsection
