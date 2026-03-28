<?php

namespace App\Http\Requests\Service;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        $service = $this->route('service');
        return $service instanceof Service
            ? $this->user()->can('update', $service)
            : $this->user()->can('create', Service::class);
    }

    public function rules(): array
    {
        return [
            'category_id'           => ['sometimes', 'integer', 'exists:service_categories,id'],
            'name'                  => ['sometimes', 'string', 'max:200'],
            'description'           => ['nullable', 'string', 'max:1000'],
            'duration_minutes'      => ['sometimes', 'integer', 'min:5', 'max:720'],
            'buffer_minutes'        => ['nullable', 'integer', 'min:0', 'max:120'],
            'price'                 => ['sometimes', 'numeric', 'min:0', 'max:9999.99'],
            'price_from'            => ['nullable', 'numeric', 'min:0'],
            'price_on_consultation' => ['nullable', 'boolean'],
            'deposit_type'          => ['nullable', 'in:none,percentage,fixed'],
            'deposit_value'         => ['nullable', 'numeric', 'min:0'],
            'online_bookable'       => ['nullable', 'boolean'],
            'show_in_menu'          => ['nullable', 'boolean'],
            'is_active'             => ['nullable', 'boolean'],
            'staff_ids'             => ['nullable', 'array'],
            'staff_ids.*'           => ['integer', 'exists:staff,id'],
            'sort_order'            => ['nullable', 'integer', 'min:0'],
        ];
    }
}
