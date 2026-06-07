<?php

namespace App\Http\Requests\Inventory;

use App\Models\InventoryItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $item = $this->route('inventory');
        return $item instanceof InventoryItem
            ? $this->user()->can('update', $item)
            : true;
    }

    public function rules(): array
    {
        return [
            'category_id'     => ['sometimes', 'integer', 'exists:inventory_categories,id'],
            'name'            => ['sometimes', 'string', 'max:255'],
            'sku'             => ['nullable', 'string', 'max:100', 'alpha_num'],
            'supplier'        => ['nullable', 'string', 'max:255'],
            'unit'            => ['nullable', 'string', 'max:50'],
            'cost_price'      => ['sometimes', 'numeric', 'min:0', 'max:99999.99'],
            'retail_price'    => ['nullable', 'numeric', 'min:0', 'max:99999.99'],
            'stock_quantity'  => ['sometimes', 'integer', 'min:0'],
            'min_stock_level' => ['sometimes', 'integer', 'min:0'],
            'reorder_quantity'=> ['nullable', 'integer', 'min:0'],
            'type'            => ['sometimes', 'in:professional,retail,both'],
            'barcode'         => ['nullable', 'string', 'max:100'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'is_active'       => ['nullable', 'boolean'],
        ];
    }
}
