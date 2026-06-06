@extends('layouts.app')
@section('title', 'Calendar')
@section('page-title', 'Calendar')
@section('content')

@php
    $filterStaffId = $filterStaffId ?? null;
    $salonTz = $salonTz ?? ($salon->timezone ?? config('app.timezone'));
    $salonTodayYmd = $salonTodayYmd ?? \Carbon\Carbon::now($salonTz)->toDateString();
    $tzAbbrev = $tzAbbrev ?? '';
    $customRangeActive = (bool) ($customRangeActive ?? false);
    $rangeFromYmd = $rangeFromYmd ?? null;
    $rangeToYmd = $rangeToYmd ?? null;
    $rangeSpanDays = (int) ($rangeSpanDays ?? 7);
    $calRoute = fn ($v, $d, array $extra = []) => route('calendar', array_filter(array_merge([
        'view' => $v,
        'date' => $d,
        'staff_id' => $filterStaffId,
        'from' => ($v === 'week' && $customRangeActive) ? $rangeFromYmd : null,
        'to' => ($v === 'week' && $customRangeActive) ? $rangeToYmd : null,
    ], $extra), fn ($value) => $value !== null && $value !== ''));
@endphp

<div class="mb-4 space-y-0.5">
    <p class="text-[11px] leading-snug text-gray-400 dark:text-gray-500">
        Times use your business timezone (<abbr title="{{ $salonTz }}" class="cursor-help decoration-dotted underline-offset-2">{{ $tzAbbrev }}</abbr>). This calendar and the <a href="{{ route('appointments.index') }}" class="text-gray-500 dark:text-gray-400 underline decoration-gray-300 dark:decoration-gray-600 hover:text-velour-600 dark:hover:text-velour-400">appointments</a> list share the same data.
    </p>
    <p class="text-[11px] leading-snug text-gray-400 dark:text-gray-500">
        Adjust staff hours or time off under <a href="{{ route('availability.index') }}" class="text-gray-500 dark:text-gray-400 underline decoration-gray-300 dark:decoration-gray-600 hover:text-velour-600 dark:hover:text-velour-400">Availability &amp; Resources</a> or <a href="{{ route('staff.index') }}" class="text-gray-500 dark:text-gray-400 underline decoration-gray-300 dark:decoration-gray-600 hover:text-velour-600 dark:hover:text-velour-400">Staff &amp; HR</a>.
    </p>
</div>

@if(!empty($selectedStaff))
    <div class="mb-5 px-5 py-4 rounded-2xl leading-relaxed bg-indigo-50 dark:bg-indigo-950/35 border border-indigo-200/80 dark:border-indigo-500/25 text-sm text-body">
        Calendar availability is currently constrained by <strong>{{ $selectedStaff->name }}</strong>'s working days and shift hours.
    </div>
@endif

@if($filterStaffId)
    @php $filterStaffMember = \App\Models\Staff::where('salon_id', $salon->id)->whereKey($filterStaffId)->first(); @endphp
    <div class="mb-6 px-5 py-4 rounded-2xl leading-relaxed bg-velour-50 dark:bg-velour-950/35 border border-velour-200/80 dark:border-velour-500/20 text-sm text-body">
        <span>Showing calendar for <strong>{{ $filterStaffMember?->name ?? 'staff #' . $filterStaffId }}</strong> only.</span>
    </div>
@endif

<div class="relative z-40 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 px-4 py-3.5 sm:px-5 sm:py-4 mb-7 shadow-sm dark:shadow-none">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="inline-flex p-1 rounded-xl bg-gray-100 dark:bg-gray-800/90 border border-gray-200/90 dark:border-gray-700 gap-0.5">
            @foreach(['day'=>'Day','week'=>'Week','month'=>'Month'] as $v => $label)
            <a href="{{ $calRoute($v, $date->toDateString()) }}"
               class="px-3.5 py-2 text-sm font-medium rounded-lg transition-all duration-150
                      {{ $view === $v
                          ? 'bg-velour-600 text-white shadow-sm ring-1 ring-black/5 dark:ring-white/10'
                          : 'text-body hover:bg-white/80 dark:hover:bg-gray-700/60' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
            <form method="GET" action="{{ route('calendar') }}" class="flex items-center gap-2">
                <input type="hidden" name="view" value="{{ $view }}">
                <input type="hidden" name="date" value="{{ $date->toDateString() }}">
                <x-searchable-select
                    id="calendar-staff-filter"
                    name="staff_id"
                    wrapper-class="min-w-0"
                    :search-url="route('lookup.staff')"
                    search-placeholder="Search staff…"
                    trigger-class="form-select text-sm min-w-[11rem] sm:min-w-[12.5rem]"
                    onchange="this.form.submit()">
                    <option value="">All staff availability</option>
                    @foreach($staff as $st)
                        <option value="{{ $st->id }}" {{ (string) $filterStaffId === (string) $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                    @endforeach
                </x-searchable-select>
            </form>
            @php
                if ($view === 'week' && $customRangeActive) {
                    $span = max(1, $rangeSpanDays);
                    $prevDate = $date->copy()->subDays($span);
                    $nextDate = $date->copy()->addDays($span);
                    $prevFrom = $start->copy()->subDays($span)->toDateString();
                    $prevTo = $end->copy()->subDays($span)->toDateString();
                    $nextFrom = $start->copy()->addDays($span)->toDateString();
                    $nextTo = $end->copy()->addDays($span)->toDateString();
                } else {
                    $prevDate = $view === 'day' ? $date->copy()->subDay() : ($view === 'month' ? $date->copy()->subMonth() : $date->copy()->subWeek());
                    $nextDate = $view === 'day' ? $date->copy()->addDay() : ($view === 'month' ? $date->copy()->addMonth() : $date->copy()->addWeek());
                    $prevFrom = $prevTo = $nextFrom = $nextTo = null;
                }
            @endphp
            <div x-data="{ openRangePicker: false }" class="relative isolate inline-flex items-stretch rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700 shadow-sm dark:shadow-none">
                <a href="{{ $calRoute($view, $prevDate->toDateString(), ($view === 'week' && $customRangeActive) ? ['from' => $prevFrom, 'to' => $prevTo] : []) }}"
                   class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-body transition-colors"
                   title="Previous">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                @if($view === 'week')
                <button type="button"
                        @click="openRangePicker = !openRangePicker"
                        @keydown.escape.window="openRangePicker = false"
                        class="flex items-center justify-center px-3 sm:px-4 text-xs sm:text-sm font-semibold text-heading tabular-nums min-w-[10.5rem] sm:min-w-[12.5rem] text-center hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                        :aria-expanded="openRangePicker">
                    {{ $start->format('d M') }} – {{ $end->format('d M Y') }}
                </button>
                @else
                <span class="flex items-center justify-center px-3 sm:px-4 text-xs sm:text-sm font-semibold text-heading tabular-nums min-w-[10.5rem] sm:min-w-[12.5rem] text-center">
                    @if($view === 'day'){{ $date->format('d F Y') }}
                    @else{{ $date->format('F Y') }}
                    @endif
                </span>
                @endif
                <a href="{{ $calRoute($view, $nextDate->toDateString(), ($view === 'week' && $customRangeActive) ? ['from' => $nextFrom, 'to' => $nextTo] : []) }}"
                   class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-body transition-colors"
                   title="Next">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @if($view === 'week')
                <div x-show="openRangePicker"
                     x-cloak
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     @click.outside="openRangePicker = false"
                     class="absolute left-0 right-0 sm:left-auto sm:right-0 top-full mt-2 w-[min(100vw-1.5rem,40rem)] rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 shadow-2xl ring-1 ring-black/5 dark:ring-white/10 z-[60] overflow-visible">
                    <form method="GET" action="{{ route('calendar') }}" class="space-y-3">
                        <input type="hidden" name="view" value="week">
                        <input type="hidden" name="staff_id" value="{{ $filterStaffId }}">
                        <x-date-range-picker
                            :inline="true"
                            :from-value="$rangeFromYmd ?? $start->toDateString()"
                            :to-value="$rangeToYmd ?? $end->toDateString()"
                            :salon-today="$salonTodayYmd"
                            class="relative z-10" />
                        <div class="flex justify-end gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                            <button type="button" class="btn-outline btn-sm" @click="openRangePicker = false">Cancel</button>
                            <button type="submit" class="btn-primary btn-sm" @click="openRangePicker = false">Apply</button>
                        </div>
                    </form>
                </div>
                @endif
            </div>
            <a href="{{ $calRoute($view, $salonTodayYmd, ['from' => null, 'to' => null]) }}" class="btn-outline btn-sm whitespace-nowrap">Today</a>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    @if($view === 'day' && !empty($dayScheduleGrid))
        @include('calendar.partials.day-schedule-grid')
    @elseif(in_array($view, ['week', 'month'], true))
        @include('calendar.partials.staff-sidebar-grid')
    @endif
</div>

@endsection
