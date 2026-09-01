<?php
namespace App\Http\Requests\Service;
use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'category_id'           => ['required','integer','exists:service_categories,id'],
            'name'                  => ['required','string','max:200'],
            'description'           => ['nullable','string','max:1000'],
            'duration_minutes'      => ['required','integer','min:5','max:720'],
            'buffer_minutes'        => ['nullable','integer','min:0','max:120'],
            'price'                 => ['required','numeric','min:0','max:9999.99'],
            'price_from'            => ['nullable','numeric','min:0'],
            'price_on_consultation' => ['nullable','boolean'],
            'deposit_type'          => ['nullable','in:none,percentage,fixed'],
            'deposit_value'         => ['nullable','numeric','min:0'],
            'online_bookable'       => ['nullable','boolean'],
            'show_in_menu'          => ['nullable','boolean'],
            'staff_ids'             => ['nullable','array'],
            'staff_ids.*'           => ['integer','exists:staff,id'],
        ];
    }
}
