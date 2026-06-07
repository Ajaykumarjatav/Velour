@php
    use App\Support\AppointmentSlotGrid;

    $slotPeriods = AppointmentSlotGrid::byPeriod();
@endphp

<div class="appt-scheduler rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 p-4 sm:p-5 shadow-sm">

    {{-- Month --}}
    <div class="mb-5">
        <label class="sr-only" for="appt-scheduler-month">Month</label>
        <select id="appt-scheduler-month"
                x-model="viewMonth"
                @change="onMonthChange()"
                class="form-select w-full sm:w-auto min-w-[10rem] text-sm font-semibold text-heading border-gray-200 dark:border-gray-600 rounded-lg py-2 pl-3 pr-9">
            <template x-for="opt in monthOptions" :key="opt.value">
                <option :value="opt.value" x-text="opt.label"></option>
            </template>
        </select>
    </div>

    {{-- Week strip --}}
    <div class="flex gap-2 overflow-x-auto pb-1 -mx-1 px-1 scrollbar-thin" role="group" aria-label="Select date">
        <template x-for="day in weekDays" :key="day.ymd">
            <button type="button"
                    @click="selectWeekDay(day.ymd)"
                    :disabled="day.isPast"
                    :aria-pressed="selectedDate === day.ymd"
                    :class="selectedDate === day.ymd
                        ? 'bg-velour-600 text-white border-velour-600 shadow-md'
                        : (day.isPast
                            ? 'bg-gray-50 dark:bg-gray-800/50 text-muted border-gray-100 dark:border-gray-800 cursor-not-allowed opacity-50'
                            : 'bg-white dark:bg-gray-800 text-heading border-gray-200 dark:border-gray-600 hover:border-velour-400')"
                    class="appt-scheduler-day flex-shrink-0 flex flex-col items-center justify-center w-[3.25rem] h-[3.25rem] sm:w-14 sm:h-14 rounded-xl border text-center transition-all">
                <span class="text-base sm:text-lg font-bold leading-none" x-text="day.dayNum"></span>
                <span class="text-[10px] sm:text-xs font-semibold uppercase tracking-wide mt-0.5 opacity-90" x-text="day.dow"></span>
            </button>
        </template>
    </div>

    {{-- Time slots --}}
    <div class="mt-6" x-show="selectedDate && staffId" x-cloak>
        <div class="flex items-center justify-between gap-2 mb-4">
            <p class="text-xs text-muted">
                <span x-show="loadingSlots">Checking availability…</span>
                <span x-show="!loadingSlots && selectedTime" x-cloak>
                    Selected: <span class="font-semibold text-velour-600 dark:text-velour-400" x-text="formatSlotLabel(selectedTime)"></span>
                </span>
            </p>
            <button type="button"
                    @click="shiftWeek(1)"
                    class="text-xs text-link font-medium sm:hidden"
                    x-show="weekDays.length">Next week →</button>
        </div>

        <ul x-show="!loadingSlots && blockedReasonMessages.length"
            class="text-xs text-amber-700 dark:text-amber-400 mb-3 list-disc pl-4 space-y-0.5"
            role="status">
            <template x-for="msg in blockedReasonMessages" :key="msg">
                <li x-text="msg"></li>
            </template>
        </ul>

        @foreach([
            'morning' => 'Morning',
            'afternoon' => 'Afternoon',
            'evening' => 'Evening',
        ] as $periodKey => $periodLabel)
            @if(count($slotPeriods[$periodKey]) > 0)
            <div class="mb-6 last:mb-0">
                <div class="relative flex items-center mb-3">
                    <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                    <span class="flex-shrink-0 mx-3 text-xs font-medium text-muted uppercase tracking-wider">{{ $periodLabel }}</span>
                    <div class="flex-grow border-t border-gray-200 dark:border-gray-700"></div>
                </div>
                <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2">
                    @foreach($slotPeriods[$periodKey] as $slotTime)
                    <button type="button"
                            data-slot="{{ $slotTime }}"
                            @click="pickSlot('{{ $slotTime }}')"
                            :disabled="isBlocked('{{ $slotTime }}') || loadingSlots"
                            :title="slotBlockTitle('{{ $slotTime }}')"
                            :class="isBlocked('{{ $slotTime }}')
                                ? 'bg-gray-100 dark:bg-gray-800/80 text-muted border-gray-200 dark:border-gray-700 cursor-not-allowed opacity-50 line-through'
                                : (selectedTime === '{{ $slotTime }}'
                                    ? 'bg-velour-600 text-white border-velour-600 font-semibold shadow-sm'
                                    : 'bg-white dark:bg-gray-800 text-slate-700 dark:text-slate-200 border-gray-200 dark:border-gray-600 hover:border-velour-400 hover:text-velour-600 dark:hover:text-velour-400')"
                            class="appt-scheduler-slot py-2.5 px-1 rounded-lg border text-xs sm:text-sm font-medium transition-all disabled:pointer-events-none">
                        {{ \Carbon\Carbon::createFromFormat('H:i', $slotTime)->format('h:i a') }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif
        @endforeach

        <p x-show="!selectedTime && selectedDate && staffId && !loadingSlots"
           class="text-xs text-amber-600 dark:text-amber-400 mt-2">Please select an available time slot.</p>
    </div>

    <p x-show="staffId && !selectedDate" class="text-sm text-muted mt-4 text-center" x-cloak>Choose a date above to see available times.</p>
    <p x-show="!staffId" class="text-sm text-muted mt-4 text-center">Select a staff member to view the schedule.</p>
</div>
