<?php
namespace App\Http\Requests\Inventory;
use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\InventoryItem::class);
    }
    public function rules(): array
    {
        return [
            'category_id'     => ['required','integer','exists:inventory_categories,id'],
            'name'            => ['required','string','max:255'],
            'sku'             => ['nullable','string','max:100','alpha_num'],
            'supplier'        => ['nullable','string','max:255'],
            'unit'            => ['nullable','string','max:50'],
            'cost_price'      => ['required','numeric','min:0','max:99999.99'],
            'retail_price'    => ['nullable','numeric','min:0','max:99999.99'],
            'stock_quantity'  => ['required','integer','min:0'],
            'min_stock_level' => ['required','integer','min:0'],
            'reorder_quantity'=> ['nullable','integer','min:0'],
            'type'            => ['required','in:professional,retail,both'],
            'barcode'         => ['nullable','string','max:100'],
            'notes'           => ['nullable','string','max:500'],
        ];
    }
}
