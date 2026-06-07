@php
    $grid = $dayScheduleGrid ?? [];
    $columns = $grid['staff_columns'] ?? [];
    $timeSlots = $grid['time_slots'] ?? [];
    $colCount = max(1, count($columns));
    $slotH = (int) ($grid['slot_height_px'] ?? 52);
    $gridH = (int) ($grid['grid_height_px'] ?? 600);
    $staffPage = (int) ($grid['staff_page'] ?? 1);
    $staffLastPage = (int) ($grid['staff_last_page'] ?? 1);
    $staffRange = $grid['staff_range_label'] ?? '0';
    $timeInset = 14;
    $bodyH = $gridH + ($timeInset * 2);
    $timeSlotCount = count($timeSlots);
    $dayCalRoute = fn (int $page) => route('calendar', array_filter([
        'view' => 'day',
        'date' => $grid['ymd'] ?? $date->toDateString(),
        'staff_id' => $filterStaffId ?? null,
        'staff_page' => $page > 1 ? $page : null,
    ], fn ($v) => $v !== null && $v !== ''));
@endphp

@push('styles')
<style>
    .day-cal {
        --day-time-w: 4.75rem;
        --day-staff-min: 8.25rem;
        --day-head-h: 3.25rem;
        --day-slot-h: {{ $slotH }}px;
        --day-time-inset: {{ $timeInset }}px;
    }
    .day-cal__scroll { overflow: auto; max-height: min(80vh, 960px); }
    .day-cal__grid {
        display: grid;
        grid-template-columns: var(--day-time-w) repeat({{ $colCount }}, minmax(var(--day-staff-min), 1fr));
        min-width: calc(var(--day-time-w) + {{ $colCount }} * var(--day-staff-min));
    }
    .day-cal__corner {
        position: sticky;
        left: 0;
        top: 0;
        z-index: 40;
        min-height: var(--day-head-h);
        background: rgb(249 250 251 / 0.98);
        border-bottom: 1px solid rgb(229 231 235);
        border-right: 1px solid rgb(229 231 235);
    }
    .dark .day-cal__corner {
        background: rgb(17 24 39 / 0.98);
        border-color: rgb(55 65 81);
    }
    .day-cal__staff-head {
        position: sticky;
        top: 0;
        z-index: 35;
        min-height: var(--day-head-h);
        padding: 0.35rem 0.4rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.15rem;
        text-align: center;
        background: rgb(249 250 251 / 0.98);
        border-bottom: 1px solid rgb(229 231 235);
        border-left: 1px solid rgb(229 231 235);
    }
    .day-cal__staff-head .day-cal__staff-avatar { width: 2rem; height: 2rem; font-size: 0.65rem; }
    .day-cal__staff-head .day-cal__staff-avatar img { width: 2rem; height: 2rem; }
    .day-cal__staff-head-name {
        max-width: 100%;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.15;
        color: rgb(17 24 39);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .dark .day-cal__staff-head-name { color: rgb(243 244 246); }
    .day-cal__staff-head-date {
        font-size: 0.6rem;
        font-weight: 500;
        line-height: 1;
        color: rgb(107 114 128);
    }
    .dark .day-cal__staff-head-date { color: rgb(156 163 175); }
    .dark .day-cal__staff-head {
        background: rgb(17 24 39 / 0.98);
        border-color: rgb(55 65 81);
    }
    .day-cal__time-col {
        position: sticky;
        left: 0;
        z-index: 30;
        background: rgb(249 250 251 / 0.98);
        border-right: 1px solid rgb(229 231 235);
        padding-top: var(--day-time-inset);
        padding-bottom: var(--day-time-inset);
        overflow: visible;
        box-sizing: border-box;
    }
    .dark .day-cal__time-col { background: rgb(17 24 39 / 0.98); border-color: rgb(55 65 81); }
    .day-cal__time-label {
        position: absolute;
        right: 0.65rem;
        transform: translateY(-50%);
        font-size: 0.68rem;
        font-weight: 500;
        line-height: 1.2;
        color: rgb(107 114 128);
        white-space: nowrap;
        pointer-events: none;
    }
    .day-cal__time-label--edge-start { transform: translateY(0); }
    .day-cal__time-label--edge-end { transform: translateY(-100%); }
    .day-cal__time-label--minor {
        right: 0.35rem;
        font-size: 0.55rem;
        font-weight: 400;
        opacity: 0.55;
        transform: translateY(-50%);
    }
    .dark .day-cal__time-label { color: rgb(156 163 175); }
    .day-cal__staff-head-slots {
        font-size: 0.58rem;
        font-weight: 600;
        line-height: 1;
        color: rgb(124 58 237);
        font-variant-numeric: tabular-nums;
    }
    .dark .day-cal__staff-head-slots { color: rgb(167 139 250); }
    .day-cal__staff-col {
        position: relative;
        border-left: 1px solid rgb(229 231 235);
        padding-top: var(--day-time-inset);
        padding-bottom: var(--day-time-inset);
        box-sizing: border-box;
        background-color: rgb(255 255 255);
        background-image:
            linear-gradient(to bottom, rgb(229 231 235) 1px, transparent 1px),
            linear-gradient(to bottom, rgb(243 244 246) 1px, transparent 1px);
        background-size: 100% var(--day-slot-h), 100% calc(var(--day-slot-h) / 3);
        background-position: 0 var(--day-time-inset);
    }
    .dark .day-cal__staff-col {
        border-color: rgb(55 65 81);
        background-color: rgb(17 24 39);
        background-image:
            linear-gradient(to bottom, rgb(55 65 81) 1px, transparent 1px),
            linear-gradient(to bottom, rgb(31 41 55) 1px, transparent 1px);
    }
    .day-cal__staff-col--blocked {
        background-color: rgb(243 244 246);
        background-image: none;
    }
    .dark .day-cal__staff-col--blocked { background-color: rgb(31 41 55); }
    .day-cal__appt {
        position: absolute;
        left: 0.35rem;
        right: 0.35rem;
        z-index: 2;
        display: flex;
        flex-direction: column;
        gap: 0.1rem;
        padding: 0.4rem 0.45rem 0.4rem 0.55rem;
        border-radius: 0.35rem;
        border-left: 4px solid var(--appt-accent, #7c3aed);
        background: var(--appt-bg, rgb(124 58 237 / 0.12));
        box-shadow: 0 1px 3px rgb(0 0 0 / 0.08);
        text-decoration: none;
        color: rgb(17 24 39);
        overflow: hidden;
        transition: box-shadow 0.15s, transform 0.15s;
    }
    .day-cal__appt:hover {
        z-index: 5;
        box-shadow: 0 6px 16px rgb(0 0 0 / 0.12);
        transform: translateY(-1px);
    }
    .dark .day-cal__appt { color: rgb(243 244 246); box-shadow: 0 1px 4px rgb(0 0 0 / 0.25); }
    .day-cal__appt-name {
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .day-cal__appt-time {
        font-size: 0.62rem;
        font-weight: 500;
        opacity: 0.9;
        white-space: nowrap;
    }
    .day-cal__appt-foot {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 0.35rem;
        margin-top: auto;
        min-height: 0;
    }
    .day-cal__appt-service {
        flex: 1;
        min-width: 0;
        font-size: 0.6rem;
        font-weight: 500;
        opacity: 0.85;
        line-height: 1.2;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .day-cal__appt-source {
        flex-shrink: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.1rem;
        height: 1.1rem;
        border-radius: 0.2rem;
        background: rgb(255 255 255 / 0.65);
        color: rgb(75 85 99);
    }
    .dark .day-cal__appt-source {
        background: rgb(0 0 0 / 0.2);
        color: rgb(209 213 219);
    }
    .day-cal__appt-source svg { width: 0.7rem; height: 0.7rem; }
    .day-cal__legend {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem 1.25rem;
        padding: 0.65rem 1rem;
        border-top: 1px solid rgb(229 231 235);
        font-size: 0.65rem;
        color: rgb(107 114 128);
    }
    .dark .day-cal__legend {
        border-color: rgb(55 65 81);
        color: rgb(156 163 175);
    }
    .day-cal__legend-item { display: inline-flex; align-items: center; gap: 0.35rem; }
    .day-cal__pager {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: rgb(107 114 128);
    }
    .dark .day-cal__pager { color: rgb(156 163 175); }
    .day-cal__pager a, .day-cal__pager span {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.5rem;
        height: 1.5rem;
        border-radius: 0.35rem;
    }
    .day-cal__pager a:hover { background: rgb(243 244 246); }
    .dark .day-cal__pager a:hover { background: rgb(55 65 81); }
    .day-cal__pager a[aria-disabled="true"] { opacity: 0.35; pointer-events: none; }
</style>
@endpush

@if(count($columns) === 0)
    <div class="p-10 text-center text-muted text-sm">
        No active staff to display. Add staff under
        <a href="{{ route('staff.index') }}" class="underline font-medium text-velour-600 dark:text-velour-400">Staff &amp; HR</a>.
    </div>
@else
    <div class="day-cal day-cal__scroll">
        <div class="day-cal__grid">
            {{-- Corner: staff pagination --}}
            <div class="day-cal__corner px-1.5 py-2 flex items-end">
                @if(($grid['staff_total'] ?? 0) > ($grid['staff_per_page'] ?? 7))
                <div class="day-cal__pager">
                    <a href="{{ $dayCalRoute(max(1, $staffPage - 1)) }}"
                       aria-label="Previous staff"
                       @if($staffPage <= 1) aria-disabled="true" @endif>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                    <span class="tabular-nums px-0.5">{{ $staffRange }}</span>
                    <a href="{{ $dayCalRoute(min($staffLastPage, $staffPage + 1)) }}"
                       aria-label="Next staff"
                       @if($staffPage >= $staffLastPage) aria-disabled="true" @endif>
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @endif
            </div>

            {{-- Staff column headers --}}
            @foreach($columns as $col)
            <div class="day-cal__staff-head">
                <x-staff-avatar class="day-cal__staff-avatar" size="xs" :url="$col['avatar_url']" :initials="$col['initials']" :color="$col['color']" />
                <p class="day-cal__staff-head-name" title="{{ $col['name'] }}">{{ $col['name'] }}</p>
                <p class="day-cal__staff-head-date">{{ $col['date_label'] }}</p>
                <p class="day-cal__staff-head-slots" title="Booked slots / total slots">{{ $col['slots_label'] ?? '0' }}</p>
            </div>
            @endforeach

            {{-- Time axis --}}
            <div class="day-cal__time-col relative" style="height: {{ $bodyH }}px;">
                @foreach($timeSlots as $i => $slot)
                <span @class([
                    'day-cal__time-label',
                    'day-cal__time-label--minor' => !empty($slot['is_minor']),
                    'day-cal__time-label--edge-start' => $i === 0 && empty($slot['is_minor']),
                    'day-cal__time-label--edge-end' => $i === $timeSlotCount - 1 && empty($slot['is_minor']),
                ]) style="top: {{ $timeInset + $slot['top_px'] }}px;">{{ $slot['label'] }}</span>
                @endforeach
            </div>

            {{-- Staff schedule columns --}}
            @foreach($columns as $col)
            <div class="day-cal__staff-col {{ ($col['blocked'] ?? false) ? 'day-cal__staff-col--blocked' : '' }}"
                 style="height: {{ $bodyH }}px;">
                @if(!($col['blocked'] ?? false))
                <a href="{{ $col['create_url'] }}"
                   class="absolute inset-0 z-0 opacity-0 hover:opacity-100 hover:bg-velour-500/5 transition-opacity"
                   aria-label="Add appointment for {{ $col['name'] }}"></a>
                @endif
                @foreach($col['blocks'] as $block)
                <a href="{{ $block['url'] }}"
                   class="day-cal__appt"
                   style="top: {{ $timeInset + $block['top_px'] }}px; height: {{ $block['height_px'] }}px; --appt-accent: {{ $block['block_color'] }}; --appt-bg: {{ $block['block_color'] }}22;"
                   title="{{ $block['client_name'] }} — {{ $block['services_label'] }} ({{ $block['source_label'] ?? '' }})">
                    <span class="day-cal__appt-name">{{ $block['client_name'] }}</span>
                    <span class="day-cal__appt-time">{{ $block['time_range_label'] }}</span>
                    <div class="day-cal__appt-foot">
                        <span class="day-cal__appt-service">{{ $block['services_label'] }}</span>
                        <span class="day-cal__appt-source" title="{{ $block['source_label'] ?? 'Booking' }}">
                            @switch($block['source_icon'] ?? 'desk')
                                @case('online')
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9v-9m0-9a9 9 0 00-9 9m9-9v9"/></svg>
                                    @break
                                @case('walk_in')
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    @break
                                @case('reference')
                                    <svg fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14 2 9.27l7.1-1.01L12 2z"/></svg>
                                    @break
                                @case('phone')
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    @break
                                @default
                                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            @endswitch
                        </span>
                    </div>
                </a>
                @endforeach
            </div>
            @endforeach
        </div>
        <div class="day-cal__legend">
            <span class="day-cal__legend-item">
                <span class="day-cal__appt-source"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9v-9m0-9a9 9 0 00-9 9m9-9v9"/></svg></span>
                Online
            </span>
            <span class="day-cal__legend-item">
                <span class="day-cal__appt-source"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></span>
                Walk-in
            </span>
            <span class="day-cal__legend-item">
                <span class="day-cal__appt-source"><svg fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l2.9 6.26L22 9.27l-5 4.87L18.18 22 12 18.27 5.82 22 7 14.14 2 9.27l7.1-1.01L12 2z"/></svg></span>
                Referral
            </span>
            <span class="day-cal__legend-item">
                <span class="day-cal__appt-source"><svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg></span>
                Desk / manual
            </span>
            <span class="day-cal__legend-item tabular-nums font-semibold text-velour-600 dark:text-velour-400">Slots: booked / total (30 min)</span>
        </div>
    </div>
@endif
