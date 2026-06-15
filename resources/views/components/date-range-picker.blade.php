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
    /** Submit the parent form when a preset or calendar range is chosen. */
    'autoSubmit' => false,
    /** Tighter layout for popovers (e.g. POS filters). */
    'compact' => false,
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
        autoSubmit: @js($autoSubmit),
        compact: @js($compact),
    })"
    x-init="init()"
    @keydown.escape.window="if (!inline) open = false"
    @click.window="closeOnOutside($event)"
    {{ $attributes->merge(['class' => 'relative w-full']) }}
>
    @unless($inline)
    <button type="button"
            x-ref="trigger"
            @click="toggle()"
            class="form-select w-full !flex items-center justify-between gap-2 text-left"
            :class="open ? '!ring-2 !ring-velour-500 !border-transparent' : ''"
            :aria-expanded="open">
        <span class="truncate min-w-0 text-heading" x-text="triggerLabel()"></span>
        <svg class="w-4 h-4 shrink-0 text-gray-400 dark:text-gray-500 transition-transform"
             :class="open ? 'rotate-180' : ''"
             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>
    @endunless

    <input type="hidden" :name="fromName" :value="from">
    <input type="hidden" :name="toName" :value="to">

    @unless($inline)
    <template x-teleport="body">
    @endunless
    <div x-show="inline || open"
         x-cloak
         x-ref="panel"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         @if($inline && $compact)
         class="relative w-full overflow-hidden"
         @elseif($inline)
         class="relative w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 overflow-hidden"
         @else
         :style="dropdownStyle"
         class="fixed z-[200] w-[min(100vw-1rem,32rem)] rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 shadow-xl ring-1 ring-black/5 dark:ring-white/10 overflow-hidden"
         @endif
        >
        <div @class([
            'flex flex-col sm:flex-row',
            'h-[21.5rem]' => $compact,
            'h-[min(70vh,26rem)]' => ! $compact,
        ])>
            <div @class([
                'shrink-0 border-b sm:border-b-0 sm:border-r border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-y-auto',
                'sm:w-[10rem] py-1 max-h-[10rem] sm:max-h-none' => $compact,
                'sm:w-[11.5rem] py-1 max-h-[12rem] sm:max-h-none' => ! $compact,
            ])>
                <template x-for="item in presets" :key="item.key">
                    <button type="button"
                            @click="applyPreset(item.key)"
                            @class([
                                'w-full text-left transition-colors border-l-2',
                                'px-3 py-1.5 text-xs leading-snug' => $compact,
                                'px-4 py-2 text-[13px]' => ! $compact,
                            ])
                            :class="preset === item.key
                                ? 'bg-velour-50 dark:bg-velour-950/50 text-velour-700 dark:text-velour-300 border-l-velour-500'
                                : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/80 border-l-transparent'"
                            x-text="item.label"></button>
                </template>
            </div>

            <div class="flex-1 min-w-0 flex flex-col min-h-0">
                <div @class([
                    'flex items-end border-b border-gray-100 dark:border-gray-800 shrink-0',
                    'gap-2 px-3 pt-3 pb-2.5' => $compact,
                    'gap-2 px-4 pt-4 pb-3' => ! $compact,
                ])>
                    <div class="flex-1 min-w-0 relative">
                        <label @class([
                            'absolute -top-2 left-2 px-0.5 font-medium text-gray-500 dark:text-gray-400',
                            'text-[10px] bg-white dark:bg-gray-950' => $compact,
                            'text-[10px] bg-white dark:bg-gray-900' => ! $compact,
                        ])>Start</label>
                        <input type="text"
                               x-model="startInput"
                               @change="commitStartInput()"
                               @keydown.enter.prevent="commitStartInput()"
                               @class([
                                   'w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-heading focus:outline-none focus:border-velour-500 focus:ring-1 focus:ring-velour-500',
                                   'px-2.5 py-1.5 text-sm' => $compact,
                                   'px-3 py-2 text-sm' => ! $compact,
                               ])>
                    </div>
                    <span class="text-gray-400 dark:text-gray-500 shrink-0 text-sm pb-2">–</span>
                    <div class="flex-1 min-w-0 relative">
                        <label @class([
                            'absolute -top-2 left-2 px-0.5 font-medium text-gray-500 dark:text-gray-400',
                            'text-[10px] bg-white dark:bg-gray-950' => $compact,
                            'text-[10px] bg-white dark:bg-gray-900' => ! $compact,
                        ])>End</label>
                        <input type="text"
                               x-model="endInput"
                               @change="commitEndInput()"
                               @keydown.enter.prevent="commitEndInput()"
                               @class([
                                   'w-full rounded-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-heading focus:outline-none focus:border-velour-500 focus:ring-1 focus:ring-velour-500',
                                   'px-2.5 py-1.5 text-sm' => $compact,
                                   'px-3 py-2 text-sm' => ! $compact,
                               ])>
                    </div>
                </div>

                <div @class([
                    'flex items-center justify-end gap-0.5 border-b border-gray-50 dark:border-gray-800/80 shrink-0',
                    'px-3 py-1.5' => $compact,
                    'px-4 py-2' => ! $compact,
                ])>
                    <button type="button" @click="shiftMonths(-1)" class="p-0.5 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" aria-label="Previous month">
                        <svg @class(['fill-none stroke-current', 'w-3.5 h-3.5' => $compact, 'w-4 h-4' => ! $compact]) viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" @click="shiftMonths(1)" class="p-0.5 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-500" aria-label="Next month">
                        <svg @class(['fill-none stroke-current', 'w-3.5 h-3.5' => $compact, 'w-4 h-4' => ! $compact]) viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <div @class([
                    'flex-1 min-h-0 overflow-y-auto',
                    'px-3 py-2 space-y-3' => $compact,
                    'px-4 py-2 space-y-4' => ! $compact,
                ])>
                    <template x-for="month in calendarMonths" :key="month.key">
                        <div>
                            <p @class([
                                'font-semibold tracking-wide text-gray-500 dark:text-gray-400 uppercase',
                                'text-[11px] mb-1.5' => $compact,
                                'text-[11px] mb-2' => ! $compact,
                            ]) x-text="month.label"></p>
                            <div class="grid grid-cols-7 gap-0 text-center font-normal text-gray-500 dark:text-gray-400 mb-0.5">
                                <template x-for="(wd, wi) in weekdays" :key="'wd-' + wi">
                                    <span @class([
                                        'flex items-center justify-center',
                                        'h-6 text-xs' => $compact,
                                        'h-7 text-[11px]' => ! $compact,
                                    ]) x-text="wd"></span>
                                </template>
                            </div>
                            <div class="grid grid-cols-7 gap-0">
                                <template x-for="(cell, idx) in month.days" :key="month.key + '-' + idx">
                                    <div @class([
                                        'w-full flex items-center justify-center',
                                        'h-8' => $compact,
                                        'h-9' => ! $compact,
                                    ])>
                                        <button type="button"
                                                x-show="cell"
                                                @click="selectDay(cell.ymd)"
                                                @class([
                                                    'rounded-full font-normal transition-colors',
                                                    'h-8 w-8 text-sm' => $compact,
                                                    'h-9 w-9 text-sm' => ! $compact,
                                                ])
                                                :class="cell && isEdge(cell.ymd) ? 'bg-velour-600 text-white hover:bg-velour-700' : (cell && inRange(cell.ymd) ? 'bg-velour-50 dark:bg-velour-950/50 text-velour-800 dark:text-velour-200' : (cell && cell.ymd > today ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800'))"
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
    @unless($inline)
    </template>
    @endunless
</div>

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('dateRangePicker', (config) => ({
        open: !!config.inline,
        inline: !!config.inline,
        autoSubmit: !!config.autoSubmit,
        compact: !!config.compact,
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
        dropdownStyle: '',
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
            this._reposition = () => {
                if (this.open && !this.inline) {
                    this.positionDropdown();
                }
            };
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
                if (!this.inline) {
                    this.positionDropdown();
                    window.addEventListener('scroll', this._reposition, true);
                    window.addEventListener('resize', this._reposition);
                }
            } else if (!this.inline) {
                window.removeEventListener('scroll', this._reposition, true);
                window.removeEventListener('resize', this._reposition);
            }
        },

        positionDropdown() {
            this.$nextTick(() => {
                const el = this.$refs.trigger;
                if (!el) {
                    return;
                }
                const rect = el.getBoundingClientRect();
                const width = Math.min(window.innerWidth - 16, this.compact ? 512 : 640);
                let left = Math.min(rect.left, window.innerWidth - width - 12);
                left = Math.max(12, left);
                const height = this.compact ? 344 : Math.min(window.innerHeight * 0.7, 352);
                let top = rect.bottom + 6;
                if (top + height > window.innerHeight - 12) {
                    top = Math.max(12, rect.top - height - 6);
                }
                this.dropdownStyle = `top:${Math.round(top)}px;left:${Math.round(left)}px;width:${width}px`;
            });
        },

        closeOnOutside(event) {
            if (!this.open || this.inline) {
                return;
            }
            const trigger = this.$refs.trigger;
            const panel = this.$refs.panel;
            if (trigger?.contains(event.target) || panel?.contains(event.target)) {
                return;
            }
            this.open = false;
            window.removeEventListener('scroll', this._reposition, true);
            window.removeEventListener('resize', this._reposition);
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

        triggerLabel() {
            if (this.preset !== 'custom') {
                const item = this.presets.find((p) => p.key === this.preset);
                if (item) {
                    return item.label;
                }
            }
            return this.triggerSummary();
        },

        syncInputs() {
            this.startInput = this.formatShortDate(this.from);
            this.endInput = this.formatShortDate(this.to);
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

        maybeSubmitForm() {
            if (!this.autoSubmit) return;
            const form = this.$el.closest('form');
            if (form) form.requestSubmit();
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
            this.maybeSubmitForm();
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
            if (!this.awaitingEnd) {
                this.maybeSubmitForm();
            }
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
            const count = this.compact ? 2 : 3;
            for (let i = 0; i < count; i++) {
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
