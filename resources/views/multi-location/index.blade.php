@extends('layouts.app')
@section('title', 'Multi-Location Management')
@section('page-title', 'Multi-Location')

@section('content')
<div class="space-y-6"
     x-data="{
        menuOpen: null,
        addOpen: false,
        editOpen: false,
        reportOpen: false,
        consolidatedOpen: false,
        selected: null,
        selectedReport: null,
        toast: @js(session('success') ?: null),
        openEdit(card){ this.selected = card; this.editOpen = true; this.menuOpen = null; },
        openReport(card){ this.selectedReport = card; this.reportOpen = true; this.menuOpen = null; }
     }"
     x-on:keydown.escape.window="menuOpen=null; addOpen=false; editOpen=false; reportOpen=false; consolidatedOpen=false">

    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-gray-900 dark:text-white leading-tight">
                    Multi-Location Management
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    {{ number_format($summary['total_locations']) }} branches · Centralized admin
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button type="button" class="btn-outline btn-sm" @click="consolidatedOpen = true">Consolidated Report</button>
                <button type="button" class="btn-primary btn-sm" @click="addOpen = true">+ Add Location</button>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-velour-100 dark:bg-velour-900/40 p-2 text-velour-700 dark:text-velour-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M5 21V7m0 0l4-4 4 4m-8 0h8m0 14V11m0 0l3-3 3 3m-6 0h6"/></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-heading">{{ number_format($summary['total_locations']) }}</p>
                <p class="text-xs text-muted">Total Locations</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-amber-100 dark:bg-amber-900/30 p-2 text-amber-700 dark:text-amber-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2M7 20H2v-2a3 3 0 015.356-1.857"/></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-heading">{{ number_format($summary['total_staff']) }}</p>
                <p class="text-xs text-muted">Total Staff</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-blue-100 dark:bg-blue-900/30 p-2 text-blue-700 dark:text-blue-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-heading">{{ number_format($summary['today_appointments']) }}</p>
                <p class="text-xs text-muted">Today's Appts</p>
            </div>
        </div>
        <div class="card p-4 flex items-center gap-3">
            <div class="rounded-xl bg-emerald-100 dark:bg-emerald-900/30 p-2 text-emerald-700 dark:text-emerald-300">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .67-3 1.5S10.343 11 12 11s3-.67 3-1.5S13.657 8 12 8zm0 0v8m0 0c-1.657 0-3 .67-3 1.5S10.343 19 12 19s3-.67 3-1.5-1.343-1.5-3-1.5z"/></svg>
            </div>
            <div>
                <p class="text-xl font-bold text-heading">@money($summary['combined_revenue'])</p>
                <p class="text-xs text-muted">Combined Revenue</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @foreach($cards as $card)
            <div class="card p-4 sm:p-5 relative">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3 class="text-lg font-semibold text-heading">{{ $card['name'] }}</h3>
                        <p class="text-xs text-muted mt-1">{{ $card['address'] ?: 'Address not set' }}</p>
                        @if($card['branch_manager'])
                            <p class="text-xs text-muted mt-1">Manager: {{ $card['branch_manager'] }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $card['status'] === 'active' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300' }}">
                            {{ $card['status'] === 'active' ? 'Active' : 'Opening Soon' }}
                        </span>
                        <div class="relative" x-on:click.outside="if(menuOpen === {{ $card['id'] }}) menuOpen = null">
                            <button type="button"
                                    class="p-2 rounded-lg border border-stone-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-stone-50 dark:hover:bg-gray-800 hover:text-gray-900 dark:hover:text-white"
                                    title="Location actions"
                                    x-on:click.stop="menuOpen = menuOpen === {{ $card['id'] }} ? null : {{ $card['id'] }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v.01M12 12v.01M12 19v.01"/>
                                </svg>
                            </button>
                            <div x-show="menuOpen === {{ $card['id'] }}" x-cloak
                                 class="absolute right-0 mt-1 w-44 rounded-xl border border-stone-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-xl z-50 py-1 text-sm text-gray-800 dark:text-gray-100">
                                <form action="{{ route('multi-location.switch', $card['id']) }}" method="POST">@csrf
                                    <button type="submit" class="w-full text-left px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800">Open Dashboard</button>
                                </form>
                                <a href="{{ route('staff.index') }}" class="block px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800">Manage Staff</a>
                                <button type="button" class="w-full text-left px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800" @click='openReport(@js($card))'>Branch Report</button>
                                <a href="{{ route('settings.index') }}" class="block px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800">Settings</a>
                                <button type="button" class="w-full text-left px-3 py-2 text-gray-800 dark:text-gray-100 hover:bg-stone-50 dark:hover:bg-gray-800" @click='openEdit(@js($card))'>Edit</button>
                                <form action="{{ route('multi-location.destroy', $card['id']) }}" method="POST" onsubmit="return confirm('Remove this location?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="w-full text-left px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Remove</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-2 mt-4">
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5">
                        <p class="text-[11px] text-muted">Staff</p>
                        <p class="font-semibold text-heading">{{ $card['staff_count'] }}</p>
                    </div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5">
                        <p class="text-[11px] text-muted">Today's Appts</p>
                        <p class="font-semibold text-heading">{{ $card['today_appointments'] }}</p>
                    </div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5">
                        <p class="text-[11px] text-muted">Monthly Rev</p>
                        <p class="font-semibold text-heading">@money($card['monthly_revenue'])</p>
                    </div>
                </div>

                <div class="mt-4 flex gap-2">
                    <form action="{{ route('multi-location.switch', $card['id']) }}" method="POST" class="flex-1">
                        @csrf
                        <button type="submit" class="w-full btn-primary btn-sm">Open Dashboard</button>
                    </form>
                    <button type="button" class="btn-outline btn-sm" @click='openReport(@js($card))'>Report</button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Add modal --}}
    <div x-show="addOpen" x-cloak class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" x-on:click.self="addOpen=false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 w-full max-w-xl p-6">
            <div class="flex items-start justify-between mb-5">
                <h3 class="font-semibold text-xl text-heading">Add New Location</h3>
                <button class="text-muted" @click="addOpen=false">✕</button>
            </div>
            <form method="POST" action="{{ route('multi-location.store') }}" class="space-y-4">
                @csrf
                <div><label class="form-label">Branch name *</label><input name="name" required class="form-input" placeholder="e.g. Delhi NCR"></div>
                <div><label class="form-label">Full address *</label><input name="address_line1" required class="form-input" placeholder="Street, Area, City, PIN"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">City *</label><input name="city" required class="form-input"></div>
                    <div><label class="form-label" for="ml-add-tz-trigger">Timezone *</label>
                        <x-searchable-select
                            id="ml-add-tz"
                            name="timezone"
                            :required="true"
                            wrapper-class="w-full min-w-0"
                            :search-url="null"
                            search-placeholder="Search timezone…"
                            trigger-class="form-select w-full">
                            @foreach($timezones as $tz)<option value="{{ $tz }}">{{ $tz }}</option>@endforeach
                        </x-searchable-select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">Phone *</label><input name="phone" required class="form-input" placeholder="+91 98765 43210"></div>
                    <div><label class="form-label">Branch manager</label><input name="branch_manager" class="form-input" placeholder="Manager's name"></div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-body"><input type="hidden" name="online_booking_enabled" value="0"><input type="checkbox" name="online_booking_enabled" value="1" checked class="rounded"> Enable online booking</label>
                    <label class="inline-flex items-center gap-2 text-sm text-body"><input type="hidden" name="notify_team_when_created" value="0"><input type="checkbox" name="notify_team_when_created" value="1" checked class="rounded"> Notify team when created</label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline" @click="addOpen=false">Cancel</button>
                    <button type="submit" class="btn-primary">Add Location</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit modal --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" x-on:click.self="editOpen=false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 w-full max-w-xl p-6">
            <div class="flex items-start justify-between mb-5">
                <h3 class="font-semibold text-xl text-heading">Edit Location</h3>
                <button class="text-muted" @click="editOpen=false">✕</button>
            </div>
            <form :action="selected ? '{{ url('multi-location') }}/' + selected.id : '#'" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <div><label class="form-label">Branch name *</label><input name="name" required class="form-input" :value="selected?.name ?? ''"></div>
                <div><label class="form-label">Full address *</label><input name="address_line1" required class="form-input" :value="selected?.address_line1 ?? ''"></div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">City *</label><input name="city" required class="form-input" :value="selected?.city ?? ''"></div>
                    <div><label class="form-label">Timezone *</label>
                        <select name="timezone" class="form-select" required>
                            @foreach($timezones as $tz)<option value="{{ $tz }}" :selected="selected?.timezone === '{{ $tz }}'">{{ $tz }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="form-label">Phone *</label><input name="phone" required class="form-input" :value="selected?.phone ?? ''"></div>
                    <div><label class="form-label">Branch manager</label><input name="branch_manager" class="form-input" :value="selected?.branch_manager ?? ''"></div>
                </div>
                <div>
                    <label class="inline-flex items-center gap-2 text-sm text-body">
                        <input type="hidden" name="online_booking_enabled" value="0">
                        <input type="checkbox" name="online_booking_enabled" value="1" class="rounded" :checked="!!selected?.online_booking_enabled">
                        Enable online booking
                    </label>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline" @click="editOpen=false">Cancel</button>
                    <button type="submit" class="btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Branch report modal --}}
    <div x-show="reportOpen" x-cloak class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" x-on:click.self="reportOpen=false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 w-full max-w-xl p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-semibold text-xl text-heading">Branch Report — <span x-text="selectedReport?.name || ''"></span></h3>
                <button class="text-muted" @click="reportOpen=false">✕</button>
            </div>
            <template x-if="selectedReport">
                <div>
                    <div class="grid grid-cols-2 gap-2">
                        <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5"><p class="text-[11px] text-muted">Monthly Revenue</p><p class="font-semibold text-heading" x-text="selectedReport.report.monthly_revenue"></p></div>
                        <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5"><p class="text-[11px] text-muted">Staff Count</p><p class="font-semibold text-heading" x-text="selectedReport.report.staff_count"></p></div>
                        <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5"><p class="text-[11px] text-muted">Today's Appointments</p><p class="font-semibold text-heading" x-text="selectedReport.report.today_appointments"></p></div>
                        <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5"><p class="text-[11px] text-muted">Avg Rating</p><p class="font-semibold text-heading" x-text="selectedReport.report.avg_rating ?? 'N/A'"></p></div>
                        <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5"><p class="text-[11px] text-muted">New Clients (MTD)</p><p class="font-semibold text-heading" x-text="selectedReport.report.new_clients_mtd"></p></div>
                        <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-2.5"><p class="text-[11px] text-muted">Top Service</p><p class="font-semibold text-heading" x-text="selectedReport.report.top_service"></p></div>
                    </div>
                    <div class="mt-4">
                        <p class="text-sm font-semibold text-heading mb-2">Revenue Trend</p>
                        <template x-for="row in selectedReport.report.trend" :key="row.label">
                            <div class="flex items-center gap-3 mb-1.5">
                                <span class="w-8 text-xs text-muted" x-text="row.label"></span>
                                <div class="flex-1 h-2 rounded-full bg-stone-100 dark:bg-gray-800 overflow-hidden">
                                    <div class="h-2 bg-velour-600 rounded-full" :style="'width:'+row.percent+'%'"></div>
                                </div>
                                <span class="text-xs text-muted w-9 text-right" x-text="row.percent + '%'"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </template>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn-outline" @click="reportOpen=false">Close</button>
                <button type="button" class="btn-primary" @click="window.print()">Export PDF</button>
            </div>
        </div>
    </div>

    {{-- Consolidated report modal --}}
    <div x-show="consolidatedOpen" x-cloak class="fixed inset-0 z-[200] bg-black/40 flex items-center justify-center p-4" x-on:click.self="consolidatedOpen=false">
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 w-full max-w-lg p-6">
            <div class="flex items-start justify-between mb-4">
                <h3 class="font-semibold text-xl text-heading">Consolidated Report</h3>
                <button class="text-muted" @click="consolidatedOpen=false">✕</button>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Combined Revenue</p><p class="font-semibold text-heading">@money($consolidated['revenue'])</p></div>
                <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Staff Count</p><p class="font-semibold text-heading">{{ number_format($consolidated['staff']) }}</p></div>
                <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Today's Appointments</p><p class="font-semibold text-heading">{{ number_format($consolidated['appointments']) }}</p></div>
                <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">New Clients (MTD)</p><p class="font-semibold text-heading">{{ number_format($consolidated['new_clients']) }}</p></div>
            </div>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" class="btn-outline" @click="consolidatedOpen=false">Close</button>
                <button type="button" class="btn-primary" @click="window.print()">Export PDF</button>
            </div>
        </div>
    </div>

    {{-- success toast --}}
    <div x-show="toast" x-cloak x-transition class="fixed bottom-6 right-6 z-[210] rounded-xl bg-emerald-600 text-white px-4 py-3 shadow-xl text-sm">
        <span x-text="toast"></span>
    </div>
</div>
@endsection

