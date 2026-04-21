<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Salon;
use App\Support\RegistrationStaff;
use App\Support\RegistrationStarterServices;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /* ── Register ──────────────────────────────────────────────────────── */
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'email'                => 'required|email|unique:users,email',
            'password'             => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'salon_name'           => 'required|string|max:255',
            'business_type_ids'    => 'required|array|min:1',
            'business_type_ids.*'  => 'integer|exists:business_types,id',
            'salon_phone'          => 'nullable|string|max:30',
            'starter_categories'   => 'nullable|array',
            'starter_categories.*' => 'string',
            'starter_services'     => 'nullable|array',
            'starter_services.*'   => 'string',
            'staff_members'          => 'nullable|array|max:10',
            'staff_members.*.name'   => 'nullable|string|max:100',
            'staff_members.*.email'  => 'nullable|email|max:150',
            'staff_members.*.phone'  => 'nullable|string|max:20',
            'staff_members.*.role'   => 'nullable|in:owner,manager,stylist,therapist,receptionist,junior',
            'staff_members.*.commission_rate' => 'nullable|numeric|min:0|max:100',
            'staff_members.*.bio'    => 'nullable|string|max:1000',
            'staff_members.*.color'  => 'nullable|string|max:7',
            'plan'                 => 'nullable|in:free,starter,pro,enterprise',
        ]);

        $typeIds = array_values(array_unique(array_map('intval', $data['business_type_ids'])));
        $allowedCategoryKeys = RegistrationStarterServices::allowedCategoryKeysForTypeIds($typeIds);
        foreach ($data['starter_categories'] ?? [] as $k) {
            if (! in_array($k, $allowedCategoryKeys, true)) {
                throw ValidationException::withMessages([
                    'starter_categories' => ['One or more selected categories are not valid for your business types.'],
                ]);
            }
        }
        $allowedStarterKeys = RegistrationStarterServices::allowedKeysForTypeIds($typeIds);
        foreach ($data['starter_services'] ?? [] as $k) {
            if (! in_array($k, $allowedStarterKeys, true)) {
                throw ValidationException::withMessages([
                    'starter_services' => ['One or more selected services are not valid for your business types.'],
                ]);
            }
        }

        $rawStaff = $request->input('staff_members', []);
        $staffRows  = [];
        foreach ($data['staff_members'] ?? [] as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }
            if (trim((string) ($row['name'] ?? '')) === '') {
                continue;
            }
            if (empty($row['role']) || ! is_string($row['role'])) {
                throw ValidationException::withMessages([
                    "staff_members.{$idx}.role" => ['Choose a role for each team member you add.'],
                ]);
            }
            $v = $rawStaff[$idx]['assign_services'] ?? null;
            $row['assign_services'] = $v === '1' || $v === true || $v === 1
                || (is_array($v) && (in_array('1', $v, true) || in_array(1, $v, true)));
            $staffRows[] = $row;
        }

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'plan'     => $data['plan'] ?? 'free',
        ]);

        $slug = Str::slug($data['salon_name']);
        $count = Salon::where('slug', 'like', $slug . '%')->count();
        if ($count) $slug .= '-' . ($count + 1);

        $salon = Salon::create([
            'owner_id'         => $user->id,
            'business_type_id' => $typeIds[0],
            'name'             => $data['salon_name'],
            'slug'             => $slug,
            'subdomain'        => $slug,
            'phone'            => $data['salon_phone'] ?? null,
            'currency'         => 'GBP',
            'timezone'         => 'Europe/London',
            'is_active'        => true,
        ]);

        $salon->businessTypes()->sync($typeIds);

        RegistrationStarterServices::seedStarterCategories($salon, $data['starter_categories'] ?? []);
        RegistrationStarterServices::seedSalon($salon, $data['starter_services'] ?? []);

        RegistrationStaff::seed($salon, $staffRows);

        $token = $user->createToken('velour-api', ['*'])->plainTextToken;

        return response()->json([
            'message' => 'Account created successfully.',
            'user'    => $this->formatUser($user),
            'salon'   => $salon,
            'token'   => $token,
        ], 201);
    }

    /* ── Login ─────────────────────────────────────────────────────────── */
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return response()->json(['message' => 'Account is suspended.'], 403);
        }

        $user->update(['last_login_at' => now()]);
        $user->tokens()->where('name', 'velour-api')->delete();
        $token = $user->createToken('velour-api', ['*'])->plainTextToken;

        $salon = Salon::where('owner_id', $user->id)
                      ->orWhereHas('staff', fn($q) => $q->where('user_id', $user->id))
                      ->first();

        return response()->json([
            'user'  => $this->formatUser($user),
            'salon' => $salon,
            'token' => $token,
        ]);
    }

    /* ── Logout ─────────────────────────────────────────────────────────── */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out.']);
    }

    /* ── Me ─────────────────────────────────────────────────────────────── */
    public function me(Request $request): JsonResponse
    {
        $user  = $request->user();
        $salon = Salon::where('owner_id', $user->id)->first();
        return response()->json(['user' => $this->formatUser($user), 'salon' => $salon]);
    }

    /* ── Update profile ─────────────────────────────────────────────────── */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'sometimes|string|max:255',
            'phone'        => 'nullable|string|max:30',
            'password'     => ['nullable', 'confirmed', PasswordRule::min(8)],
        ]);

        $user = $request->user();
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update(array_filter($data, fn($v) => $v !== null));

        return response()->json(['user' => $this->formatUser($user)]);
    }

    /* ── Avatar upload ──────────────────────────────────────────────────── */
    public function updateAvatar(Request $request): JsonResponse
    {
        $request->validate(['avatar' => 'required|image|max:2048']);
        $path = $request->file('avatar')->store('avatars', 'public');
        $request->user()->update(['avatar' => $path]);
        return response()->json(['avatar' => $path]);
    }

    /* ── Forgot password ────────────────────────────────────────────────── */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent to your email.'])
            : response()->json(['message' => 'Email not found.'], 422);
    }

    /* ── Reset password ─────────────────────────────────────────────────── */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been reset.'])
            : response()->json(['message' => 'Invalid or expired token.'], 422);
    }

    /* ── Verify email ───────────────────────────────────────────────────── */
    public function verifyEmail(Request $request, int $id, string $hash): JsonResponse
    {
        $user = User::findOrFail($id);

        if (! hash_equals($hash, sha1($user->email))) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
        }

        return response()->json(['message' => 'Email verified.']);
    }

    /* ── Token refresh ──────────────────────────────────────────────────── */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $request->user()->currentAccessToken()->delete();
        $token = $user->createToken('velour-api', ['*'])->plainTextToken;
        return response()->json(['token' => $token]);
    }

    /* ── Private helpers ─────────────────────────────────────────────────── */
    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'avatar'        => $user->avatar,
            'phone'         => $user->phone,
            'plan'          => $user->plan,
            'is_active'     => $user->is_active,
            'last_login_at' => $user->last_login_at,
            'created_at'    => $user->created_at,
        ];
    }
}
