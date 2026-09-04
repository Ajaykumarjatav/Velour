<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\OnboardNewTenant;
use App\Models\Salon;
use App\Models\Staff;
use App\Models\User;
use App\Rules\ValidTurnstile;
use App\Services\LoginActivityService;
use App\Support\AuthPanel;
use App\Support\AuthRedirect;
use App\Support\ProfileCompletion;
use App\Support\TrustedDevice;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ── Login ─────────────────────────────────────────────────────────────────

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request, LoginActivityService $activity)
    {
        $credentials = $request->validate([
            'email'                 => ['required', 'email'],
            'password'              => ['required'],
            'cf-turnstile-response' => [new ValidTurnstile()],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt(
            ['email' => $credentials['email'], 'password' => $credentials['password']],
            $remember
        )) {
            $activity->recordFailure($request, $credentials['email']);

            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();
            $activity->recordFailure($request, $credentials['email'], 'account_suspended');

            return back()
                ->withErrors(['email' => 'Your account has been suspended. Please contact support.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        if ($remember) {
            $request->session()->put('auth.remember_requested', true);
        }

        // Reset 2FA session flag on fresh login
        session()->forget('two_factor_passed');
        session()->forget('two_factor_code_sent');

        // If 2FA is enabled, redirect to challenge BEFORE giving access
        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.challenge');
        }

        $activity->recordSuccess($user, $request);

        if ($remember) {
            TrustedDevice::issue($user);
        }

        if ($user->force_password_change) {
            return redirect()->route('password.force.show');
        }

        if (AuthPanel::typeFor($user) === AuthPanel::PLATFORM) {
            return redirect()->to(AuthRedirect::afterLoginUrl($request, $user));
        }

        if (AuthPanel::typeFor($user) === AuthPanel::STAFF) {
            return redirect()->to(AuthRedirect::afterLoginUrl($request, $user));
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
                $store = \App\Support\SalonUrl::key($salon);
                \Illuminate\Support\Facades\URL::defaults(['store' => $store]);

                return redirect()->to(route('setup-progress', ['store' => $store]));
            }
        }

        return redirect()->to(AuthRedirect::afterLoginUrl($request, $user));
    }

    public function showForcePassword()
    {
        return view('auth.force-password');
    }

    public function forcePasswordUpdate(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ]);

        $user->update([
            'password' => Hash::make($data['password']),
            'force_password_change' => false,
        ]);

        return redirect()->to(AuthRedirect::afterLoginUrl($request, $user))
            ->with('success', 'Password changed successfully.');
    }

    // ── Register ──────────────────────────────────────────────────────────────

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'business_name' => \App\Support\SalonSlug::uniqueNameRules(),
            'name'          => ['required', 'string', 'min:2', 'max:100'],
            'email'         => ['required', 'email', 'unique:users,email'],
            'password'      => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()],
        ], \App\Support\SalonSlug::uniqueNameMessages());

        $user = User::create([
            'name'          => $data['name'],
            'email'         => $data['email'],
            'password'      => Hash::make($data['password']),
            'plan'          => config('billing.default_plan', 'trial'),
            'trial_ends_at' => now()->addDays((int) config('billing.trial_days', 15)),
            'is_active'     => true,
        ]);

        // Assign tenant_admin role to salon owners
        $user->assignRole('tenant_admin');

        // Salon name + storefront URL slug come from business name — never from the owner's personal name.
        $businessName = trim($data['business_name']);
        $slug = \App\Support\SalonSlug::uniqueFromName($businessName);
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
            'name'             => $businessName,
            'slug'             => $slug,
            'subdomain'        => $slug,
            'email'            => $data['email'],
            'phone'            => null,
            'currency'         => \App\Helpers\CurrencyHelper::defaultCode(),
            'timezone'         => \App\Support\SalonTime::defaultTimezone(),
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

    public function logout(Request $request, LoginActivityService $activity)
    {
        $user = Auth::user();
        if ($user) {
            $activity->recordLogout($user);
        }

        Auth::logout();
        TrustedDevice::forget();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    // ── Email Verification ────────────────────────────────────────────────────

    public function verificationNotice()
    {
        if (Auth::user()->hasVerifiedEmail()) {
            return redirect()->to(AuthPanel::homeUrl(Auth::user()));
        }
        return view('auth.verify-email');
    }

    public function verifyEmail(Request $request, int $id, string $hash)
    {
        $user = User::find($id);
        if (! $user) {
            return redirect()->route('login')
                ->with('error', 'This verification link is invalid. Please register again or request a new link.');
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            return redirect()->route('login')
                ->with('error', 'This verification link does not match the account email. Please request a new verification email.');
        }

        // Already verified — old/reused links should not ask to resend verification.
        if ($user->hasVerifiedEmail()) {
            if (Auth::check() && (int) Auth::id() === (int) $user->id) {
                return redirect()->to(AuthPanel::homeUrl($user))
                    ->with('success', 'Your email is already verified.');
            }

            return redirect()->route('login')
                ->with('success', 'Your email is already verified. Please sign in to continue.');
        }

        $token = (string) $request->query('token', '');
        $tokenOk = $token !== '' && \App\Support\EmailVerificationToken::assertValid($user, $token);

        // Legacy emails used Laravel signed URLs (no token). Accept if signature is valid.
        $signedOk = false;
        if (! $tokenOk && $request->hasValidSignature(absolute: true)) {
            $signedOk = true;
        }
        if (! $tokenOk && ! $signedOk && $request->hasValidSignature(absolute: false)) {
            $signedOk = true;
        }

        if (! $tokenOk && ! $signedOk) {
            return redirect()->route('login')
                ->with('error', 'This verification link is invalid or expired. Sign in and use “Resend verification email”.');
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        \App\Support\EmailVerificationToken::forget($user);

        if (Auth::check() && (int) Auth::id() === (int) $user->id) {
            $salon = $user->salons()->orderBy('id')->first();
            if ($salon) {
                $completion = ProfileCompletion::forSalon($salon);
                if ($completion['percentage'] < 100) {
                    return redirect()
                        ->to(\App\Support\SalonUrl::route('onboarding.index', ['store' => \App\Support\SalonUrl::key($salon)]))
                        ->with('success', 'Email verified. Continue your setup to go live.');
                }
            }

            return redirect()->to(AuthPanel::homeUrl($user))
                ->with('success', 'Email verified. Welcome to EasyGrox!');
        }

        return redirect()->route('login')
            ->with('success', 'Email verified successfully. Please sign in to continue.');
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

        Password::sendResetLink($request->only('email'));

        // Always the same response to avoid account enumeration.
        return back()->with(
            'success',
            'If that email exists in our system, a reset link has been sent.'
        )->onlyInput('email');
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
