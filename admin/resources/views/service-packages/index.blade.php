@extends('layouts.app')
@section('title', 'Plans/Packages')
@section('page-title', 'Plans/Packages')
@section('content')

@php
    $section = $section ?? 'packages';
@endphp

<div class="space-y-8 pb-8" x-data="{ tierModal: null, openTier(m) { this.tierModal = m } }" x-on:keydown.escape.window="tierModal=null">

    @if(session('success'))
    <div class="px-4 py-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-2xl border border-velour-200/60 dark:border-velour-800/80 bg-gradient-to-br from-velour-50 via-white to-violet-50/90 dark:from-gray-900 dark:via-gray-900/95 dark:to-velour-950/40 px-6 py-8 sm:px-10 sm:py-10 shadow-sm dark:shadow-none">
        <div class="relative flex flex-col lg:flex-row lg:items-end lg:justify-between gap-6">
            <div class="max-w-2xl space-y-3">
                <h2 class="text-2xl sm:text-3xl font-bold text-heading tracking-tight">Plans/Packages</h2>
                <p class="text-sm sm:text-base text-body leading-relaxed">
                    Bundle services into packages, and manage <strong class="text-heading">loyalty plans</strong> assigned to clients on the Clients page.
                </p>
            </div>
            @if($section === 'packages')
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 shrink-0">
                @can('create', \App\Models\ServicePackage::class)
                    <a href="{{ route('service-packages.create') }}" class="btn-primary inline-flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Create package
                    </a>
                @endcan
                <a href="{{ route('services.index') }}" class="btn-outline inline-flex items-center justify-center gap-2">Browse services</a>
            </div>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="inline-flex p-1 rounded-xl bg-gray-100 dark:bg-gray-800/90 border border-gray-200/90 dark:border-gray-700 gap-0.5">
        <a href="{{ route('service-packages.index') }}"
           class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $section === 'packages' ? 'bg-velour-600 text-white shadow-sm' : 'text-body hover:bg-white/80 dark:hover:bg-gray-700/60' }}">
            Service packages
        </a>
        <a href="{{ route('service-packages.index', ['section' => 'loyalty']) }}"
           class="px-4 py-2 text-sm font-semibold rounded-lg transition-all {{ $section === 'loyalty' ? 'bg-velour-600 text-white shadow-sm' : 'text-body hover:bg-white/80 dark:hover:bg-gray-700/60' }}">
            Loyalty plans
        </a>
    </div>

    @if($section === 'loyalty')
        @include('service-packages.partials.loyalty-plans')
    @else
        @if($packages->isEmpty())
            <div class="relative overflow-hidden rounded-2xl border border-dashed border-gray-300 dark:border-gray-600 bg-gray-50/50 dark:bg-gray-800/30 px-8 py-14 text-center">
                <h3 class="text-lg font-semibold text-heading mb-2">No packages yet</h3>
                <p class="text-muted text-sm max-w-md mx-auto mb-8 leading-relaxed">
                    Create your first package to bundle two or more services with a bundle price.
                </p>
                @can('create', \App\Models\ServicePackage::class)
                    <a href="{{ route('service-packages.create') }}" class="btn-primary">Create your first package</a>
                @endcan
            </div>
        @else
            <div>
                <p class="text-sm text-muted mb-5">
                    <span class="font-medium text-heading">{{ $packages->count() }}</span> {{ Str::plural('package', $packages->count()) }}
                </p>
                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-2">
                    @foreach($packages as $pkg)
                        @php
                            $hasSavings = $pkg->services_sum_price !== null && (float) $pkg->services_sum_price > (float) $pkg->price;
                            $savingsPct = $hasSavings && (float) $pkg->services_sum_price > 0
                                ? round(100 - ((float) $pkg->price / (float) $pkg->services_sum_price) * 100)
                                : null;
                        @endphp
                        <article class="group relative flex flex-col rounded-2xl border border-gray-200/80 dark:border-gray-700/80 bg-white dark:bg-gray-900/60 p-6 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <h3 class="font-bold text-heading text-lg truncate">{{ $pkg->name }}</h3>
                                    <p class="text-xs text-muted mt-1">{{ $pkg->services_count }} {{ Str::plural('service', $pkg->services_count) }}</p>
                                </div>
                                <p class="text-xl font-bold tabular-nums text-velour-600 dark:text-velour-400 shrink-0">
                                    {{ \App\Helpers\CurrencyHelper::format((float) $pkg->price, $salon->currency ?? 'GBP') }}
                                </p>
                            </div>
                            <div class="mt-4 flex flex-wrap gap-2">
                                @can('update', $pkg)
                                    <a href="{{ route('service-packages.edit', $pkg) }}" class="btn-primary btn-sm">Edit</a>
                                @endcan
                                @can('delete', $pkg)
                                    <form action="{{ route('service-packages.destroy', $pkg) }}" method="POST" class="inline" onsubmit="return confirm('Remove this package?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-outline btn-sm text-red-600 dark:text-red-400">Delete</button>
                                    </form>
                                @endcan
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>

@endsection
