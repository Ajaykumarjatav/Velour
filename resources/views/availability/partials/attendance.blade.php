@php
    $state = $attendanceState ?? null;
    $period = $attendancePeriod ?? 'week';
    $staffFilterId = $attendanceStaffId ?? null;
    $staffwise = $staffwise ?? false;
    $lockedStaff = $lockedStaff ?? null;
    $anchor = isset($attendanceAnchor) ? $attendanceAnchor->copy() : now();

    $attendanceUrl = function (array $overrides = []) use ($period, $staffFilterId, $anchor, $staffwise) {
        $base = [
            'tab' => 'attendance',
            'period' => $period,
        ];
        if ($staffFilterId) {
            $base['staff_id'] = $staffFilterId;
        }
        if ($staffwise && $staffFilterId) {
            $base['staffwise'] = 1;
        }
        if ($period === 'month') {
            $base['month'] = $anchor->format('Y-m');
        } elseif ($period === 'year') {
            $base['year'] = $anchor->format('Y');
        } else {
            $base['week'] = $anchor->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString();
        }

        return route('availability.index', array_merge($base, $overrides));
    };

    if ($period === 'week') {
        $prevAnchor = $anchor->copy()->subWeek();
        $nextAnchor = $anchor->copy()->addWeek();
    } elseif ($period === 'month') {
        $prevAnchor = $anchor->copy()->subMonth();
        $nextAnchor = $anchor->copy()->addMonth();
    } else {
        $prevAnchor = $anchor->copy()->subYear();
        $nextAnchor = $anchor->copy()->addYear();
    }

    $prevUrl = $attendanceUrl(match ($period) {
        'month' => ['month' => $prevAnchor->format('Y-m')],
        'year' => ['year' => $prevAnchor->format('Y')],
        default => ['week' => $prevAnchor->toDateString()],
    });

    $nextUrl = $attendanceUrl(match ($period) {
        'month' => ['month' => $nextAnchor->format('Y-m')],
        'year' => ['year' => $nextAnchor->format('Y')],
        default => ['week' => $nextAnchor->toDateString()],
    });

    $exportUrl = route('availability.attendance.export', array_filter([
        'period' => $period,
        'staff_id' => $staffFilterId,
        'week' => $period === 'week' ? $anchor->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString() : null,
        'month' => $period === 'month' ? $anchor->format('Y-m') : null,
        'year' => $period === 'year' ? $anchor->format('Y') : null,
    ], fn ($v) => $v !== null && $v !== ''));

    if ($state) {
        $sampleStaffId = $state['rows'][0]['staff_id'] ?? 1;
        $state['urls'] = [
            'store'    => route('availability.attendance.store'),
            'clockIn'  => preg_replace('/\/\d+\//', '/__STAFF__/', route('availability.attendance.clock-in', ['staff' => $sampleStaffId])),
            'clockOut' => preg_replace('/\/\d+\//', '/__STAFF__/', route('availability.attendance.clock-out', ['staff' => $sampleStaffId])),
        ];
        $state['period'] = $period;
        $state['readonly_browse'] = \App\Support\AuthPanel::isAdminStoreBrowse();
    }
@endphp

@if(!$state)
    <p class="text-sm text-muted p-6">Attendance data is unavailable.</p>
@else
<div class="space-y-5"
     x-data="attendanceHub(@js($state))"
     @attendance-updated.window="toast = $event.detail.message">
    <div class="card p-4 sm:p-6">
        <div class="flex flex-col gap-4 mb-4">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-3">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-heading">Staff attendance</h2>
                    <p class="text-xs text-muted mt-1">Updates save instantly — no page reload. Export for payroll or records.</p>
                </div>
                <a href="{{ $exportUrl }}"
                   class="btn-outline btn-sm whitespace-nowrap inline-flex items-center gap-1.5 self-start">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/>
                    </svg>
                    Export CSV
                </a>
            </div>

            <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-3">
                <div class="inline-flex p-1 rounded-xl bg-gray-100 dark:bg-gray-800/90 border border-gray-200/90 dark:border-gray-700 gap-0.5">
                    @foreach(['week' => 'Week', 'month' => 'Month', 'year' => 'Year'] as $p => $label)
                    <a href="{{ $attendanceUrl(['period' => $p]) }}"
                       class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-lg transition-colors
                              {{ $period === $p ? 'bg-velour-600 text-white shadow-sm' : 'text-body hover:bg-white/80 dark:hover:bg-gray-700/60' }}">
                        {{ $label }}
                    </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('availability.index') }}" class="flex items-center gap-2 min-w-0">
                    <input type="hidden" name="tab" value="attendance">
                    <input type="hidden" name="period" value="{{ $period }}">
                    @if($staffwise && $staffFilterId)
                        <input type="hidden" name="staff_id" value="{{ $staffFilterId }}">
                        <input type="hidden" name="staffwise" value="1">
                    @endif
                    @if($period === 'month')
                        <input type="hidden" name="month" value="{{ $anchor->format('Y-m') }}">
                    @elseif($period === 'year')
                        <input type="hidden" name="year" value="{{ $anchor->format('Y') }}">
                    @else
                        <input type="hidden" name="week" value="{{ $anchor->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString() }}">
                    @endif
                    @if($staffwise && $lockedStaff)
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-sm font-semibold bg-velour-100 text-velour-800 dark:bg-velour-900/40 dark:text-velour-200 border border-velour-200/60 dark:border-velour-800/50">
                            <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0"
                                  style="background-color: {{ $lockedStaff->color ?: '#7C3AED' }}">{{ $lockedStaff->display_initials }}</span>
                            {{ $lockedStaff->name }}
                        </span>
                        <a href="{{ route('availability.index', ['tab' => 'attendance', 'week' => $anchor->copy()->startOfWeek(\Carbon\Carbon::MONDAY)->toDateString(), 'period' => $period]) }}"
                           class="text-xs text-velour-600 hover:underline whitespace-nowrap">View all staff</a>
                    @else
                    <label for="attendance-staff-filter" class="sr-only">Staff</label>
                    <select id="attendance-staff-filter" name="staff_id" class="form-select text-sm min-w-[10rem] max-w-full" onchange="this.form.submit()">
                        <option value="">All staff</option>
                        @foreach($staff as $st)
                            <option value="{{ $st->id }}" {{ (string) $staffFilterId === (string) $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                        @endforeach
                    </select>
                    @endif
                </form>

                <div class="inline-flex items-stretch rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700 text-sm sm:ml-auto">
                    <a href="{{ $prevUrl }}" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800" title="Previous">‹</a>
                    <span class="px-3 py-2 font-semibold text-heading tabular-nums whitespace-nowrap" x-text="periodLabel"></span>
                    <a href="{{ $nextUrl }}" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800" title="Next">›</a>
                </div>
            </div>

            @if($period === 'year')
            <p class="text-xs text-muted">Year view shows monthly totals (P = present, A = absent, L = late, ½ = half day, Lv = leave). Use <strong>Export CSV</strong> for day-by-day detail.</p>
            @elseif($period === 'month' && !$staffFilterId)
            <p class="text-xs text-muted">Tip: select one staff member for an easier month view, or export for a full report.</p>
            @endif
        </div>

        <div x-show="toast"
             x-transition
             x-cloak
             class="mb-4 px-4 py-2.5 rounded-xl text-sm font-medium bg-emerald-50 text-emerald-900 border border-emerald-200/80 dark:bg-emerald-950/40 dark:text-emerald-100 dark:border-emerald-800/50"
             x-text="toast"></div>
        <div x-show="errorMsg"
             x-transition
             x-cloak
             class="mb-4 px-4 py-2.5 rounded-xl text-sm font-medium bg-rose-50 text-rose-900 border border-rose-200/80 dark:bg-rose-950/40 dark:text-rose-100 dark:border-rose-800/50"
             x-text="errorMsg"></div>

        <template x-if="showQuickClock">
            <div class="mb-5 p-4 rounded-xl bg-velour-50/80 dark:bg-velour-950/25 border border-velour-200/80 dark:border-velour-800/40">
                <p class="text-xs font-semibold text-heading mb-3">Today — quick clock</p>
                <div class="flex flex-wrap gap-2">
                    <template x-for="row in quickClockRows()" :key="'qc-' + row.staff_id">
                        <div class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-2 py-1.5 text-xs">
                            <span class="font-medium text-body max-w-[8rem] truncate" x-text="row.staff_name"></span>
                            <span class="text-muted tabular-nums" x-show="cell(row.staff_id, today).clock_in"
                                  x-text="clockLabel(row.staff_id, today)"></span>
                            <button type="button"
                                    @click="clockIn(row.staff_id)"
                                    :disabled="isLoading(row.staff_id, today, 'in')"
                                    class="px-2 py-0.5 rounded-md bg-emerald-600 text-white font-semibold hover:bg-emerald-700 disabled:opacity-50">
                                In
                            </button>
                            <button type="button"
                                    @click="clockOut(row.staff_id)"
                                    :disabled="isLoading(row.staff_id, today, 'out')"
                                    class="px-2 py-0.5 rounded-md bg-gray-700 text-white font-semibold hover:bg-gray-800 dark:bg-gray-600 disabled:opacity-50">
                                Out
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        <div class="overflow-x-auto -mx-2 px-2">
            <table class="w-full text-sm" :class="period === 'month' ? 'min-w-[900px]' : (period === 'year' ? 'min-w-[640px]' : 'min-w-[720px]')">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left py-2 pr-3 font-semibold text-heading w-36 sticky left-0 bg-white dark:bg-gray-900 z-10">Staff</th>
                        <template x-for="day in days" :key="dayKey(day)">
                            <th class="text-center py-2 font-semibold text-muted"
                                :class="[
                                    day.is_today ? 'text-velour-600 dark:text-velour-400' : '',
                                    day.compact ? 'px-0.5 text-[10px] w-7' : 'px-1 text-xs',
                                ]"
                                x-text="day.label"></th>
                        </template>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <tr x-show="rows.length === 0">
                        <td :colspan="days.length + 1" class="py-10 text-center text-muted">
                            No staff members. <a href="{{ route('staff.create') }}" class="text-link">Add staff</a> first.
                        </td>
                    </tr>
                    <template x-for="row in rows" :key="row.staff_id">
                        <tr>
                            <td class="py-2 pr-3 sticky left-0 bg-white dark:bg-gray-900 z-10">
                                <div class="flex items-center gap-2 min-w-0">
                                    <template x-if="row.avatar_url">
                                        <img :src="row.avatar_url" alt="" class="w-8 h-8 rounded-full object-cover ring-2 ring-white dark:ring-gray-900 shrink-0">
                                    </template>
                                    <template x-if="!row.avatar_url">
                                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-[10px] font-bold text-white shrink-0 ring-2 ring-white dark:ring-gray-900"
                                              :style="'background-color:' + row.color"
                                              x-text="row.initials"></span>
                                    </template>
                                    <span class="font-medium text-body truncate text-xs sm:text-sm" x-text="row.staff_name"></span>
                                </div>
                            </td>
                            <template x-for="day in days" :key="row.staff_id + '-' + dayKey(day)">
                                <td class="py-1 text-center align-top"
                                    :class="[
                                        day.is_today ? 'bg-velour-50/40 dark:bg-velour-950/15' : '',
                                        day.compact ? 'px-0.5' : 'px-1',
                                    ]">
                                    <template x-if="cell(row.staff_id, dayKey(day)).readonly || period === 'year'">
                                        <div>
                                            <span class="inline-block font-semibold px-1 py-0.5 rounded-md leading-tight"
                                                  :class="[
                                                      statusClass(cell(row.staff_id, dayKey(day)).status),
                                                      day.compact ? 'text-[9px]' : 'text-[10px]',
                                                      cell(row.staff_id, dayKey(day)).status === 'summary' ? 'text-[9px] max-w-[4.5rem] mx-auto' : '',
                                                  ]"
                                                  x-text="cell(row.staff_id, dayKey(day)).label"></span>
                                            <p class="text-[9px] text-muted mt-0.5 tabular-nums"
                                               x-show="cell(row.staff_id, dayKey(day)).clock_in && cell(row.staff_id, dayKey(day)).status !== 'day_off' && period !== 'year'"
                                               x-text="clockLabel(row.staff_id, dayKey(day))"></p>
                                        </div>
                                    </template>
                                    <template x-if="canEdit(row.staff_id, dayKey(day)) && cell(row.staff_id, dayKey(day)).status">
                                        <div>
                                            <span class="inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded-md"
                                                  :class="statusClass(cell(row.staff_id, dayKey(day)).status)"
                                                  x-text="cell(row.staff_id, dayKey(day)).label"></span>
                                            <p class="text-[9px] text-muted mt-0.5 tabular-nums"
                                               x-show="cell(row.staff_id, dayKey(day)).clock_in"
                                               x-text="clockLabel(row.staff_id, dayKey(day))"></p>
                                            <div class="flex flex-wrap justify-center gap-0.5 mt-0.5" x-show="!day.compact">
                                                <template x-for="btn in actionButtons" :key="btn.status">
                                                    <button type="button"
                                                            :title="btn.title"
                                                            @click="setStatus(row.staff_id, dayKey(day), btn.status)"
                                                            :disabled="isLoading(row.staff_id, dayKey(day), btn.status)"
                                                            class="w-5 h-5 rounded text-[9px] font-bold border border-gray-200/80 dark:border-gray-600 hover:bg-white/80 dark:hover:bg-gray-800 disabled:opacity-40"
                                                            x-text="btn.short"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="canEdit(row.staff_id, dayKey(day)) && !cell(row.staff_id, dayKey(day)).status">
                                        <div class="flex flex-col gap-0.5 items-center">
                                            <span class="text-[10px] text-muted">—</span>
                                            <div class="flex flex-wrap justify-center gap-0.5" x-show="!day.compact">
                                                <template x-for="btn in quickButtons" :key="btn.status">
                                                    <button type="button"
                                                            :title="btn.title"
                                                            @click="setStatus(row.staff_id, dayKey(day), btn.status)"
                                                            :disabled="isLoading(row.staff_id, dayKey(day), btn.status)"
                                                            class="w-5 h-5 rounded text-[9px] font-bold border border-gray-200 dark:border-gray-600 hover:bg-velour-50 dark:hover:bg-velour-950/40 disabled:opacity-40"
                                                            x-text="btn.short"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="flex flex-wrap gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800 text-[11px] text-muted">
            <span><span class="inline-block w-2 h-2 rounded-full bg-emerald-500 mr-1"></span> Present</span>
            <span><span class="inline-block w-2 h-2 rounded-full bg-rose-500 mr-1"></span> Absent</span>
            <span><span class="inline-block w-2 h-2 rounded-full bg-amber-500 mr-1"></span> Late</span>
            <span><span class="inline-block w-2 h-2 rounded-full bg-violet-500 mr-1"></span> On leave</span>
            <span><span class="inline-block w-2 h-2 rounded-full bg-gray-400 mr-1"></span> Day off</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('attendanceHub', (initial) => ({
        period: initial.period || 'week',
        days: initial.days || [],
        rows: initial.rows || [],
        today: initial.today,
        urls: initial.urls || {},
        periodLabel: '',
        toast: null,
        errorMsg: null,
        loadingKey: null,
        toastTimer: null,
        actionButtons: [
            { status: 'present', short: 'P', title: 'Present' },
            { status: 'absent', short: 'A', title: 'Absent' },
            { status: 'late', short: 'L', title: 'Late' },
            { status: 'half_day', short: '½', title: 'Half day' },
        ],
        quickButtons: [
            { status: 'present', short: 'P', title: 'Present' },
            { status: 'absent', short: 'A', title: 'Absent' },
            { status: 'late', short: 'L', title: 'Late' },
        ],
        statusClasses: {
            present: 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/35 dark:text-emerald-100',
            absent: 'bg-rose-100 text-rose-900 dark:bg-rose-900/35 dark:text-rose-100',
            late: 'bg-amber-100 text-amber-900 dark:bg-amber-900/35 dark:text-amber-100',
            half_day: 'bg-sky-100 text-sky-900 dark:bg-sky-900/35 dark:text-sky-100',
            on_leave: 'bg-violet-100 text-violet-900 dark:bg-violet-900/35 dark:text-violet-100',
            day_off: 'bg-gray-100 text-muted dark:bg-gray-800/50',
            summary: 'bg-gray-100 text-body dark:bg-gray-800/60 dark:text-gray-200',
        },
        init() {
            this.updatePeriodLabel();
        },
        updatePeriodLabel() {
            const start = new Date((initial.range_start || initial.week_start) + 'T12:00:00');
            const end = new Date((initial.range_end || initial.week_end) + 'T12:00:00');
            const fmt = (d, opts) => d.toLocaleDateString(undefined, opts);
            if (this.period === 'year') {
                this.periodLabel = String(start.getFullYear());
                return;
            }
            if (this.period === 'month') {
                this.periodLabel = fmt(start, { month: 'long', year: 'numeric' });
                return;
            }
            this.periodLabel = fmt(start, { day: 'numeric', month: 'short' })
                + ' – '
                + fmt(end, { day: 'numeric', month: 'short' })
                + ' '
                + end.getFullYear();
        },
        dayKey(day) {
            return day.month_key || day.ymd;
        },
        get csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },
        get showQuickClock() {
            if (initial.readonly_browse) return false;
            if (this.period !== 'week' || !this.days.length) return false;
            const first = this.days[0].ymd;
            const last = this.days[this.days.length - 1].ymd;
            return this.today >= first && this.today <= last;
        },
        cell(staffId, key) {
            const row = this.rows.find((r) => r.staff_id === staffId);
            return row?.cells?.[key] || {};
        },
        canEdit(staffId, key) {
            if (initial.readonly_browse) return false;
            if (this.period === 'year') return false;
            const c = this.cell(staffId, key);
            return !c.readonly;
        },
        statusClass(status) {
            return this.statusClasses[status] || 'bg-gray-50 dark:bg-gray-800/40 text-muted';
        },
        clockLabel(staffId, key) {
            const c = this.cell(staffId, key);
            if (!c.clock_in) return '';
            return c.clock_out ? `${c.clock_in}–${c.clock_out}` : c.clock_in;
        },
        quickClockRows() {
            return this.rows.filter((row) => {
                const c = this.cell(row.staff_id, this.today);
                return c.scheduled && !c.on_leave;
            });
        },
        isLoading(staffId, key, suffix) {
            return this.loadingKey === `${staffId}|${key}|${suffix}`;
        },
        setLoading(staffId, key, suffix) {
            this.loadingKey = `${staffId}|${key}|${suffix}`;
        },
        clearLoading() {
            this.loadingKey = null;
        },
        showToast(message, isError = false) {
            if (this.toastTimer) clearTimeout(this.toastTimer);
            if (isError) {
                this.errorMsg = message;
                this.toast = null;
                this.toastTimer = setTimeout(() => { this.errorMsg = null; }, 4000);
            } else {
                this.toast = message;
                this.errorMsg = null;
                this.toastTimer = setTimeout(() => { this.toast = null; }, 3000);
            }
        },
        applyCell(staffId, ymd, cell) {
            const idx = this.rows.findIndex((r) => r.staff_id === staffId);
            if (idx === -1) return;
            const row = this.rows[idx];
            this.rows[idx] = {
                ...row,
                cells: { ...(row.cells || {}), [ymd]: cell },
            };
        },
        staffUrl(template, staffId) {
            return template.replace('__STAFF__', String(staffId));
        },
        async post(url, body) {
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body,
                credentials: 'same-origin',
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.ok) {
                throw new Error(data.message || 'Something went wrong.');
            }
            return data;
        },
        async setStatus(staffId, ymd, status) {
            this.setLoading(staffId, ymd, status);
            try {
                const fd = new FormData();
                fd.append('_token', this.csrf);
                fd.append('staff_id', staffId);
                fd.append('date', ymd);
                fd.append('status', status);
                const data = await this.post(this.urls.store, fd);
                this.applyCell(staffId, ymd, data.cell);
                this.showToast(data.message);
            } catch (e) {
                this.showToast(e.message, true);
            } finally {
                this.clearLoading();
            }
        },
        async clockIn(staffId) {
            this.setLoading(staffId, this.today, 'in');
            try {
                const fd = new FormData();
                fd.append('_token', this.csrf);
                const data = await this.post(this.staffUrl(this.urls.clockIn, staffId), fd);
                this.applyCell(staffId, this.today, data.cell);
                this.showToast(data.message);
            } catch (e) {
                this.showToast(e.message, true);
            } finally {
                this.clearLoading();
            }
        },
        async clockOut(staffId) {
            this.setLoading(staffId, this.today, 'out');
            try {
                const fd = new FormData();
                fd.append('_token', this.csrf);
                const data = await this.post(this.staffUrl(this.urls.clockOut, staffId), fd);
                this.applyCell(staffId, this.today, data.cell);
                this.showToast(data.message);
            } catch (e) {
                this.showToast(e.message, true);
            } finally {
                this.clearLoading();
            }
        },
    }));
});
</script>
@endpush
@endif
