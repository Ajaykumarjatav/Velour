@extends('layouts.app')
@section('title', $facility->name)
@section('page-title', $facility->name)

@section('content')
<div class="max-w-3xl space-y-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted">{{ $facility->category }} · {{ \App\Models\Facility::kindOptions()[$facility->kind] ?? $facility->kind }}</p>
        <div class="flex flex-wrap gap-2">
            @can('update', $facility)
                <a href="{{ route('facilities.edit', $facility) }}" class="btn-outline btn-sm">Edit</a>
            @endcan
            <a href="{{ route('facilities.index') }}" class="text-link text-sm font-medium">← All facilities</a>
        </div>
    </div>

    <div class="card p-6">
        @php
            $badge = match ($facility->status) {
                \App\Models\Facility::STATUS_OPERATIONAL => 'badge-green',
                \App\Models\Facility::STATUS_IN_USE => 'badge-blue',
                \App\Models\Facility::STATUS_MAINTENANCE => 'badge-yellow',
                default => 'badge-gray',
            };
        @endphp
        <div class="flex items-start justify-between gap-3 mb-4">
            <h2 class="text-lg font-semibold text-heading">{{ $facility->name }}</h2>
            <span class="{{ $badge }} shrink-0">{{ \App\Models\Facility::statusOptions()[$facility->status] ?? $facility->status }}</span>
        </div>
        @if($facility->occupancy_capacity > 0)
            @php $pct = $facility->occupancyPercent(); @endphp
            <div class="mb-4">
                <div class="flex justify-between text-xs text-muted mb-1">
                    <span>Occupancy</span>
                    <span class="tabular-nums">{{ $facility->occupancy_current }} / {{ $facility->occupancy_capacity }} ({{ $pct }}%)</span>
                </div>
                <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                    <div class="h-full rounded-full bg-emerald-500 dark:bg-emerald-600 transition-all" style="width: {{ min(100, $pct) }}%"></div>
                </div>
            </div>
        @endif
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-muted">Last maintenance</dt>
                <dd class="mt-1 font-medium text-heading">{{ $facility->last_maintenance_on?->format('j M Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-[11px] uppercase tracking-wide text-muted">Next maintenance</dt>
                <dd class="mt-1 font-medium text-heading">{{ $facility->next_maintenance_on?->format('j M Y') ?? '—' }}</dd>
            </div>
        </dl>
        @if(!empty($facility->equipment_features))
            <div class="mt-5">
                <p class="text-[11px] uppercase tracking-wide text-muted mb-2">Equipment &amp; features</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($facility->equipment_features as $tag)
                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
        @endif
        @if($facility->notes)
            <div class="mt-5 pt-5 border-t border-gray-100 dark:border-gray-800">
                <p class="text-[11px] uppercase tracking-wide text-muted mb-1">Notes</p>
                <p class="text-sm text-body whitespace-pre-line">{{ $facility->notes }}</p>
            </div>
        @endif
    </div>

    @can('delete', $facility)
    <div class="card p-5 border-red-200/80 dark:border-red-900/50">
        <p class="text-sm font-medium text-red-800 dark:text-red-300">Remove this facility</p>
        <p class="text-xs text-muted mt-1 mb-3">This cannot be undone.</p>
        <form action="{{ route('facilities.destroy', $facility) }}" method="POST" onsubmit="return confirm('Delete this facility?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-outline text-sm border-red-300 text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-400 dark:hover:bg-red-950/30">Delete</button>
        </form>
    </div>
    @endcan
</div>
@endsection
