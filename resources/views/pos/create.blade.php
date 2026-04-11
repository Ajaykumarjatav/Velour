@extends('layouts.app')
@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@php
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
        'price' => (float) $p->price,
        'qty'   => $p->quantity,
        'cat'   => 'Products',
        'type'  => 'product',
    ])->values()->toArray();
    $allItems = array_merge($allServices, $allProducts);
    $cats = collect($allItems)->pluck('cat')->unique()->sort()->values()->toArray();
@endphp

@section('content')
{{-- Full-bleed split layout — overrides the default content padding --}}
<div
    x-data="posApp()"
    x-init="init()"
    class="flex -mx-4 sm:-mx-6 -mt-4"
    style="height:calc(100vh - 64px); overflow:hidden;"
>

    {{-- ══ LEFT PANEL ══ --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-hidden px-5 py-4">

        {{-- Sub-heading --}}
        <p class="text-sm text-muted mb-4">Walk-in / Checkout billing</p>

        {{-- Category filter pills --}}
        <div class="flex items-center gap-2 mb-4 flex-wrap">
            <button @click="activeCategory = 'All'"
                    :class="activeCategory === 'All'
                        ? 'bg-velour-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-body border border-gray-200 dark:border-gray-700 hover:border-velour-300'"
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition-all">
                All
            </button>
            @foreach($cats as $cat)
            <button @click="activeCategory = '{{ $cat }}'"
                    :class="activeCategory === '{{ $cat }}'
                        ? 'bg-velour-600 text-white'
                        : 'bg-white dark:bg-gray-800 text-body border border-gray-200 dark:border-gray-700 hover:border-velour-300'"
                    class="px-4 py-1.5 rounded-full text-sm font-semibold transition-all">
                {{ $cat }}
            </button>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="relative mb-4">
            <span class="absolute inset-y-0 left-3 flex items-center text-muted pointer-events-none text-sm font-bold">#</span>
            <input x-model="search"
                   type="text"
                   placeholder="Scan barcode or search service..."
                   class="form-input pl-8 w-full">
        </div>

        {{-- Service grid --}}
        <div class="flex-1 overflow-y-auto">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pb-4">
                <template x-for="item in filteredItems" :key="item.type + item.id">
                    <button
                        @click="openServicePicker(item)"
                        :class="hasAnyLineForItem(item)
                            ? 'border-velour-400 ring-1 ring-velour-200 dark:ring-velour-800'
                            : 'border-gray-100 dark:border-gray-700 hover:border-gray-200 dark:hover:border-gray-600 hover:shadow-sm'"
                        class="card relative text-left p-4 rounded-2xl border transition-all cursor-pointer">
                        {{-- In-cart tick --}}
                        <div x-show="hasAnyLineForItem(item)"
                             class="absolute top-3 right-3 w-5 h-5 bg-velour-600 rounded-full flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <p class="font-semibold text-heading text-sm leading-tight pr-6" x-text="item.name"></p>
                        <p class="text-xs text-muted mt-1" x-text="(item.duration ? item.duration + 'min · ' : '') + item.cat"></p>
                        <p class="text-sm font-bold mt-2 text-velour-600" x-text="'{{ $sym }}' + item.price.toLocaleString()"></p>
                    </button>
                </template>
                <div x-show="filteredItems.length === 0" class="col-span-3 py-12 text-center text-sm text-muted">
                    No items match your search.
                </div>
            </div>
        </div>

        {{-- Recent transactions --}}
        <div class="flex-shrink-0 border-t border-gray-100 dark:border-gray-800 pt-3 space-y-0.5 max-h-36 overflow-y-auto">
            @forelse($recentTransactions as $txn)
            <div class="flex items-center justify-between py-1.5 text-sm">
                <div class="min-w-0">
                    <span class="font-medium text-heading">
                        {{ $txn->client ? $txn->client->first_name.' '.$txn->client->last_name : 'Walk-in Client' }}
                    </span>
                    <span class="text-xs text-muted ml-2">{{ $txn->reference }} · {{ ucfirst(str_replace('_',' ',$txn->payment_method)) }}</span>
                </div>
                <div class="flex items-center gap-3 flex-shrink-0 ml-4">
                    <span class="font-bold {{ $txn->status === 'refunded' ? 'text-red-500' : 'text-heading' }}">
                        {{ $txn->status === 'refunded' ? '-' : '' }}@money($txn->total)
                    </span>
                    @php $badge = ['completed'=>'badge-green','refunded'=>'badge-yellow','voided'=>'badge-red'][$txn->status] ?? 'badge-gray'; @endphp
                    <span class="{{ $badge }}">{{ ucfirst($txn->status) }}</span>
                    <a href="{{ route('pos.show', $txn->id) }}" class="text-muted hover:text-body">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </a>
                </div>
            </div>
            @empty
            <p class="text-xs text-muted py-2">No recent transactions.</p>
            @endforelse
        </div>

        {{-- Service variant / add-on picker --}}
        <div x-show="pickerOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             @keydown.escape.window="pickerOpen = false">
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-sm w-full p-5 space-y-4" @click.outside="pickerOpen = false">
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

    {{-- ══ RIGHT PANEL — Current Bill ══ --}}
    <div class="flex-shrink-0 w-80 xl:w-96 flex flex-col border-l border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <h2 class="font-bold text-heading text-base">Current Bill</h2>
        </div>

        {{-- Cart items --}}
        <div class="flex-1 overflow-y-auto px-5 py-3 space-y-3">
            <template x-if="cart.length === 0">
                <div class="flex flex-col items-center justify-center h-32 text-muted">
                    <svg class="w-10 h-10 mb-2 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <p class="text-sm">Tap a service to add it</p>
                </div>
            </template>

            <template x-for="(item, idx) in cart" :key="lineSignature(item)">
                <div class="flex items-center gap-2">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-heading truncate" x-text="item.name"></p>
                        <p class="text-xs text-muted" x-text="'{{ $sym }}' + item.price.toLocaleString()"></p>
                    </div>
                    {{-- Qty controls --}}
                    <div class="flex items-center gap-1 flex-shrink-0">
                        <button @click="decQty(idx)"
                                class="w-6 h-6 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-muted hover:border-velour-400 hover:text-velour-600 transition-colors text-sm font-bold leading-none">−</button>
                        <span class="w-5 text-center text-sm font-bold text-heading" x-text="item.qty"></span>
                        <button @click="incQty(idx)"
                                class="w-6 h-6 rounded-full border border-gray-200 dark:border-gray-700 flex items-center justify-center text-muted hover:border-velour-400 hover:text-velour-600 transition-colors text-sm font-bold leading-none">+</button>
                    </div>
                    <span class="text-sm font-bold text-heading w-16 text-right flex-shrink-0"
                          x-text="'{{ $sym }}' + (item.price * item.qty).toLocaleString()"></span>
                    <button @click="removeItem(idx)" class="text-muted hover:text-red-400 transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </template>
        </div>

        {{-- Totals + actions --}}
        <div class="flex-shrink-0 border-t border-gray-100 dark:border-gray-800 px-5 py-4 space-y-3">

            {{-- Subtotal / GST / Total --}}
            <div class="space-y-1.5 text-sm">
                <div class="flex justify-between text-muted">
                    <span>Subtotal</span>
                    <span x-text="'{{ $sym }}' + subtotal.toLocaleString()"></span>
                </div>
                <div class="flex justify-between text-muted">
                    <span>GST ({{ $taxRate }}%)</span>
                    <span x-text="'{{ $sym }}' + gst.toLocaleString()"></span>
                </div>
                <div class="flex justify-between font-bold text-heading text-base pt-1.5 border-t border-gray-100 dark:border-gray-800">
                    <span>Total</span>
                    <span x-text="'{{ $sym }}' + total.toLocaleString()"></span>
                </div>
            </div>

            {{-- Hidden form --}}
            <form id="pos-form" action="{{ route('pos.store') }}" method="POST">
                @csrf
                <input type="hidden" name="tax_rate" value="{{ $taxRate }}">
                <input type="hidden" name="payment_method" x-bind:value="paymentMethod">
                <input type="hidden" name="client_id" x-bind:value="clientId">
                <input type="hidden" name="discount_amount" value="0">
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
            <div class="grid grid-cols-3 gap-1.5">
                @foreach(['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank'] as $val => $lbl)
                <button @click="paymentMethod = '{{ $val }}'"
                        :class="paymentMethod === '{{ $val }}'
                            ? 'bg-velour-600 text-white border-velour-600'
                            : 'bg-white dark:bg-gray-800 text-body border-gray-200 dark:border-gray-700 hover:border-gray-300'"
                        class="py-2 rounded-xl border text-xs font-semibold transition-all">
                    {{ $lbl }}
                </button>
                @endforeach
            </div>

            {{-- Client selector --}}
            <div class="flex items-end gap-2">
                <select x-model="clientId" id="pos-client-select" class="form-select flex-1 min-w-0 text-sm">
                    <option value="">Walk-in Client</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}</option>
                    @endforeach
                </select>
                <x-relation-quick-create-trigger type="client" select-id="pos-client-select" />
            </div>

            {{-- Clear / Charge --}}
            <div class="flex gap-2 pt-1">
                <button @click="clearCart()"
                        :disabled="cart.length === 0"
                        class="flex-1 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-sm font-semibold text-body hover:bg-gray-50 dark:hover:bg-gray-800 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    Clear
                </button>
                <button @click="submitSale()"
                        :disabled="cart.length === 0"
                        class="flex-[2] py-2.5 rounded-xl text-sm font-bold text-white bg-velour-600 hover:bg-velour-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    Charge
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
const POS_ITEMS    = @json($allItems);
const POS_TAX_RATE = {{ $taxRate }};

function posApp() {
    return {
        search:         '',
        activeCategory: 'All',
        cart:           [],
        paymentMethod:  'cash',
        clientId:       '',
        pickerOpen:     false,
        pickItem:       null,
        pickVariantName:'',
        pickAddonNames: [],

        init() {},

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

        openServicePicker(item) {
            if (item.type !== 'service') {
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
        },

        removeItem(idx) { this.cart.splice(idx, 1); },
        clearCart()     { this.cart = []; },

        get subtotal() { return this.cart.reduce((s, i) => s + i.price * i.qty, 0); },
        get gst()      { return Math.round(this.subtotal * (POS_TAX_RATE / 100) * 100) / 100; },
        get total()    { return Math.round((this.subtotal + this.gst) * 100) / 100; },

        submitSale() {
            if (this.cart.length === 0) return;
            document.getElementById('pos-form').submit();
        },
    };
}
</script>
@endpush

@endsection
