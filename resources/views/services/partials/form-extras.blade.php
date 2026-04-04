@php
    $svcModel = $service ?? null;
    $variantsOld = old('variants', $svcModel ? ($svcModel->variants ?? []) : []);
    $addonsOld = old('addons', $svcModel ? ($svcModel->addons ?? []) : []);
    if (! is_array($variantsOld)) {
        $variantsOld = [];
    }
    if (! is_array($addonsOld)) {
        $addonsOld = [];
    }
@endphp

<div class="border-t border-gray-200 dark:border-gray-700 pt-5 mt-5 space-y-5"
     x-data="{
        variants: {{ \Illuminate\Support\Js::from(array_values($variantsOld)) }},
        addons: {{ \Illuminate\Support\Js::from(array_values($addonsOld)) }},
        addVariant() { this.variants.push({ name: '', price: '' }); },
        addAddon() { this.addons.push({ name: '', price: '' }); },
     }">

    <div>
        <label class="form-label">Staff level</label>
        <select name="staff_level" class="form-select max-w-xs">
            <option value="any" {{ old('staff_level', $svcModel?->staff_level ?? 'any') === 'any' ? 'selected' : '' }}>Any</option>
            <option value="standard" {{ old('staff_level', $svcModel?->staff_level ?? 'any') === 'standard' ? 'selected' : '' }}>Standard</option>
            <option value="senior" {{ old('staff_level', $svcModel?->staff_level ?? 'any') === 'senior' ? 'selected' : '' }}>Senior</option>
            <option value="apprentice" {{ old('staff_level', $svcModel?->staff_level ?? 'any') === 'apprentice' ? 'selected' : '' }}>Apprentice</option>
        </select>
    </div>

    <label class="flex items-center gap-2 cursor-pointer">
        <input type="hidden" name="dynamic_pricing_enabled" value="0">
        <input type="checkbox" name="dynamic_pricing_enabled" value="1"
               {{ old('dynamic_pricing_enabled', $svcModel?->dynamic_pricing_enabled ?? false) ? 'checked' : '' }}
               class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
        <span class="text-sm text-body">Enable dynamic pricing for this service</span>
    </label>

    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="form-label mb-0">Variant prices <span class="text-muted font-normal text-xs">(optional)</span></label>
            <button type="button" @click="addVariant()" class="text-xs text-velour-600 dark:text-velour-400 font-medium hover:underline">+ Add variant</button>
        </div>
        <template x-for="(row, index) in variants" :key="index">
            <div class="flex flex-wrap gap-2 mb-2 items-end">
                <input type="text" :name="'variants[' + index + '][name]'" x-model="row.name" class="form-input flex-1 min-w-[8rem]" placeholder="e.g. Short">
                <input type="number" :name="'variants[' + index + '][price]'" x-model="row.price" min="0" step="0.01" class="form-input w-28" placeholder="Price">
                <button type="button" @click="variants.splice(index,1)" class="text-xs text-red-500 hover:underline pb-2">Remove</button>
            </div>
        </template>
        <p class="text-xs text-muted">Different price points for lengths or tiers (saved with the service).</p>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="form-label mb-0">Add-ons <span class="text-muted font-normal text-xs">(optional)</span></label>
            <button type="button" @click="addAddon()" class="text-xs text-velour-600 dark:text-velour-400 font-medium hover:underline">+ Add add-on</button>
        </div>
        <template x-for="(row, index) in addons" :key="'a'+index">
            <div class="flex flex-wrap gap-2 mb-2 items-end">
                <input type="text" :name="'addons[' + index + '][name]'" x-model="row.name" class="form-input flex-1 min-w-[8rem]" placeholder="e.g. Head massage">
                <input type="number" :name="'addons[' + index + '][price]'" x-model="row.price" min="0" step="0.01" class="form-input w-28" placeholder="+Price">
                <button type="button" @click="addons.splice(index,1)" class="text-xs text-red-500 hover:underline pb-2">Remove</button>
            </div>
        </template>
    </div>

    <div>
        <label class="form-label">Add-ons (comma separated)</label>
        <input type="text" name="addons_text" value="{{ old('addons_text') }}"
               class="form-input @error('addons_text') form-input-error @enderror"
               placeholder="e.g. Deep Conditioning +300, Head Massage +200">
        @error('addons_text')<p class="form-error">{{ $message }}</p>@enderror
        <p class="form-hint">Parsed and merged with the add-on rows above when you save.</p>
    </div>
</div>
