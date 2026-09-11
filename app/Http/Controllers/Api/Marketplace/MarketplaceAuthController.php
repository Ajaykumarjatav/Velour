<?php

namespace App\Http\Controllers\Api\Marketplace;

use App\Http\Controllers\Controller;
use App\Models\MarketplaceCustomer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class MarketplaceAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = \App\Support\PhoneCountry::exceptCountryFields($request->validate(\App\Support\PhoneCountry::merge([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:marketplace_customers,email',
            'password' => ['required', 'string', PasswordRule::min(8)],
        ], 'phone', true)));

        $customer = MarketplaceCustomer::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'phone' => trim($data['phone']),
            'password' => Hash::make($data['password']),
            'default_notes' => '',
            'marketing_consent' => false,
        ]);

        return response()->json([
            'message' => 'Account created.',
            'customer' => $this->format($customer),
            'token' => $this->issueToken($customer),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $customer = MarketplaceCustomer::query()
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        if (! $customer || ! Hash::check($data['password'], $customer->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $customer->tokens()->where('name', 'marketplace')->delete();

        return response()->json([
            'customer' => $this->format($customer),
            'token' => $this->issueToken($customer),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['customer' => $this->format($request->user())]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = \App\Support\PhoneCountry::exceptCountryFields($request->validate(\App\Support\PhoneCountry::merge([
            'name' => 'sometimes|string|max:150',
            'default_notes' => 'nullable|string|max:500',
            'marketing_consent' => 'nullable|boolean',
        ])));

        /** @var MarketplaceCustomer $customer */
        $customer = $request->user();
        $customer->fill([
            'name' => array_key_exists('name', $data) ? trim($data['name']) : $customer->name,
            'phone' => array_key_exists('phone', $data) ? trim($data['phone']) : $customer->phone,
            'default_notes' => array_key_exists('default_notes', $data) ? (string) $data['default_notes'] : $customer->default_notes,
            'marketing_consent' => array_key_exists('marketing_consent', $data)
                ? (bool) $data['marketing_consent']
                : $customer->marketing_consent,
        ])->save();

        return response()->json(['customer' => $this->format($customer->fresh())]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $data = $request->validate(['email' => 'required|email']);

        $customer = MarketplaceCustomer::query()
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        $payload = ['message' => 'If an account exists for that email, a verification code has been sent.'];

        if ($customer) {
            $otp = (string) random_int(100000, 999999);
            $customer->update([
                'otp_hash' => Hash::make($otp),
                'otp_expires_at' => now()->addMinutes(15),
            ]);

            if (app()->environment(['local', 'testing'])) {
                $payload['debug_otp'] = $otp;
            }
        }

        return response()->json($payload);
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'password' => ['required', 'string', PasswordRule::min(8)],
        ]);

        $customer = MarketplaceCustomer::query()
            ->where('email', strtolower(trim($data['email'])))
            ->first();

        if (
            ! $customer
            || ! $customer->otp_hash
            || ! $customer->otp_expires_at
            || $customer->otp_expires_at->isPast()
            || ! Hash::check($data['otp'], $customer->otp_hash)
        ) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired verification code.'],
            ]);
        }

        $customer->update([
            'password' => Hash::make($data['password']),
            'otp_hash' => null,
            'otp_expires_at' => null,
        ]);
        $customer->tokens()->delete();

        return response()->json(['message' => 'Password reset successfully. You can now log in.']);
    }

    private function issueToken(MarketplaceCustomer $customer): string
    {
        return $customer->createToken('marketplace', ['marketplace'])->plainTextToken;
    }

    private function format(MarketplaceCustomer $customer): array
    {
        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'default_notes' => $customer->default_notes,
            'marketing_consent' => (bool) $customer->marketing_consent,
        ];
    }
}
