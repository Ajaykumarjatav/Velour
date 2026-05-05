@extends('layouts.app')
@section('title', 'Services')
@section('page-title', 'Services')

@section('header-actions')
    <a href="{{ route('services.create') }}"
       class="btn-primary inline-flex items-center gap-2 text-sm font-semibold px-4 py-2 rounded-xl shadow-md shadow-velour-600/20 dark:shadow-velour-900/25 active:scale-[0.97] transition-transform duration-150">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>+ Add Service</span>
    </a>
@endsection

@section('content')

@php
    $rulesPayload = $pricingRules->map(fn ($r) => [
        'title' => $r->title,
        'description' => $r->description ?? '',
        'adjustment_percent' => (int) $r->adjustment_percent,
        'enabled' => (bool) $r->enabled,
    ])->values()->all();

    $svcGradients = [
        'linear-gradient(145deg, #5b21b6 0%, #7c3aed 45%, #a78bfa 100%)',
        'linear-gradient(145deg, #4c1d95 0%, #6d28d9 50%, #818cf8 100%)',
        'linear-gradient(145deg, #6d28d9 0%, #8b5cf6 40%, #c4b5fd 100%)',
        'linear-gradient(145deg, #2e1065 0%, #5b21b6 55%, #a78bfa 100%)',
    ];

    $hasFilters = $search || $filterCategoryId || ($statusFilter ?? '') !== ''
        || request()->filled('price_min') || request()->filled('price_max')
        || request()->filled('duration_min') || request()->filled('duration_max');
@endphp

@push('styles')
<style type="text/tailwindcss">
    .svc-row {
        @apply grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_auto_auto] gap-x-5 gap-y-3 items-center
               min-h-[72px] py-4 px-5 border-b border-gray-100 dark:border-gray-800/90
               transition-all duration-200 ease-out
               hover:bg-gray-50/90 dark:hover:bg-gray-800/35
               hover:-translate-y-px hover:shadow-sm dark:hover:shadow-none;
    }
    .svc-row:last-child { border-bottom-width: 0; }
    .svc-icon-btn {
        @apply inline-flex h-9 w-9 items-center justify-center rounded-xl transition-all duration-150
               active:scale-95 focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900;
    }
    .svc-icon-btn--blue {
        @apply text-blue-600 dark:text-blue-400 bg-blue-50/90 dark:bg-blue-950/45 hover:bg-blue-100 dark:hover:bg-blue-900/55 focus:ring-blue-500/40;
    }
    .svc-icon-btn--neutral {
        @apply text-gray-600 dark:text-gray-400 bg-gray-100/80 dark:bg-gray-800/60 hover:bg-gray-200/90 dark:hover:bg-gray-700/70 focus:ring-gray-400/40;
    }
    .svc-icon-btn--danger {
        @apply text-red-600 dark:text-red-400 bg-red-50/90 dark:bg-red-950/35 hover:bg-red-100 dark:hover:bg-red-900/45 focus:ring-red-500/40;
    }
</style>
@endpush

<div class="space-y-8"
     x-data="{
        pricingOpen: false,
        variantOpen: false,
        variantUrl: '',
        variantTitle: '',
        vRows: [],
        aRows: [],
        rules: {{ \Illuminate\Support\Js::from($rulesPayload) }},
        catOpen: {{ \Illuminate\Support\Js::from($accordionOpen ?? []) }},
        toggleCat(id) { this.catOpen[id] = !this.catOpen[id]; },
        openPricing() { this.pricingOpen = true; },
        closePricing() { this.pricingOpen = false; },
        addRule() { this.rules.push({ title: '', description: '', adjustment_percent: 0, enabled: true }); },
        removeRule(i) { this.rules.splice(i, 1); },
        openVariants(url, title, variants, addons) {
            this.variantUrl = url;
            this.variantTitle = title;
            this.vRows = JSON.parse(JSON.stringify(variants.length ? variants : [{ name: '', price: '' }]));
            this.aRows = JSON.parse(JSON.stringify(addons.length ? addons : [{ name: '', price: '' }]));
            this.variantOpen = true;
        },
        closeVariants() { this.variantOpen = false; },
        addVRow() { this.vRows.push({ name: '', price: '' }); },
        removeVRow(i) { this.vRows.splice(i, 1); },
        addARow() { this.aRows.push({ name: '', price: '' }); },
        removeARow(i) { this.aRows.splice(i, 1); },
     }">

    <p class="text-sm text-muted">
        <span class="font-medium text-heading">{{ number_format($totalServices) }} services</span>
        <span class="text-muted"> — catalog for booking, POS, and reports.</span>
    </p>

    {{-- Search & filters --}}
    <div class="rounded-2xl border border-gray-200/90 dark:border-gray-700/80 bg-white/85 dark:bg-gray-900/45 backdrop-blur-sm p-5 sm:p-5 shadow-sm dark:shadow-none space-y-5">
        <form method="GET" action="{{ route('services.index') }}" class="space-y-4">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-end">
                <div class="lg:col-span-4 space-y-1.5">
                    <label for="svc-search" class="text-xs font-medium text-muted uppercase tracking-wide">Search</label>
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input id="svc-search" type="search" name="search" value="{{ $search }}" placeholder="Name or description…"
                               class="form-input pl-10 rounded-xl border-gray-200 dark:border-gray-600 w-full">
                    </div>
                </div>
                <div class="lg:col-span-3 space-y-1.5">
                    <label for="svc-category-trigger" class="text-xs font-medium text-muted uppercase tracking-wide">Category</label>
                    <x-searchable-select
                        id="svc-category"
                        name="category_id"
                        wrapper-class="w-full min-w-0"
                        :search-url="null"
                        search-placeholder="Search category…"
                        trigger-class="form-input rounded-xl border-gray-200 dark:border-gray-600 w-full">
                        <option value="">All categories</option>
                        @foreach($categoryChips as $chip)
                            <option value="{{ $chip->id }}" @selected((string) $filterCategoryId === (string) $chip->id)>
                                {{ $chip->name }}@if($chip->businessType) — {{ $chip->businessType->name }}@endif
                            </option>
                        @endforeach
                    </x-searchable-select>
                </div>
                <div class="lg:col-span-2 space-y-1.5">
                    <label for="svc-status-trigger" class="text-xs font-medium text-muted uppercase tracking-wide">Status</label>
                    <x-searchable-select
                        id="svc-status"
                        name="status"
                        wrapper-class="w-full min-w-0"
                        :search-url="null"
                        search-placeholder="Status…"
                        trigger-class="form-input rounded-xl border-gray-200 dark:border-gray-600 w-full">
                        <option value="" @selected(($statusFilter ?? '') === '')>All</option>
                        <option value="active" @selected(($statusFilter ?? '') === 'active')>Active</option>
                        <option value="inactive" @selected(($statusFilter ?? '') === 'inactive')>Inactive</option>
                    </x-searchable-select>
                </div>
                <div class="lg:col-span-3 grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted uppercase tracking-wide">Price min</label>
                        <input type="number" name="price_min" value="{{ request('price_min') }}" step="0.01" min="0" placeholder="0"
                               class="form-input rounded-xl border-gray-200 dark:border-gray-600 w-full text-sm">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-xs font-medium text-muted uppercase tracking-wide">Price max</label>
                        <input type="number" name="price_max" value="{{ request('price_max') }}" step="0.01" min="0" placeholder="∞"
                               class="form-input rounded-xl border-gray-200 dark:border-gray-600 w-full text-sm">
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-muted uppercase tracking-wide">Duration min (min)</label>
                    <input type="number" name="duration_min" value="{{ request('duration_min') }}" min="0" step="1" placeholder="0"
                           class="form-input rounded-xl border-gray-200 dark:border-gray-600 w-full text-sm">
                </div>
                <div class="space-y-1.5">
                    <label class="text-xs font-medium text-muted uppercase tracking-wide">Duration max (min)</label>
                    <input type="number" name="duration_max" value="{{ request('duration_max') }}" min="0" step="1" placeholder="∞"
                           class="form-input rounded-xl border-gray-200 dark:border-gray-600 w-full text-sm">
                </div>
                <div class="flex flex-wrap gap-2 sm:col-span-2 lg:col-span-2 lg:justify-end lg:pb-0.5">
                    <button type="submit" class="btn-primary rounded-xl px-5">Apply filters</button>
                    @if($hasFilters)
                        <a href="{{ route('services.index') }}" class="btn-outline rounded-xl px-5">Reset</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="flex flex-wrap gap-2 pt-1 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ route('service-packages.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-3 py-2 text-sm font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors duration-200">
                <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Packages
            </a>
            <a href="{{ route('service-categories.index') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-3 py-2 text-sm font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors duration-200">
                <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Categories
            </a>
            <button type="button" @click="openPricing()" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 dark:border-gray-600 px-3 py-2 text-sm font-medium text-body hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors duration-200">
                <svg class="w-4 h-4 text-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Dynamic pricing
            </button>
        </div>
    </div>

    <div class="space-y-6">
        @foreach($categories as $cat)
            <section class="rounded-2xl border border-gray-200/90 dark:border-gray-700/80 bg-white/90 dark:bg-gray-900/50 overflow-hidden shadow-sm dark:shadow-none transition-shadow duration-200 hover:shadow-md dark:hover:shadow-none">
                <button type="button"
                        @click="toggleCat({{ $cat->id }})"
                        class="w-full text-left px-4 py-3 sm:px-5 sm:py-3.5 bg-gradient-to-r from-velour-100/95 via-velour-50/80 to-transparent dark:from-velour-950/55 dark:via-velour-950/25 dark:to-transparent border-b border-gray-100 dark:border-gray-800/80 flex items-center justify-between gap-3 group transition-colors duration-200 hover:from-velour-100 dark:hover:from-velour-950/65">
                    <div class="min-w-0">
                        <h2 class="text-base sm:text-lg font-semibold text-heading tracking-tight text-velour-900 dark:text-velour-100 flex items-center gap-2 leading-snug">
                            <svg class="w-4 h-4 shrink-0 text-velour-600 dark:text-velour-400 transition-transform duration-200 opacity-75 group-hover:opacity-100"
                                 :class="catOpen[{{ $cat->id }}] ? 'rotate-0' : '-rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            {{ $cat->name }}
                        </h2>
                        @if($cat->businessType)
                            <p class="text-xs text-muted mt-0.5 pl-6 font-medium">{{ $cat->businessType->name }}</p>
                        @endif
                    </div>
                    <span class="inline-flex items-center rounded-full bg-white/95 dark:bg-gray-800/90 border border-velour-200/80 dark:border-velour-800/50 px-2.5 py-0.5 text-[11px] font-medium text-velour-800 dark:text-velour-200 tabular-nums shrink-0">
                        {{ $cat->services->count() }} {{ Str::plural('service', $cat->services->count()) }}
                    </span>
                </button>

                <div x-show="catOpen[{{ $cat->id }}]"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="divide-y divide-gray-100 dark:divide-gray-800/90">
                    @foreach($cat->services as $svc)
                        @php
                            $vList = $svc->normalizedVariants();
                            $aList = $svc->normalizedAddons();
                            $vCount = count($vList);
                            $aCount = count($aList);
                            $gi = abs(crc32((string) $svc->id)) % count($svcGradients);
                        @endphp
                        <div class="svc-row">
                            <div class="flex items-center gap-4 min-w-0">
                                @if($svc->image_url)
                                    <img src="{{ $svc->image_url }}" alt="" width="48" height="48" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0 shadow-sm">
                                @else
                                    <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center text-white shadow-md ring-1 ring-black/10 dark:ring-white/10"
                                         style="background: {{ $svcGradients[$gi] }}">
                                        <svg class="w-6 h-6 opacity-95" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <h3 class="font-normal text-base text-heading leading-snug">{{ $svc->name }}</h3>
                                        @if($svc->dynamic_pricing_enabled)
                                            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/50 text-amber-800 dark:text-amber-200">Dynamic</span>
                                        @endif
                                        @if(($svc->service_location ?? 'onsite') === 'home')
                                            <span class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/45 text-violet-800 dark:text-violet-200">Home</span>
                                        @endif
                                    </div>
                                    @if($svc->description)
                                        <p class="text-sm text-muted line-clamp-2 font-normal">{{ $svc->description }}</p>
                                    @endif
                                    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted pt-0.5">
                                        <span class="inline-flex items-center gap-1.5 font-normal">
                                            <svg class="w-3.5 h-3.5 opacity-70 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            {{ $svc->duration_minutes }} min
                                        </span>
                                        @if($vCount > 0)
                                            <button type="button" class="text-velour-600 dark:text-velour-400 hover:underline font-medium text-xs"
                                                    @click="openVariants({{ \Illuminate\Support\Js::from(route('services.variants', $svc)) }}, {{ \Illuminate\Support\Js::from($svc->name) }}, {{ \Illuminate\Support\Js::from($vList) }}, {{ \Illuminate\Support\Js::from($aList) }})">
                                                {{ $vCount }} {{ Str::plural('variant', $vCount) }}
                                            </button>
                                        @endif
                                        @if($aCount > 0)
                                            <button type="button" class="text-velour-600 dark:text-velour-400 hover:underline font-medium text-xs"
                                                    @click="openVariants({{ \Illuminate\Support\Js::from(route('services.variants', $svc)) }}, {{ \Illuminate\Support\Js::from($svc->name) }}, {{ \Illuminate\Support\Js::from($vList) }}, {{ \Illuminate\Support\Js::from($aList) }})">
                                                {{ $aCount }} add-on{{ $aCount !== 1 ? 's' : '' }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-between lg:justify-end gap-3 lg:border-l lg:border-gray-100 lg:dark:border-gray-800 lg:pl-5">
                                <p class="text-base font-medium text-muted tabular-nums lg:text-right">@money($svc->price)</p>
                                <span class="{{ $svc->is_active ? 'badge-green' : 'badge-gray' }} shrink-0">
                                    {{ $svc->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="flex items-center justify-end gap-2 lg:pl-2">
                                <button type="button"
                                        class="svc-icon-btn svc-icon-btn--blue"
                                        title="Variants"
                                        aria-label="Edit variants and add-ons"
                                        @click="openVariants({{ \Illuminate\Support\Js::from(route('services.variants', $svc)) }}, {{ \Illuminate\Support\Js::from($svc->name) }}, {{ \Illuminate\Support\Js::from($vList) }}, {{ \Illuminate\Support\Js::from($aList) }})">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </button>
                                <a href="{{ route('services.edit', $svc->id) }}"
                                   class="svc-icon-btn svc-icon-btn--neutral"
                                   title="Edit"
                                   aria-label="Edit service">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <form action="{{ route('services.destroy', $svc->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Delete this service?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="svc-icon-btn svc-icon-btn--danger" title="Delete" aria-label="Delete service">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        @if($uncategorised->count())
            <section class="rounded-2xl border border-dashed border-amber-300/80 dark:border-amber-700/60 bg-white/70 dark:bg-gray-900/40 overflow-hidden">
                <div class="px-4 py-3 sm:px-5 sm:py-3.5 border-b border-gray-100 dark:border-gray-800/80 bg-amber-50/60 dark:bg-amber-950/25">
                    <h2 class="text-base sm:text-lg font-semibold text-heading tracking-tight">Uncategorised</h2>
                    <p class="text-xs text-muted mt-0.5 font-medium">Assign a category when you can.</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-gray-800/90">
                    @foreach($uncategorised as $svc)
                        @php $gi = abs(crc32((string) $svc->id)) % count($svcGradients); @endphp
                        <div class="svc-row">
                            <div class="flex items-center gap-4 min-w-0">
                                @if($svc->image_url)
                                    <img src="{{ $svc->image_url }}" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-600 flex-shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-xl flex-shrink-0 flex items-center justify-center text-white shadow-md" style="background: {{ $svcGradients[$gi] }}">
                                        <svg class="w-6 h-6 opacity-95" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <h3 class="font-normal text-base text-heading">{{ $svc->name }}</h3>
                                    <p class="text-xs text-muted mt-1 inline-flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        {{ $svc->duration_minutes }} min
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between lg:justify-end gap-3 lg:border-l lg:border-gray-100 lg:dark:border-gray-800 lg:pl-5">
                                <p class="text-base font-medium text-muted tabular-nums">@money($svc->price)</p>
                                @if(($svc->service_location ?? 'onsite') === 'home')
                                    <span class="text-[10px] font-semibold uppercase px-2 py-0.5 rounded-full bg-violet-100 dark:bg-violet-900/45 text-violet-800 dark:text-violet-200">Home</span>
                                @endif
                            </div>
                            <div class="flex justify-end">
                                <a href="{{ route('services.edit', $svc->id) }}" class="svc-icon-btn svc-icon-btn--neutral" title="Edit" aria-label="Edit service">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($totalServices === 0)
            <div class="card">
                <div class="empty-state">
                    <p class="empty-state-title">No services yet</p>
                    <a href="{{ route('services.create') }}" class="btn-primary mt-4">Add your first service</a>
                </div>
            </div>
        @elseif($categories->isEmpty() && $uncategorised->isEmpty())
            <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white/80 dark:bg-gray-900/40 p-10 text-center">
                <p class="text-heading font-medium">No services match your filters</p>
                <p class="text-sm text-muted mt-2">Try adjusting search, category, or filters.</p>
                <a href="{{ route('services.index') }}" class="btn-outline mt-6 inline-flex rounded-xl">Reset filters</a>
            </div>
        @endif
    </div>

    {{-- Dynamic pricing modal --}}
    <div x-show="pricingOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="closePricing()">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 space-y-4"
             @click.outside="closePricing()">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-heading">Dynamic Pricing Rules</h3>
                <button type="button" class="text-muted hover:text-heading" @click="closePricing()" aria-label="Close">&times;</button>
            </div>
            <p class="text-xs text-muted bg-blue-50 dark:bg-blue-900/20 border border-blue-100 dark:border-blue-800 rounded-xl px-3 py-2">
                Dynamic pricing automatically adjusts service prices based on time, staff level, and demand. Rules are stored for your salon; connect them to checkout in a future release.
            </p>
            <form action="{{ route('services.pricing-rules') }}" method="POST" class="space-y-3">
                @csrf
                @method('PUT')
                <template x-for="(rule, idx) in rules" :key="idx">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-3 space-y-2">
                        <div class="flex justify-between gap-2">
                            <input type="text" :name="'rules['+idx+'][title]'" x-model="rule.title" class="form-input text-sm flex-1" placeholder="Rule title">
                            <input type="number" :name="'rules['+idx+'][adjustment_percent]'" x-model="rule.adjustment_percent" class="form-input w-24 text-sm" placeholder="%">
                        </div>
                        <input type="text" :name="'rules['+idx+'][description]'" x-model="rule.description" class="form-input text-sm" placeholder="Condition description">
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="hidden" :name="'rules['+idx+'][enabled]'" :value="rule.enabled ? 1 : 0">
                                <input type="checkbox" :checked="rule.enabled" @change="rule.enabled = $event.target.checked" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                Enabled
                            </label>
                            <button type="button" class="text-xs text-red-500 hover:underline" @click="removeRule(idx)">Remove</button>
                        </div>
                    </div>
                </template>
                <button type="button" class="btn-outline w-full text-sm" @click="addRule()">+ New Rule</button>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline" @click="closePricing()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Rules</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Variants / add-ons modal --}}
    <div x-show="variantOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="closeVariants()">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 space-y-4"
             @click.outside="closeVariants()">
            <div class="flex items-center justify-between gap-2">
                <h3 class="text-lg font-semibold text-heading">Edit Variants — <span x-text="variantTitle"></span></h3>
                <button type="button" class="text-muted hover:text-heading" @click="closeVariants()" aria-label="Close">&times;</button>
            </div>
            <form method="POST" x-bind:action="variantUrl" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-heading">Variants</span>
                        <button type="button" class="text-xs text-velour-600 dark:text-velour-400 hover:underline" @click.prevent="addVRow()">+ Add variant</button>
                    </div>
                    <template x-for="(row, idx) in vRows" :key="'v'+idx">
                        <div class="flex flex-wrap gap-2 mb-2 items-end">
                            <input type="text" class="form-input flex-1 min-w-[6rem] text-sm" :name="'variants['+idx+'][name]'" x-model="row.name" placeholder="Name">
                            <input type="number" class="form-input w-24 text-sm" :name="'variants['+idx+'][price]'" x-model="row.price" min="0" step="0.01" placeholder="Price">
                            <button type="button" class="text-xs text-red-500 hover:underline pb-2" @click.prevent="removeVRow(idx)">Remove</button>
                        </div>
                    </template>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-heading">Add-ons</span>
                        <button type="button" class="text-xs text-velour-600 dark:text-velour-400 hover:underline" @click.prevent="addARow()">+ Add add-on</button>
                    </div>
                    <template x-for="(row, idx) in aRows" :key="'a'+idx">
                        <div class="flex flex-wrap gap-2 mb-2 items-end">
                            <input type="text" class="form-input flex-1 min-w-[6rem] text-sm" :name="'addons['+idx+'][name]'" x-model="row.name" placeholder="Name">
                            <input type="number" class="form-input w-24 text-sm" :name="'addons['+idx+'][price]'" x-model="row.price" min="0" step="0.01" placeholder="Price">
                            <button type="button" class="text-xs text-red-500 hover:underline pb-2" @click.prevent="removeARow(idx)">Remove</button>
                        </div>
                    </template>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline" @click="closeVariants()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Variants</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
