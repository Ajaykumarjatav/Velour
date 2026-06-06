@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')

@php
    $tzAbbr = $tzAbbr ?? '';
    $revenueChange = $revenueChange ?? null;
    $profileCompletion = $profileCompletion ?? ['percentage' => 100];
    $canManageDesk = $canManageDesk ?? false;
    $pendingLeaveRequests = $pendingLeaveRequests ?? collect();
    $openDeskItems = $openDeskItems ?? collect();
    $myDeskSubmissions = $myDeskSubmissions ?? collect();
    $deskKindLabels = $deskKindLabels ?? [];
    $deskStaffForAssign = $deskStaffForAssign ?? collect();
    $deskTz = \App\Support\SalonTime::timezone($salon);
@endphp

@if(!empty($stylistDashboardScoped))
<div class="mb-4 rounded-xl border border-velour-200 dark:border-velour-800 bg-velour-50 dark:bg-velour-950/40 px-4 py-3 text-sm text-velour-900 dark:text-velour-100">
    <p class="font-medium">Your personal dashboard</p>
    <p class="mt-1 text-velour-800/90 dark:text-velour-200/90">Numbers and lists here include only <strong>your</strong> appointments, POS sales credited to you, and clients you have booked.</p>
</div>
@endif

<p class="mb-4 text-[11px] leading-snug text-gray-400 dark:text-gray-500">
    Figures use your salon timezone (<abbr title="{{ $salon->timezone ?? 'UTC' }}">{{ $tzAbbr }}</abbr>). Revenue is counted when a POS sale is completed (<a href="{{ route('reports.show', ['type' => 'revenue', 'from' => \App\Support\SalonTime::monthStartDateString($salon), 'to' => \App\Support\SalonTime::todayDateString($salon)]) }}" class="text-gray-500 dark:text-gray-400 underline decoration-gray-300 dark:decoration-gray-600 hover:text-velour-600 dark:hover:text-velour-400">Revenue report</a>).
</p>

{{-- ══ Analytics slider ══ --}}
@php
    $analyticsWidgetsJson = json_encode($analyticsWidgets ?? []);
    $detailListsJson = json_encode($detailLists ?? []);
    $periodBoundsJson = json_encode($periodBounds ?? []);
@endphp
<div x-data="analyticsSlider({{ $analyticsWidgetsJson }}, {{ $detailListsJson }}, {{ $periodBoundsJson }})" class="mb-7">
    {{-- Period tabs --}}
    <div class="flex items-center gap-2 mb-4 flex-wrap">
        <template x-for="p in periods" :key="p.key">
            <button type="button"
                    @click="period = p.key; active = null"
                    :class="period === p.key
                        ? 'bg-velour-600 text-white border-velour-600 shadow-sm'
                        : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-300 border-gray-200 dark:border-gray-700 hover:border-velour-300'"
                    class="px-3.5 py-1.5 text-xs font-semibold rounded-full border transition-all duration-150"
                    x-text="p.label">
            </button>
        </template>
    </div>

    {{-- Slider --}}
    <div class="relative group/slider">
        <button type="button" @click="scrollLeft()" aria-label="Scroll left"
                class="analytics-slider-arrow left-0 rounded-r-xl opacity-0 group-hover/slider:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </button>
        <div x-ref="slider" class="analytics-slider flex gap-4 overflow-x-auto snap-x snap-mandatory scroll-smooth pb-2 px-1">
            <template x-for="w in widgets" :key="w.key">
                <button type="button"
                        @click="active = active === w.key ? null : w.key"
                        :class="active === w.key
                            ? 'border-velour-500 bg-velour-50/80 dark:bg-velour-950/40 dark:border-velour-400 ring-1 ring-velour-200 dark:ring-velour-800'
                            : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/80 hover:border-velour-200 dark:hover:border-velour-700'"
                        class="analytics-widget-card snap-start shrink-0 w-[230px] sm:w-[250px] min-h-[7.5rem] rounded-2xl border p-5 text-left transition-all duration-200 cursor-pointer shadow-sm hover:shadow-md">
                    <div class="flex items-center gap-2.5 mb-3">
                        <span class="w-10 h-10 rounded-xl flex items-center justify-center text-base shrink-0"
                              :class="w.iconBg" x-html="w.icon"></span>
                        <span class="text-xs font-semibold uppercase tracking-wide text-muted truncate" x-text="w.label"></span>
                    </div>
                    <p class="text-2xl font-bold text-heading tabular-nums leading-tight" x-text="w.format(data[period]?.[w.dataKey] ?? 0)"></p>
                    <p class="text-xs text-muted mt-1.5 truncate" x-text="w.sub(data[period])"></p>
                </button>
            </template>
        </div>
        <button type="button" @click="scrollRight()" aria-label="Scroll right"
                class="analytics-slider-arrow right-0 rounded-l-xl opacity-0 group-hover/slider:opacity-100 transition-opacity">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </button>
    </div>

    {{-- Detail panel (expands below) --}}
    <div x-show="active !== null" x-cloak x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0 -translate-y-2"
         class="mt-3 rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/90 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-heading" x-text="activeLabel()"></h3>
            <a :href="activeLink()" class="text-xs font-medium text-velour-600 dark:text-velour-400 hover:underline">View all &rarr;</a>
        </div>
        <div class="max-h-[250px] overflow-y-auto p-4 sm:p-5">
            {{-- Appointments --}}
            <div x-show="active === 'appointments'">
                <template x-if="filteredList('appointments').length === 0">
                    <p class="text-sm text-muted text-center py-4">No appointments for this period.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="(item, idx) in filteredList('appointments')" :key="idx">
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shrink-0" :style="'background-color:' + item.color" x-text="item.initial"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-heading truncate" x-text="item.name"></p>
                                <p class="text-xs text-muted truncate" x-text="item.services"></p>
                            </div>
                            <span class="text-xs font-semibold text-body shrink-0" x-text="item.time"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Pending tasks --}}
            <div x-show="active === 'pending_tasks'">
                <template x-if="filteredList('tasks').length === 0">
                    <p class="text-sm text-muted text-center py-4">No pending tasks for this period.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="(item, idx) in filteredList('tasks')" :key="idx">
                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <span class="mt-0.5 text-[10px] font-bold uppercase px-1.5 py-0.5 rounded"
                                  :class="item.priority === 'high' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200'"
                                  x-text="item.priority"></span>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-heading truncate" x-text="item.title"></p>
                                <p class="text-xs text-muted" x-text="item.ago"></p>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            {{-- New customers --}}
            <div x-show="active === 'new_customers'">
                <template x-if="filteredList('clients').length === 0">
                    <p class="text-sm text-muted text-center py-4">No new customers for this period.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="(item, idx) in filteredList('clients')" :key="idx">
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="w-8 h-8 rounded-full bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-xs font-bold text-velour-700 dark:text-velour-300 shrink-0" x-text="item.initial"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-heading truncate" x-text="item.name"></p>
                                <p class="text-xs text-muted" x-text="item.email"></p>
                            </div>
                            <span class="text-xs text-muted shrink-0" x-text="item.ago"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Staff status (not period-filtered) --}}
            <div x-show="active === 'staff_status'">
                <template x-if="lists.staff.length === 0">
                    <p class="text-sm text-muted text-center py-4">No staff members.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="(item, idx) in lists.staff" :key="idx">
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0" :style="'background-color:' + item.color" x-text="item.initials"></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-heading" x-text="item.name"></p>
                            </div>
                            <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">Active</span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Website traffic --}}
            <div x-show="active === 'website_traffic'" class="py-6 text-center">
                <p class="text-sm text-muted">Website traffic tracking is not yet connected.</p>
                <p class="text-xs text-muted mt-2">Connect Google Analytics or integrate a tracking pixel to see real visitor data here.</p>
            </div>

            {{-- Reviews --}}
            <div x-show="active === 'reviews'">
                <template x-if="filteredList('reviews').length === 0">
                    <p class="text-sm text-muted text-center py-4">No reviews for this period.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="(item, idx) in filteredList('reviews')" :key="idx">
                        <div class="flex items-start gap-3 p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="flex gap-0.5 shrink-0 mt-0.5">
                                <template x-for="star in 5">
                                    <svg class="w-3.5 h-3.5" :class="star <= item.rating ? 'text-amber-400' : 'text-gray-200 dark:text-gray-600'" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-heading" x-text="item.name"></p>
                                <p class="text-xs text-muted truncate" x-text="item.comment"></p>
                            </div>
                            <span class="text-xs text-muted shrink-0" x-text="item.ago"></span>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Revenue --}}
            <div x-show="active === 'revenue'">
                <template x-if="filteredList('sales').length === 0">
                    <p class="text-sm text-muted text-center py-4">No sales for this period.</p>
                </template>
                <div class="space-y-2">
                    <template x-for="(item, idx) in filteredList('sales')" :key="idx">
                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-body" x-text="item.name"></p>
                                <p class="text-xs text-muted" x-text="item.ago"></p>
                            </div>
                            <span class="text-sm font-bold text-heading shrink-0" x-text="'₹' + item.total"></span>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Action center: admin priorities + staff requests --}}
@if($canManageDesk || $stylistDashboardScoped)
<div id="action-center" class="card mb-7 overflow-hidden scroll-mt-24">
    <div class="card-header flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h2 class="section-title">Action center</h2>
            <p class="text-xs text-muted mt-1">
                @if($canManageDesk)
                    Pending leave, team messages, and your office to-dos in one place.
                @else
                    Send a suggestion, product request, or note to management — it appears on the salon dashboard.
                @endif
            </p>
        </div>
        @if($canManageDesk)
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 justify-end">
                <a href="{{ route('tasks.index') }}" class="text-link text-xs font-medium whitespace-nowrap">Task board →</a>
                <a href="{{ route('availability.index', ['tab' => 'leave']) }}" class="text-link text-xs font-medium whitespace-nowrap">All leave →</a>
            </div>
        @endif
    </div>
    <div class="p-5 sm:p-6 space-y-6">

        @if($canManageDesk && $pendingLeaveRequests->isEmpty() && $openDeskItems->isEmpty())
        <p class="text-sm text-muted">No pending leave, tasks, or staff messages.</p>
        @endif

        @if($stylistDashboardScoped && !$canManageDesk && $myDeskSubmissions->isEmpty())
        <p class="text-sm text-muted">No open messages to management.</p>
        @endif

        @if($canManageDesk && $pendingLeaveRequests->isNotEmpty())
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Leave awaiting approval</h3>
            <ul class="space-y-2">
                @foreach($pendingLeaveRequests as $leave)
                <li class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-xl border border-amber-200/80 dark:border-amber-900/50 bg-amber-50/50 dark:bg-amber-950/20 px-4 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-heading">
                            {{ $leave->staff?->name ?? 'Staff' }}
                            <span class="text-muted font-normal">&middot; {{ $leave->leave_type }}</span>
                        </p>
                        <p class="text-xs text-muted mt-0.5">
                            {{ $leave->start_date?->format('d M Y') }} – {{ $leave->end_date?->format('d M Y') }}
                            @if($leave->notes)
                                <span class="block mt-1 text-body/90">{{ \Illuminate\Support\Str::limit($leave->notes, 120) }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2 shrink-0">
                        <form method="POST" action="{{ route('availability.leave.approve', $leave) }}" class="inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-primary text-xs py-1.5 px-3">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('availability.leave.reject', $leave) }}" class="inline" onsubmit="return confirm('Reject this leave request?');">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn-outline text-xs py-1.5 px-3 border-red-200 text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30">Reject</button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($canManageDesk && $openDeskItems->isNotEmpty())
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Tasks &amp; team messages</h3>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                @foreach($openDeskItems as $item)
                <li class="px-4 py-3 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 bg-white dark:bg-gray-900/40">
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($item->status === 'in_progress')
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">In progress</span>
                            @else
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200">Open</span>
                            @endif
                            @if($item->priority === 'high')
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">High</span>
                            @elseif($item->priority === 'low')
                                <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300">Low</span>
                            @endif
                            <span class="text-[10px] font-semibold uppercase text-muted">{{ $deskKindLabels[$item->kind] ?? $item->kind }}</span>
                            @if($item->staff)
                                <span class="text-xs text-muted">from {{ $item->staff->name }}</span>
                            @endif
                            @if($item->assignedStaff)
                                <span class="text-xs text-muted">→ {{ $item->assignedStaff->name }}</span>
                            @endif
                        </div>
                        <p class="text-sm font-medium text-heading mt-1">{{ $item->title }}</p>
                        @if($item->body)
                            <p class="text-xs text-body/90 mt-1 whitespace-pre-line">{{ $item->body }}</p>
                        @endif
                        <p class="text-[11px] text-muted mt-1">
                            {{ $item->created_at->diffForHumans() }}
                            @if($item->due_at)
                                <span class="ml-2">· Due {{ $item->due_at->timezone($deskTz)->format('M j, Y') }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2 shrink-0">
                        @if($item->status !== 'in_progress')
                        <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="in_progress">
                            <button type="submit" class="btn-outline text-xs py-1.5 px-3 border-amber-200 text-amber-800 dark:border-amber-800 dark:text-amber-200">In progress</button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="open">
                            <button type="submit" class="btn-outline text-xs py-1.5 px-3">Open</button>
                        </form>
                        @endif
                        <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="done">
                            <button type="submit" class="btn-primary text-xs py-1.5 px-3">Done</button>
                        </form>
                        <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="dismissed">
                            <button type="submit" class="btn-outline text-xs py-1.5 px-3">Dismiss</button>
                        </form>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($stylistDashboardScoped && $myDeskSubmissions->isNotEmpty())
        <div>
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Your open messages to management</h3>
            <ul class="space-y-2 text-sm">
                @foreach($myDeskSubmissions as $item)
                <li class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2">
                    <span class="font-medium text-heading">{{ $item->title }}</span>
                    <span class="text-xs text-muted"> · {{ $deskKindLabels[$item->kind] ?? $item->kind }}</span>
                    @if($item->body)
                        <p class="text-xs text-muted mt-1 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($item->body, 200) }}</p>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-7">

    {{-- Upcoming appointments --}}
    <div class="lg:col-span-2 card">
        <div class="card-header flex items-center justify-between">
            <h2 class="section-title">Upcoming Appointments</h2>
            <div class="flex items-center gap-3">
                <a href="{{ route('calendar') }}" class="text-link text-xs font-medium">Calendar</a>
                <a href="{{ route('appointments.index') }}" class="text-link text-sm font-medium">View all →</a>
            </div>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($upcomingAppointments as $apt)
            <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                     style="background-color: {{ $apt->staff?->color ?? '#7C3AED' }}">
                    {{ strtoupper(substr($apt->client?->first_name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-heading">
                        {{ $apt->client?->first_name }} {{ $apt->client?->last_name }}
                    </p>
                    <p class="text-xs text-muted truncate">
                        {{ $apt->services->pluck('service_name')->filter()->join(', ') ?: 'Appointment' }}
                        &middot; {{ $apt->staff?->name }}
                    </p>
                </div>
                <div class="text-right flex-shrink-0">
                    <p class="text-sm font-semibold text-body">@bizclock($apt->starts_at)</p>
                    <p class="text-xs text-muted">@bizshortdate($apt->starts_at)</p>
                </div>
                <a href="{{ route('appointments.show', $apt->id) }}"
                   class="flex-shrink-0 p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/30 text-muted hover:text-velour-600 dark:hover:text-velour-400">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @empty
            <div class="empty-state">
                <p class="empty-state-title">No upcoming appointments</p>
                <p class="empty-state-sub mt-1 max-w-sm">The <a href="{{ route('calendar') }}" class="text-link">calendar</a> and this list use the same schedule.</p>
                <a href="{{ route('appointments.create') }}" class="mt-3 text-link text-sm font-medium">
                    Book an appointment →
                </a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Right column --}}
    <div class="space-y-6">

        {{-- Weekly revenue chart --}}
        <div class="card p-6">
            <div class="flex items-center justify-between gap-2 mb-5">
                <h2 class="section-title">Revenue (7 days)</h2>
                <a href="{{ route('revenue.index') }}" class="text-link text-xs font-medium">Details</a>
            </div>
            <div class="flex items-end gap-1.5 h-24">
                @php $maxRev = max(collect($weeklyRevenue)->pluck('revenue')->max(), 1); @endphp
                @foreach($weeklyRevenue as $day)
                <div class="flex-1 flex flex-col items-center gap-1">
                    <div class="w-full rounded-t-md bg-velour-200 dark:bg-velour-800 hover:bg-velour-400 dark:hover:bg-velour-600 transition-colors"
                         style="height: {{ max(4, ($day['revenue'] / $maxRev) * 80) }}px"
                         title="@money($day['revenue'])"></div>
                    <span class="text-xs text-muted">{{ $day['date'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Recent sales --}}
        <div class="card">
            <div class="card-header flex items-center justify-between">
                <h2 class="section-title">Today's sales</h2>
                <a href="{{ route('pos.index') }}" class="text-link text-xs font-medium">POS</a>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($recentSales as $sale)
                <div class="flex items-center justify-between px-6 py-3.5">
                    <div>
                        <p class="text-sm font-medium text-body">
                            {{ $sale->client?->first_name ?? 'Walk-in' }} {{ $sale->client?->last_name }}
                        </p>
                        <p class="text-xs text-muted">{{ ($sale->completed_at ?? $sale->created_at)->diffForHumans() }}</p>
                    </div>
                    <span class="text-sm font-bold text-heading">@money($sale->total)</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <p class="text-sm text-muted">No completed POS sales yet today.</p>
                    <p class="text-xs text-muted mt-2">Record a sale in <a href="{{ route('pos.index') }}" class="text-link font-medium">Point of Sale</a> — revenue appears after checkout completes.</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection

@push('styles')
<style>
    .analytics-slider {
        scrollbar-width: thin;
        -webkit-overflow-scrolling: touch;
    }
    .analytics-slider::-webkit-scrollbar {
        height: 4px;
    }
    .analytics-slider::-webkit-scrollbar-thumb {
        background: rgb(156 163 175 / 0.4);
        border-radius: 2px;
    }
    .analytics-slider-arrow {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 48px;
        background: rgb(255 255 255 / 0.92);
        border: 1px solid rgb(229 231 235);
        color: rgb(107 114 128);
        box-shadow: 0 2px 8px rgb(0 0 0 / 0.08);
        cursor: pointer;
        transition: opacity 0.2s;
    }
    .dark .analytics-slider-arrow {
        background: rgb(31 41 55 / 0.92);
        border-color: rgb(55 65 81);
        color: rgb(209 213 219);
    }
    .analytics-slider-arrow:hover {
        color: rgb(124 58 237);
    }
</style>
@endpush

@push('scripts')
<script>
function analyticsSlider(serverData, detailLists, periodBounds) {
    return {
        period: 'today',
        active: null,
        data: serverData,
        lists: detailLists,
        bounds: periodBounds,
        periods: [
            { key: 'today', label: 'Today' },
            { key: 'weekly', label: 'Weekly' },
            { key: 'monthly', label: 'Monthly' },
            { key: 'yearly', label: 'Yearly' },
        ],
        widgets: [
            {
                key: 'appointments',
                label: 'Appointments',
                dataKey: 'appointments',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>',
                iconBg: 'bg-blue-100 text-blue-600 dark:bg-blue-900/40 dark:text-blue-300',
                format: (v) => String(v),
                sub: (d) => 'Excl. cancelled & no-show',
                link: @json(route('appointments.index')),
            },
            {
                key: 'pending_tasks',
                label: 'Pending Tasks',
                dataKey: 'pending_tasks',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>',
                iconBg: 'bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-300',
                format: (v) => String(v),
                sub: () => 'Open & in-progress',
                link: @json(route('tasks.index')),
            },
            {
                key: 'new_customers',
                label: 'New Customers',
                dataKey: 'new_customers',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
                iconBg: 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/40 dark:text-emerald-300',
                format: (v) => String(v),
                sub: (d) => d ? (d.new_bookings || 0) + ' new bookings' : '',
                link: @json(route('clients.index')),
            },
            {
                key: 'staff_status',
                label: 'Staff Status',
                dataKey: 'staff_active',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><circle cx="20" cy="7" r="4"/></svg>',
                iconBg: 'bg-indigo-100 text-indigo-600 dark:bg-indigo-900/40 dark:text-indigo-300',
                format: (v) => v + ' active',
                sub: (d) => (d?.staff_on_leave || 0) + ' on leave',
                link: @json(route('settings.index', ['tab' => 'team'])),
            },
            {
                key: 'website_traffic',
                label: 'Website Traffic',
                dataKey: 'website_visits',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>',
                iconBg: 'bg-cyan-100 text-cyan-600 dark:bg-cyan-900/40 dark:text-cyan-300',
                format: (v) => v > 0 ? String(v) : '—',
                sub: () => 'Not connected yet',
                link: @json(route('go-live')),
            },
            {
                key: 'reviews',
                label: 'Reviews',
                dataKey: 'reviews_count',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>',
                iconBg: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/40 dark:text-yellow-300',
                format: (v) => String(v),
                sub: (d) => d?.reviews_avg ? '★ ' + d.reviews_avg + ' avg' : 'No ratings yet',
                link: @json(route('reviews.index')),
            },
            {
                key: 'revenue',
                label: 'Revenue',
                dataKey: 'revenue',
                icon: '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
                iconBg: 'bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-300',
                format: (v) => '₹' + Number(v).toLocaleString('en-IN', { minimumFractionDigits: 0, maximumFractionDigits: 0 }),
                sub: () => 'Total for period',
                link: @json(route('revenue.index')),
            },
        ],
        filteredList(listKey) {
            const items = this.lists[listKey] || [];
            const startDate = this.bounds[this.period] || '';
            if (!startDate) return items;
            return items.filter(item => item.date >= startDate);
        },
        scrollLeft() {
            this.$refs.slider.scrollBy({ left: -266, behavior: 'smooth' });
        },
        scrollRight() {
            this.$refs.slider.scrollBy({ left: 266, behavior: 'smooth' });
        },
        activeLabel() {
            const w = this.widgets.find(x => x.key === this.active);
            return w ? w.label : '';
        },
        activeLink() {
            const w = this.widgets.find(x => x.key === this.active);
            return w ? w.link : '#';
        },
    };
}
</script>
@endpush
