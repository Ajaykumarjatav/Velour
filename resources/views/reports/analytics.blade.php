@extends('layouts.app')
@section('title', 'Reports & Analytics')
@section('page-title', 'Analytics')

@section('content')
@php
    $periodLinks = [
        '7d' => '7d',
        '1m' => '1m',
        '3m' => '3m',
        '12m' => '12m',
    ];
    $currencySymbol = \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP');
@endphp

<div class="space-y-6"
     x-data="{
        customOpen: false,
        staffReportOpen: false,
        selectedStaff: null,
        staffRows: @js($staffRows->values()),
        pickStaff(row){ this.selectedStaff = row; this.staffReportOpen = true; }
     }"
     x-on:keydown.escape.window="customOpen=false; staffReportOpen=false">

    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-gray-900 dark:text-white leading-tight">
                    Reports &amp; Analytics
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $fyLabel }} · {{ $salon->name }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <div class="inline-flex rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-1">
                    @foreach($periodLinks as $key => $label)
                        <a href="{{ route('reports.analytics', ['period' => $key]) }}"
                           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors {{ $period === $key ? 'bg-velour-600 text-white' : 'text-muted hover:text-body' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>
                <button type="button" class="btn-outline btn-sm" onclick="window.print()">Export PDF</button>
                <button type="button" class="btn-primary btn-sm" @click="customOpen = true">Custom Report</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-muted">{{ $kpis['revenue']['label'] }}</p>
                <span class="text-[11px] {{ ($kpis['revenue']['delta'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if(!is_null($kpis['revenue']['delta'])) {{ $kpis['revenue']['delta'] >= 0 ? '↑' : '' }}{{ $kpis['revenue']['delta'] }}% @endif
                </span>
            </div>
            <p class="text-3xl font-bold text-heading mt-2">@money($kpis['revenue']['value'])</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-muted">{{ $kpis['bookings']['label'] }}</p>
                <span class="text-[11px] {{ ($kpis['bookings']['delta'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if(!is_null($kpis['bookings']['delta'])) {{ $kpis['bookings']['delta'] >= 0 ? '↑' : '' }}{{ $kpis['bookings']['delta'] }}% @endif
                </span>
            </div>
            <p class="text-3xl font-bold text-heading mt-2">{{ number_format($kpis['bookings']['value']) }}</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-muted">{{ $kpis['ticket']['label'] }}</p>
                <span class="text-[11px] {{ ($kpis['ticket']['delta'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if(!is_null($kpis['ticket']['delta'])) {{ $kpis['ticket']['delta'] >= 0 ? '↑' : '' }}{{ $kpis['ticket']['delta'] }}% @endif
                </span>
            </div>
            <p class="text-3xl font-bold text-heading mt-2">@money($kpis['ticket']['value'])</p>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <p class="text-xs text-muted">{{ $kpis['retention']['label'] }}</p>
                <span class="text-[11px] {{ ($kpis['retention']['delta'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    @if(!is_null($kpis['retention']['delta'])) {{ $kpis['retention']['delta'] >= 0 ? '↑' : '' }}{{ $kpis['retention']['delta'] }}% @endif
                </span>
            </div>
            <p class="text-3xl font-bold text-heading mt-2">{{ round($kpis['retention']['value']) }}%</p>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4">
        <div class="card p-5">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-lg font-semibold text-heading">Revenue vs Expenses</h2>
                <button type="button" class="btn-outline btn-sm" onclick="window.print()">Download</button>
            </div>
            <div class="h-56 flex items-end gap-2.5">
                @foreach($monthlyBars as $m)
                    <div class="flex-1 min-w-[24px] h-full flex items-end gap-1 justify-center relative group">
                        <div class="w-[44%] bg-velour-600 rounded-t" style="height: {{ max(6, $m['revenue_h']) }}%"></div>
                        <div class="w-[44%] bg-amber-300 dark:bg-amber-700/90 rounded-t" style="height: {{ max(6, $m['expense_h']) }}%"></div>
                        <div class="absolute -top-12 hidden group-hover:block bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-2 py-1 text-xs shadow">
                            <p>Revenue: @money($m['revenue'])</p>
                            <p>Expense: @money($m['expense'])</p>
                        </div>
                        <span class="absolute -bottom-6 text-[10px] text-muted">{{ $m['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card p-5">
            <h2 class="text-lg font-semibold text-heading mb-3">Weekly Performance</h2>
            <div class="relative h-56 border border-gray-100 dark:border-gray-800 rounded-xl p-3">
                <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="absolute inset-3 w-[calc(100%-1.5rem)] h-[calc(100%-1.5rem)]">
                    @php
                        $points = collect($weeklyPoints)->map(fn($p) => $p['x'].','.$p['y'])->implode(' ');
                    @endphp
                    <polyline fill="none" stroke="#A16207" stroke-width="1.8" points="{{ $points }}" />
                </svg>
                <div class="absolute left-3 right-3 bottom-2 flex justify-between">
                    @foreach($weeklyPoints as $p)
                        <span class="text-[10px] text-muted">{{ $p['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-heading">Staff Performance</h2>
            <button type="button" class="btn-outline btn-sm">Full Report</button>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table w-full min-w-[760px]">
                <thead>
                <tr>
                    <th>Staff</th>
                    <th>Role</th>
                    <th class="text-right">Appts</th>
                    <th class="text-right">Revenue</th>
                    <th class="text-right">Rating</th>
                    <th>Utilization</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($staffRows as $row)
                    <tr>
                        <td>
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full text-white text-[11px] font-semibold flex items-center justify-center" style="background-color: {{ $row['color'] }}">
                                    {{ substr($row['initials'], 0, 2) }}
                                </div>
                                <span class="font-semibold text-heading">{{ $row['name'] }}</span>
                            </div>
                        </td>
                        <td class="text-muted capitalize">{{ str_replace('_', ' ', $row['role']) }}</td>
                        <td class="text-right text-heading font-semibold">{{ $row['appts'] }}</td>
                        <td class="text-right text-heading font-semibold">@money($row['revenue'])</td>
                        <td class="text-right text-heading font-semibold">{{ $row['rating'] ? '★ '.$row['rating'] : '—' }}</td>
                        <td>
                            <div class="w-28 h-2 rounded-full bg-gray-100 dark:bg-gray-800 overflow-hidden">
                                <div class="h-2 bg-velour-600 rounded-full" style="width: {{ $row['utilization'] }}%"></div>
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn-outline btn-sm"
                                    x-on:click.prevent="pickStaff(staffRows[{{ $loop->index }}])">Report</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-muted">No staff data for this period.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div x-show="customOpen" x-cloak class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" @click.self="customOpen=false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 max-w-md w-full p-6">
            <h3 class="text-xl font-semibold text-heading mb-3">Custom Report</h3>
            <p class="text-sm text-muted mb-4">Use existing detailed reports with date ranges:</p>
            <div class="grid grid-cols-2 gap-2">
                <a href="{{ route('reports.show', 'revenue') }}" class="btn-outline text-center">Revenue</a>
                <a href="{{ route('reports.show', 'appointments') }}" class="btn-outline text-center">Appointments</a>
                <a href="{{ route('reports.show', 'staff') }}" class="btn-outline text-center">Staff</a>
                <a href="{{ route('reports.show', 'clients') }}" class="btn-outline text-center">Clients</a>
            </div>
            <div class="mt-4 flex justify-end">
                <button type="button" class="btn-primary" @click="customOpen=false">Done</button>
            </div>
        </div>
    </div>

    <div x-show="staffReportOpen" x-cloak class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" @click.self="staffReportOpen=false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 max-w-lg w-full p-0 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h3 class="text-xl font-semibold text-heading">Performance Report — <span x-text="selectedStaff ? selectedStaff.name : ''"></span></h3>
                <button type="button" class="text-muted hover:text-body" @click="staffReportOpen=false">✕</button>
            </div>
            <template x-if="selectedStaff">
                <div class="p-6 space-y-4 text-sm">
                    <div class="rounded-xl bg-stone-50 dark:bg-gray-800/50 border border-stone-200 dark:border-gray-700 p-3 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full text-white text-xs font-semibold flex items-center justify-center" :style="'background-color:'+selectedStaff.color">
                            <span x-text="selectedStaff.initials"></span>
                        </div>
                        <div>
                            <p class="font-semibold text-heading" x-text="selectedStaff.name"></p>
                            <p class="text-xs text-muted capitalize" x-text="selectedStaff.role.replace('_',' ')"></p>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100 dark:divide-gray-800">
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Total Appointments</span><span class="font-semibold text-heading" x-text="selectedStaff.appts"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Revenue Generated</span><span class="font-semibold text-heading" x-text="'{{ $currencySymbol }}' + Number(selectedStaff.revenue).toLocaleString()"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Commission Earned</span><span class="font-semibold text-heading" x-text="'{{ $currencySymbol }}' + Number(selectedStaff.commission_earned).toLocaleString()"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Avg Rating</span><span class="font-semibold text-heading" x-text="selectedStaff.rating ? selectedStaff.rating + ' ★' : 'N/A'"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Utilization</span><span class="font-semibold text-heading" x-text="selectedStaff.utilization + '%'"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">No-Show Rate</span><span class="font-semibold text-heading" x-text="selectedStaff.no_show_rate + '%'"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Top Service</span><span class="font-semibold text-heading" x-text="selectedStaff.top_service"></span></div>
                        <div class="py-2 flex items-center justify-between"><span class="text-muted">Repeat Client Rate</span><span class="font-semibold text-heading" x-text="selectedStaff.repeat_client_rate + '%'"></span></div>
                    </div>
                </div>
            </template>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-2">
                <button type="button" class="btn-outline" @click="staffReportOpen=false">Close</button>
                <button type="button" class="btn-primary" onclick="window.print()">Export PDF</button>
            </div>
        </div>
    </div>
</div>
@endsection

