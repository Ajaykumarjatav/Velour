@extends('layouts.app')

@section('title', 'Setup progress')
@section('page-title', 'Setup progress')

@section('content')
<div class="max-w-4xl mx-auto space-y-4 sm:space-y-5">
    {{-- Summary card --}}
    <div class="card overflow-hidden">
        <div class="card-body sm:p-6 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-lg sm:text-xl font-semibold text-heading tracking-tight">Setup progress</h1>
                <p class="text-sm text-muted mt-1">{{ $salon->name }} — onboarding status</p>
            </div>
            <a href="{{ route('go-live') }}" class="btn-outline text-sm shrink-0 rounded-xl">
                Open Go Live
            </a>
        </div>
        <div class="px-5 sm:px-6 pb-5 sm:pb-6 pt-0 border-t border-gray-100 dark:border-gray-800">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-body font-medium">Completion</span>
                <span class="text-heading font-semibold tabular-nums">{{ $percent }}%</span>
            </div>
            <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                <div class="h-2.5 rounded-full transition-all duration-300 {{ $percent >= 100 ? 'bg-green-500 dark:bg-green-400' : ($percent >= 60 ? 'bg-amber-500 dark:bg-amber-400' : 'bg-rose-500 dark:bg-rose-400') }}"
                     style="width: {{ min(100, max(0, $percent)) }}%"></div>
            </div>
            <p class="mt-2 text-xs text-muted">{{ $completed }} of {{ $total }} checkpoints completed</p>
        </div>
    </div>

    {{-- Checklist --}}
    <div class="card overflow-hidden">
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($items as $item)
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-4 py-4 sm:px-5 sm:py-5 min-h-[72px] hover:bg-gray-50/80 dark:hover:bg-gray-800/30 transition-colors duration-150">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-heading">{{ $item['label'] }}</p>
                        <p class="text-xs text-muted mt-0.5 capitalize">{{ $item['priority'] }} priority</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0 sm:pl-4">
                        @if($item['done'])
                            <span class="inline-flex items-center rounded-full bg-green-100 dark:bg-green-900/35 px-2.5 py-1 text-xs font-semibold text-green-800 dark:text-green-300 border border-green-200/80 dark:border-green-800/50">
                                Done
                            </span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/35 px-2.5 py-1 text-xs font-semibold text-amber-800 dark:text-amber-200 border border-amber-200/80 dark:border-amber-800/50">
                                Pending
                            </span>
                            <a href="{{ $item['link'] }}" class="btn-outline text-xs py-2 px-3 rounded-xl font-medium">
                                Fix
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
