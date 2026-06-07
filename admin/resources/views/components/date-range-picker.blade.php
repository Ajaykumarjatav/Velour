@props([
    'fromName' => 'from',
    'toName' => 'to',
    'fromValue' => '',
    'toValue' => '',
    'salonToday' => null,
    'fromLabel' => 'From',
    'toLabel' => 'To',
    /** Embed picker panel without trigger (e.g. calendar week popup). */
    'inline' => false,
])

@php
    $salonToday = $salonToday ?: now()->toDateString();
    $initialFrom = $fromValue ?: $salonToday;
    $initialTo = $toValue ?: $salonToday;
@endphp

<div
    x-data="dateRangePicker({
        from: @js($initialFrom),
        to: @js($initialTo),
        today: @js($salonToday),
        fromName: @js($fromName),
        toName: @js($toName),
        inline: @js($inline),
    })"
    x-init="init()"
    @unless($inline)
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    @endunless
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    @unless($inline)
    <button type="button"
            @click="toggle()"
            class="w-full text-left group"
            :aria-expanded="open">
        <span class="block text-xs text-gray-500 dark:text-gray-400 mb-1 truncate" x-text="presetLabel()"></span>
        <span class="flex w-full items-center justify-between gap-2 min-h-[2.5rem] rounded-lg border px-3 py-2 text-sm transition-all duration-150 bg-white dark:bg-gray-900"
              :class="open
                  ? 'border-velour-500 ring-1 ring-velour-500/30 shadow-sm'
                  : 'border-gray-200 dark:border-gray-700 group-hover:border-gray-300 dark:group-hover:border-gray-600'">
            <span class="text-heading truncate min-w-0" x-text="triggerSummary()"></span>
            <span class="shrink-0 w-0 h-0 border-l-[5px] border-r-[5px] border-t-[6px] border-l-transparent border-r-transparent transition-colors"
                  :class="open ? 'border-t-velour-600' : 'border-t-gray-400 dark:border-t-gray-500'"
                  aria-hidden="true"></span>
        </span>
    </button>
    @endunless

    <input type="hidden" :name="fromName" :value="from">
    <input type="hidden" :name="toName" :value="to">

    @if($inline)
    <div class="relative w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden">
    @else
    <div x-show="open"
         x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute left-0 top-full z-[70] mt-1.5 w-[min(100vw-1.5rem,40rem)] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl overflow-hidden">
    @endif
        <div class="flex flex-col sm:flex-row h-[min(70vh,22rem)]">
            <div class="sm:w-[11.5rem] shrink-0 border-b sm:border-b-0 sm:border-r border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 py-1 overflow-y-auto">
                <template x-for="item in presets" :key="item.key">
                    <button type="button"
                            @click="applyPreset(item.key)"
                            class="w-full text-left px-4 py-2 text-[13px] transition-colors"
                            :class="preset === item.key
                                ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/80'"
                            x-text="item.label"></button>
                </template>
            </div>

            <div class="flex-1 min-w-0 flex flex-col min-h-0">
                <div class="flex items-end gap-2 px-4 pt-4 pb-3 border-b border-gray-100 dark:border-gray-800 shrink-0">
                    <div class="flex-1 min-w-0 relative">
                        <label class="absolute -top-2 left-2.5 px-1 text-[10px] font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900">Start date</label>
                        <input type="text"
                               x-model="startInput"
                               @change="commitStartInput()"
                               @keydown.enter.prevent="commitStartInput()"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-heading focus:outline-none focus:border-velour-500 focus:ring-1 focus:ring-velour-500">
                    </div>
                    <span class="text-gray-400 dark:text-gray-500 pb-2.5 shrink-0 text-sm">–</span>
                    <div class="flex-1 min-w-0 relative">
                        <label class="absolute -top-2 left-2.5 px-1 text-[10px] font-medium text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-900">End date</label>
                        <input type="text"
                               x-model="endInput"
                               @change="commitEndInput()"
                               @keydown.enter.prevent="commitEndInput()"
                               class="w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 px-3 py-2 text-sm text-heading focus:outline-none focus:border-velour-500 focus:ring-1 focus:ring-velour-500">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-1 px-4 py-2 border-b border-gray-50 dark:border-gray-800/80 shrink-0">
                    <button type="button" @click="shiftMonths(-1)" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" aria-label="Previous month">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" @click="shiftMonths(1)" class="p-1 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" aria-label="Next month">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <div class="flex-1 min-h-0 overflow-y-auto px-4 py-2 space-y-4">
                    <template x-for="month in calendarMonths" :key="month.key">
                        <div>
                            <p class="text-[11px] font-semibold tracking-wide text-gray-500 dark:text-gray-400 mb-2 uppercase" x-text="month.label"></p>
                            <div class="grid grid-cols-7 gap-0 text-center text-[11px] font-normal text-gray-500 dark:text-gray-400 mb-1">
                                <template x-for="(wd, wi) in weekdays" :key="'wd-' + wi">
                                    <span class="h-7 flex items-center justify-center" x-text="wd"></span>
                                </template>
                            </div>
                            <div class="grid grid-cols-7 gap-0">
                                <template x-for="(cell, idx) in month.days" :key="month.key + '-' + idx">
                                    <div class="h-9 w-full flex items-center justify-center">
                                        <button type="button"
                                                x-show="cell"
                                                @click="selectDay(cell.ymd)"
                                                class="h-9 w-9 rounded-full text-sm font-normal transition-colors"
                                                :class="cell && isEdge(cell.ymd) ? 'bg-blue-600 text-white hover:bg-blue-700' : (cell && inRange(cell.ymd) ? 'bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-200' : (cell && cell.ymd > today ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'))"
                                                x-text="cell ? cell.day : ''"></button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dateRangePicker', (config) => ({
        open: !!config.inline,
        inline: !!config.inline,
        from: config.from,
        to: config.to,
        today: config.today,
        fromName: config.fromName,
        toName: config.toName,
        preset: 'custom',
        viewAnchor: config.from,
        awaitingEnd: false,
        startInput: '',
        endInput: '',
        calendarMonths: [],
        weekdays: ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
        presets: [
            { key: 'custom', label: 'Custom' },
            { key: 'today', label: 'Today' },
            { key: 'yesterday', label: 'Yesterday' },
            { key: 'this_week', label: 'This week (Sun – Today)' },
            { key: 'last_7', label: 'Last 7 days' },
            { key: 'last_week', label: 'Last week (Sun – Sat)' },
            { key: 'last_14', label: 'Last 14 days' },
            { key: 'this_month', label: 'This month' },
            { key: 'last_30', label: 'Last 30 days' },
            { key: 'last_month', label: 'Last month' },
            { key: 'all_time', label: 'All time' },
        ],

        init() {
            this.normalizeRange();
            this.syncInputs();
            this.detectPreset();
            this.refreshCalendar();
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.viewAnchor = this.to || this.from || this.today;
                this.awaitingEnd = false;
                this.syncInputs();
                this.refreshCalendar();
            }
        },

        refreshCalendar() {
            this.calendarMonths = this.monthsToRender();
        },

        parseYmd(ymd) {
            const [y, m, d] = String(ymd).split('-').map(Number);
            return new Date(y, m - 1, d);
        },

        ymdFromDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },

        addDays(ymd, days) {
            const date = this.parseYmd(ymd);
            date.setDate(date.getDate() + days);
            return this.ymdFromDate(date);
        },

        startOfWeekSunday(ymd) {
            const date = this.parseYmd(ymd);
            date.setDate(date.getDate() - date.getDay());
            return this.ymdFromDate(date);
        },

        monthStart(ymd) {
            const date = this.parseYmd(ymd);
            return this.ymdFromDate(new Date(date.getFullYear(), date.getMonth(), 1));
        },

        monthEnd(ymd) {
            const date = this.parseYmd(ymd);
            return this.ymdFromDate(new Date(date.getFullYear(), date.getMonth() + 1, 0));
        },

        prevMonthStart(ymd) {
            const date = this.parseYmd(ymd);
            return this.ymdFromDate(new Date(date.getFullYear(), date.getMonth() - 1, 1));
        },

        normalizeRange() {
            if (!this.from) this.from = this.today;
            if (!this.to) this.to = this.from;
            if (this.to < this.from) {
                const swap = this.from;
                this.from = this.to;
                this.to = swap;
            }
        },

        formatDisplay(ymd) {
            if (!ymd) return '—';
            const [y, m, d] = ymd.split('-');
            return `${d}-${m}-${y}`;
        },

        formatLongDate(ymd) {
            if (!ymd) return '';
            return this.parseYmd(ymd).toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric',
            });
        },

        formatShortDate(ymd) {
            if (!ymd) return '';
            const date = this.parseYmd(ymd);
            return `${date.getMonth() + 1}/${date.getDate()}/${date.getFullYear()}`;
        },

        presetLabel() {
            const item = this.presets.find((p) => p.key === this.preset);
            return item ? item.label : 'Custom';
        },

        triggerSummary() {
            if (!this.from && !this.to) return 'Select dates';
            if (this.from === this.to) return this.formatLongDate(this.from);
            return `${this.formatLongDate(this.from)} – ${this.formatLongDate(this.to)}`;
        },

        syncInputs() {
            this.startInput = this.formatShortDate(this.from);
            this.endInput = this.formatLongDate(this.to);
        },

        parseDisplayInput(value) {
            const trimmed = String(value || '').trim();
            if (!trimmed) return null;

            const natural = Date.parse(trimmed);
            if (!Number.isNaN(natural)) {
                return this.ymdFromDate(new Date(natural));
            }

            let match = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})$/);
            if (match) return `${match[1]}-${match[2]}-${match[3]}`;

            match = trimmed.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
            if (match) {
                const d = String(match[1]).padStart(2, '0');
                const m = String(match[2]).padStart(2, '0');
                return `${match[3]}-${m}-${d}`;
            }

            match = trimmed.match(/^(\d{1,2})[\/\.](\d{1,2})[\/\.](\d{4})$/);
            if (match) {
                const d = String(match[1]).padStart(2, '0');
                const m = String(match[2]).padStart(2, '0');
                return `${match[3]}-${m}-${d}`;
            }

            match = trimmed.match(/^(\d{1,2})[\/\-.](\d{1,2})[\/\-.](\d{2})$/);
            if (match) {
                const d = String(match[1]).padStart(2, '0');
                const m = String(match[2]).padStart(2, '0');
                const y = Number(match[3]) < 70 ? `20${match[3]}` : `19${match[3]}`;
                return `${y}-${m}-${d}`;
            }

            return null;
        },

        commitStartInput() {
            const parsed = this.parseDisplayInput(this.startInput);
            if (parsed) {
                this.from = parsed;
                this.preset = 'custom';
                this.normalizeRange();
                this.viewAnchor = this.from;
                this.syncInputs();
                this.detectPreset();
                this.refreshCalendar();
            } else {
                this.syncInputs();
            }
        },

        commitEndInput() {
            const parsed = this.parseDisplayInput(this.endInput);
            if (parsed) {
                this.to = parsed;
                this.preset = 'custom';
                this.normalizeRange();
                this.syncInputs();
                this.detectPreset();
                this.refreshCalendar();
            } else {
                this.syncInputs();
            }
        },

        applyPreset(key) {
            const t = this.today;
            this.preset = key;

            switch (key) {
                case 'today':
                    this.from = this.to = t;
                    break;
                case 'yesterday':
                    this.from = this.to = this.addDays(t, -1);
                    break;
                case 'this_week':
                    this.from = this.startOfWeekSunday(t);
                    this.to = t;
                    break;
                case 'last_7':
                    this.from = this.addDays(t, -6);
                    this.to = t;
                    break;
                case 'last_week': {
                    const lastSat = this.addDays(this.startOfWeekSunday(t), -1);
                    this.from = this.startOfWeekSunday(lastSat);
                    this.to = lastSat;
                    break;
                }
                case 'last_14':
                    this.from = this.addDays(t, -13);
                    this.to = t;
                    break;
                case 'this_month':
                    this.from = this.monthStart(t);
                    this.to = t;
                    break;
                case 'last_30':
                    this.from = this.addDays(t, -29);
                    this.to = t;
                    break;
                case 'last_month': {
                    const prev = this.prevMonthStart(t);
                    this.from = prev;
                    this.to = this.monthEnd(prev);
                    break;
                }
                case 'all_time':
                    this.from = '2020-01-01';
                    this.to = t;
                    break;
                default:
                    return;
            }

            this.viewAnchor = this.to || this.from;
            this.awaitingEnd = false;
            this.syncInputs();
            this.refreshCalendar();
        },

        detectPreset() {
            const checks = [
                ['today', () => this.from === this.today && this.to === this.today],
                ['yesterday', () => this.from === this.addDays(this.today, -1) && this.to === this.from],
                ['this_week', () => this.from === this.startOfWeekSunday(this.today) && this.to === this.today],
                ['last_7', () => this.from === this.addDays(this.today, -6) && this.to === this.today],
                ['last_week', () => {
                    const lastSat = this.addDays(this.startOfWeekSunday(this.today), -1);
                    return this.from === this.startOfWeekSunday(lastSat) && this.to === lastSat;
                }],
                ['last_14', () => this.from === this.addDays(this.today, -13) && this.to === this.today],
                ['this_month', () => this.from === this.monthStart(this.today) && this.to === this.today],
                ['last_30', () => this.from === this.addDays(this.today, -29) && this.to === this.today],
                ['last_month', () => {
                    const prev = this.prevMonthStart(this.today);
                    return this.from === prev && this.to === this.monthEnd(prev);
                }],
                ['all_time', () => this.from === '2020-01-01' && this.to === this.today],
            ];

            for (const [key, fn] of checks) {
                if (fn()) {
                    this.preset = key;
                    return;
                }
            }
            this.preset = 'custom';
        },

        selectDay(ymd) {
            if (!this.awaitingEnd) {
                this.from = ymd;
                this.to = ymd;
                this.awaitingEnd = true;
            } else {
                if (ymd < this.from) {
                    this.to = this.from;
                    this.from = ymd;
                } else {
                    this.to = ymd;
                }
                this.awaitingEnd = false;
            }

            this.preset = 'custom';
            this.normalizeRange();
            this.syncInputs();
            this.detectPreset();
        },

        inRange(ymd) {
            return this.from && this.to && ymd >= this.from && ymd <= this.to;
        },

        isEdge(ymd) {
            return ymd === this.from || ymd === this.to;
        },

        shiftMonths(delta) {
            const date = this.parseYmd(this.viewAnchor);
            date.setMonth(date.getMonth() + delta);
            this.viewAnchor = this.ymdFromDate(date);
            this.refreshCalendar();
        },

        monthsToRender() {
            const anchor = this.parseYmd(this.viewAnchor);
            const months = [];
            for (let i = 0; i < 3; i++) {
                const date = new Date(anchor.getFullYear(), anchor.getMonth() + i, 1);
                months.push(this.buildMonth(date));
            }
            return months;
        },

        buildMonth(date) {
            const year = date.getFullYear();
            const month = date.getMonth();
            const first = new Date(year, month, 1);
            const last = new Date(year, month + 1, 0);
            const offset = first.getDay();
            const days = [];

            for (let i = 0; i < offset; i++) {
                days.push(null);
            }
            for (let day = 1; day <= last.getDate(); day++) {
                const ymd = this.ymdFromDate(new Date(year, month, day));
                days.push({ ymd, day });
            }

            return {
                key: `${year}-${month}`,
                label: date.toLocaleDateString('en-US', { month: 'long', year: 'numeric' }).toUpperCase(),
                days,
            };
        },
    }));
});
</script>
@endpush
@endonce
