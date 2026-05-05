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
            <a href="{{ route('appointments.create') }}" class="btn-primary btn-sm whitespace-nowrap">+ New</a>
        </div>
    </div>
</div>

<div class="card overflow-hidden">
    @if($view === 'month')
        <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/35">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
            <div class="px-3 py-3 text-[11px] font-semibold text-muted uppercase tracking-wider text-center">{{ $day }}</div>
            @endforeach
        </div>
        @php
            $firstDay   = $date->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
            $lastDay    = $date->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
            $current    = $firstDay->copy();
            $apptsByDay = $appointments->groupBy(fn ($a) => \Carbon\Carbon::parse($a['start'])->timezone($salonTz)->toDateString());
        @endphp
        <div class="grid grid-cols-7">
            @while($current->lte($lastDay))
            @php $dayStr = $current->toDateString(); $isToday = ($dayStr === $salonTodayYmd); $isCurrentMonth = $current->month === $date->month; @endphp
            <div class="min-h-[104px] border-r border-b border-gray-100 dark:border-gray-800/90 p-2.5 {{ !$isCurrentMonth ? 'bg-gray-50/80 dark:bg-gray-800/25' : '' }}">
                <span class="inline-flex items-center justify-center w-7 h-7 text-sm mb-1.5
                    {{ $isToday ? 'bg-velour-600 text-white rounded-full font-bold shadow-sm ring-2 ring-velour-500/30' : ($isCurrentMonth ? 'text-heading' : 'text-muted') }}">
                    {{ $current->day }}
                </span>
                @foreach($apptsByDay->get($dayStr, []) as $apt)
                <a href="{{ $apt['url'] }}"
                   class="block truncate text-xs px-2 py-1 rounded-lg text-white mb-1 font-medium shadow-sm ring-1 ring-black/10 dark:ring-white/10"
                   style="background-color: {{ $apt['color'] }}">
                    {{ \Carbon\Carbon::parse($apt['start'])->timezone($salonTz)->format('H:i') }} {{ $apt['title'] }}
                </a>
                @endforeach
            </div>
            @php $current->addDay(); @endphp
            @endwhile
        </div>

    @elseif($view === 'week')
        @php
            $hours = range($hourStart ?? 8, $hourEnd ?? 20);
            $days  = collect();
            $d     = $start->copy();
            while ($d->lte($end)) { $days->push($d->copy()); $d->addDay(); }
        @endphp
        <div class="overflow-x-auto">
            <div class="min-w-[720px]">
                <div class="grid border-b border-gray-100 dark:border-gray-800 bg-gray-50/90 dark:bg-gray-800/35" style="grid-template-columns: 64px repeat({{ $days->count() }}, 1fr)">
                    <div class="py-3.5"></div>
                    @foreach($days as $day)
                    @php $isColToday = $day->toDateString() === $salonTodayYmd; @endphp
                    <div class="py-3.5 text-center border-l border-gray-100 dark:border-gray-800 {{ $isColToday ? 'bg-velour-50/80 dark:bg-velour-950/25' : '' }}">
                        <p class="text-[11px] font-semibold text-muted uppercase tracking-wider">{{ $day->format('D') }}</p>
                        <p class="text-lg font-semibold tracking-tight mt-0.5 {{ $isColToday ? 'text-velour-600 dark:text-velour-400' : 'text-heading' }}">{{ $day->format('j') }}</p>
                    </div>
                    @endforeach
                </div>
                @foreach($hours as $hour)
                <div class="grid border-b border-gray-100/80 dark:border-gray-800/80 min-h-[58px]" style="grid-template-columns: 64px repeat({{ $days->count() }}, 1fr)">
                    <div class="py-2.5 pr-3 text-right text-xs text-muted tabular-nums">{{ sprintf('%02d:00', $hour) }}</div>
                    @foreach($days as $day)
                    @php
                        $dayMeta = $availabilityByDate[$day->toDateString()] ?? null;
                        $blockedBySalon = !($dayMeta['salon_open'] ?? false) || $hour < (int) ($dayMeta['shop_start'] ?? 0) || $hour >= (int) ($dayMeta['shop_end'] ?? 24);
                        $blockedByStaff = !empty($selectedStaff) && (
                            !($dayMeta['staff_works'] ?? true)
                            || $hour < (int) ($dayMeta['staff_start'] ?? 0)
                            || $hour >= (int) ($dayMeta['staff_end'] ?? 24)
                        );
                        $blocked = $blockedBySalon || $blockedByStaff;
                        $isColToday = $day->toDateString() === $salonTodayYmd;
                        $cellTint = $blocked
                            ? 'bg-gray-100/80 dark:bg-gray-800/45'
                            : ($isColToday ? 'bg-velour-50/40 dark:bg-velour-950/15' : '');
                    @endphp
                    <div class="border-l border-gray-100 dark:border-gray-800/80 relative p-1 {{ $cellTint }}">
                        @foreach($appointments as $apt)
                        @php $aptStart = \Carbon\Carbon::parse($apt['start'])->timezone($salonTz); @endphp
                        @if($aptStart->toDateString() === $day->toDateString() && (int) $aptStart->format('G') === $hour)
                        <a href="{{ $apt['url'] }}"
                           class="block text-xs text-white rounded-xl px-2.5 py-1.5 mb-0.5 truncate font-medium shadow-sm ring-1 ring-black/10 dark:ring-white/10 hover:brightness-110 transition-[filter]"
                           style="background-color: {{ $apt['color'] }}">
                            {{ $aptStart->format('H:i') }} {{ $apt['title'] }}
                        </a>
                        @endif
                        @endforeach
                        @if($blocked)
                            <div class="absolute inset-0 pointer-events-none opacity-35">
                                <div class="h-full w-full border border-dashed border-gray-300/90 dark:border-gray-600/80 rounded-sm m-0.5"></div>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

    @else
        @php
            $hours = range($hourStart ?? 8, $hourEnd ?? 20);
            $dayAppointments = $appointments->filter(fn ($a) => \Carbon\Carbon::parse($a['start'])->timezone($salonTz)->toDateString() === $date->toDateString());
            $dayMeta = $availabilityByDate[$date->toDateString()] ?? null;
        @endphp
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($hours as $hour)
            <div class="flex min-h-[68px]">
                <div class="w-[4.25rem] sm:w-20 py-3.5 pr-3 sm:pr-4 text-right text-xs text-muted tabular-nums flex-shrink-0 bg-gray-50/50 dark:bg-gray-800/20">{{ sprintf('%02d:00', $hour) }}</div>
                @php
                    $blockedBySalon = !($dayMeta['salon_open'] ?? false) || $hour < (int) ($dayMeta['shop_start'] ?? 0) || $hour >= (int) ($dayMeta['shop_end'] ?? 24);
                    $blockedByStaff = !empty($selectedStaff) && (
                        !($dayMeta['staff_works'] ?? true)
                        || $hour < (int) ($dayMeta['staff_start'] ?? 0)
                        || $hour >= (int) ($dayMeta['staff_end'] ?? 24)
                    );
                    $blocked = $blockedBySalon || $blockedByStaff;
                @endphp
                <div class="flex-1 border-l border-gray-100 dark:border-gray-800 p-2 sm:p-2.5 {{ $blocked ? 'bg-gray-100/75 dark:bg-gray-800/40' : '' }}">
                    @foreach($dayAppointments as $apt)
                    @if((int) \Carbon\Carbon::parse($apt['start'])->timezone($salonTz)->format('G') === $hour)
                    <a href="{{ $apt['url'] }}"
                       class="inline-block text-sm text-white rounded-xl px-3 py-2 mr-2 mb-1 font-medium shadow-sm ring-1 ring-black/10 dark:ring-white/10 hover:brightness-110 transition-[filter]"
                       style="background-color: {{ $apt['color'] }}">
                        {{ \Carbon\Carbon::parse($apt['start'])->timezone($salonTz)->format('H:i') }} — {{ $apt['title'] }}
                        <span class="opacity-75 text-xs ml-1">{{ $apt['staff'] }}</span>
                    </a>
                    @endif
                    @endforeach
                    @if($blocked)
                        <div class="text-[10px] text-muted">Unavailable</div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
