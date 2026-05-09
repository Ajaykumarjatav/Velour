@extends('layouts.app')
@section('title', 'Facilities')
@section('page-title', 'Facilities')

@section('content')
<div class="space-y-7">
    <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
        <div>
            <p class="text-sm text-muted mt-1 max-w-xl">Rooms, stations, and areas for <span class="font-medium text-heading">{{ $salon->name }}</span>. Track occupancy and maintenance in one place.</p>
        </div>
        <form action="{{ route('facilities.index') }}" method="GET" class="flex flex-1 flex-wrap items-center gap-2 min-w-0 lg:max-w-md lg:flex-initial">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search by name or category…" class="form-input flex-1 min-w-0">
            <button type="submit" class="btn-secondary shrink-0">Search</button>
            @if($search)
                <a href="{{ route('facilities.index') }}" class="btn-outline shrink-0">Clear</a>
            @endif
        </form>
        @can('create', \App\Models\Facility::class)
            <a href="{{ route('facilities.create') }}" class="btn-primary shrink-0 w-full lg:w-auto text-center whitespace-nowrap">+ Add facility</a>
        @endcan
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card p-4 sm:p-5 border-l-4 border-l-gray-400 dark:border-l-gray-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Total</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $total }}</p>
        </div>
        <div class="card p-4 sm:p-5 border-l-4 border-l-emerald-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Operational</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $operational }}</p>
        </div>
        <div class="card p-4 sm:p-5 border-l-4 border-l-amber-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Under maintenance</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $underMaintenance }}</p>
        </div>
        <div class="card p-4 sm:p-5 border-l-4 border-l-velour-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Avg. occupancy</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $avgOccupancy !== null ? $avgOccupancy.'%' : '—' }}</p>
        </div>
    </div>

    @if($facilities->isEmpty())
        <div class="card p-10 text-center">
            <p class="text-sm text-muted">No facilities yet.</p>
            @can('create', \App\Models\Facility::class)
                <a href="{{ route('facilities.create') }}" class="btn-primary mt-4 inline-flex">Add your first facility</a>
            @endcan
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-5">
            @foreach($facilities as $f)
                @php
                    $iconTone = match ($f->kind) {
                        'styling_floor' => 'bg-violet-100 dark:bg-violet-900/35 text-violet-700 dark:text-violet-300',
                        'wash_station' => 'bg-sky-100 dark:bg-sky-900/35 text-sky-700 dark:text-sky-300',
                        'treatment_room' => 'bg-emerald-100 dark:bg-emerald-900/35 text-emerald-700 dark:text-emerald-300',
                        'spa_suite' => 'bg-rose-100 dark:bg-rose-900/35 text-rose-700 dark:text-rose-300',
                        'retail' => 'bg-amber-100 dark:bg-amber-900/35 text-amber-800 dark:text-amber-200',
                        default => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
                    };
                    $statusBadge = match ($f->status) {
                        \App\Models\Facility::STATUS_OPERATIONAL => 'badge-green',
                        \App\Models\Facility::STATUS_IN_USE => 'badge-blue',
                        \App\Models\Facility::STATUS_MAINTENANCE => 'badge-yellow',
                        default => 'badge-gray',
                    };
                    $pct = $f->occupancy_capacity > 0 ? $f->occupancyPercent() : null;
                @endphp
                <article class="card flex flex-col overflow-hidden shadow-sm dark:shadow-none border border-gray-200 dark:border-gray-800">
                    <div class="p-5 flex-1 flex flex-col gap-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center shrink-0 {{ $iconTone }}">
                                    @include('facilities._kind-icon', ['kind' => $f->kind])
                                </div>
                                <div class="min-w-0">
                                    <h2 class="font-semibold text-heading leading-snug truncate">{{ $f->name }}</h2>
                                    <p class="text-xs text-muted mt-0.5">{{ $f->category }}</p>
                                </div>
                            </div>
                            <span class="{{ $statusBadge }} shrink-0 text-[10px]">{{ \App\Models\Facility::statusOptions()[$f->status] ?? $f->status }}</span>
                        </div>

                        @if($pct !== null)
                            <div>
                                <div class="flex justify-between text-[11px] text-muted mb-1.5">
                                    <span>Occupancy</span>
                                    <span class="tabular-nums text-body font-medium">{{ $f->occupancy_current }} / {{ $f->occupancy_capacity }}</span>
                                </div>
                                <div class="h-2 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                                    <div class="h-full rounded-full bg-emerald-500 dark:bg-emerald-500/90 transition-all" style="width: {{ min(100, $pct) }}%"></div>
                                </div>
                            </div>
                        @endif

                        @if(!empty($f->equipment_features))
                            <div>
                                <p class="text-[10px] font-semibold uppercase tracking-wide text-muted mb-2">Equipment &amp; features</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach(array_slice($f->equipment_features, 0, 6) as $tag)
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-50 dark:bg-gray-800/80 text-body border border-gray-200/80 dark:border-gray-700">{{ \Illuminate\Support\Str::limit($tag, 42) }}</span>
                                    @endforeach
                                    @if(count($f->equipment_features) > 6)
                                        <span class="text-[11px] text-muted">+{{ count($f->equipment_features) - 6 }} more</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-2 gap-3 text-xs pt-1 border-t border-gray-100 dark:border-gray-800">
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-muted">Last maintenance</p>
                                <p class="font-medium text-heading mt-0.5">{{ $f->last_maintenance_on?->format('j M Y') ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] uppercase tracking-wide text-muted">Next maintenance</p>
                                <p class="font-medium text-heading mt-0.5">{{ $f->next_maintenance_on?->format('j M Y') ?? '—' }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-5 py-3.5 bg-gray-50/80 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-800 flex flex-wrap gap-2">
                        <a href="{{ route('facilities.show', $f) }}" class="btn-primary text-sm flex-1 min-w-[6rem] text-center">View</a>
                        @can('delete', $f)
                            <form action="{{ route('facilities.destroy', $f) }}" method="POST" class="inline flex-1 min-w-[6rem]" onsubmit="return confirm('Delete ' + @json($f->name) + '?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-outline text-sm w-full border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900/60 dark:text-red-400 dark:hover:bg-red-950/25">Delete</button>
                            </form>
                        @endcan
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
