<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\OnboardNewTenant;
use App\Models\Salon;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class AuthController extends Controller
{
    // ── Login ─────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Your account has been suspended. Please contact support.']);
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        // Reset 2FA session flag on fresh login
        session()->forget('two_factor_passed');
        session()->forget('two_factor_code_sent');

        // If 2FA is enabled, redirect to challenge BEFORE giving access
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        // Super-admins don't operate within a tenant; send them to the admin area
        if ($user->system_role === 'super_admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        return redirect()->intended(route('dashboard'));
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'unique:users,email'],
            'password'    => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
            'salon_name'  => ['required', 'string', 'max:150'],
            'salon_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'plan'     => 'growth',
            'is_active'=> true,
        ]);

        // Assign tenant_admin role to salon owners
        $user->assignRole('tenant_admin');

        // Create salon
        $slug  = Str::slug($data['salon_name']);
        $count = Salon::withoutGlobalScopes()->where('slug', 'like', $slug . '%')->count();
        if ($count) $slug .= '-' . ($count + 1);

        $salon = Salon::withoutGlobalScopes()->create([
            'owner_id'   => $user->id,
            'name'       => $data['salon_name'],
            'slug'       => $slug,
            'subdomain'  => $slug,
            'phone'      => $data['salon_phone'] ?? null,
            'currency'   => 'GBP',
            'timezone'   => 'Europe/London',
            'is_active'  => true,
        ]);

        // Fire Registered event → triggers verification email
        event(new Registered($user));

        // Dispatch onboarding job
        dispatch(new OnboardNewTenant($user, $salon));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('verification.notice')
            ->with('success', 'Account created! Please check your email to verify your address.');
    }

    // ── Logout ────────────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Email Verification ────────────────────────────────────────────────────

    public function verificationNotice()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->route('dashboard');
        }
        return view('auth.verify-email');
    }

    public function verifyEmail(EmailVerificationRequest $request)
    {
        $request->fulfill(); // marks verified + fires Verified event
        return redirect()->route('dashboard')->with('success', 'Email verified. Welcome to Velour!');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('info', 'Email already verified.');
        }

        $request->user()->sendEmailVerificationNotification();

        return back()->with('success', 'Verification email resent. Please check your inbox.');
    }

    // ── Password Reset ────────────────────────────────────────────────────────

    public function showForgotPassword()
    {
        return view('auth.forgot');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'If that email exists in our system, a reset link has been sent.')
            : back()->withErrors(['email' => 'We could not send a reset link to that email.']);
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])->save();
                $user->tokens()->delete(); // invalidate all Sanctum tokens on reset
                session()->forget('two_factor_passed');
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Password reset successfully. Please sign in.')
            : back()->withErrors(['email' => __($status)]);
    }
}
