@php
    $grid = $staffSidebarGrid ?? ['days' => [], 'rows' => [], 'layout' => 'week'];
    $days = $grid['days'] ?? [];
    $rows = $grid['rows'] ?? [];
    $layout = $grid['layout'] ?? 'week';
    $isMonth = $layout === 'month';
    $sidebarWidth = '11.5rem';
    $dayMinWidth = match ($layout) {
        'day' => '12rem',
        'month' => '4.5rem',
        default => '7.5rem',
    };
    $dayCount = max(1, count($days));
@endphp

@push('styles')
<style>
    .staff-cal { --staff-sidebar: {{ $sidebarWidth }}; --staff-day-min: {{ $dayMinWidth }}; }
    .staff-cal__scroll { overflow: auto; max-height: min(80vh, 960px); }
    .staff-cal__table {
        display: grid;
        grid-template-columns: var(--staff-sidebar) repeat({{ $dayCount }}, minmax(var(--staff-day-min), 1fr));
        min-width: calc(var(--staff-sidebar) + {{ $dayCount }} * var(--staff-day-min));
    }
    .staff-cal__sidebar {
        position: sticky;
        left: 0;
        z-index: 4;
        background: rgb(249 250 251 / 0.98);
    }
    .dark .staff-cal__sidebar { background: rgb(17 24 39 / 0.98); }
    .staff-cal__head {
        position: sticky;
        top: 0;
        z-index: 5;
        background: rgb(249 250 251 / 0.98);
    }
    .dark .staff-cal__head { background: rgb(17 24 39 / 0.98); }
    .staff-cal__block {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.35rem;
        border-radius: 0.5rem;
        padding: 0.45rem 0.55rem;
        font-size: 0.68rem;
        font-weight: 600;
        line-height: 1.2;
        margin-bottom: 0.35rem;
        text-decoration: none;
        color: rgb(17 24 39);
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.05);
        transition: filter 0.15s, box-shadow 0.15s;
    }
    .dark .staff-cal__block { color: rgb(243 244 246); }
    .staff-cal__block:hover {
        filter: brightness(0.97);
        box-shadow: 0 3px 10px rgb(0 0 0 / 0.1);
    }
    .staff-cal__block-time { white-space: nowrap; }
    .staff-cal__block-dur { opacity: 0.85; white-space: nowrap; font-weight: 500; }
    .staff-cal--month .staff-cal__block {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.1rem;
        padding: 0.3rem 0.35rem;
        font-size: 0.6rem;
        margin-bottom: 0.25rem;
    }
    .staff-cal--month .staff-cal__block-dur { display: none; }
    .staff-cal--month .staff-cal__block-title {
        font-weight: 500;
        opacity: 0.9;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .staff-cal__cell-week-start {
        border-left-width: 2px !important;
        border-left-color: rgb(209 213 219) !important;
    }
    .dark .staff-cal__cell-week-start {
        border-left-color: rgb(75 85 99) !important;
    }
</style>
@endpush

@if(count($rows) === 0)
    <div class="p-10 text-center text-muted text-sm">
        No active staff to display. Add staff under
        <a href="{{ route('staff.index') }}" class="underline font-medium text-velour-600 dark:text-velour-400">Staff &amp; HR</a>.
    </div>
@else
    <div class="staff-cal staff-cal__scroll {{ $isMonth ? 'staff-cal--month' : '' }}">
        <div class="staff-cal__table">
            <div class="staff-cal__head staff-cal__sidebar border-b border-r border-gray-100 dark:border-gray-800 min-h-[3.25rem]"></div>
            @foreach($days as $dayIndex => $day)
            <div class="staff-cal__head border-b border-l border-gray-100 dark:border-gray-800 py-2 text-center min-h-[3.25rem] flex flex-col items-center justify-center
                {{ ($dayIndex % 7) === 0 && $isMonth ? 'staff-cal__cell-week-start' : '' }}
                {{ $day['is_today'] ? 'bg-velour-50/90 dark:bg-velour-950/30' : '' }}
                {{ !($day['is_current_month'] ?? true) ? 'opacity-60' : '' }}">
                <a href="{{ $day['day_url'] ?? '#' }}" class="group flex flex-col items-center" title="{{ $day['ymd'] }}">
                    <span class="inline-flex items-center justify-center w-7 h-7 text-xs font-bold rounded-full transition-colors
                        {{ $day['is_today']
                            ? 'bg-velour-600 text-white shadow-sm'
                            : (($day['is_current_month'] ?? true) ? 'text-heading group-hover:bg-gray-200/80 dark:group-hover:bg-gray-700/80' : 'text-muted') }}">
                        {{ $day['day_num'] }}
                    </span>
                    @if(!$isMonth && count($days) <= 7)
                    <span class="text-[10px] text-muted uppercase tracking-wide mt-0.5">{{ $day['label'] }}</span>
                    @endif
                </a>
            </div>
            @endforeach

            @foreach($rows as $row)
            <div class="staff-cal__sidebar border-b border-r border-gray-100 dark:border-gray-800 px-3 py-3 flex items-center gap-2.5 min-h-[3.5rem]">
                <x-staff-avatar size="xs" :url="$row['avatar_url']" :initials="$row['initials']" :color="$row['color']" />
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-heading truncate leading-tight">{{ $row['name'] }}</p>
                    <p class="text-[11px] text-muted tabular-nums mt-0.5">{{ $row['week_hours_label'] }}</p>
                    @if(!empty($row['role']) && !$isMonth)
                        <p class="text-[10px] text-muted truncate">{{ $row['role'] }}</p>
                    @endif
                </div>
            </div>

            @foreach($days as $dayIndex => $day)
            @php
                $cell = $row['cells'][$day['ymd']] ?? ['blocks' => [], 'blocked' => false, 'create_url' => '#'];
                $blocks = $cell['blocks'] ?? [];
                $blocked = (bool) ($cell['blocked'] ?? false);
                $outsideMonth = !($day['is_current_month'] ?? true);
            @endphp
            <div class="border-b border-l border-gray-100 dark:border-gray-800 p-1 min-h-[3.5rem] relative
                {{ ($dayIndex % 7) === 0 && $isMonth ? 'staff-cal__cell-week-start' : '' }}
                {{ $day['is_today'] ? 'bg-velour-50/30 dark:bg-velour-950/15' : '' }}
                {{ $outsideMonth ? 'bg-gray-50/80 dark:bg-gray-800/30' : '' }}
                {{ $blocked ? 'bg-gray-100/70 dark:bg-gray-800/45' : (!$outsideMonth ? 'hover:bg-velour-50/40 dark:hover:bg-velour-950/10' : '') }}">
                @if(!$blocked && empty($blocks))
                <a href="{{ $cell['create_url'] }}" class="absolute inset-0.5 rounded opacity-0 hover:opacity-100 hover:bg-velour-500/5 transition-opacity" aria-label="Add appointment"></a>
                @endif
                @foreach($blocks as $block)
                <a href="{{ $block['url'] }}"
                   class="staff-cal__block ring-1 ring-black/5 dark:ring-white/10"
                   style="background-color: {{ $block['block_color'] }}33;"
                   title="{{ trim($block['title']) ?: 'Appointment' }} — {{ $block['services_label'] }}">
                    @if(!empty($block['compact']))
                    <span class="staff-cal__block-time">{{ $block['start_short'] }}</span>
                    <span class="staff-cal__block-title">{{ $block['title_short'] }}</span>
                    @else
                    <span class="staff-cal__block-time truncate">{{ $block['time_label'] }}</span>
                    <span class="staff-cal__block-dur">{{ $block['duration_label'] }}</span>
                    @endif
                </a>
                @endforeach
                @if($blocked && empty($blocks))
                    <span class="text-[10px] text-muted px-0.5">—</span>
                @endif
            </div>
            @endforeach
            @endforeach
        </div>
    </div>
@endif
