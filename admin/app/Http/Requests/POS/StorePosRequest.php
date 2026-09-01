<?php
namespace App\Http\Requests\POS;
use Illuminate\Foundation\Http\FormRequest;

class StorePosRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'client_id'               => ['nullable','integer','exists:clients,id'],
            'staff_id'                => ['required','integer','exists:staff,id'],
            'appointment_id'          => ['nullable','integer','exists:appointments,id'],
            'items'                   => ['required','array','min:1'],
            'items.*.name'            => ['required','string','max:255'],
            'items.*.type'            => ['required','in:service,product,voucher,tip'],
            'items.*.quantity'        => ['required','integer','min:1','max:999'],
            'items.*.unit_price'      => ['required','numeric','min:0','max:99999.99'],
            'items.*.discount'        => ['nullable','numeric','min:0'],
            'items.*.staff_id'        => ['nullable','integer','exists:staff,id'],
            'discount_code'           => ['nullable','string','max:50','alpha_num'],
            'tip_amount'              => ['nullable','numeric','min:0','max:999.99'],
            'payment_method'          => ['required','in:cash,card,split,voucher,account'],
            'amount_tendered'         => ['nullable','numeric','min:0'],
            'stripe_payment_intent_id'=> ['nullable','string','max:255'],
            'notes'                   => ['nullable','string','max:500'],
        ];
    }
}
