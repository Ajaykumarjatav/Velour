@extends('layouts.app')
@section('title', 'Staff & HR')
@section('page-title', 'Staff & HR')

@php
    $sym = \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP');
    $fmtMoney = fn (float $n) => \App\Helpers\CurrencyHelper::format($n, $salon->currency ?? 'GBP');
    $fmtShort = function (float $v) use ($sym) {
        if ($v >= 1000) {
            return $sym . round($v / 1000) . 'k';
        }

        return $sym . number_format($v, 0);
    };
    $timeChoices = [];
    for ($h = 7; $h <= 21; $h++) {
        foreach (['00', '30'] as $mm) {
            if ($h === 21 && $mm === '30') {
                break;
            }
            $timeChoices[] = sprintf('%02d:%s', $h, $mm);
        }
    }
@endphp

@section('content')

<div class="max-w-7xl mx-auto space-y-6"
     x-data="{
        payrollOpen: false,
        scheduleOpen: false,
        menuOpenId: null,
        scheduleStaff: { id: 0, name: '', working_days: [], start_time: '09:00', end_time: '18:00' },
        openPayroll() { this.payrollOpen = true; this.menuOpenId = null; },
        closePayroll() { this.payrollOpen = false; },
        openSchedule(payload) {
            this.scheduleStaff = {
                id: payload.id,
                name: payload.name,
                working_days: payload.working_days && payload.working_days.length ? [...payload.working_days] : ['Mon','Tue','Wed','Thu','Fri'],
                start_time: payload.start_time || '09:00',
                end_time: payload.end_time || '18:00',
            };
            this.scheduleOpen = true;
            this.menuOpenId = null;
        },
        closeSchedule() { this.scheduleOpen = false; },
        toggleMenu(id) { this.menuOpenId = this.menuOpenId === id ? null : id; },
     }"
     @keydown.escape.window="payrollOpen = false; scheduleOpen = false; menuOpenId = null">

    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
        <div>
            <p class="text-sm text-muted">{{ $totalTeam }} team member{{ $totalTeam === 1 ? '' : 's' }} · {{ $onDuty }} on duty today</p>
            <p class="text-xs text-muted mt-1">Full add/edit forms are unchanged — use <strong>View</strong> / <strong>Edit</strong> for profile details.</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <button type="button" @click="openPayroll()" class="btn-outline text-sm inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Payroll
            </button>
            <a href="{{ route('staff.create') }}" class="btn-primary text-sm">+ Add Staff</a>
        </div>
    </div>

    {{-- Revenue chart (this month) --}}
    <div class="card p-5 sm:p-6">
        <h2 class="text-base font-bold text-heading mb-4">Team revenue this month</h2>
        <p class="text-xs text-muted mb-4">Completed appointments only · {{ $monthStart->format('F Y') }}</p>
        @if(count($chart))
            <div class="space-y-3">
                @foreach($chart as $row)
                    @php $pct = $maxRev > 0 ? round(($row['revenue'] / $maxRev) * 100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="font-medium text-body truncate pr-2">{{ $row['name'] }}</span>
                            <span class="text-muted shrink-0">{{ $fmtMoney($row['revenue']) }}</span>
                        </div>
                        <div class="h-2.5 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full bg-velour-600 transition-all" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-muted">No staff to show yet.</p>
        @endif
    </div>

    {{-- Staff cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
        @forelse($staff as $member)
            @php
                $initials = strtoupper(mb_substr($member->first_name, 0, 1) . mb_substr($member->last_name, 0, 1));
                if (trim((string) ($member->initials ?? '')) !== '') {
                    $initials = strtoupper(mb_substr(trim($member->initials), 0, 2));
                }
                $st = $member->start_time ? substr($member->start_time, 0, 5) : '09:00';
                $en = $member->end_time ? substr($member->end_time, 0, 5) : '18:00';
                $rating = $member->reviews_avg_rating;
                $specs = is_array($member->specialisms) ? $member->specialisms : [];
                $schedulePayload = [
                    'id' => $member->id,
                    'name' => $member->name,
                    'working_days' => $member->working_days,
                    'start_time' => $st,
                    'end_time' => $en,
                ];
            @endphp
            <div class="card p-5 hover:shadow-md transition-shadow flex flex-col relative">
                <div class="flex items-start gap-3">
                    @if($member->avatar_url)
                        <img src="{{ $member->avatar_url }}" alt="" width="48" height="48" class="w-12 h-12 rounded-full object-cover border border-gray-200 dark:border-gray-700 shrink-0">
                    @else
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-bold text-sm shrink-0"
                             style="background-color: {{ $member->color ?? '#7C3AED' }}">{{ $initials }}</div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-heading truncate">{{ $member->name }}</h3>
                        <p class="text-xs text-muted capitalize">{{ str_replace('_', ' ', $member->role) }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-1 shrink-0">
                        @if($member->hub_on_leave_today)
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">On leave</span>
                        @endif
                        <span class="{{ $member->is_active ? 'badge-green' : 'badge-gray' }} text-[10px]">
                            {{ $member->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <div class="relative">
                            <button type="button" @click.stop="toggleMenu({{ $member->id }})"
                                    class="p-1.5 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white"
                                    aria-label="More actions" aria-haspopup="true" :aria-expanded="menuOpenId === {{ $member->id }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                </svg>
                            </button>
                            <div x-show="menuOpenId === {{ $member->id }}" x-cloak @click.outside="menuOpenId = null"
                                 class="absolute right-0 mt-1 w-44 py-1 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg z-20 text-sm text-gray-800 dark:text-gray-100">
                                <a href="{{ route('staff.show', $member) }}" class="block px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">View profile</a>
                                <a href="{{ route('calendar', ['view' => 'week', 'date' => now()->toDateString(), 'staff_id' => $member->id]) }}" class="block px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">View schedule</a>
                                <a href="{{ route('staff.edit', $member) }}" class="block px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">Edit</a>
                                <a href="{{ route('availability.index', ['tab' => 'leave']) }}" class="block px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">Leave &amp; blocks</a>
                                <button type="button" @click="openPayroll(); menuOpenId = null" class="w-full text-left px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800">Payroll</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1 mt-3 text-sm">
                    <span class="text-amber-500">★</span>
                    <span class="font-semibold text-heading">{{ $rating !== null ? number_format((float) $rating, 1) : '—' }}</span>
                    <span class="text-xs text-muted">/ 5</span>
                </div>

                <div class="grid grid-cols-2 gap-2 mt-3 text-xs">
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                        <p class="text-muted">Shift</p>
                        <p class="font-semibold text-body mt-0.5">{{ $st }}–{{ $en }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                        <p class="text-muted">Appointments</p>
                        <p class="font-semibold text-body mt-0.5">{{ $member->hub_appts_month }} <span class="text-muted font-normal">this mo.</span></p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                        <p class="text-muted">Revenue</p>
                        <p class="font-semibold text-body mt-0.5">{{ $fmtShort((float) $member->hub_revenue_month) }}</p>
                    </div>
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/60 p-2">
                        <p class="text-muted">Commission</p>
                        <p class="font-semibold text-body mt-0.5">{{ rtrim(rtrim(number_format((float) ($member->commission_rate ?? 0), 2), '0'), '.') }}%</p>
                    </div>
                </div>

                @if(count($specs))
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach(array_slice($specs, 0, 6) as $tag)
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-velour-50 dark:bg-velour-900/30 text-velour-800 dark:text-velour-200">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif

                <div class="grid grid-cols-2 gap-2 mt-4 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <a href="{{ route('staff.show', $member) }}" class="btn-outline text-center text-sm py-2">View</a>
                    <a href="{{ route('staff.edit', $member) }}" class="btn-secondary text-center text-sm py-2">Edit</a>
                </div>
                <div class="grid grid-cols-3 gap-2 mt-2">
                    <a href="{{ route('calendar', ['view' => 'week', 'date' => now()->toDateString(), 'staff_id' => $member->id]) }}"
                       class="btn-primary text-center text-xs py-2 col-span-1">Schedule</a>
                    <button type="button" @click="openPayroll()" class="btn-outline text-center text-xs py-2">Payroll</button>
                    <a href="{{ route('staff.show', $member) }}#notes" class="btn-outline text-center text-xs py-2 flex items-center justify-center" title="Open profile">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    </a>
                </div>

                <button type="button"
                        class="mt-2 w-full text-xs text-velour-600 dark:text-velour-400 font-medium py-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20"
                        data-staff='@json($schedulePayload)'
                        @click="openSchedule(JSON.parse($event.currentTarget.dataset.staff))">
                    Weekly schedule…
                </button>
            </div>
        @empty
            <div class="col-span-full empty-state">
                <p class="empty-state-title">No staff members yet</p>
                <a href="{{ route('staff.create') }}" class="btn-primary mt-4">Add your first staff member</a>
            </div>
        @endforelse
    </div>

    {{-- Payroll modal --}}
    <div x-show="payrollOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden flex flex-col" @click.outside="closePayroll()">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <div>
                    <h3 class="text-lg font-serif font-bold text-heading">Payroll summary</h3>
                    <p class="text-xs text-muted">{{ $monthStart->format('F Y') }} · Est. tax {{ (int) ($taxRate * 100) }}% on base + commission</p>
                </div>
                <button type="button" class="text-muted hover:text-heading text-2xl leading-none" @click="closePayroll()">&times;</button>
            </div>
            <div class="overflow-y-auto p-4 sm:p-5">
                <div class="overflow-x-auto rounded-xl border border-gray-100 dark:border-gray-800">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 dark:bg-gray-800/80 text-left text-xs uppercase text-muted">
                                <th class="px-3 py-2">Staff</th>
                                <th class="px-3 py-2">Base</th>
                                <th class="px-3 py-2">Commission</th>
                                <th class="px-3 py-2">Tax</th>
                                <th class="px-3 py-2">Net pay</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($payrollRows as $row)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-body">{{ $row['staff']->name }}</td>
                                    <td class="px-3 py-2">
                                        <form method="POST" action="{{ route('staff.base-salary', $row['staff']) }}" class="flex flex-wrap items-center gap-1">
                                            @csrf
                                            @method('PATCH')
                                            <input type="number" name="base_salary" step="0.01" min="0" value="{{ $row['base'] ?: '' }}" placeholder="0"
                                                   class="form-input w-24 text-xs py-1 px-2">
                                            <button type="submit" class="text-[10px] font-semibold text-velour-600 hover:underline">Save</button>
                                        </form>
                                    </td>
                                    <td class="px-3 py-2 text-emerald-600 dark:text-emerald-400">+{{ $fmtMoney($row['commission']) }}</td>
                                    <td class="px-3 py-2 text-rose-600 dark:text-rose-400">−{{ $fmtMoney($row['tax']) }}</td>
                                    <td class="px-3 py-2 font-bold text-heading">{{ $fmtMoney($row['net']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="text-xs text-muted mt-3">Base salary is stored per staff (not on the classic add/edit forms). Commission follows each member’s % × completed revenue this month.</p>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 flex flex-wrap justify-end gap-2 bg-gray-50/80 dark:bg-gray-900/50">
                <button type="button" class="btn-outline text-sm" @click="closePayroll()">Close</button>
                <a href="{{ route('staff.payroll.export', ['month' => $monthStart->format('Y-m')]) }}" class="btn-primary text-sm inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Export payroll
                </a>
            </div>
        </div>
    </div>

    {{-- Weekly schedule modal --}}
    <div x-show="scheduleOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto" @click.outside="closeSchedule()">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
                <h3 class="text-lg font-serif font-bold text-heading">Weekly schedule — <span x-text="scheduleStaff.name"></span></h3>
                <button type="button" class="text-muted hover:text-heading text-2xl" @click="closeSchedule()">&times;</button>
            </div>
            <div class="px-5 py-3 text-xs text-sky-800 dark:text-sky-200 bg-sky-50 dark:bg-sky-900/20 rounded-xl mx-5 mt-4">
                Toggle working days and set hours. Matches existing <code class="text-[10px]">working_days</code> / booking rules.
            </div>
            <form method="POST" x-bind:action="'{{ url('/staff') }}/' + scheduleStaff.id + '/weekly-schedule'" class="p-5 space-y-4">
                @csrf
                @method('PUT')
                <div class="flex flex-wrap gap-2">
                    <template x-for="day in ['Mon','Tue','Wed','Thu','Fri','Sat','Sun']" :key="day">
                        <label class="inline-flex items-center gap-1.5 px-2 py-1 rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer text-xs font-medium"
                               :class="scheduleStaff.working_days.includes(day) ? 'bg-velour-50 dark:bg-velour-900/30 border-velour-200' : 'opacity-60'">
                            <input type="checkbox" name="working_days[]" :value="day" x-model="scheduleStaff.working_days"
                                   class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                            <span x-text="day"></span>
                        </label>
                    </template>
                </div>
                <p class="text-xs text-muted">Unchecked days are off. Start/end apply to every checked day (matches your Staff record).</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <div class="flex-1">
                        <label class="form-label text-xs">Start</label>
                        <select name="start_time" class="form-select text-sm" x-model="scheduleStaff.start_time">
                            @foreach($timeChoices as $tc)
                                <option value="{{ $tc }}">{{ $tc }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="form-label text-xs">End</label>
                        <select name="end_time" class="form-select text-sm" x-model="scheduleStaff.end_time">
                            @foreach($timeChoices as $tc)
                                <option value="{{ $tc }}">{{ $tc }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline text-sm" @click="closeSchedule()">Cancel</button>
                    <button type="submit" class="btn-primary text-sm">Save schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
