@extends('layouts.app')
@section('title', isset($item) ? 'Edit Item' : 'Add Item')
@section('page-title', isset($item) ? 'Edit Inventory Item' : 'Add Inventory Item')
@section('content')

<div class="max-w-xl">
    <div class="card p-6">
        @php $action = isset($item) ? route('inventory.update', $item->id) : route('inventory.store'); @endphp
        <form action="{{ $action }}" method="POST" class="space-y-5">
            @csrf
            @if(isset($item)) @method('PUT') @endif

            <div>
                <label class="form-label">Item name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $item->name ?? '') }}" required
                       class="form-input @error('name') form-input-error @enderror">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $item->sku ?? '') }}"
                           class="form-input font-mono @error('sku') form-input-error @enderror">
                    @error('sku')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Barcode</label>
                    <input type="text" name="barcode" value="{{ old('barcode', $item->barcode ?? '') }}"
                           class="form-input font-mono @error('barcode') form-input-error @enderror">
                    @error('barcode')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Category</label>
                <select name="inventory_category_id" class="form-select @error('inventory_category_id') form-input-error @enderror">
                    <option value="">No category</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('inventory_category_id', $item->inventory_category_id ?? '') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
                @error('inventory_category_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-4">
                @if(!isset($item))
                <div>
                    <label class="form-label">Starting quantity <span class="text-red-500">*</span></label>
                    <input type="number" name="quantity" min="0" value="{{ old('quantity', 0) }}" required
                           class="form-input @error('quantity') form-input-error @enderror">
                    @error('quantity')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                @endif
                <div>
                    <label class="form-label">Low stock alert at <span class="text-red-500">*</span></label>
                    <input type="number" name="low_stock_threshold" min="0"
                           value="{{ old('low_stock_threshold', $item->low_stock_threshold ?? 5) }}" required
                           class="form-input @error('low_stock_threshold') form-input-error @enderror">
                    @error('low_stock_threshold')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Cost price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }})</label>
                    <input type="number" name="cost_price" min="0" step="0.01"
                           value="{{ old('cost_price', $item->cost_price ?? '') }}"
                           class="form-input @error('cost_price') form-input-error @enderror">
                    @error('cost_price')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="form-label">Retail price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }})</label>
                    <input type="number" name="retail_price" min="0" step="0.01"
                           value="{{ old('retail_price', $item->retail_price ?? '') }}"
                           class="form-input @error('retail_price') form-input-error @enderror">
                    @error('retail_price')<p class="form-error">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="form-label">Supplier</label>
                <input type="text" name="supplier" value="{{ old('supplier', $item->supplier ?? '') }}"
                       class="form-input @error('supplier') form-input-error @enderror">
                @error('supplier')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 sm:flex-none">{{ isset($item) ? 'Save Changes' : 'Add Item' }}</button>
                <a href="{{ route('inventory.index') }}" class="btn-outline">Cancel</a>
            </div>
        </form>
    </div>
</div>

@endsection
