@extends('layouts.app')
@section('title', 'Activity Log')
@section('page-title', 'Activity Log')

@section('content')
<div class="space-y-5 max-w-5xl mx-auto pb-10">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="font-serif text-2xl text-heading tracking-tight">Activity log</h1>
            <p class="text-sm text-muted mt-1">
                See who used the app and when — kept for {{ $retentionDays }} days (default view: last 30 days).
            </p>
        </div>
    </div>

    <form method="GET" action="{{ route('activity.index') }}" class="card p-4 flex flex-wrap gap-3 items-end">
        <div class="min-w-[10rem]">
            <label class="form-label text-xs">User</label>
            <select name="user_id" class="form-select text-sm">
                <option value="">All users</option>
                @foreach($teamUsers as $u)
                    <option value="{{ $u->id }}" @selected((string) $filterUserId === (string) $u->id)>
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="min-w-[8rem]">
            <label class="form-label text-xs">Type</label>
            <select name="action" class="form-select text-sm">
                <option value="">All</option>
                <option value="writes" @selected(request('action') === 'writes')>Changes only</option>
                <option value="views" @selected(request('action') === 'views')>Page views</option>
            </select>
        </div>
        <div class="flex-1 min-w-[12rem]">
            <label class="form-label text-xs">Search</label>
            <input type="search" name="q" value="{{ request('q') }}" placeholder="Label, email, path…" class="form-input text-sm">
        </div>
        <div class="w-full sm:w-auto sm:min-w-[14rem]">
            <label class="form-label text-xs">Date range</label>
            <x-date-range-picker :from-value="$from" :to-value="$to" class="w-full" />
        </div>
        <div class="flex gap-2">
            <a href="{{ route('activity.index') }}" class="btn-outline text-sm">Reset (30 days)</a>
            <button type="submit" class="btn-primary text-sm">Filter</button>
        </div>
    </form>

    @if($activities->isEmpty())
        <div class="card p-12 text-center text-muted text-sm">
            No activity in this range yet. Browse the app or make changes — usage will appear here.
        </div>
    @else
        <div class="space-y-6">
            @foreach($grouped as $date => $rows)
                <section class="card overflow-hidden">
                    <header class="px-4 py-2.5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-800/40 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-heading">
                            {{ \Carbon\Carbon::parse($date)->format('l, j F Y') }}
                        </h2>
                        <span class="text-xs text-muted">{{ $rows->count() }} events</span>
                    </header>
                    <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($rows as $row)
                            @php
                                $isWrite = $row->action === 'action.write';
                            @endphp
                            <li class="px-4 py-3 flex flex-col sm:flex-row sm:items-start gap-2 sm:gap-4 hover:bg-gray-50/60 dark:hover:bg-gray-800/30">
                                <div class="w-16 shrink-0 text-xs font-mono text-muted tabular-nums pt-0.5">
                                    {{ $row->occurred_at->format('H:i') }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="text-[10px] font-bold uppercase tracking-wide px-1.5 py-0.5 rounded
                                            {{ $isWrite ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200' : 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200' }}">
                                            {{ $isWrite ? 'Change' : 'View' }}
                                        </span>
                                        <p class="text-sm font-medium text-heading">{{ $row->label }}</p>
                                    </div>
                                    <p class="text-xs text-muted mt-0.5">
                                        <span class="font-medium text-body">{{ $row->user_name ?: 'User' }}</span>
                                        @if($row->user_email)
                                            · {{ $row->user_email }}
                                        @endif
                                        @if($row->method)
                                            · <span class="font-mono">{{ $row->method }}</span>
                                        @endif
                                        @if($row->ip_address)
                                            · {{ $row->ip_address }}
                                        @endif
                                    </p>
                                    @if($row->path)
                                        <p class="text-[11px] text-muted font-mono mt-0.5 truncate" title="{{ $row->path }}">{{ $row->path }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endforeach
        </div>

        <div class="mt-4">{{ $activities->links() }}</div>
    @endif

    @if($modelChanges->isNotEmpty())
        <div class="card p-4 sm:p-5 mt-8">
            <h2 class="text-sm font-semibold text-heading mb-3">Record changes (same period)</h2>
            <p class="text-xs text-muted mb-3">Created / updated / deleted rows from the data audit trail.</p>
            <ul class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($modelChanges as $activity)
                    <li class="py-2.5 flex flex-wrap gap-2 text-sm">
                        <span class="text-xs font-bold uppercase text-muted w-20">{{ $activity->event }}</span>
                        <span class="text-body">{{ class_basename($activity->subject_type ?? 'Record') }} #{{ $activity->subject_id }}</span>
                        <span class="text-xs text-muted">by {{ $activity->causer?->name ?? 'System' }} · {{ $activity->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
@endsection
