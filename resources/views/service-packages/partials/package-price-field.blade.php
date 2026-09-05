@php
    $currencyCode = $currentSalon->currency ?? \App\Helpers\CurrencyHelper::defaultCode();
    $priceGuardCatalog = $servicesPayload ?? [];
    $priceGuardIds = $initialSelectedIds ?? [];
@endphp
<div x-data="packagePriceGuard(@js($priceGuardCatalog), @js($priceGuardIds), @js((string) ($priceValue ?? '')))"
     @package-services-changed.window="catalogTotal = Number($event.detail.total) || 0; syncValidity($refs.priceEl)"
     x-effect="syncValidity($refs.priceEl)">
    <label class="form-label">Package price ({{ \App\Helpers\CurrencyHelper::symbol($currencyCode) }}) <span class="text-red-500">*</span></label>
    <input type="number" name="price" x-ref="priceEl" x-model="price" value="{{ $priceValue }}" required min="0" step="0.01"
           class="form-input @error('price') form-input-error @enderror"
           :class="overLimit() ? 'form-input-error' : ''">
    <p class="form-error" x-show="overLimit()" x-cloak>
        Package price cannot be greater than the selected services total.
    </p>
    @error('price')
        <p class="form-error" x-show="!overLimit()">{{ $message }}</p>
    @enderror
</div>

@once
@push('scripts')
<script>
function packagePriceGuard(catalog, initialIds, initialPrice) {
    const byId = Object.fromEntries((catalog || []).map(s => [s.id, s]));
    const ids = Array.isArray(initialIds) ? initialIds.map(id => parseInt(id, 10)) : [];
    const catalogTotal = ids.reduce((sum, id) => {
        const s = byId[id];
        return sum + (s ? Number(s.price) || 0 : 0);
    }, 0);

    return {
        price: initialPrice ?? '',
        catalogTotal,
        overLimit() {
            const p = parseFloat(this.price);
            if (Number.isNaN(p) || this.catalogTotal <= 0) {
                return false;
            }
            return p > this.catalogTotal + 0.001;
        },
        syncValidity(el) {
            if (!el) {
                return;
            }
            if (this.overLimit()) {
                el.setCustomValidity('Package price cannot be greater than the selected services total.');
            } else {
                el.setCustomValidity('');
            }
        },
    };
}
</script>
@endpush
@endonce
