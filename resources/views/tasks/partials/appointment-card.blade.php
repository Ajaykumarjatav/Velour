@php
    $isMissed = \App\Support\AppointmentLifecycle::isPastUnresolved($appointment, $salon);
    $statusLabel = \App\Support\AppointmentLifecycle::displayStatusLabel($appointment, $salon);
    $clientName = trim(($appointment->client?->first_name ?? '').' '.($appointment->client?->last_name ?? '')) ?: 'Walk-in';
    $serviceNames = $appointment->services->pluck('service_name')->filter()->implode(', ');
    $timeLabel = $appointment->ends_at
        ? \App\Support\DisplayFormatter::businessTimeRange($salon, $appointment->starts_at, $appointment->ends_at)
        : \App\Support\DisplayFormatter::businessTime($salon, $appointment->starts_at);
@endphp
<article class="card p-4 shadow-sm border-violet-200/80 dark:border-violet-900/40 ring-1 ring-violet-500/10">
    <div class="flex items-start justify-between gap-2">
        <h3 class="text-sm font-semibold text-heading leading-snug">{{ $clientName }}</h3>
        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded shrink-0 bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200">Booking</span>
    </div>
    @if($serviceNames)
        <p class="text-xs text-body/90 mt-2">{{ $serviceNames }}</p>
    @endif
    @if($isMissed)
        <p class="text-[11px] font-medium text-red-600 dark:text-red-400 mt-2">Overdue · {{ $statusLabel }}</p>
    @else
        <p class="text-[11px] font-medium text-violet-700 dark:text-violet-300 mt-2">{{ $statusLabel }}</p>
    @endif
    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-muted">
        <span class="inline-flex items-center gap-1 min-w-0">
            <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            <span class="truncate">{{ $appointment->staff?->name ?? 'Unassigned' }}</span>
        </span>
        <span class="inline-flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            {{ $timeLabel }}
        </span>
    </div>
    <p class="text-[10px] text-muted mt-2">
        Today’s appointment
        @if($appointment->reference)
            · {{ $appointment->reference }}
        @endif
    </p>

    <x-unless-admin-browse>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <a href="{{ route('appointments.show', $appointment) }}" class="rounded-full border border-gray-200 dark:border-gray-700 px-2.5 py-1 text-[11px] font-medium text-body hover:bg-gray-100 dark:hover:bg-gray-800">Open</a>
        @if($appointment->status === 'pending')
            <form method="POST" action="{{ route('appointments.confirm', $appointment) }}" class="inline">
                @csrf @method('PATCH')
                <button type="submit" class="rounded-full border border-sky-200 dark:border-sky-900 text-sky-800 dark:text-sky-200 px-2.5 py-1 text-[11px] font-medium hover:bg-sky-50 dark:hover:bg-sky-950/40">Confirm</button>
            </form>
        @elseif($appointment->status === 'hold')
            <form method="POST" action="{{ route('appointments.status', $appointment) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="confirmed">
                <button type="submit" class="rounded-full border border-sky-200 dark:border-sky-900 text-sky-800 dark:text-sky-200 px-2.5 py-1 text-[11px] font-medium hover:bg-sky-50 dark:hover:bg-sky-950/40">Confirm</button>
            </form>
        @endif
        @if(in_array($appointment->status, ['confirmed', 'checked_in', 'in_progress'], true))
            <form method="POST" action="{{ route('appointments.complete', $appointment) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="redirect" value="tasks">
                <button type="submit" class="rounded-full border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-200 px-2.5 py-1 text-[11px] font-medium hover:bg-emerald-50 dark:hover:bg-emerald-950/40">{{ $appointment->isFullyPaid() ? 'Done' : 'Complete & pay' }}</button>
            </form>
        @endif
        @if($isMissed)
            <form method="POST" action="{{ route('appointments.status', $appointment) }}" class="inline" onsubmit="return confirm('Mark this appointment as no-show?');">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="no_show">
                <button type="submit" class="rounded-full border border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-200 px-2.5 py-1 text-[11px] font-medium hover:bg-amber-50 dark:hover:bg-amber-950/40">No-show</button>
            </form>
        @endif
    </div>
    </x-unless-admin-browse>
</article>
