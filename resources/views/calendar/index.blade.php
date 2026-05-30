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

<div class="alert-info mb-7 text-sm">
    <div class="space-y-2 min-w-0">
        <p class="font-medium leading-relaxed">Times use your business timezone (<abbr title="{{ $salonTz }}" class="cursor-help decoration-dotted underline-offset-2">{{ $tzAbbrev }}</abbr>). This calendar and the <a href="{{ route('appointments.index') }}" class="underline font-semibold decoration-velour-600/40 dark:decoration-velour-400/40 underline-offset-2">appointments</a> list share the same data.</p>
        <p class="text-xs text-blue-800/90 dark:text-blue-200/90 leading-relaxed">Adjust staff hours or time off under <a href="{{ route('availability.index') }}" class="underline font-medium underline-offset-2">Availability &amp; Resources</a> or <a href="{{ route('staff.index') }}" class="underline font-medium underline-offset-2">Staff &amp; HR</a>.</p>
    </div>
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

<div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 px-4 py-3.5 sm:px-5 sm:py-4 mb-7 shadow-sm dark:shadow-none">
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
            <div x-data="{ openRangePicker: false }" class="inline-flex items-stretch rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700 overflow-visible shadow-sm dark:shadow-none relative">
                <a href="{{ $calRoute($view, $prevDate->toDateString(), ($view === 'week' && $customRangeActive) ? ['from' => $prevFrom, 'to' => $prevTo] : []) }}"
                   class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-body transition-colors"
                   title="Previous">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <button type="button"
                        @click="openRangePicker = !openRangePicker"
                        class="flex items-center justify-center px-3 sm:px-4 text-xs sm:text-sm font-semibold text-heading tabular-nums min-w-[10.5rem] sm:min-w-[12.5rem] text-center hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                    @if($view === 'day')   {{ $date->format('d F Y') }}
                    @elseif($view === 'month') {{ $date->format('F Y') }}
                    @else {{ $start->format('d M') }} – {{ $end->format('d M Y') }}
                    @endif
                </button>
                <a href="{{ $calRoute($view, $nextDate->toDateString(), ($view === 'week' && $customRangeActive) ? ['from' => $nextFrom, 'to' => $nextTo] : []) }}"
                   class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800 text-body transition-colors"
                   title="Next">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                <div x-show="openRangePicker"
                     x-cloak
                     @click.outside="openRangePicker = false"
                     class="absolute top-full right-0 mt-2 w-72 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-3 shadow-xl z-20">
                    <form method="GET" action="{{ route('calendar') }}" class="space-y-3">
                        <input type="hidden" name="view" value="week">
                        <input type="hidden" name="staff_id" value="{{ $filterStaffId }}">
                        <div>
                            <label class="form-label text-xs">From</label>
                            <input type="date" name="from" class="form-input text-sm" value="{{ $rangeFromYmd ?? $start->toDateString() }}">
                        </div>
                        <div>
                            <label class="form-label text-xs">To</label>
                            <input type="date" name="to" class="form-input text-sm" value="{{ $rangeToYmd ?? $end->toDateString() }}">
                        </div>
                        <div class="flex justify-end gap-2 pt-1">
                            <button type="button" class="btn-outline btn-sm" @click="openRangePicker = false">Cancel</button>
                            <button type="submit" class="btn-primary btn-sm">Apply</button>
                        </div>
                    </form>
                </div>
            </div>
            <a href="{{ $calRoute($view, $salonTodayYmd, ['from' => null, 'to' => null]) }}" class="btn-outline btn-sm whitespace-nowrap">Today</a>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    @if(in_array($view, ['week', 'day', 'month'], true))
        @include('calendar.partials.staff-sidebar-grid')
    @endif
</div>

@endsection
