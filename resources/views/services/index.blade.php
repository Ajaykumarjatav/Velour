@extends('layouts.app')
@section('title', 'Services')
@section('page-title', 'Services')
@section('content')

@php
    $rulesPayload = $pricingRules->map(fn ($r) => [
        'title' => $r->title,
        'description' => $r->description ?? '',
        'adjustment_percent' => (int) $r->adjustment_percent,
        'enabled' => (bool) $r->enabled,
    ])->values()->all();
@endphp

<div class="space-y-6"
     x-data="{
        pricingOpen: false,
        variantOpen: false,
        variantUrl: '',
        variantTitle: '',
        vRows: [],
        aRows: [],
        rules: {{ \Illuminate\Support\Js::from($rulesPayload) }},
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

    <p class="text-sm text-muted">{{ number_format($totalServices) }} services · The foundation of booking, POS &amp; analytics</p>

    <form method="GET" action="{{ route('services.index') }}" class="flex flex-col sm:flex-row flex-wrap gap-3 mb-2">
        @if($filterCategoryId)
            <input type="hidden" name="category_id" value="{{ $filterCategoryId }}">
        @endif
        <input type="search" name="search" value="{{ $search }}" placeholder="Search services…"
               class="form-input flex-1 min-w-[12rem] max-w-md">
        <div class="flex gap-2">
            <button type="submit" class="btn-secondary">Search</button>
            @if($search)
                <a href="{{ route('services.index', array_filter(['category_id' => $filterCategoryId])) }}" class="btn-outline">Clear</a>
            @endif
        </div>
    </form>

    <div class="flex flex-wrap gap-2 mb-2">
        <a href="{{ route('services.index', array_filter(['search' => $search])) }}"
           class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ !$filterCategoryId ? 'bg-velour-600 border-velour-600 text-white' : 'border-gray-300 dark:border-gray-600 text-body hover:bg-gray-50 dark:hover:bg-gray-800' }}">
            All Categories
        </a>
        @foreach($categoryChips as $chip)
            <a href="{{ route('services.index', array_filter(['category_id' => $chip->id, 'search' => $search])) }}"
               class="px-3 py-1.5 rounded-full text-xs font-medium border transition-colors {{ (string) $filterCategoryId === (string) $chip->id ? 'bg-velour-600 border-velour-600 text-white' : 'border-gray-300 dark:border-gray-600 text-body hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                {{ $chip->name }}
            </a>
        @endforeach
    </div>

    <div class="flex justify-end gap-3 mb-6">
        <a href="{{ route('service-categories.index') }}" class="btn-outline">Manage Categories</a>
        <button type="button" @click="openPricing()" class="btn-outline inline-flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
            Dynamic Pricing
        </button>
        <a href="{{ route('services.create') }}" class="btn-primary">+ Add Service</a>
    </div>

    <div class="space-y-6">
        @forelse($categories as $cat)
            <div class="table-wrap">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60 flex items-center justify-between">
                    <h3 class="font-semibold text-heading">{{ $cat->name }}</h3>
                    <span class="text-xs text-muted">{{ $cat->services->count() }} services</span>
                </div>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Service</th>
                        <th class="whitespace-nowrap w-[1%]">Duration</th>
                        <th class="text-right whitespace-nowrap w-[1%]">Price</th>
                        <th class="text-center w-[1%]">Status</th>
                        <th class="text-right w-[1%]">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cat->services as $svc)
                        @php
                            $vList = $svc->normalizedVariants();
                            $aList = $svc->normalizedAddons();
                        @endphp
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="w-3 h-3 rounded-full flex-shrink-0" style="background-color: {{ $svc->color ?? '#7C3AED' }}"></div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <p class="font-medium text-heading">{{ $svc->name }}</p>
                                            <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-800 text-muted">{{ $cat->name }}</span>
                                            @if($svc->dynamic_pricing_enabled)
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-200">Dynamic ⚡</span>
                                            @endif
                                        </div>
                                        @if($svc->description)
                                            <p class="text-xs text-muted mt-0.5 pl-0">{{ Str::limit($svc->description, 80) }}</p>
                                        @endif
                                        <div class="flex flex-wrap gap-2 mt-2 text-[11px]">
                                            <span class="text-muted">Base <strong class="text-heading">@money($svc->price)</strong></span>
                                            <span class="text-muted">· {{ $svc->duration_minutes }} min</span>
                                            <span class="text-muted">· {{ count($vList) }} variants</span>
                                            <span class="text-muted">· {{ count($aList) }} add-ons</span>
                                        </div>
                                        @if(count($vList))
                                            <div class="flex flex-wrap gap-1.5 mt-2">
                                                @foreach(array_slice($vList, 0, 4) as $vr)
                                                    <span class="inline-flex px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-800 text-muted">{{ $vr['name'] }}: @money($vr['price'])</span>
                                                @endforeach
                                                @if(count($vList) > 4)
                                                    <span class="text-xs text-muted">+{{ count($vList) - 4 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                        @if(count($aList))
                                            <div class="flex flex-wrap gap-1.5 mt-1">
                                                @foreach(array_slice($aList, 0, 4) as $ad)
                                                    <span class="inline-flex px-2 py-0.5 rounded-lg border border-gray-200 dark:border-gray-700 text-xs text-body">{{ $ad['name'] }} +@money($ad['price'])</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-muted whitespace-nowrap">{{ $svc->duration_minutes }} min</td>
                            <td class="font-semibold text-heading text-right whitespace-nowrap">@money($svc->price)</td>
                            <td class="text-center">
                                <span class="{{ $svc->is_active ? 'badge-green' : 'badge-gray' }}">
                                    {{ $svc->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-right">
                                <div class="flex justify-end gap-2 flex-wrap">
                                    <button type="button"
                                            class="text-xs text-link font-medium"
                                            @click="openVariants({{ \Illuminate\Support\Js::from(route('services.variants', $svc)) }}, {{ \Illuminate\Support\Js::from($svc->name) }}, {{ \Illuminate\Support\Js::from($vList) }}, {{ \Illuminate\Support\Js::from($aList) }})">
                                        Variants
                                    </button>
                                    <a href="{{ route('services.edit', $svc->id) }}" class="text-xs text-link font-medium">Edit</a>
                                    <form action="{{ route('services.destroy', $svc->id) }}" method="POST"
                                          onsubmit="return confirm('Delete this service?')" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @empty
        @endforelse

        @if($uncategorised->count())
            <div class="table-wrap">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/60">
                    <h3 class="font-semibold text-heading">Uncategorised</h3>
                </div>
                <table class="data-table">
                    <thead>
                    <tr>
                        <th>Service</th>
                        <th class="whitespace-nowrap w-[1%]">Duration</th>
                        <th class="text-right whitespace-nowrap w-[1%]">Price</th>
                        <th class="text-right w-[1%]">Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($uncategorised as $svc)
                        <tr>
                            <td class="font-medium text-heading">{{ $svc->name }}</td>
                            <td class="text-muted whitespace-nowrap">{{ $svc->duration_minutes }} min</td>
                            <td class="font-semibold text-heading text-right whitespace-nowrap">@money($svc->price)</td>
                            <td class="text-right">
                                <a href="{{ route('services.edit', $svc->id) }}" class="text-xs text-link font-medium">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        @if($categories->isEmpty() && $uncategorised->isEmpty())
            <div class="card">
                <div class="empty-state">
                    <p class="empty-state-title">No services yet</p>
                    <a href="{{ route('services.create') }}" class="btn-primary mt-4">Add your first service</a>
                </div>
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
