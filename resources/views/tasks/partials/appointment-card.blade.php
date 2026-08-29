@php
    $isMissed = \App\Support\AppointmentLifecycle::isPastUnresolved($appointment, $salon);
    $statusLabel = \App\Support\AppointmentLifecycle::displayStatusLabel($appointment, $salon);
    $clientName = trim(($appointment->client?->first_name ?? '').' '.($appointment->client?->last_name ?? '')) ?: 'Walk-in';
    $serviceNames = $appointment->services->pluck('service_name')->filter()->implode(', ');
    $tz = \App\Support\SalonTime::timezone($salon);
    $todayLocal = \Carbon\Carbon::now($tz)->startOfDay();
    $aptDay = $appointment->starts_at->copy()->timezone($tz)->startOfDay();
    $isToday = $aptDay->equalTo($todayLocal);
    $aptLocal = $appointment->starts_at->copy()->timezone($tz);
    $dateLabel = $aptLocal->format('D, j M');
    $timeLabel = $appointment->ends_at
        ? \App\Support\DisplayFormatter::businessTimeRange($salon, $appointment->starts_at, $appointment->ends_at)
        : \App\Support\DisplayFormatter::businessTime($salon, $appointment->starts_at);
    $timeLabel = $dateLabel.' · '.$timeLabel;
    $bookingDayLabel = $isToday ? 'Today’s appointment' : 'Upcoming appointment';
    $canMutateBooking = ! in_array($appointment->status, ['completed', 'cancelled', 'no_show'], true);
    $canCancelBooking = $canMutateBooking && auth()->user()->dashboardScopedStaffId() === null;
    $boardStaff = $boardStaff ?? collect();
    $serviceIds = $appointment->services->pluck('service_id')->filter()->values()->all();
    $todayYmd = \App\Support\SalonTime::todayDateString($salon);
    $currentStaffId = (string) ($appointment->staff_id ?? '');
    $scopedStaffId = auth()->user()->dashboardScopedStaffId();
    if ($scopedStaffId !== null) {
        $currentStaffId = (string) $scopedStaffId;
    }
    $confirmConflict = (int) session('booking_confirm_conflict_id') === (int) $appointment->id;
    $confirmConflictMessage = session('booking_confirm_conflict_message');
@endphp
<article class="card p-4 shadow-sm border-violet-200/80 dark:border-violet-900/40 ring-1 ring-violet-500/10"
         @if($canMutateBooking)
         x-data="taskBoardBookingCard(@js([
             'occupiedUrl' => route('appointments.occupied-slots'),
             'excludeId' => $appointment->id,
             'serviceIds' => $serviceIds,
             'staffId' => $currentStaffId,
             'today' => $todayYmd,
             'slotTimes' => \App\Support\AppointmentSlotGrid::allTimes(),
         ]))"
         @if($confirmConflict) x-init="openReschedule()" @endif
         @endif>
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
        {{ $bookingDayLabel }}
        @if($appointment->reference)
            · {{ $appointment->reference }}
        @endif
    </p>

    @if($confirmConflict)
        <div class="mt-3 rounded-xl border border-red-200 dark:border-red-800 bg-red-50/90 dark:bg-red-950/30 p-3 space-y-1">
            <p class="text-xs font-semibold text-red-700 dark:text-red-300">Scheduling conflict detected</p>
            <p class="text-[11px] text-red-600 dark:text-red-400">{{ $confirmConflictMessage ?: 'This staff member is already booked at the requested time.' }}</p>
            <p class="text-[11px] text-red-600/90 dark:text-red-400/90">This booking was not confirmed. Choose a different time below.</p>
        </div>
    @endif

    <x-unless-admin-browse>
    <div class="mt-3 flex flex-wrap items-center gap-2">
        <a href="{{ route('appointments.show', $appointment) }}" class="rounded-full border border-gray-200 dark:border-gray-700 px-2.5 py-1 text-[11px] font-medium text-body hover:bg-gray-100 dark:hover:bg-gray-800">Open</a>
        @if($appointment->status === 'pending')
            <form method="POST" action="{{ route('appointments.confirm', $appointment) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="redirect" value="tasks">
                <button type="submit" class="rounded-full border border-sky-200 dark:border-sky-900 text-sky-800 dark:text-sky-200 px-2.5 py-1 text-[11px] font-medium hover:bg-sky-50 dark:hover:bg-sky-950/40">Confirm</button>
            </form>
        @elseif($appointment->status === 'hold')
            <form method="POST" action="{{ route('appointments.status', $appointment) }}" class="inline">
                @csrf @method('PATCH')
                <input type="hidden" name="status" value="confirmed">
                <input type="hidden" name="redirect" value="tasks">
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
        @if($canMutateBooking)
            <button type="button"
                    @click="openReschedule()"
                    class="rounded-full border border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-200 px-2.5 py-1 text-[11px] font-medium hover:bg-amber-50 dark:hover:bg-amber-950/40">
                Reschedule
            </button>
        @endif
        @if($canCancelBooking)
            <button type="button"
                    @click="panel = panel === 'cancel' ? null : 'cancel'"
                    class="rounded-full border border-red-200 dark:border-red-900 text-red-700 dark:text-red-300 px-2.5 py-1 text-[11px] font-medium hover:bg-red-50 dark:hover:bg-red-950/40">
                Cancel
            </button>
        @endif
    </div>

    @if($canMutateBooking)
    <div x-show="panel === 'reschedule'" x-cloak class="mt-3 rounded-xl border border-amber-200 dark:border-amber-800 bg-amber-50/80 dark:bg-amber-950/20 p-3 space-y-3">
        <p class="text-xs font-semibold text-amber-800 dark:text-amber-300">Reschedule on this board</p>
        <form method="POST" action="{{ route('appointments.reschedule', $appointment) }}" class="space-y-3" @submit="if (!selectedTime) { $event.preventDefault(); slotError = 'Pick an available time slot.'; }">
            @csrf @method('PATCH')
            <input type="hidden" name="redirect" value="tasks">
            <div>
                <label class="block text-[11px] font-medium text-muted mb-1">Staff</label>
                @if($scopedStaffId !== null)
                    <input type="hidden" name="staff_id" :value="staffId">
                    <p class="text-xs text-body">{{ $appointment->staff?->name ?? ('Staff #'.$scopedStaffId) }}</p>
                @else
                    <select name="staff_id" x-model="staffId" @change="onStaffOrDateChange()" class="form-select text-xs py-1.5 w-full">
                        @foreach($boardStaff as $st)
                            <option value="{{ $st->id }}" @selected((string) $st->id === (string) $currentStaffId)>{{ $st->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <label class="block text-[11px] font-medium text-muted mb-1">Date</label>
                <input type="date" x-model="selectedDate" @change="onStaffOrDateChange()" :min="today" class="form-input text-xs py-1.5 w-full">
            </div>
            <div>
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <label class="block text-[11px] font-medium text-muted">Available slots</label>
                    <span x-show="loadingSlots" class="text-[10px] text-muted">Checking…</span>
                </div>
                <p x-show="slotError" class="text-[11px] text-red-600 dark:text-red-400 mb-1.5" x-text="slotError"></p>
                <p x-show="!loadingSlots && availableSlots.length === 0" class="text-[11px] text-muted py-2">No open slots for this staff on the selected date.</p>
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5 max-h-36 overflow-y-auto">
                    <template x-for="slot in availableSlots" :key="slot">
                        <button type="button"
                                @click="pickSlot(slot)"
                                :class="selectedTime === slot
                                    ? 'border-amber-500 bg-amber-500 text-white'
                                    : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-body hover:border-amber-400'"
                                class="rounded-lg border px-1.5 py-1.5 text-[11px] font-semibold tabular-nums transition-colors"
                                x-text="formatSlot(slot)"></button>
                    </template>
                </div>
            </div>
            <input type="hidden" name="starts_at" :value="startsAtValue">
            <div class="flex flex-wrap gap-2">
                <button type="submit" :disabled="!selectedTime || loadingSlots"
                        class="rounded-full bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-white px-3 py-1.5 text-[11px] font-semibold">
                    Confirm reschedule
                </button>
                <button type="button" @click="panel = null" class="rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-[11px] text-muted hover:text-body">Close</button>
            </div>
        </form>
    </div>
    @endif

    @if($canCancelBooking)
    <div x-show="panel === 'cancel'" x-cloak class="mt-3 rounded-xl border border-red-200 dark:border-red-800 bg-red-50/80 dark:bg-red-950/20 p-3 space-y-3">
        <p class="text-xs font-semibold text-red-700 dark:text-red-300">Cancel this booking</p>
        <form method="POST" action="{{ route('appointments.cancel', $appointment) }}" class="space-y-2"
              onsubmit="return confirm('Cancel this appointment?');">
            @csrf @method('PATCH')
            <input type="hidden" name="redirect" value="tasks">
            <textarea name="cancellation_reason" rows="2" class="form-textarea text-xs" placeholder="Reason (optional)"></textarea>
            <div class="flex flex-wrap gap-2">
                <button type="submit" class="rounded-full bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 text-[11px] font-semibold">Confirm cancel</button>
                <button type="button" @click="panel = null" class="rounded-full border border-gray-200 dark:border-gray-700 px-3 py-1.5 text-[11px] text-muted hover:text-body">Close</button>
            </div>
        </form>
    </div>
    @endif
    </x-unless-admin-browse>
</article>
