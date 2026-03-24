<?php
namespace App\Http\Requests\Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'       => ['required','string','max:100','regex:/^[\pL\s\-]+$/u'],
            'email'      => ['required','email:rfc,dns','max:255','unique:users,email'],
            'password'   => ['required','confirmed', Password::min(8)->letters()->mixedCase()->numbers()],
            'phone'      => ['nullable','string','max:30','regex:/^[\+\d\s\(\)\-]+$/'],
            'salon_name' => ['required','string','max:150'],
            'plan'       => ['nullable','in:starter,growth,pro'],
        ];
    }
}
