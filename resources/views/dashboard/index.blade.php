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

<div class="alert-info mb-7 text-sm flex flex-wrap items-start justify-between gap-3">
    <p>Figures use your salon timezone (<abbr title="{{ $salon->timezone ?? 'UTC' }}">{{ $tzAbbr }}</abbr>). Revenue is counted when a POS sale is completed (see <a href="{{ route('reports.show', ['type' => 'revenue', 'from' => \App\Support\SalonTime::monthStartDateString($salon), 'to' => \App\Support\SalonTime::todayDateString($salon)]) }}" class="underline font-semibold">Revenue report</a>).</p>
</div>

{{-- KPI cards (today's POS total + list: see "Today's sales" in the right column only) --}}
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-5 mb-7">
    <div class="stat-card">
        <p class="stat-label">Month Revenue</p>
        <p class="stat-value">@money($monthRevenue)</p>
        @if($revenueChange !== null && $lastMonthRevenue > 0)
            <p class="text-xs mt-1 {{ $revenueChange >= 0 ? 'text-green-500' : 'text-red-500' }}">
                {{ $revenueChange >= 0 ? '▲' : '▼' }} {{ abs($revenueChange) }}% vs last month
            </p>
            <p class="stat-sub">Last month total @money($lastMonthRevenue)</p>
        @elseif($lastMonthRevenue > 0)
            <p class="text-xs mt-1 text-muted">Last month @money($lastMonthRevenue)</p>
        @else
            <p class="text-xs mt-1 text-muted">No prior month to compare</p>
        @endif
    </div>
    <div class="stat-card">
        <p class="stat-label">Today's Appointments</p>
        <p class="stat-value">{{ $todayAppointments }}</p>
        <p class="stat-sub">Scheduled today (excl. cancelled / no-show)</p>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Clients</p>
        <p class="stat-value">{{ number_format($totalClients) }}</p>
        <p class="text-xs text-green-500 mt-1">+{{ $newClientsThisMonth }} this month</p>
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
        <p class="text-sm text-muted">No pending leave, tasks, or staff messages. Add a reminder below anytime.</p>
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

        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
            @if($canManageDesk)
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Add</h3>
            <form action="{{ route('action-items.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @csrf
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Type</label>
                    <select name="kind" class="form-select text-sm">
                        <option value="{{ \App\Models\SalonActionItem::KIND_ADMIN_TODO }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_ADMIN_TODO] ?? 'To-do' }}</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_STAFF_SUGGESTION }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_STAFF_SUGGESTION] ?? 'Suggestion' }}</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_INVENTORY_REQUEST }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_INVENTORY_REQUEST] ?? 'Product' }}</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_GENERAL }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_GENERAL] ?? 'General' }}</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Priority</label>
                    <select name="priority" class="form-select text-sm">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Assign <span class="text-muted font-normal">(optional)</span></label>
                    <select name="assigned_staff_id" class="form-select text-sm">
                        <option value="">—</option>
                        @foreach($deskStaffForAssign as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Due <span class="text-muted font-normal">(optional)</span></label>
                    <input type="date" name="due_at" class="form-input text-sm">
                </div>
                <div class="md:col-span-12">
                    <label class="form-label text-xs">Title</label>
                    <input type="text" name="title" required maxlength="200" class="form-input text-sm" placeholder="e.g. Order colour tubes, Call supplier…">
                </div>
                <div class="md:col-span-12">
                    <label class="form-label text-xs">Details <span class="text-muted font-normal">(optional)</span></label>
                    <textarea name="body" rows="2" maxlength="5000" class="form-textarea text-sm" placeholder="Extra context…"></textarea>
                </div>
                <div class="md:col-span-12">
                    <button type="submit" class="btn-primary text-sm">Save to action center</button>
                </div>
                @foreach(['kind','title','body','priority','assigned_staff_id','due_at'] as $f)
                    @error($f)<p class="md:col-span-12 text-xs text-red-600">{{ $message }}</p>@enderror
                @endforeach
            </form>
            @else
            <h3 class="text-xs font-semibold uppercase tracking-wide text-muted mb-3">Message management</h3>
            <form action="{{ route('action-items.store') }}" method="POST" class="space-y-3 max-w-xl">
                @csrf
                <div>
                    <label class="form-label text-xs">Type</label>
                    <select name="kind" class="form-select text-sm">
                        <option value="{{ \App\Models\SalonActionItem::KIND_STAFF_SUGGESTION }}">Suggestion / feedback</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_INVENTORY_REQUEST }}">Product or supplies needed</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_GENERAL }}">General message</option>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs">Subject</label>
                    <input type="text" name="title" required maxlength="200" class="form-input text-sm" placeholder="Short summary">
                </div>
                <div>
                    <label class="form-label text-xs">Details</label>
                    <textarea name="body" rows="3" maxlength="5000" class="form-textarea text-sm" placeholder="What do you need or want to suggest?"></textarea>
                </div>
                <button type="submit" class="btn-primary text-sm">Send</button>
                @foreach(['kind','title','body'] as $f)
                    @error($f)<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @endforeach
            </form>
            @endif
        </div>
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
                        {{ $apt->services->pluck('service.name')->filter()->join(', ') ?: 'Appointment' }}
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
