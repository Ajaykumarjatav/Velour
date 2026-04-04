@extends('layouts.app')
@section('title', 'Calendar')
@section('page-title', 'Calendar')
@section('content')

@php
    $filterStaffId = $filterStaffId ?? null;
    $calRoute = fn ($v, $d) => route('calendar', array_filter(['view' => $v, 'date' => $d, 'staff_id' => $filterStaffId]));
@endphp

@if($filterStaffId)
    @php $filterStaffMember = \App\Models\Staff::where('salon_id', $salon->id)->whereKey($filterStaffId)->first(); @endphp
    <div class="mb-4 px-4 py-3 rounded-xl bg-velour-50 dark:bg-velour-900/20 border border-velour-100 dark:border-velour-800 text-sm text-body flex flex-wrap items-center justify-between gap-2">
        <span>Showing calendar for <strong>{{ $filterStaffMember?->name ?? 'staff #' . $filterStaffId }}</strong> only.</span>
        <a href="{{ route('calendar', ['view' => $view, 'date' => $date->toDateString()]) }}" class="text-velour-700 dark:text-velour-300 font-semibold hover:underline">Show all staff</a>
    </div>
@endif

<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex gap-2">
        @foreach(['day'=>'Day','week'=>'Week','month'=>'Month'] as $v => $label)
        <a href="{{ $calRoute($v, $date->toDateString()) }}"
           class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors
                  {{ $view === $v ? 'bg-velour-600 text-white border-velour-600' : 'border-gray-300 dark:border-gray-700 text-body hover:border-velour-400 bg-white dark:bg-gray-900' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    <div class="flex items-center gap-2">
        @php
            $prevDate = $view === 'day' ? $date->copy()->subDay() : ($view === 'month' ? $date->copy()->subMonth() : $date->copy()->subWeek());
            $nextDate = $view === 'day' ? $date->copy()->addDay() : ($view === 'month' ? $date->copy()->addMonth() : $date->copy()->addWeek());
        @endphp
        <a href="{{ $calRoute($view, $prevDate->toDateString()) }}"
           class="p-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 text-body">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <span class="text-sm font-semibold text-heading min-w-[160px] text-center">
            @if($view === 'day')   {{ $date->format('d F Y') }}
            @elseif($view === 'month') {{ $date->format('F Y') }}
            @else {{ $start->format('d M') }} – {{ $end->format('d M Y') }}
            @endif
        </span>
        <a href="{{ $calRoute($view, $nextDate->toDateString()) }}"
           class="p-2 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800 text-body">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        <a href="{{ $calRoute($view, now()->toDateString()) }}" class="btn-outline btn-sm">Today</a>
        <a href="{{ route('appointments.create') }}" class="btn-primary btn-sm">+ New</a>
    </div>
</div>

<div class="card overflow-hidden">
    @if($view === 'month')
        <div class="grid grid-cols-7 border-b border-gray-100 dark:border-gray-800">
            @foreach(['Mon','Tue','Wed','Thu','Fri','Sat','Sun'] as $day)
            <div class="px-3 py-2 text-xs font-semibold text-muted uppercase tracking-wide text-center">{{ $day }}</div>
            @endforeach
        </div>
        @php
            $firstDay   = $date->copy()->startOfMonth()->startOfWeek(\Carbon\Carbon::MONDAY);
            $lastDay    = $date->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
            $current    = $firstDay->copy();
            $apptsByDay = $appointments->groupBy(fn($a) => \Carbon\Carbon::parse($a['start'])->toDateString());
        @endphp
        <div class="grid grid-cols-7">
            @while($current->lte($lastDay))
            @php $dayStr = $current->toDateString(); $isToday = $current->isToday(); $isCurrentMonth = $current->month === $date->month; @endphp
            <div class="min-h-[100px] border-r border-b border-gray-100 dark:border-gray-800 p-2 {{ !$isCurrentMonth ? 'bg-gray-50 dark:bg-gray-800/30' : '' }}">
                <span class="inline-flex items-center justify-center w-7 h-7 text-sm mb-1
                    {{ $isToday ? 'bg-velour-600 text-white rounded-full font-bold' : ($isCurrentMonth ? 'text-heading' : 'text-muted') }}">
                    {{ $current->day }}
                </span>
                @foreach($apptsByDay->get($dayStr, []) as $apt)
                <a href="{{ $apt['url'] }}"
                   class="block truncate text-xs px-1.5 py-0.5 rounded-md text-white mb-0.5 font-medium"
                   style="background-color: {{ $apt['color'] }}">
                    {{ \Carbon\Carbon::parse($apt['start'])->format('H:i') }} {{ $apt['title'] }}
                </a>
                @endforeach
            </div>
            @php $current->addDay(); @endphp
            @endwhile
        </div>

    @elseif($view === 'week')
        @php
            $hours = range(8, 20);
            $days  = collect();
            $d     = $start->copy();
            while ($d->lte($end)) { $days->push($d->copy()); $d->addDay(); }
        @endphp
        <div class="overflow-x-auto">
            <div class="min-w-[700px]">
                <div class="grid border-b border-gray-100 dark:border-gray-800" style="grid-template-columns: 60px repeat({{ $days->count() }}, 1fr)">
                    <div class="py-3"></div>
                    @foreach($days as $day)
                    <div class="py-3 text-center border-l border-gray-100 dark:border-gray-800">
                        <p class="text-xs font-semibold text-muted uppercase">{{ $day->format('D') }}</p>
                        <p class="text-lg font-bold {{ $day->isToday() ? 'text-velour-600 dark:text-velour-400' : 'text-heading' }}">{{ $day->format('j') }}</p>
                    </div>
                    @endforeach
                </div>
                @foreach($hours as $hour)
                <div class="grid border-b border-gray-50 dark:border-gray-800/50 min-h-[56px]" style="grid-template-columns: 60px repeat({{ $days->count() }}, 1fr)">
                    <div class="py-2 pr-3 text-right text-xs text-muted">{{ sprintf('%02d:00', $hour) }}</div>
                    @foreach($days as $day)
                    <div class="border-l border-gray-50 dark:border-gray-800/50 relative p-0.5">
                        @foreach($appointments as $apt)
                        @php $aptStart = \Carbon\Carbon::parse($apt['start']); @endphp
                        @if($aptStart->toDateString() === $day->toDateString() && $aptStart->hour === $hour)
                        <a href="{{ $apt['url'] }}"
                           class="block text-xs text-white rounded-lg px-2 py-1 mb-0.5 truncate font-medium shadow-sm"
                           style="background-color: {{ $apt['color'] }}">
                            {{ $aptStart->format('H:i') }} {{ $apt['title'] }}
                        </a>
                        @endif
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

    @else
        @php
            $hours = range(8, 20);
            $dayAppointments = $appointments->filter(fn($a) => \Carbon\Carbon::parse($a['start'])->toDateString() === $date->toDateString());
        @endphp
        <div class="divide-y divide-gray-50 dark:divide-gray-800">
            @foreach($hours as $hour)
            <div class="flex min-h-[64px]">
                <div class="w-16 py-3 pr-4 text-right text-xs text-muted flex-shrink-0">{{ sprintf('%02d:00', $hour) }}</div>
                <div class="flex-1 border-l border-gray-100 dark:border-gray-800 p-1.5">
                    @foreach($dayAppointments as $apt)
                    @if(\Carbon\Carbon::parse($apt['start'])->hour === $hour)
                    <a href="{{ $apt['url'] }}"
                       class="inline-block text-sm text-white rounded-xl px-3 py-2 mr-2 mb-1 font-medium shadow-sm"
                       style="background-color: {{ $apt['color'] }}">
                        {{ \Carbon\Carbon::parse($apt['start'])->format('H:i') }} — {{ $apt['title'] }}
                        <span class="opacity-75 text-xs ml-1">{{ $apt['staff'] }}</span>
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
