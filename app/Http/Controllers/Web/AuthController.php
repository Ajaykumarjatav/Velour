<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\OnboardNewTenant;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\User;
use App\Support\ProfileCompletion;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

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

        if ($user->force_password_change) {
            return redirect()->route('password.force.show');
        }

        // Super-admins don't operate within a tenant; send them to the admin area
        if ($user->system_role === 'super_admin') {
            return redirect()->intended(route('admin.dashboard'));
        }

        // Self-heal: invited staff can end up with a soft-deleted staff row.
        if (! $user->salons()->exists() && ! $user->staffProfile()->exists()) {
            $staff = Staff::withoutGlobalScopes()
                ->where('user_id', $user->id)
                ->orderByDesc('id')
                ->first();
            if ($staff && $staff->deleted_at !== null) {
                $staff->restore();
                $staff->update(['is_active' => true]);
            }
        }

        $salon = $user->salons()->orderBy('id')->first();
        if ($salon) {
            $completion = ProfileCompletion::forSalon($salon);
            if ($completion['percentage'] < 100) {
                return redirect()->route('setup-progress');
            }
        }

        return redirect()->intended(route('dashboard'));
    }

    public function showForcePassword()
    {
        return view('auth.force-password');
    }

    public function forcePasswordUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
            'force_password_change' => false,
        ]);

        return redirect()->intended(route('dashboard'))->with('success', 'Password changed successfully.');
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                 => ['required', 'string', 'max:100'],
            'email'                => ['required', 'email', 'unique:users,email'],
            'password'             => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'plan'     => 'free',
            'is_active'=> true,
        ]);

        // Assign tenant_admin role to salon owners
        $user->assignRole('tenant_admin');

        // Create a default salon; richer setup now happens in Settings post-login.
        $defaultSalonName = trim($data['name']) !== '' ? ($data['name'] . "'s Salon") : 'My Salon';
        $slug  = Str::slug($defaultSalonName);
        if ($slug === '') {
            $slug = 'my-salon';
        }
        $count = Salon::withoutGlobalScopes()->where('slug', 'like', $slug . '%')->count();
        if ($count) $slug .= '-' . ($count + 1);
        $defaultBusinessTypeId = (int) \App\Models\BusinessType::query()->orderBy('sort_order')->value('id');
        if ($defaultBusinessTypeId < 1) {
            $defaultBusinessTypeId = (int) \App\Models\BusinessType::query()->orderBy('id')->value('id');
        }
        if ($defaultBusinessTypeId < 1) {
            throw ValidationException::withMessages([
                'email' => ['Registration is temporarily unavailable. Please contact support.'],
            ]);
        }

        $salon = Salon::withoutGlobalScopes()->create([
            'owner_id'         => $user->id,
            'business_type_id' => $defaultBusinessTypeId,
            'name'             => $defaultSalonName,
            'slug'             => $slug,
            'subdomain'        => $slug,
            'phone'            => null,
            'currency'         => 'GBP',
            'timezone'         => 'Europe/London',
            'is_active'        => true,
        ]);

        // Registered event queues/sends verification email — don't block signup if mail fails
        $verificationEmailSent = true;
        try {
            event(new Registered($user));
        } catch (\Throwable $e) {
            $verificationEmailSent = false;
            report($e);
        }

        dispatch(new OnboardNewTenant($user, $salon));

        Auth::login($user);
        $request->session()->regenerate();

        $redirect = redirect()->route('verification.notice')->with(
            'success',
            $verificationEmailSent
                ? 'Account created! Please check your email to verify your address.'
                : 'Account created!'
        );

        if (! $verificationEmailSent) {
            $redirect->with(
                'email_error',
                'We could not send the verification email (mail server error). Check your SMTP settings in .env, then use “Resend verification email” below.'
            );
        }

        return $redirect;
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
        $user = $request->user();
        $salon = $user->salons()->orderBy('id')->first();
        if ($salon) {
            $completion = ProfileCompletion::forSalon($salon);
            if ($completion['percentage'] < 100) {
                return redirect()->route('onboarding.index')->with('success', 'Email verified. Continue your setup to go live.');
            }
        }

        return redirect()->route('dashboard')->with('success', 'Email verified. Welcome to Velour!');
    }

    public function resendVerification(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return back()->with('info', 'Email already verified.');
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            report($e);

            return back()->with(
                'email_error',
                'We could not send the email. Verify your mail configuration (MAIL_* in .env) and try again.'
            );
        }

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
