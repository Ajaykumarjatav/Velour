@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6 shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-slate-900">Setup Progress</h1>
                <p class="text-sm text-slate-600 mt-1">{{ $salon->name }} onboarding status</p>
            </div>
            <a href="{{ route('go-live') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
                Open Go Live
            </a>
        </div>

        <div class="mt-5">
            <div class="flex items-center justify-between text-sm mb-2">
                <span class="text-slate-700 font-medium">Completion</span>
                <span class="text-slate-900 font-semibold">{{ $percent }}%</span>
            </div>
            <div class="h-2.5 w-full rounded-full bg-slate-200 overflow-hidden">
                <div class="h-2.5 rounded-full {{ $percent >= 100 ? 'bg-green-500' : ($percent >= 60 ? 'bg-amber-500' : 'bg-rose-500') }}" style="width: {{ $percent }}%"></div>
            </div>
            <p class="mt-2 text-xs text-slate-500">{{ $completed }} of {{ $total }} checkpoints completed</p>
        </div>
    </div>

    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-2 sm:p-3 shadow-sm">
        <div class="divide-y divide-slate-100">
            @foreach($items as $item)
                <div class="flex items-center justify-between gap-3 px-3 py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-900">{{ $item['label'] }}</p>
                        <p class="text-xs text-slate-500">{{ ucfirst($item['priority']) }} priority</p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($item['done'])
                            <span class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-700">Done</span>
                        @else
                            <span class="inline-flex items-center rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-700">Pending</span>
                            <a href="{{ $item['link'] }}" class="inline-flex items-center rounded-lg border border-slate-300 px-2.5 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Fix</a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

