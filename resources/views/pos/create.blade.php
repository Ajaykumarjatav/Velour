@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@php
    $prefillFromAppointment = $prefillFromAppointment ?? null;
    $sym     = \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP');
    $taxRate = 18;
    $allServices = $services->map(fn($s) => [
        'id'        => $s->id,
        'name'      => $s->name,
        'price'     => (float) $s->price,
        'duration'  => $s->duration_minutes,
        'cat'       => $s->category?->name ?? 'Other',
        'type'      => 'service',
        'variants'  => $s->normalizedVariants(),
        'addons'    => $s->normalizedAddons(),
    ])->values()->toArray();
    $allProducts = $products->map(fn($p) => [
        'id'    => $p->id,
        'name'  => $p->name,
        'price' => (float) $p->retail_price,
        'qty'   => (int) $p->stock_quantity,
        'cat'   => $p->category?->name ?? 'Retail',
        'type'  => 'product',
    ])->values()->toArray();
    $allItems = array_merge($allServices, $allProducts);
    $serviceCategories = collect($allServices)
        ->countBy('cat')->sortDesc()->keys()->values()->toArray();
    $retailCategories = collect($allProducts)
        ->countBy('cat')->sortDesc()->keys()->values()->toArray();
    $serviceCategoryCounts = collect($allServices)->countBy('cat')->all();
    $retailCategoryCounts   = collect($allProducts)->countBy('cat')->all();
    $defaultSection = request('tab') === 'retail' ? 'product' : 'service';
    $retailProductCount = count($allProducts);
@endphp

@push('styles')
<style>
    .pos-shell { min-height: calc(100dvh - 3.25rem); }
    @media (min-width: 1024px) {
        .pos-shell { height: calc(100dvh - 4rem); min-height: calc(100dvh - 4rem); }
    }
    .pos-sidebar-scroll::-webkit-scrollbar { width: 4px; }
    .pos-sidebar-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 9999px; }
    .dark .pos-sidebar-scroll::-webkit-scrollbar-thumb { background: #4b5563; }
    .pos-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(10.5rem, 1fr));
        gap: 1rem;
    }
    @media (min-width: 1280px) {
        .pos-grid { grid-template-columns: repeat(auto-fill, minmax(11rem, 1fr)); }
    }
    .pos-card-selected {
        border-color: #8b5cf6 !important;
        background: rgba(139, 92, 246, 0.07);
        box-shadow: 0 4px 16px rgba(139, 92, 246, 0.22);
    }
    .dark .pos-card-selected {
        background: #1a102d;
        box-shadow: 0 4px 20px rgba(139, 92, 246, 0.35);
    }
    .pos-register-panel {
        display: flex;
        flex-direction: column;
        min-height: 0;
        overflow: hidden;
    }
    @media (min-width: 1024px) {
        .pos-register-panel { height: calc(100dvh - 4rem); max-height: calc(100dvh - 4rem); }
    }
    .pos-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #9ca3af;
    }
    .dark .pos-section-label { color: #6b7280; }
    .pos-cart-header {
        flex-shrink: 0;
        z-index: 10;
        background: inherit;
    }
    .pos-register-panel { background: #fff; }
    .dark .pos-register-panel { background: #030712; }
    .pos-checkout-divider {
        height: 3px;
        background: linear-gradient(90deg, transparent, #d1d5db 20%, #d1d5db 80%, transparent);
        box-shadow: 0 -1px 0 #e5e7eb, 0 1px 0 #e5e7eb;
    }
    .dark .pos-checkout-divider {
        background: linear-gradient(90deg, transparent, #4b5563 20%, #4b5563 80%, transparent);
        box-shadow: 0 -1px 0 #374151, 0 1px 0 #374151;
    }
    .pos-qty-stepper {
        display: inline-flex;
        align-items: center;
        height: 34px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        overflow: hidden;
        flex-shrink: 0;
    }
    .dark .pos-qty-stepper {
        border-color: #4b5563;
        background: #111827;
    }
    .pos-qty-stepper button {
        width: 28px;
        height: 34px;
        font-size: 14px;
        line-height: 1;
    }
    .pos-qty-stepper span {
        width: 22px;
        text-align: center;
        font-size: 12px;
        font-weight: 700;
    }
    .pos-cart-scroll {
        flex: 1 1 0;
        min-height: 0;
        overflow-y: auto;
    }
    .pos-checkout-foot {
        flex: 0 0 auto;
        flex-shrink: 0;
    }
    .pos-total-amount {
        font-size: 1.375rem;
        font-weight: 700;
        line-height: 1.2;
        letter-spacing: -0.01em;
    }
    .pos-search-field {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        width: 100%;
        padding: 0 0.875rem;
        border-radius: 0.75rem;
        border: 1px solid #d1d5db;
        background: #fff;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .dark .pos-search-field {
        border-color: #374151;
        background: #1f2937;
    }
    .pos-search-field:focus-within {
        border-color: transparent;
        box-shadow: 0 0 0 2px #8b5cf6;
    }
    .pos-search-field svg {
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        color: #9ca3af;
        pointer-events: none;
    }
    .dark .pos-search-field svg {
        color: #6b7280;
    }
    .pos-search-field input {
        flex: 1;
        min-width: 0;
        border: 0;
        background: transparent;
        padding: 0.75rem 0;
        font-size: 0.875rem;
        line-height: 1.25rem;
        color: #111827;
        outline: none;
        box-shadow: none;
    }
    .dark .pos-search-field input {
        color: #f3f4f6;
    }
    .pos-search-field input::placeholder {
        color: #9ca3af;
    }
    .dark .pos-search-field input::placeholder {
        color: #6b7280;
    }
    .pos-search-field input::-webkit-search-cancel-button,
    .pos-search-field input::-webkit-search-decoration {
        -webkit-appearance: none;
        appearance: none;
    }
    .pos-client-add-btn {
        height: 36px;
        width: 36px;
        border-radius: 10px;
        background: #1a2235;
        border: 1px solid #374151;
    }
</style>
@endpush

@section('header-actions')
    <a href="{{ route('pos.index') }}" class="btn-outline btn-sm hidden sm:inline-flex">All sales</a>
@endsection

@section('content')
<div class="-mx-4 sm:-mx-6 lg:-mx-7 -mb-4 sm:-mb-6 lg:-mb-7">
<div
    x-data="posApp()"
    x-init="init()"
    :class="cart.length > 0 ? 'pb-[4.5rem] lg:pb-0' : ''"
    class="pos-shell flex flex-col lg:flex-row w-full min-w-0 max-w-full rounded-none lg:rounded-none border-y lg:border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 overflow-hidden"
>

    {{-- ── Left: category sidebar (desktop) ── --}}
    <aside class="hidden lg:flex flex-col w-[13.5rem] xl:w-[14.5rem] shrink-0 self-stretch border-r border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-900/50">
        <nav class="flex flex-col p-3 gap-1 shrink-0">
            <a href="{{ route('pos.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-sm font-medium text-muted hover:text-heading hover:bg-white/80 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                Sales history
            </a>
            <button type="button" @click="selectSection('service')"
                    :class="section === 'service' ? 'bg-velour-600 text-white shadow-sm' : 'text-body hover:bg-white dark:hover:bg-gray-800'"
                    class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold text-left transition-colors">
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 15.536a5 5 0 00-7.07 0l-1.414 1.414a5 5 0 107.07-7.07l1.415 1.415zm2.828-9.9a9 9 0 11-12.728 0 9 9 0 0112.728 0z"/></svg>
                    Services
                </span>
                <span class="text-[10px] font-bold tabular-nums opacity-80" x-text="serviceItemCount"></span>
            </button>
            <button type="button" @click="selectSection('product')"
                    :class="section === 'product' ? 'bg-velour-600 text-white shadow-sm' : 'text-body hover:bg-white dark:hover:bg-gray-800'"
                    class="flex items-center justify-between gap-2 px-3 py-2.5 rounded-lg text-sm font-semibold text-left transition-colors">
                <span class="flex items-center gap-2.5">
                    <svg class="w-4 h-4 shrink-0 opacity-90" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Retail
                </span>
                <span class="text-[10px] font-bold tabular-nums opacity-80">{{ $retailProductCount }}</span>
            </button>
        </nav>

        <div class="mx-3 border-t border-gray-200 dark:border-gray-700 shrink-0" aria-hidden="true"></div>

        <div class="flex-1 min-h-0 overflow-y-auto pos-sidebar-scroll p-3 pt-2 space-y-0.5 max-h-[calc(100dvh-14rem)]">
            <p class="px-3 py-1.5 pos-section-label">Categories</p>
            <button type="button" @click="activeCategory = 'All'"
                    :class="activeCategory === 'All' ? 'border-l-2 border-velour-600 bg-white dark:bg-gray-800 text-velour-600 dark:text-velour-400 font-semibold pl-[10px]' : 'border-l-2 border-transparent text-body hover:bg-white/80 dark:hover:bg-gray-800 pl-3'"
                    class="w-full text-left pr-3 py-2 rounded-r-lg text-[13px] transition-colors flex items-center justify-between gap-2">
                <span>All</span>
                <span class="text-[10px] text-muted tabular-nums shrink-0" x-text="categoryCount('All')"></span>
            </button>
            <template x-for="cat in visibleSidebarCategories" :key="cat">
                <button type="button" @click="activeCategory = cat"
                        :title="cat"
                        :class="activeCategory === cat ? 'border-l-2 border-velour-600 bg-white dark:bg-gray-800 text-velour-600 dark:text-velour-400 font-semibold pl-[10px]' : 'border-l-2 border-transparent text-body hover:bg-white/80 dark:hover:bg-gray-800 pl-3'"
                        class="w-full text-left pr-3 py-2 rounded-r-lg text-[13px] leading-snug transition-colors flex items-center justify-between gap-2">
                    <span class="line-clamp-1 min-w-0" x-text="cat"></span>
                    <span class="text-[10px] text-muted tabular-nums shrink-0" x-text="categoryCount(cat)"></span>
                </button>
            </template>
            <button type="button" x-show="hiddenCategoryCount > 0" x-cloak @click="categoriesExpanded = !categoriesExpanded"
                    class="w-full text-left px-3 py-2.5 mt-1 rounded-lg text-xs font-semibold text-velour-600 dark:text-velour-400 hover:bg-white/80 dark:hover:bg-gray-800 transition-colors flex items-center gap-1">
                    <span x-text="categoriesExpanded ? 'Show fewer' : 'More'"></span>
                    <svg class="w-3.5 h-3.5 transition-transform" :class="categoriesExpanded ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    <span x-show="!categoriesExpanded" class="text-muted font-normal" x-text="'(' + hiddenCategoryCount + ')'"></span>
            </button>
        </div>
    </aside>

    {{-- ── Center: search + grid ── --}}
    <main class="flex flex-col flex-1 min-w-0 min-h-0 border-r border-gray-200 dark:border-gray-800">

        {{-- Mobile section switcher --}}
        <div class="lg:hidden shrink-0 flex border-b border-gray-200 dark:border-gray-800">
            <button type="button" @click="selectSection('service')"
                    :class="section === 'service' ? 'text-velour-600 border-b-2 border-velour-600' : 'text-muted'"
                    class="flex-1 py-2.5 text-sm font-semibold">Services</button>
            <button type="button" @click="selectSection('product')"
                    :class="section === 'product' ? 'text-velour-600 border-b-2 border-velour-600' : 'text-muted'"
                    class="flex-1 py-2.5 text-sm font-semibold">Retail</button>
        </div>

        @if(! empty($prefillFromAppointment['lines'] ?? []))
        <div class="shrink-0 px-4 py-2 text-xs text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/30 border-b border-emerald-100 dark:border-emerald-900/40">
            Appointment services added — confirm payment and complete sale.
        </div>
        @endif

        {{-- Search --}}
        <div class="shrink-0 px-5 py-4 border-b border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950">
            <div class="pos-search-field w-full lg:w-[70%]">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="pos-search" x-model="search" type="search" autocomplete="off"
                       :placeholder="section === 'product' ? 'Search products by name…' : 'Search services by name…'">
            </div>
            {{-- Mobile category picker --}}
            <div class="lg:hidden mt-2">
                <select x-model="activeCategory" class="form-select w-full text-sm py-2">
                    <option value="All">All categories</option>
                    <template x-for="cat in sidebarCategories" :key="cat">
                        <option :value="cat" x-text="cat"></option>
                    </template>
                </select>
            </div>
        </div>

        {{-- Grid header --}}
        <div class="shrink-0 px-5 py-3 flex items-baseline justify-between gap-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/20">
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-heading truncate" x-text="gridHeading"></h2>
                <p class="text-xs text-muted mt-0.5" x-text="gridSubheading"></p>
            </div>
            <span class="text-xs font-medium text-muted tabular-nums shrink-0" x-text="filteredItems.length + ' items'"></span>
        </div>

        {{-- Item grid --}}
        <div class="flex-1 min-h-0 overflow-y-auto p-5 bg-gray-50/30 dark:bg-gray-950/50">
            <div class="pos-grid">
                <template x-for="item in filteredItems" :key="item.type + item.id">
                    <button type="button"
                            @click="openServicePicker(item)"
                            :class="hasAnyLineForItem(item) ? 'pos-card-selected' : 'border-gray-200/90 dark:border-gray-700/90 bg-white dark:bg-gray-900 hover:border-velour-300 dark:hover:border-velour-600 hover:shadow-md'"
                            class="relative group flex flex-col text-left rounded-xl border px-4 py-3.5 h-[6.25rem] transition-all duration-150">
                        <p class="text-[15px] font-semibold text-heading leading-tight line-clamp-1 pr-7" x-text="item.name"></p>
                        <div class="mt-auto space-y-1 pt-2">
                            <p class="text-lg font-bold tabular-nums text-velour-600 dark:text-velour-400 leading-none"
                               x-text="'{{ $sym }}' + formatMoney(item.price)"></p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1 leading-none"
                               x-show="item.duration || (item.type === 'product' && item.qty)">
                                <svg x-show="item.duration" class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span x-show="item.duration" x-text="formatDuration(item.duration)"></span>
                                <span x-show="item.type === 'product' && item.qty" x-text="item.qty + ' in stock'"></span>
                            </p>
                        </div>
                        <span x-show="hasAnyLineForItem(item)" x-cloak
                              class="absolute top-3 right-3 h-5 w-5 rounded-full bg-velour-600 text-white flex items-center justify-center shadow-md ring-2 ring-white dark:ring-gray-900">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </span>
                    </button>
                </template>
            </div>
            <div x-show="filteredItems.length === 0" x-cloak class="py-16 text-center text-sm text-muted px-4">
                <p x-show="section === 'product'">No retail products available. In <a href="{{ route('inventory.index') }}" class="text-link">Inventory &amp; Retail</a>, set a <strong>retail price</strong> and <strong>stock &gt; 0</strong>.</p>
                <p x-show="section !== 'product'">No services found in this category.</p>
            </div>
        </div>
    </main>

    {{-- ── Right: current sale (sticky checkout) ── --}}
    <aside id="pos-register" tabindex="-1"
           class="pos-register-panel w-full lg:w-[21rem] xl:w-[22rem] shrink-0 border-t lg:border-t-0 border-gray-200 dark:border-gray-800">

        <div class="pos-cart-header px-4 py-2 border-b border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950">
            <h2 class="text-base font-semibold text-heading">
                Current sale
                <span class="text-sm font-normal text-muted" x-show="cart.length" x-text="'(' + cartQtyCount + ')'"></span>
            </h2>
        </div>

        @if($errors->any())
        <div class="shrink-0 mx-4 mt-3 rounded-lg border border-red-200 dark:border-red-900/50 bg-red-50 dark:bg-red-950/30 px-3 py-2 text-xs text-red-800 dark:text-red-200 space-y-0.5">
            @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
        </div>
        @endif

        {{-- Cart lines — takes all remaining height --}}
        <div class="pos-cart-scroll px-3 py-1.5">
            <template x-if="cart.length === 0">
                <div class="py-10 text-center">
                    <p class="text-sm text-muted">Select items to add</p>
                </div>
            </template>

            <template x-for="group in cartGroups" :key="group.label">
                <div x-show="group.rows.length">
                    <p class="pos-section-label py-1 sticky top-0 bg-white dark:bg-gray-950 z-[1]" x-text="group.label"></p>
                    <template x-for="row in group.rows" :key="lineSignature(row.item)">
                        <div class="py-1.5 border-b border-gray-100 dark:border-gray-800/60 last:border-0">
                            <div class="flex items-baseline justify-between gap-2">
                                <p class="text-[13px] font-semibold text-heading leading-tight line-clamp-1 min-w-0" x-text="row.item.name"></p>
                                <span class="text-[13px] font-bold tabular-nums text-heading shrink-0"
                                      x-text="'{{ $sym }}' + formatMoney(row.item.price * row.item.qty)"></span>
                            </div>
                            <div class="flex items-center justify-between gap-2 mt-1">
                                <p class="text-[10px] text-gray-500 dark:text-gray-400 flex items-center gap-1 min-w-0"
                                   x-show="row.item.duration">
                                    <svg class="w-2.5 h-2.5 opacity-70 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span x-text="formatDuration(row.item.duration)"></span>
                                </p>
                                <p class="text-[10px] text-gray-500 dark:text-gray-400"
                                   x-show="row.item.type === 'product' && !row.item.duration">Retail</p>
                                <div class="flex items-center gap-1.5 ml-auto shrink-0">
                                    <div class="pos-qty-stepper text-heading">
                                        <button type="button" @click="decQty(row.idx)" class="hover:bg-gray-200 dark:hover:bg-gray-800">−</button>
                                        <span class="tabular-nums" x-text="row.item.qty"></span>
                                        <button type="button" @click="incQty(row.idx)" class="hover:bg-gray-200 dark:hover:bg-gray-800">+</button>
                                    </div>
                                    <button type="button" @click="removeItem(row.idx)"
                                            class="p-1 text-muted hover:text-red-500 transition-colors" aria-label="Remove">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        {{-- Compact fixed checkout --}}
        <div class="pos-checkout-foot border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/90">
            <div class="px-3 py-2.5 space-y-2">

                <div class="flex items-end justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[10px] text-muted uppercase tracking-wide font-semibold">Total</p>
                        <p class="pos-total-amount tabular-nums text-velour-600 dark:text-velour-400 truncate"
                           x-text="'{{ $sym }}' + formatMoney(total)"></p>
                        <p class="text-[10px] text-muted mt-0.5" x-show="cart.length"
                           x-text="cartQtyCount + ' items · ' + taxModeLabel"></p>
                    </div>
                    <div class="text-right text-[11px] text-muted space-y-0.5 shrink-0">
                        <div class="tabular-nums">Sub <span class="text-heading" x-text="'{{ $sym }}' + formatMoney(subtotal)"></span></div>
                        <div class="tabular-nums">GST <span class="text-heading" x-text="'{{ $sym }}' + formatMoney(gst)"></span></div>
                        <button type="button" @click="taxMode = taxMode === 'excluded' ? 'included' : 'excluded'"
                                class="text-[10px] text-velour-600 dark:text-velour-400 hover:underline"
                                x-text="taxMode === 'excluded' ? '→ incl.' : '→ excl.'"></button>
                    </div>
                </div>

                <form id="pos-form" action="{{ route('pos.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="tax_rate" value="{{ $taxRate }}">
                    <input type="hidden" name="tax_mode" x-bind:value="taxMode">
                    <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
                    <input type="hidden" name="client_id" x-bind:value="clientId">
                    <input type="hidden" name="discount_amount" value="0">
                    @if(request('appointment'))
                    <input type="hidden" name="appointment_id" value="{{ (int) request('appointment') }}">
                    @endif
                    <template x-for="(item, idx) in cart" :key="lineSignature(item)">
                        <span>
                            <input type="hidden" :name="'items['+idx+'][type]'"  :value="item.type">
                            <input type="hidden" :name="'items['+idx+'][id]'"    :value="item.id">
                            <input type="hidden" :name="'items['+idx+'][qty]'"   :value="item.qty">
                            <input type="hidden" :name="'items['+idx+'][price]'" :value="item.price">
                            <input type="hidden" :name="'items['+idx+'][name]'"  :value="item.name">
                        </span>
                    </template>
                </form>

                <div class="flex rounded-lg bg-gray-200/70 dark:bg-gray-800 p-0.5 gap-0.5">
                    <button type="button" @click="paymentMethod = 'cash'"
                            :class="paymentMethod === 'cash' ? 'bg-velour-600 text-white' : 'text-muted hover:text-heading'"
                            class="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-md text-[11px] font-semibold transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Cash
                    </button>
                    <button type="button" @click="paymentMethod = 'card'"
                            :class="paymentMethod === 'card' ? 'bg-velour-600 text-white' : 'text-muted hover:text-heading'"
                            class="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-md text-[11px] font-semibold transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Card
                    </button>
                    <button type="button" @click="paymentMethod = 'bank_transfer'"
                            :class="paymentMethod === 'bank_transfer' ? 'bg-velour-600 text-white' : 'text-muted hover:text-heading'"
                            class="flex-1 flex items-center justify-center gap-1 py-1.5 rounded-md text-[11px] font-semibold transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                        Bank
                    </button>
                </div>

                <div x-show="cartHasServices" class="space-y-1">
                    <label for="pos-staff-select" class="text-[10px] font-semibold uppercase tracking-wide text-muted">Staff member</label>
                    <select id="pos-staff-select" name="staff_id" form="pos-form" x-model="saleStaffId"
                            class="form-select w-full text-xs !py-2 !min-h-[36px]">
                        <template x-for="s in staffList" :key="s.id">
                            <option :value="String(s.id)" x-text="s.name"></option>
                        </template>
                    </select>
                </div>

                <div class="flex gap-1.5 items-center">
                    <x-searchable-select
                        id="pos-client-select"
                        panel-placement="top"
                        wrapper-class="flex-1 min-w-0"
                        :search-url="route('lookup.clients')"
                        search-placeholder="Search customer…"
                        trigger-class="form-select w-full text-xs !py-2 !min-h-[36px]"
                        x-model="clientId">
                        <option value="">Walk-in customer</option>
                        @foreach($clients as $c)
                        <option value="{{ $c->id }}" data-sticky="1">{{ $c->full_name }}{{ $c->phone ? ' — '.$c->phone : '' }}</option>
                        @endforeach
                    </x-searchable-select>
                    @if(auth()->user()->dashboardScopedStaffId() === null)
                    <x-relation-quick-create-trigger
                        type="client"
                        select-id="pos-client-select"
                        title="Add customer"
                        :client-loyalty-tiers="$clientQuickCreateLoyaltyTiers ?? collect()"
                        button-class="pos-client-add-btn inline-flex items-center justify-center shrink-0 text-velour-400 hover:text-velour-300 transition-colors focus:outline-none focus:ring-2 focus:ring-velour-500" />
                    @endif
                </div>

                <label class="flex items-center gap-2 cursor-pointer text-xs text-body">
                    <input type="checkbox" name="payment_received" value="1" form="pos-form" x-model="paymentReceived"
                           class="rounded border-gray-300 dark:border-gray-600 text-velour-600 w-3.5 h-3.5">
                    <span>Payment received</span>
                </label>

                <div class="flex gap-2 pt-0.5">
                    <button type="button" @click="clearCart()" :disabled="cart.length === 0"
                            class="px-3 py-2 rounded-lg border border-red-300/80 dark:border-red-800/60 text-xs font-semibold text-red-600 dark:text-red-400 disabled:opacity-40 hover:bg-red-50 dark:hover:bg-red-950/20 transition-colors"
                            title="Clear sale">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                    <button type="button" @click="submitSale()"
                            :disabled="cart.length === 0 || !paymentReceived"
                            class="flex-1 py-2 rounded-lg text-xs font-bold text-white bg-velour-600 hover:bg-velour-700 disabled:opacity-40 transition-colors">
                        Complete sale
                    </button>
                </div>
            </div>
        </div>
    </aside>

    {{-- Variant picker --}}
    <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="pickerOpen = false">
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-xl max-w-sm w-full max-h-[85dvh] overflow-y-auto p-5 space-y-4" @click.outside="pickerOpen = false">
            <div class="flex justify-between items-start gap-2">
                <h3 class="font-semibold text-heading text-base" x-text="pickItem ? pickItem.name : ''"></h3>
                <button type="button" class="text-muted hover:text-heading text-xl leading-none p-1" @click="pickerOpen = false">&times;</button>
            </div>
            <template x-if="pickItem && pickItem.variants && pickItem.variants.length">
                <div>
                    <label class="form-label text-sm">Variant</label>
                    <select x-model="pickVariantName" class="form-select text-sm">
                        <option value="">Base price</option>
                        <template x-for="v in pickItem.variants" :key="v.name">
                            <option :value="v.name" x-text="v.name + ' — {{ $sym }}' + Number(v.price).toFixed(2)"></option>
                        </template>
                    </select>
                </div>
            </template>
            <template x-if="pickItem && pickItem.addons && pickItem.addons.length">
                <div>
                    <label class="form-label text-sm">Add-ons</label>
                    <div class="space-y-2 max-h-40 overflow-y-auto">
                        <template x-for="a in pickItem.addons" :key="a.name">
                            <label class="flex items-center gap-2.5 text-sm text-body cursor-pointer">
                                <input type="checkbox" :value="a.name" x-model="pickAddonNames" class="rounded border-gray-300 text-velour-600">
                                <span x-text="a.name + ' +{{ $sym }}' + Number(a.price).toFixed(2)"></span>
                            </label>
                        </template>
                    </div>
                </div>
            </template>
            <div class="flex justify-end gap-2 pt-1">
                <button type="button" class="btn-outline btn-sm" @click="pickerOpen = false">Cancel</button>
                <button type="button" class="btn-primary btn-sm" @click="confirmServicePick()">Add to sale</button>
            </div>
        </div>
    </div>

    {{-- Mobile checkout bar --}}
    <div x-show="cart.length > 0" x-cloak
         class="lg:hidden fixed left-0 right-0 z-[45] border-t border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-950 px-4 py-3 flex items-center justify-between gap-3 shadow-lg"
         style="bottom: 0; padding-bottom: max(0.75rem, env(safe-area-inset-bottom, 0px))">
        <div>
            <p class="text-xs text-muted" x-text="cartQtyCount + ' items'"></p>
            <p class="text-lg font-bold tabular-nums text-velour-600 dark:text-velour-400" x-text="'{{ $sym }}' + formatMoney(total)"></p>
        </div>
        <button type="button" @click="scrollToRegister()"
                class="shrink-0 rounded-xl bg-velour-600 px-5 py-2.5 text-sm font-bold text-white">
            Checkout
        </button>
    </div>
</div>
</div>

@push('scripts')
<script>
const POS_ITEMS              = @json($allItems);
const POS_TAX_RATE           = {{ $taxRate }};
const POS_PREFILL            = @json($prefillFromAppointment);
const POS_SERVICE_CATEGORIES = @json($serviceCategories);
const POS_RETAIL_CATEGORIES  = @json($retailCategories);
const POS_SERVICE_CAT_COUNTS = @json($serviceCategoryCounts);
const POS_RETAIL_CAT_COUNTS  = @json($retailCategoryCounts);
const POS_STAFF              = @json($staffMembers->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])->values());
const POS_DEFAULT_STAFF_ID   = @json($defaultStaffId);

function posApp() {
    return {
        search:              '',
        section:             @json($defaultSection),
        activeCategory:      'All',
        categoriesExpanded:  false,
        serviceCategories:   POS_SERVICE_CATEGORIES,
        retailCategories:    POS_RETAIL_CATEGORIES,
        serviceCatCounts:    POS_SERVICE_CAT_COUNTS,
        retailCatCounts:     POS_RETAIL_CAT_COUNTS,
        serviceItemCount:    POS_ITEMS.filter(i => i.type === 'service').length,
        cart:            [],
        paymentMethod:   'cash',
        paymentReceived: false,
        clientId:        '',
        taxMode:         'excluded',
        pickerOpen:      false,
        pickItem:        null,
        pickVariantName: '',
        pickAddonNames:  [],
        staffList:       POS_STAFF,
        saleStaffId:     POS_DEFAULT_STAFF_ID ? String(POS_DEFAULT_STAFF_ID) : (POS_STAFF[0] ? String(POS_STAFF[0].id) : ''),

        formatMoney(n) {
            const x = Number(n);
            if (Number.isNaN(x)) return '0.00';
            return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        formatDuration(mins) {
            const m = Number(mins);
            if (!m || m < 1) return '';
            if (m < 60) return m + ' min';
            const h = Math.floor(m / 60);
            const r = m % 60;
            return r ? `${h}h ${r}m` : `${h}h`;
        },

        scrollToRegister() {
            document.getElementById('pos-register')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        },

        selectSection(s) {
            this.section = s;
            this.activeCategory = 'All';
            this.search = '';
            this.categoriesExpanded = false;
        },

        get sidebarCategories() {
            return this.section === 'product' ? this.retailCategories : this.serviceCategories;
        },

        get visibleSidebarCategories() {
            const cats = this.sidebarCategories;
            if (this.categoriesExpanded || cats.length <= 5) return cats;
            return cats.slice(0, 5);
        },

        get hiddenCategoryCount() {
            if (this.categoriesExpanded) return 0;
            return Math.max(0, this.sidebarCategories.length - 5);
        },

        get cartServices() {
            return this.cart
                .map((item, idx) => ({ item, idx }))
                .filter(row => row.item.type === 'service');
        },

        get cartProducts() {
            return this.cart
                .map((item, idx) => ({ item, idx }))
                .filter(row => row.item.type === 'product');
        },

        get cartGroups() {
            const groups = [];
            if (this.cartServices.length) groups.push({ label: 'Services', rows: this.cartServices });
            if (this.cartProducts.length) groups.push({ label: 'Retail', rows: this.cartProducts });
            return groups;
        },

        get cartHasServices() {
            return this.cart.some(i => i.type === 'service');
        },

        get taxModeLabel() {
            return this.taxMode === 'included' ? 'GST included' : 'GST excluded';
        },

        categoryCount(cat) {
            const counts = this.section === 'product' ? this.retailCatCounts : this.serviceCatCounts;
            if (cat === 'All') {
                const type = this.section === 'product' ? 'product' : 'service';
                return POS_ITEMS.filter(i => i.type === type).length;
            }
            return counts[cat] || 0;
        },

        get gridHeading() {
            if (this.search.trim()) return 'Search results';
            if (this.activeCategory === 'All') {
                return this.section === 'product' ? 'All products' : 'All services';
            }
            return this.activeCategory;
        },

        get gridSubheading() {
            if (this.search.trim()) return 'Matching "' + this.search.trim() + '"';
            if (this.activeCategory === 'All') {
                return this.section === 'product' ? 'Browse retail inventory' : 'Browse service catalogue';
            }
            return this.section === 'product' ? 'Retail products' : 'Bookable services';
        },

        init() {
            const selectEl = document.getElementById('pos-client-select');
            if (!selectEl) return;

            this.clientId = selectEl.value || '';
            selectEl.addEventListener('change', () => {
                this.clientId = selectEl.value || '';
            });

            this.$watch('clientId', (value) => {
                const normalized = value == null ? '' : String(value);
                if (String(selectEl.value || '') === normalized) return;
                selectEl.value = normalized;
                selectEl.dispatchEvent(new Event('change', { bubbles: true }));
            });

            this.$nextTick(() => {
                if (POS_PREFILL && POS_PREFILL.staff_id) {
                    this.saleStaffId = String(POS_PREFILL.staff_id);
                }
                if (POS_PREFILL && Array.isArray(POS_PREFILL.lines) && POS_PREFILL.lines.length && this.cart.length === 0) {
                    for (const line of POS_PREFILL.lines) {
                        const cat = POS_ITEMS.find((i) => i.type === line.type && i.id === line.id);
                        if (!cat) continue;
                        this.addToCart({ ...cat });
                    }
                }
                if (POS_PREFILL && POS_PREFILL.client_id) {
                    const idStr = String(POS_PREFILL.client_id);
                    this.clientId = idStr;
                    if (String(selectEl.value || '') !== idStr) {
                        selectEl.value = idStr;
                    }
                    selectEl.dispatchEvent(new Event('input', { bubbles: true }));
                    selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });
        },

        get cartQtyCount() {
            return this.cart.reduce((n, i) => n + (Number(i.qty) || 0), 0);
        },

        get filteredItems() {
            const type = this.section === 'product' ? 'product' : 'service';
            return POS_ITEMS.filter(item => {
                const matchType   = item.type === type;
                const matchCat    = this.activeCategory === 'All' || item.cat === this.activeCategory;
                const matchSearch = !this.search || item.name.toLowerCase().includes(this.search.toLowerCase());
                return matchType && matchCat && matchSearch;
            });
        },

        lineSignature(line) {
            return line.type + '-' + line.id + '-' + line.name;
        },

        hasAnyLineForItem(item) {
            return this.cart.some(c => c.type === item.type && c.id === item.id);
        },

        productStockLeft(item) {
            if (item.type !== 'product') return Infinity;
            const inCart = this.cart
                .filter(c => c.type === 'product' && c.id === item.id)
                .reduce((n, c) => n + (Number(c.qty) || 0), 0);
            return Math.max(0, (Number(item.qty) || 0) - inCart);
        },

        openServicePicker(item) {
            if (item.type !== 'service') {
                if (this.productStockLeft(item) < 1) {
                    alert('Not enough stock for this product.');
                    return;
                }
                this.addToCart({ ...item });
                return;
            }
            const hasV = item.variants && item.variants.length;
            const hasA = item.addons && item.addons.length;
            if (!hasV && !hasA) {
                this.addToCart({ ...item });
                return;
            }
            this.pickItem        = JSON.parse(JSON.stringify(item));
            this.pickVariantName = '';
            this.pickAddonNames  = [];
            this.pickerOpen      = true;
        },

        confirmServicePick() {
            const item = this.pickItem;
            if (!item) return;
            let price = Number(item.price);
            let name  = item.name;
            if (this.pickVariantName && item.variants) {
                const v = item.variants.find(x => x.name === this.pickVariantName);
                if (v) {
                    price = Number(v.price);
                    name += ' (' + v.name + ')';
                }
            }
            if (item.addons && this.pickAddonNames.length) {
                for (const an of this.pickAddonNames) {
                    const ad = item.addons.find(x => x.name === an);
                    if (ad) {
                        price += Number(ad.price);
                        name += ' +' + ad.name;
                    }
                }
            }
            this.addToCart({ ...item, price, name });
            this.pickerOpen = false;
        },

        addToCart(item) {
            const line = { ...item };
            delete line.variants;
            delete line.addons;
            const sig = this.lineSignature(line);
            const idx = this.cart.findIndex(c => this.lineSignature(c) === sig);
            if (idx >= 0) { this.cart[idx].qty++; }
            else          { this.cart.push({ ...line, qty: 1 }); }
        },

        incQty(idx) {
            const line = this.cart[idx];
            if (line.type === 'product') {
                const catalog = POS_ITEMS.find(i => i.type === 'product' && i.id === line.id);
                const maxStock = catalog ? Number(catalog.qty) : 0;
                if (line.qty >= maxStock) {
                    alert('Not enough stock.');
                    return;
                }
            }
            this.cart[idx].qty++;
        },

        decQty(idx) {
            if (this.cart[idx].qty > 1) { this.cart[idx].qty--; }
            else                        { this.cart.splice(idx, 1); }
        },

        removeItem(idx) {
            this.cart.splice(idx, 1);
        },

        clearCart() { this.cart = []; },

        get subtotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        get gst() {
            if (this.taxMode === 'included') {
                const base = this.subtotal / (1 + (POS_TAX_RATE / 100));
                return Math.round((this.subtotal - base) * 100) / 100;
            }
            return Math.round(this.subtotal * (POS_TAX_RATE / 100) * 100) / 100;
        },
        get total() {
            if (this.taxMode === 'included') {
                return Math.round(this.subtotal * 100) / 100;
            }
            return Math.round((this.subtotal + this.gst) * 100) / 100;
        },

        submitSale() {
            if (this.cart.length === 0) return;
            if (!this.paymentReceived) {
                alert('Confirm payment received before completing the sale.');
                return;
            }
            const missingStaff = this.cartHasServices && !this.saleStaffId;
            if (missingStaff) {
                alert('Select staff for this sale.');
                return;
            }
            if (this.staffList.length === 0 && this.cart.some(i => i.type === 'service')) {
                alert('Add at least one active staff member before selling services.');
                return;
            }
            document.getElementById('pos-form').submit();
        },
    };
}
</script>
@endpush
@endsection
