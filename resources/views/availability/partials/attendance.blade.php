@php
    $state = $attendanceState ?? null;
    $weekStart = isset($attendanceWeek) ? $attendanceWeek->copy() : now()->startOfWeek(\Carbon\Carbon::MONDAY);
    $prevWeek = $weekStart->copy()->subWeek()->toDateString();
    $nextWeek = $weekStart->copy()->addWeek()->toDateString();
    if ($state) {
        $sampleStaffId = $state['rows'][0]['staff_id'] ?? 1;
        $state['urls'] = [
            'store'    => route('availability.attendance.store'),
            'clockIn'  => preg_replace('/\/\d+\//', '/__STAFF__/', route('availability.attendance.clock-in', ['staff' => $sampleStaffId])),
            'clockOut' => preg_replace('/\/\d+\//', '/__STAFF__/', route('availability.attendance.clock-out', ['staff' => $sampleStaffId])),
        ];
    }
@endphp

@if(!$state)
    <p class="text-sm text-muted p-6">Attendance data is unavailable.</p>
@else
<div class="space-y-5"
     x-data="attendanceHub(@js($state))"
     @attendance-updated.window="toast = $event.detail.message">
    <div class="card p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
                <h2 class="text-base sm:text-lg font-bold text-heading">Staff attendance</h2>
                <p class="text-xs text-muted mt-1">Updates save instantly — no page reload.</p>
            </div>
            <div class="inline-flex items-stretch rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 divide-x divide-gray-200 dark:divide-gray-700 text-sm">
                <a href="{{ route('availability.index', ['tab' => 'attendance', 'week' => $prevWeek]) }}" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800" title="Previous week">‹</a>
                <span class="px-3 py-2 font-semibold text-heading tabular-nums whitespace-nowrap" x-text="weekLabel"></span>
                <a href="{{ route('availability.index', ['tab' => 'attendance', 'week' => $nextWeek]) }}" class="p-2 hover:bg-gray-50 dark:hover:bg-gray-800" title="Next week">›</a>
            </div>
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
            <table class="w-full text-sm min-w-[720px]">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-800">
                        <th class="text-left py-2 pr-3 font-semibold text-heading w-36 sticky left-0 bg-white dark:bg-gray-900 z-10">Staff</th>
                        <template x-for="day in days" :key="day.ymd">
                            <th class="text-center py-2 px-1 font-semibold text-muted text-xs"
                                :class="day.is_today ? 'text-velour-600 dark:text-velour-400' : ''"
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
                                        <img :src="row.avatar_url" alt="" class="w-9 h-9 rounded-full object-cover ring-2 ring-white dark:ring-gray-900 shrink-0">
                                    </template>
                                    <template x-if="!row.avatar_url">
                                        <span class="w-9 h-9 rounded-full flex items-center justify-center text-xs font-bold text-white shrink-0 ring-2 ring-white dark:ring-gray-900"
                                              :style="'background-color:' + row.color"
                                              x-text="row.initials"></span>
                                    </template>
                                    <span class="font-medium text-body truncate" x-text="row.staff_name"></span>
                                </div>
                            </td>
                            <template x-for="day in days" :key="row.staff_id + '-' + day.ymd">
                                <td class="py-1.5 px-1 text-center align-top"
                                    :class="day.is_today ? 'bg-velour-50/40 dark:bg-velour-950/15' : ''">
                                    <template x-if="cell(row.staff_id, day.ymd).readonly">
                                        <div>
                                            <span class="inline-block text-[10px] font-semibold px-2 py-1 rounded-md"
                                                  :class="statusClass(cell(row.staff_id, day.ymd).status)"
                                                  x-text="cell(row.staff_id, day.ymd).label"></span>
                                            <p class="text-[9px] text-muted mt-0.5 tabular-nums"
                                               x-show="cell(row.staff_id, day.ymd).clock_in && cell(row.staff_id, day.ymd).status !== 'day_off'"
                                               x-text="clockLabel(row.staff_id, day.ymd)"></p>
                                        </div>
                                    </template>
                                    <template x-if="!cell(row.staff_id, day.ymd).readonly && cell(row.staff_id, day.ymd).status">
                                        <div>
                                            <span class="inline-block text-[10px] font-semibold px-2 py-1 rounded-md"
                                                  :class="statusClass(cell(row.staff_id, day.ymd).status)"
                                                  x-text="cell(row.staff_id, day.ymd).label"></span>
                                            <p class="text-[9px] text-muted mt-0.5 tabular-nums"
                                               x-show="cell(row.staff_id, day.ymd).clock_in"
                                               x-text="clockLabel(row.staff_id, day.ymd)"></p>
                                            <div class="flex flex-wrap justify-center gap-0.5 mt-1">
                                                <template x-for="btn in actionButtons" :key="btn.status">
                                                    <button type="button"
                                                            :title="btn.title"
                                                            @click="setStatus(row.staff_id, day.ymd, btn.status)"
                                                            :disabled="isLoading(row.staff_id, day.ymd, btn.status)"
                                                            class="w-5 h-5 rounded text-[9px] font-bold border border-gray-200/80 dark:border-gray-600 hover:bg-white/80 dark:hover:bg-gray-800 disabled:opacity-40"
                                                            x-text="btn.short"></button>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="!cell(row.staff_id, day.ymd).readonly && !cell(row.staff_id, day.ymd).status">
                                        <div class="flex flex-col gap-0.5 items-center">
                                            <span class="text-[10px] text-muted">—</span>
                                            <div class="flex flex-wrap justify-center gap-0.5">
                                                <template x-for="btn in quickButtons" :key="btn.status">
                                                    <button type="button"
                                                            :title="btn.title"
                                                            @click="setStatus(row.staff_id, day.ymd, btn.status)"
                                                            :disabled="isLoading(row.staff_id, day.ymd, btn.status)"
                                                            class="w-6 h-6 rounded text-[10px] font-bold border border-gray-200 dark:border-gray-600 hover:bg-velour-50 dark:hover:bg-velour-950/40 disabled:opacity-40"
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
        days: initial.days || [],
        rows: initial.rows || [],
        today: initial.today,
        urls: initial.urls || {},
        weekLabel: '',
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
        },
        init() {
            const start = new Date(initial.week_start + 'T12:00:00');
            const end = new Date(initial.week_end + 'T12:00:00');
            const fmt = (d) => d.toLocaleDateString(undefined, { day: 'numeric', month: 'short' });
            this.weekLabel = fmt(start) + ' – ' + fmt(end) + ' ' + end.getFullYear();
        },
        get csrf() {
            return document.querySelector('meta[name="csrf-token"]')?.content || '';
        },
        get showQuickClock() {
            if (!this.days.length) return false;
            const first = this.days[0].ymd;
            const last = this.days[this.days.length - 1].ymd;
            return this.today >= first && this.today <= last;
        },
        cell(staffId, ymd) {
            const row = this.rows.find((r) => r.staff_id === staffId);
            return row?.cells?.[ymd] || {};
        },
        statusClass(status) {
            return this.statusClasses[status] || 'bg-gray-50 dark:bg-gray-800/40 text-muted';
        },
        clockLabel(staffId, ymd) {
            const c = this.cell(staffId, ymd);
            if (!c.clock_in) return '';
            return c.clock_out ? `${c.clock_in}–${c.clock_out}` : c.clock_in;
        },
        quickClockRows() {
            return this.rows.filter((row) => {
                const c = this.cell(row.staff_id, this.today);
                return c.scheduled && !c.on_leave;
            });
        },
        isLoading(staffId, ymd, suffix) {
            return this.loadingKey === `${staffId}|${ymd}|${suffix}`;
        },
        setLoading(staffId, ymd, suffix) {
            this.loadingKey = `${staffId}|${ymd}|${suffix}`;
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
