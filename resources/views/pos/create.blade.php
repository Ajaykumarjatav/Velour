@extends('layouts.app')
@section('title', 'New Sale')
@section('page-title', 'New Sale')
@section('content')

@php $currencySymbol = \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP'); @endphp

<div class="max-w-2xl" x-data="posForm('{{ $currencySymbol }}')">
    <div class="card p-6">
        <form action="{{ route('pos.store') }}" method="POST">
            @csrf

            <div class="mb-5">
                <label class="form-label">Client <span class="text-muted">(optional)</span></label>
                <select name="client_id" class="form-select">
                    <option value="">Walk-in</option>
                    @foreach($clients as $c)
                    <option value="{{ $c->id }}">{{ $c->first_name }} {{ $c->last_name }}{{ $c->phone ? ' — '.$c->phone : '' }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-5">
                <label class="form-label mb-2">Items <span class="text-red-500">*</span></label>
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden mb-3">
                    <div class="bg-gray-50 dark:bg-gray-800/60 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-semibold text-muted uppercase tracking-wide">Services</p>
                    </div>
                    @foreach($services as $svc)
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-velour-50 dark:hover:bg-velour-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-800 last:border-0">
                        <input type="checkbox" x-on:change="toggleItem($event, 'service', {{ $svc->id }}, {{ $svc->price }})" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                        <span class="flex-1 text-sm text-body">{{ $svc->name }}</span>
                        <span class="text-sm font-semibold text-heading">@money($svc->price)</span>
                    </label>
                    @endforeach
                </div>

                @if($products->count())
                <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 dark:bg-gray-800/60 px-4 py-2 border-b border-gray-200 dark:border-gray-700">
                        <p class="text-xs font-semibold text-muted uppercase tracking-wide">Products</p>
                    </div>
                    @foreach($products as $prod)
                    <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-velour-50 dark:hover:bg-velour-900/20 cursor-pointer border-b border-gray-50 dark:border-gray-800 last:border-0">
                        <input type="checkbox" x-on:change="toggleItem($event, 'product', {{ $prod->id }}, {{ $prod->price }})" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                        <span class="flex-1 text-sm text-body">{{ $prod->name }}</span>
                        <span class="text-xs text-muted mr-2">{{ $prod->quantity }} in stock</span>
                        <span class="text-sm font-semibold text-heading">@money($prod->price)</span>
                    </label>
                    @endforeach
                </div>
                @endif

                <template x-for="(item, idx) in selectedItems" :key="idx">
                    <div>
                        <input type="hidden" :name="'items['+idx+'][type]'" :value="item.type">
                        <input type="hidden" :name="'items['+idx+'][id]'"    :value="item.id">
                        <input type="hidden" :name="'items['+idx+'][qty]'"   :value="item.qty">
                        <input type="hidden" :name="'items['+idx+'][price]'" :value="item.price">
                    </div>
                </template>
            </div>

            <div x-show="selectedItems.length > 0" class="bg-gray-50 dark:bg-gray-800/60 rounded-xl p-4 mb-5 space-y-2">
                <h3 class="text-sm font-semibold text-heading mb-3">Order Summary</h3>
                <template x-for="item in selectedItems" :key="item.id+item.type">
                    <div class="flex justify-between text-sm">
                        <span x-text="item.label" class="text-body"></span>
                        <span class="font-medium text-heading" x-text="currencySymbol + (item.price * item.qty).toFixed(2)"></span>
                    </div>
                </template>
                <div class="border-t border-gray-200 dark:border-gray-700 pt-2 flex justify-between font-bold text-heading">
                    <span>Total</span>
                    <span x-text="currencySymbol + total.toFixed(2)"></span>
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label mb-2">Payment method <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach(['cash' => 'Cash', 'card' => 'Card', 'bank_transfer' => 'Bank', 'voucher' => 'Voucher'] as $val => $label)
                    <label class="flex items-center justify-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all
                                  has-[:checked]:border-velour-600 has-[:checked]:bg-velour-50 dark:has-[:checked]:bg-velour-900/20
                                  border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
                        <input type="radio" name="payment_method" value="{{ $val }}" class="sr-only" {{ $loop->first ? 'checked' : '' }}>
                        <span class="text-sm font-medium text-body">{{ $label }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-5">
                <label class="form-label">Discount ({{ $currencySymbol }}) <span class="text-muted">(optional)</span></label>
                <input type="number" name="discount_amount" min="0" step="0.01" placeholder="0.00" class="form-input w-full sm:w-40">
            </div>

            <div class="mb-6">
                <label class="form-label">Notes</label>
                <input type="text" name="notes" placeholder="Receipt note…" class="form-input">
            </div>

            <div class="flex gap-3">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">Complete Sale</button>
                <a href="{{ route('pos.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function posForm(currencySymbol) {
    return {
        currencySymbol: currencySymbol,
        selectedItems: [],
        get total() { return this.selectedItems.reduce((s, i) => s + i.price * i.qty, 0); },
        toggleItem(event, type, id, price) {
            const checked = event.target.checked;
            const label   = event.target.closest('label').querySelector('span.flex-1').textContent.trim();
            if (checked) { this.selectedItems.push({ type, id, qty: 1, price, label }); }
            else { this.selectedItems = this.selectedItems.filter(i => !(i.type === type && i.id === id)); }
        }
    }
}
</script>
@endpush

@endsection
