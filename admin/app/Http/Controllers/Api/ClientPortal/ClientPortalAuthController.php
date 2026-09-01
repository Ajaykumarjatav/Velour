<?php

namespace App\Http\Controllers\Api\ClientPortal;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Support\PublicSalonAccess;
use App\Notifications\ClientResetPasswordNotification;
use App\Scopes\TenantScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class ClientPortalAuthController extends Controller
{
    public function register(Request $request, string $salonSlug): JsonResponse
    {
        $salon = $request->attributes->get('salon') ?? PublicSalonAccess::findBySlugOrFail($salonSlug);

        $data = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'required|string|max:100',
            'email'             => 'required|email|max:150',
            'phone'             => 'required|string|max:30',
            'password'          => ['required', 'confirmed', PasswordRule::min(8)],
            'address'           => 'nullable|string|max:500',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'nullable|string|max:20',
            'marketing_consent' => 'nullable|boolean',
        ]);

        $existing = Client::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where(function ($q) use ($data) {
                $q->where('email', $data['email'])->orWhere('phone', $data['phone']);
            })
            ->first();

        if ($existing?->hasPortalAccount()) {
            throw ValidationException::withMessages([
                'email' => ['An account already exists with this email or phone. Please log in.'],
            ]);
        }

        $colors = ['#C4556B','#B8943A','#5A8A72','#3B82F6','#8B5CF6','#D97706','#059669'];

        if ($existing) {
            $existing->update([
                'first_name'        => trim($data['first_name']),
                'last_name'         => trim($data['last_name']),
                'email'             => $data['email'],
                'phone'             => $data['phone'],
                'password'          => Hash::make($data['password']),
                'address'           => $data['address'] ?? $existing->address,
                'date_of_birth'     => $data['date_of_birth'] ?? $existing->date_of_birth,
                'gender'            => $data['gender'] ?? $existing->gender,
                'marketing_consent' => $data['marketing_consent'] ?? $existing->marketing_consent,
                'source'            => $existing->source ?: 'online_portal',
            ]);
            $client = $existing->fresh();
        } else {
            $client = Client::create([
                'salon_id'          => $salon->id,
                'first_name'        => trim($data['first_name']),
                'last_name'         => trim($data['last_name']),
                'email'             => $data['email'],
                'phone'             => $data['phone'],
                'password'          => Hash::make($data['password']),
                'address'           => $data['address'] ?? null,
                'date_of_birth'     => $data['date_of_birth'] ?? null,
                'gender'            => $data['gender'] ?? null,
                'marketing_consent' => $data['marketing_consent'] ?? false,
                'email_consent'     => true,
                'sms_consent'       => true,
                'source'            => 'online_portal',
                'color'             => $colors[array_rand($colors)],
            ]);
        }

        $client->tokens()->where('name', 'client-portal')->delete();
        $token = $client->createToken('client-portal', ['client'])->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'client'  => $this->formatClient($client),
            'token'   => $token,
        ], 201);
    }

    public function login(Request $request, string $salonSlug): JsonResponse
    {
        $salon = $request->attributes->get('salon') ?? PublicSalonAccess::findBySlugOrFail($salonSlug);

        $data = $request->validate([
            'login'    => 'required|string|max:150',
            'password' => 'required|string',
        ]);

        $login = trim($data['login']);

        $client = Client::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where(function ($q) use ($login) {
                $q->where('email', $login)->orWhere('phone', $login);
            })
            ->first();

        if (! $client || ! $client->hasPortalAccount() || ! Hash::check($data['password'], $client->password)) {
            return response()->json(['message' => 'Invalid email/phone or password.'], 401);
        }

        if (in_array($client->status, ['blocked', 'erased'], true)) {
            return response()->json(['message' => 'This account is not available.'], 403);
        }

        $client->tokens()->where('name', 'client-portal')->delete();
        $token = $client->createToken('client-portal', ['client'])->plainTextToken;

        return response()->json([
            'client' => $this->formatClient($client),
            'token'  => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(['client' => $this->formatClient($request->user())]);
    }

    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'first_name'        => 'sometimes|string|max:100',
            'last_name'         => 'sometimes|string|max:100',
            'email'             => 'sometimes|email|max:150',
            'phone'             => 'sometimes|string|max:30',
            'address'           => 'nullable|string|max:500',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'nullable|string|max:20',
            'marketing_consent' => 'nullable|boolean',
        ]);

        /** @var Client $client */
        $client = $request->user();

        if (isset($data['email']) && $data['email'] !== $client->email) {
            $taken = Client::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $client->salon_id)
                ->where('email', $data['email'])
                ->where('id', '!=', $client->id)
                ->exists();
            if ($taken) {
                throw ValidationException::withMessages(['email' => ['This email is already in use.']]);
            }
        }

        if (isset($data['phone']) && $data['phone'] !== $client->phone) {
            $taken = Client::withoutGlobalScope(TenantScope::class)
                ->where('salon_id', $client->salon_id)
                ->where('phone', $data['phone'])
                ->where('id', '!=', $client->id)
                ->exists();
            if ($taken) {
                throw ValidationException::withMessages(['phone' => ['This phone number is already in use.']]);
            }
        }

        $client->update($data);

        return response()->json(['client' => $this->formatClient($client->fresh())]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => 'required|string',
            'password'         => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        /** @var Client $client */
        $client = $request->user();

        if (! Hash::check($data['current_password'], $client->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $client->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password updated.']);
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|max:2048']);

        /** @var Client $client */
        $client = $request->user();
        $path = $request->file('avatar')->store('client-avatars', 'public');
        $client->update(['avatar' => $path]);

        return response()->json([
            'avatar' => $path,
            'client' => $this->formatClient($client->fresh()),
        ]);
    }

    public function forgotPassword(Request $request, string $salonSlug): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $salon = $request->attributes->get('salon') ?? PublicSalonAccess::findBySlugOrFail($salonSlug);

        $client = Client::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('email', $request->email)
            ->whereNotNull('password')
            ->first();

        if ($client) {
            $token = Password::broker('clients')->createToken($client);
            $client->notify(new ClientResetPasswordNotification($token, $salonSlug));
        }

        return response()->json(['message' => 'If an account exists for that email, a reset link has been sent.']);
    }

    public function resetPassword(Request $request, string $salonSlug): JsonResponse
    {
        $data = $request->validate([
            'token'    => 'required|string',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $salon = $request->attributes->get('salon') ?? PublicSalonAccess::findBySlugOrFail($salonSlug);

        $client = Client::withoutGlobalScope(TenantScope::class)
            ->where('salon_id', $salon->id)
            ->where('email', $data['email'])
            ->first();

        if (! $client) {
            return response()->json(['message' => 'Invalid reset request.'], 422);
        }

        $status = Password::broker('clients')->reset(
            ['email' => $data['email'], 'password' => $data['password'], 'token' => $data['token']],
            function (Client $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password reset successfully. You can now log in.'])
            : response()->json(['message' => 'Invalid or expired reset token.'], 422);
    }

    private function formatClient(Client $client): array
    {
        return [
            'id'                => $client->id,
            'first_name'        => $client->first_name,
            'last_name'         => $client->last_name,
            'full_name'         => $client->full_name,
            'email'             => $client->email,
            'phone'             => $client->phone,
            'address'           => $client->address,
            'date_of_birth'     => $client->date_of_birth?->format('Y-m-d'),
            'gender'            => $client->gender,
            'avatar'            => $client->avatar,
            'avatar_url'        => $client->avatar ? asset('storage/'.$client->avatar) : null,
            'marketing_consent' => (bool) $client->marketing_consent,
        ];
    }
}
