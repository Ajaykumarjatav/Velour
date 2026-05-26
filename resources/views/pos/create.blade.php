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
        'cat'   => 'Products',
        'type'  => 'product',
    ])->values()->toArray();
    $allItems = array_merge($allServices, $allProducts);
    $cats = collect($allItems)->pluck('cat')->unique()->sort()->values()->toArray();
@endphp

@section('content')
{{-- POS: stay inside main padding (no negative margins) — avoids horizontal scroll / overlap with fixed sidebar --}}
<div
    x-data="posApp()"
    x-init="init()"
    :class="cart.length > 0 ? 'pb-[6rem] lg:pb-0' : ''"
    class="flex flex-col lg:flex-row w-full min-w-0 max-w-full rounded-2xl border border-gray-200/90 dark:border-gray-800 bg-white dark:bg-gray-950 shadow-sm lg:min-h-[calc(100dvh-9.5rem)]"
>

    {{-- ══ CATALOGUE (primary) ══ --}}
    <div class="flex flex-col flex-1 min-w-0 min-h-0 border-b lg:border-b-0 lg:border-r border-gray-200/80 dark:border-gray-800 bg-gradient-to-b from-gray-50/90 via-white to-white dark:from-gray-950/80 dark:via-gray-900/40 dark:to-gray-900/20">

        {{-- Top bar --}}
        <div class="flex flex-col gap-3 px-4 sm:px-5 pt-4 sm:pt-5 pb-3 border-b border-gray-200/60 dark:border-gray-800/80 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-velour-600 dark:text-velour-400 mb-1">Checkout</p>
                <h1 class="text-base sm:text-lg font-bold text-heading tracking-tight">Walk-in &amp; desk sales</h1>
                <p class="text-[11px] sm:text-xs text-muted mt-0.5 max-w-xl leading-relaxed">Add services first, then retail products on the same bill. Totals include GST as set below. Prices follow your live catalogue.</p>
                @if(! empty($prefillFromAppointment['lines'] ?? []))
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-300/90 mt-2 font-medium">Booked services from the appointment were added to the bill (catalogue prices). Confirm payment method and complete the sale.</p>
                @endif
            </div>
            <a href="{{ route('pos.index') }}"
               class="inline-flex w-full sm:w-auto items-center justify-center gap-1.5 px-3 py-2 rounded-lg text-xs font-semibold border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/80 text-body hover:border-velour-300 dark:hover:border-velour-600 shrink-0">
                <svg class="w-4 h-4 text-muted shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                All transactions
            </a>
        </div>

        {{-- Categories: wrap so every label is visible (no truncation / hidden pills) --}}
        <div class="relative min-w-0 px-4 sm:px-5 pt-2.5 pb-2">
            <p class="text-[10px] font-semibold text-muted uppercase tracking-wide mb-1.5">Category</p>
            <div class="flex flex-wrap gap-1.5 sm:gap-2">
                <button type="button" @click="activeCategory = 'All'" title="All items"
                        :class="activeCategory === 'All'
                            ? 'bg-velour-600 text-white border-transparent shadow-sm'
                            : 'bg-white dark:bg-gray-900 text-body border-gray-200 dark:border-gray-700 hover:border-velour-400/60'"
                        class="rounded-lg border px-2.5 py-1 text-left text-xs font-medium leading-snug transition-colors">
                    All items
                </button>
                @foreach($cats as $cat)
                <button type="button" @click="activeCategory = '{{ $cat }}'" title="{{ $cat }}"
                        :class="activeCategory === '{{ $cat }}'
                            ? 'bg-velour-600 text-white border-transparent shadow-sm'
                            : 'bg-white dark:bg-gray-900 text-body border-gray-200 dark:border-gray-700 hover:border-velour-400/60'"
                        class="max-w-full rounded-lg border px-2.5 py-1 text-left text-xs font-medium leading-snug transition-colors">
                    {{ $cat }}
                </button>
                @endforeach
            </div>
        </div>

        {{-- Search --}}
        <div class="px-4 sm:px-5 pb-3">
            <label for="pos-search" class="sr-only">Search or barcode</label>
            <div class="relative">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-velour-500/70 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="pos-search" x-model="search" type="search" autocomplete="off"
                       placeholder="Search or scan barcode…"
                       class="form-input w-full pl-11 pr-3 py-2.5 text-sm rounded-xl border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm focus:ring-2 focus:ring-velour-500/30 focus:border-velour-500">
            </div>
        </div>

        {{-- Service / product grid --}}
        <div class="flex-1 min-h-0 overflow-y-auto overflow-x-hidden px-4 sm:px-5 pb-4">
            {{-- 3 columns from lg (catalogue + register); 1–2 cols on smaller screens --}}
            <div class="grid w-full min-w-0 grid-cols-1 min-[480px]:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-2.5 [&>*]:min-w-0">
                <template x-for="item in filteredItems" :key="item.type + item.id">
                    <button type="button"
                            @click="openServicePicker(item)"
                            :class="hasAnyLineForItem(item)
                                ? 'ring-2 ring-velour-500/80 border-velour-400/60 bg-velour-50/50 dark:bg-velour-950/25'
                                : 'border-gray-200/90 dark:border-gray-700/90 hover:border-velour-300/60 dark:hover:border-velour-700 hover:shadow-md'"
                            class="group relative flex min-w-0 w-full flex-col text-left rounded-xl border bg-white/90 dark:bg-gray-900/50 backdrop-blur-sm p-3 transition-all duration-200 min-h-[92px] sm:min-h-[100px]">
                        <div class="flex items-start gap-2">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-xs font-bold uppercase bg-gradient-to-br from-velour-100 to-velour-200/80 text-velour-800 dark:from-velour-900/60 dark:to-velour-800/40 dark:text-velour-200 border border-velour-200/50 dark:border-velour-700/50"
                                  x-text="(item.name || '?').charAt(0)"></span>
                            <div class="min-w-0 flex-1 pt-0">
                                <span class="inline-flex items-center rounded px-1.5 py-px text-[9px] font-bold uppercase tracking-wide bg-gray-100 dark:bg-gray-800 text-muted mb-1"
                                      x-text="item.type === 'product' ? 'Retail' : 'Service'"></span>
                                <p class="font-semibold text-heading text-xs leading-snug line-clamp-2 group-hover:text-velour-700 dark:group-hover:text-velour-300 transition-colors" x-text="item.name"></p>
                                <p class="text-[10px] text-muted mt-0.5 line-clamp-1" x-text="(item.duration ? item.duration + ' min · ' : '') + item.cat"></p>
                            </div>
                        </div>
                        <div class="mt-auto pt-2 flex items-end justify-between gap-2 border-t border-gray-100/80 dark:border-gray-800/80 min-w-0">
                            <span class="text-sm font-bold tabular-nums text-velour-600 dark:text-velour-400 truncate min-w-0"
                                  x-text="'{{ $sym }}' + formatMoney(item.price)"></span>
                            <span x-show="item.type === 'product' && item.qty != null" class="text-[10px] text-muted tabular-nums shrink-0" x-text="'Stock ' + item.qty"></span>
                        </div>
                        <div x-show="hasAnyLineForItem(item)" x-cloak
                             class="absolute top-2 right-2 flex h-6 w-6 items-center justify-center rounded-full bg-velour-600 text-white shadow-md">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </button>
                </template>
            </div>
            <div x-show="filteredItems.length === 0" x-cloak class="py-16 text-center rounded-2xl border border-dashed border-gray-200 dark:border-gray-800 bg-white/50 dark:bg-gray-900/30">
                <p class="text-sm font-medium text-heading">No items match</p>
                <p class="text-xs text-muted mt-1">Try another category or clear your search.</p>
            </div>
        </div>

        {{-- Recent sales (contained panel) --}}
        <div class="flex-shrink-0 mx-4 sm:mx-5 mb-4 rounded-2xl border border-gray-200/80 dark:border-gray-800 bg-white/70 dark:bg-gray-900/40 overflow-hidden">
            <button type="button" @click="recentOpen = !recentOpen"
                    class="flex w-full items-center justify-between px-4 py-3 text-left hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition-colors">
                <span class="text-xs font-bold uppercase tracking-wide text-muted">Recent sales</span>
                <span class="flex items-center gap-2 text-xs text-muted">
                    <span>{{ $recentTransactions->count() }} shown</span>
                    <svg class="w-4 h-4 transition-transform" :class="recentOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </span>
            </button>
            <div x-show="recentOpen"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="border-t border-gray-100 dark:border-gray-800 max-h-48 overflow-y-auto">
                @forelse($recentTransactions as $txn)
                <div class="flex flex-wrap items-center gap-x-3 gap-y-2 px-3 sm:px-4 py-2.5 text-sm border-b border-gray-100/80 dark:border-gray-800/80 last:border-0 hover:bg-gray-50/50 dark:hover:bg-gray-800/30">
                    <div class="min-w-0 flex-1 basis-[min(100%,12rem)]">
                        <p class="font-medium text-heading truncate">
                            {{ $txn->client ? $txn->client->first_name.' '.$txn->client->last_name : 'Walk-in Client' }}
                        </p>
                        <p class="text-[11px] text-muted truncate font-mono">{{ $txn->reference }} · {{ ucfirst(str_replace('_',' ',$txn->payment_method)) }}</p>
                    </div>
                    <span class="font-bold tabular-nums text-sm shrink-0 {{ $txn->status === 'refunded' ? 'text-red-500' : 'text-heading' }}">
                        {{ $txn->status === 'refunded' ? '-' : '' }}@money($txn->total)
                    </span>
                    @php $badge = ['completed'=>'badge-green','refunded'=>'badge-yellow','voided'=>'badge-red'][$txn->status] ?? 'badge-gray'; @endphp
                    <span class="{{ $badge }} text-[10px] shrink-0 hidden sm:inline">{{ ucfirst($txn->status) }}</span>
                    <a href="{{ route('pos.show', $txn->id) }}"
                       class="shrink-0 p-1.5 rounded-lg text-velour-600 hover:bg-velour-50 dark:hover:bg-velour-950/50 transition-colors ml-auto sm:ml-0"
                       title="View receipt">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </div>
                @empty
                <p class="px-4 py-6 text-center text-xs text-muted">No recent transactions yet.</p>
                @endforelse
            </div>
        </div>

        {{-- Service variant / add-on picker --}}
        <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/50"
             @keydown.escape.window="pickerOpen = false">
            <div class="bg-white dark:bg-gray-900 rounded-t-2xl sm:rounded-2xl shadow-xl max-w-sm w-full max-h-[min(90dvh,36rem)] overflow-y-auto p-5 space-y-4 sm:m-auto" @click.outside="pickerOpen = false">
                <div class="flex justify-between items-start gap-2">
                    <h3 class="font-semibold text-heading text-sm" x-text="pickItem ? pickItem.name : ''"></h3>
                    <button type="button" class="text-muted hover:text-heading" @click="pickerOpen = false">&times;</button>
                </div>
                <template x-if="pickItem && pickItem.variants && pickItem.variants.length">
                    <div>
                        <label class="form-label text-xs">Variant</label>
                        <select x-model="pickVariantName" class="form-select text-sm">
                            <option value="">Use base list price</option>
                            <template x-for="v in pickItem.variants" :key="v.name">
                                <option :value="v.name" x-text="v.name + ' — {{ $sym }}' + Number(v.price).toFixed(2)"></option>
                            </template>
                        </select>
                    </div>
                </template>
                <template x-if="pickItem && pickItem.addons && pickItem.addons.length">
                    <div>
                        <label class="form-label text-xs">Add-ons</label>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <template x-for="a in pickItem.addons" :key="a.name">
                                <label class="flex items-center gap-2 text-sm text-body cursor-pointer">
                                    <input type="checkbox" :value="a.name" x-model="pickAddonNames" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                    <span x-text="a.name + ' +{{ $sym }}' + Number(a.price).toFixed(2)"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" class="btn-outline text-sm" @click="pickerOpen = false">Cancel</button>
                    <button type="button" class="btn-primary text-sm" @click="confirmServicePick()">Add to bill</button>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ REGISTER (bill) ══ --}}
    <div id="pos-register" tabindex="-1"
         class="scroll-mt-24 flex w-full min-w-0 flex-col overflow-hidden bg-white dark:bg-gray-950 max-h-[min(62vh,560px)] lg:max-h-[calc(100dvh-9.5rem)] lg:w-96 lg:flex-shrink-0 lg:self-start lg:sticky lg:top-24 lg:z-10 xl:w-[26rem]">

        <div class="shrink-0 px-3.5 py-2.5 sm:px-4 sm:py-3 bg-gradient-to-r from-velour-600 to-velour-700 text-white">
            <p class="text-[9px] font-bold uppercase tracking-[0.18em] text-white/70">Register</p>
            <h2 class="font-bold text-sm sm:text-base mt-0.5 leading-tight">Current bill</h2>
        </div>

        @if($errors->any())
            <div class="shrink-0 mx-3 mt-2 sm:mx-4 rounded-lg border border-red-200 dark:border-red-900/60 bg-red-50/90 dark:bg-red-950/35 px-3 py-2 text-xs text-red-900 dark:text-red-100 space-y-1">
                @foreach($errors->all() as $err)<p>{{ $err }}</p>@endforeach
            </div>
        @endif

        {{-- Cart items (compact rows — more lines visible before scroll) --}}
        <div class="flex-1 min-h-0 overflow-y-auto px-3 py-2 sm:px-4 sm:py-2.5 space-y-1.5">
            <template x-if="cart.length === 0">
                <div class="flex flex-col items-center justify-center py-8 px-3 rounded-xl border border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/20 text-muted">
                    <svg class="w-10 h-10 mb-2 text-velour-400/40" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.25" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-xs font-medium text-heading">Bill is empty</p>
                    <p class="text-[10px] text-center mt-0.5 max-w-[11rem] leading-relaxed">Add items from the catalogue.</p>
                </div>
            </template>

            <template x-for="(item, idx) in cart" :key="lineSignature(item)">
                <div class="flex items-center gap-2 rounded-lg border border-gray-100 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/25 px-2 py-1.5">
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-heading leading-tight line-clamp-2" x-text="item.name"></p>
                        <p class="text-[10px] text-muted tabular-nums mt-px" x-text="'{{ $sym }}' + formatMoney(item.price) + ' ea'"></p>
                    </div>
                    <div class="flex items-center gap-0.5 shrink-0 rounded-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-px">
                        <button type="button" @click="decQty(idx)" aria-label="Decrease quantity"
                                class="h-6 w-6 rounded flex items-center justify-center text-muted hover:bg-velour-50 dark:hover:bg-velour-950/50 text-xs font-bold leading-none">−</button>
                        <span class="w-5 text-center text-[11px] font-bold text-heading tabular-nums" x-text="item.qty"></span>
                        <button type="button" @click="incQty(idx)" aria-label="Increase quantity"
                                class="h-6 w-6 rounded flex items-center justify-center text-muted hover:bg-velour-50 dark:hover:bg-velour-950/50 text-xs font-bold leading-none">+</button>
                    </div>
                    <span class="text-xs font-bold text-heading tabular-nums text-right shrink-0 w-[3.75rem] sm:w-16"
                          x-text="'{{ $sym }}' + formatMoney(item.price * item.qty)"></span>
                    <button type="button" @click="removeItem(idx)" class="text-muted hover:text-red-500 shrink-0 p-1 rounded hover:bg-red-50 dark:hover:bg-red-950/30" aria-label="Remove line">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Totals + actions --}}
        <div class="shrink-0 border-t border-gray-200 dark:border-gray-800 px-3 py-2.5 sm:px-4 sm:py-3 space-y-2 sm:space-y-2.5 bg-gray-50/80 dark:bg-gray-950/40">

            {{-- Subtotal / GST / Total --}}
            <div class="rounded-lg bg-white dark:bg-gray-900/80 border border-gray-200/80 dark:border-gray-800 px-3 py-2 space-y-1 text-xs shadow-sm">
                <div class="flex justify-between text-muted">
                    <span>Subtotal</span>
                    <span class="tabular-nums text-heading font-medium" x-text="'{{ $sym }}' + formatMoney(subtotal)"></span>
                </div>
                <div class="flex justify-between text-muted">
                    <span>GST ({{ $taxRate }}%)</span>
                    <span class="tabular-nums text-heading font-medium" x-text="'{{ $sym }}' + formatMoney(gst)"></span>
                </div>
                <div class="flex justify-between font-bold text-heading text-sm pt-1.5 border-t border-gray-100 dark:border-gray-800">
                    <span>Total</span>
                    <span class="tabular-nums text-velour-600 dark:text-velour-400" x-text="'{{ $sym }}' + formatMoney(total)"></span>
                </div>
            </div>

            {{-- Hidden form --}}
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

            {{-- Payment method --}}
            <div class="grid grid-cols-3 gap-1">
                @foreach(['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank'] as $val => $lbl)
                <button @click="paymentMethod = '{{ $val }}'"
                        :class="paymentMethod === '{{ $val }}'
                            ? 'bg-velour-600 text-white border-velour-600'
                            : 'bg-white dark:bg-gray-800 text-body border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                        class="py-1.5 rounded-lg border text-[11px] font-semibold transition-all">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>

            {{-- GST mode --}}
            <div class="grid grid-cols-2 gap-1">
                <button @click="taxMode = 'excluded'"
                        :class="taxMode === 'excluded'
                            ? 'bg-velour-600 text-white border-velour-600'
                            : 'bg-white dark:bg-gray-800 text-body border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                        class="py-1.5 rounded-lg border text-[10px] font-semibold transition-all leading-tight">
                    <span class="max-[380px]:hidden">GST Excluded</span><span class="hidden max-[380px]:inline">Excl. GST</span>
                </button>
                <button @click="taxMode = 'included'"
                        :class="taxMode === 'included'
                            ? 'bg-velour-600 text-white border-velour-600'
                            : 'bg-white dark:bg-gray-800 text-body border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                        class="py-1.5 rounded-lg border text-[10px] font-semibold transition-all leading-tight">
                    <span class="max-[380px]:hidden">GST Included</span><span class="hidden max-[380px]:inline">Incl. GST</span>
                </button>
            </div>

            {{-- Client selector --}}
            <div class="flex flex-col min-[400px]:flex-row items-stretch min-[400px]:items-end gap-2">
                <x-searchable-select
                    id="pos-client-select"
                    panel-placement="top"
                    wrapper-class="flex-1 min-w-0"
                    :search-url="route('lookup.clients')"
                    search-placeholder="Search client…"
                    :hint="auth()->user()->dashboardScopedStaffId() === null ? 'Search client in database or keep Walk-in Client.' : null"
                    trigger-class="form-select w-full text-xs py-2"
                    x-model="clientId">
                    <option value="">Walk-in Client</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}" data-sticky="1">{{ $c->first_name }} {{ $c->last_name }}{{ $c->phone ? ' — '.$c->phone : '' }}</option>
                    @endforeach
                </x-searchable-select>
                @if(auth()->user()->dashboardScopedStaffId() === null)
                <div x-show="!clientId" x-cloak>
                    <x-relation-quick-create-trigger type="client" select-id="pos-client-select" :client-loyalty-tiers="$clientQuickCreateLoyaltyTiers ?? collect()" />
                </div>
                @endif
            </div>

            <label class="flex items-start gap-2.5 rounded-lg border px-2.5 py-2 cursor-pointer transition-colors"
                   :class="paymentReceived ? 'border-emerald-400/70 bg-emerald-50/80 dark:bg-emerald-950/30 dark:border-emerald-700/60' : 'border-amber-200/90 dark:border-amber-800/70 bg-amber-50/50 dark:bg-amber-950/20'">
                <input type="checkbox" name="payment_received" value="1" form="pos-form" x-model="paymentReceived" class="mt-0.5 rounded border-gray-300 dark:border-gray-600 text-emerald-600 shrink-0">
                <span class="text-[11px] leading-snug text-body">
                    <span class="font-semibold text-heading">Payment received</span>
                    <span class="text-muted"> — confirm you have collected this amount (cash, card, or bank) before completing the sale.</span>
                </span>
            </label>

            {{-- Clear / Charge --}}
            <div class="flex gap-1.5 pt-0.5">
                <button @click="clearCart()"
                        type="button"
                        :disabled="cart.length === 0"
                        class="flex-1 py-2 rounded-lg border border-gray-200 dark:border-gray-700 text-xs font-semibold text-body hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    Clear
                </button>
                <button type="button"
                        @click="submitSale()"
                        :disabled="cart.length === 0 || !paymentReceived || !cartAllowsProducts"
                        class="flex-[2] py-2 rounded-lg text-xs font-bold text-white bg-velour-600 hover:bg-velour-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    Complete sale
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile: quick jump to register (catalogue scrolls above chat FAB) --}}
    <div x-show="cart.length > 0" x-cloak
         class="lg:hidden fixed left-0 right-0 z-[45] border-t border-gray-200/90 dark:border-gray-800 bg-white/95 dark:bg-gray-950/95 backdrop-blur-md shadow-[0_-4px_20px_rgba(0,0,0,0.08)] pr-14 sm:pr-16"
         style="bottom: 0; padding-bottom: max(0.65rem, env(safe-area-inset-bottom, 0px))">
        <div class="flex items-center justify-between gap-3 px-4 pt-2.5 max-w-lg mx-auto w-full">
            <div class="min-w-0">
                <p class="text-[11px] text-muted truncate" x-text="cartQtyCount + ' item' + (cartQtyCount !== 1 ? 's' : '') + ' in bill'"></p>
                <p class="text-lg font-bold tabular-nums text-velour-600 dark:text-velour-400 leading-tight" x-text="'{{ $sym }}' + formatMoney(total)"></p>
            </div>
            <button type="button" @click="scrollToRegister()"
                    class="shrink-0 rounded-xl bg-velour-600 px-4 py-2.5 text-sm font-bold text-white shadow-md shadow-velour-600/25 hover:bg-velour-700 active:scale-[0.98] transition-transform">
                Review &amp; pay
            </button>
        </div>
    </div>

</div>

@push('scripts')
<script>
const POS_ITEMS    = @json($allItems);
const POS_TAX_RATE = {{ $taxRate }};
const POS_PREFILL  = @json($prefillFromAppointment);

function posApp() {
    return {
        search:         '',
        activeCategory: 'All',
        cart:           [],
        paymentMethod:  'cash',
        paymentReceived: false,
        clientId:       '',
        taxMode:        'excluded',
        recentOpen:     true,
        pickerOpen:     false,
        pickItem:       null,
        pickVariantName:'',
        pickAddonNames: [],

        formatMoney(n) {
            const x = Number(n);
            if (Number.isNaN(x)) return '0.00';
            return x.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        },

        scrollToRegister() {
            document.getElementById('pos-register')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            document.getElementById('pos-register')?.focus({ preventScroll: true });
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
            return POS_ITEMS.filter(item => {
                const matchCat    = this.activeCategory === 'All' || item.cat === this.activeCategory;
                const matchSearch = !this.search || item.name.toLowerCase().includes(this.search.toLowerCase());
                return matchCat && matchSearch;
            });
        },

        lineSignature(line) {
            return line.type + '-' + line.id + '-' + line.name;
        },

        hasAnyLineForItem(item) {
            return this.cart.some(c => c.type === item.type && c.id === item.id);
        },

        get hasServiceInCart() {
            return this.cart.some(c => c.type === 'service');
        },

        get hasProductInCart() {
            return this.cart.some(c => c.type === 'product');
        },

        get cartAllowsProducts() {
            return !this.hasProductInCart || this.hasServiceInCart;
        },

        openServicePicker(item) {
            if (item.type !== 'service') {
                if (!this.hasServiceInCart) {
                    alert('Add at least one service to the bill before adding retail products.');
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

        incQty(idx) { this.cart[idx].qty++; },

        decQty(idx) {
            if (this.cart[idx].qty > 1) { this.cart[idx].qty--; }
            else                        { this.cart.splice(idx, 1); }
            this.$nextTick(() => this.pruneRetailWithoutService());
        },

        removeItem(idx) {
            this.cart.splice(idx, 1);
            this.$nextTick(() => this.pruneRetailWithoutService());
        },

        pruneRetailWithoutService() {
            if (this.hasServiceInCart || !this.hasProductInCart) return;
            const n = this.cart.length;
            this.cart = this.cart.filter(c => c.type !== 'product');
            if (this.cart.length < n) {
                alert('Retail items were removed — add a service again before selling products.');
            }
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
                alert('Confirm that payment has been received before completing the sale.');
                return;
            }
            if (!this.cartAllowsProducts) {
                alert('Add at least one service before selling retail products on this bill.');
                return;
            }
            document.getElementById('pos-form').submit();
        },
    };
}
</script>
@endpush

@endsection
