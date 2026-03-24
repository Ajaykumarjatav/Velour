<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Notifications\TwoFactorCodeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * TwoFactorController
 *
 * Handles the complete 2FA lifecycle:
 *
 *  Setup flow (settings page):
 *    GET  /settings/two-factor          → showSetup()   — choose method
 *    POST /settings/two-factor/totp     → setupTotp()   — generate QR / secret
 *    POST /settings/two-factor/confirm  → confirmTotp() — verify first TOTP code
 *    POST /settings/two-factor/email    → setupEmail()  — enable email 2FA
 *    DELETE /settings/two-factor        → disable()     — turn off 2FA
 *    GET  /settings/two-factor/recovery → showRecovery()
 *    POST /settings/two-factor/recovery/regenerate → regenerateCodes()
 *
 *  Login challenge flow:
 *    GET  /two-factor/challenge         → showChallenge()
 *    POST /two-factor/challenge         → challenge()
 *    POST /two-factor/challenge/resend  → resendCode()  — email OTP only
 *    POST /two-factor/recovery          → recovery()
 */
class TwoFactorController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────────
    // Setup — entry point
    // ─────────────────────────────────────────────────────────────────────────

    public function showSetup(Request $request)
    {
        $user = $request->user();

        return view('auth.two-factor.setup', [
            'user'    => $user,
            'enabled' => $user->hasTwoFactorEnabled(),
            'method'  => $user->two_factor_method,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TOTP — Generate secret + QR code
    // ─────────────────────────────────────────────────────────────────────────

    public function setupTotp(Request $request)
    {
        $user   = $request->user();
        $google2fa = app('pragmarx.google2fa');

        // Generate a new secret and store temporarily in session (not DB yet —
        // we only commit it once the user verifies a valid TOTP code)
        $secret = $google2fa->generateSecretKey();
        session(['two_factor_pending_secret' => $secret]);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            config('app.name', 'Velour'),
            $user->email,
            $secret
        );

        return view('auth.two-factor.totp-setup', [
            'user'      => $user,
            'secret'    => $secret,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }

    public function confirmTotp(Request $request)
    {
        $request->validate(['code' => 'required|string|size:6|regex:/^\d{6}$/']);

        $user   = $request->user();
        $secret = session('two_factor_pending_secret');

        if (! $secret) {
            return back()->withErrors(['code' => 'Session expired. Please start 2FA setup again.']);
        }

        $google2fa = app('pragmarx.google2fa');
        $valid     = $google2fa->verifyKey($secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid code. Check your authenticator app and try again.']);
        }

        $user->enableTotpTwoFactor($secret);
        session()->forget('two_factor_pending_secret');

        return redirect()->route('two-factor.recovery')
            ->with('success', 'Authenticator app 2FA enabled. Save your recovery codes now.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Email OTP setup
    // ─────────────────────────────────────────────────────────────────────────

    public function setupEmail(Request $request)
    {
        $user = $request->user();
        $user->enableEmailTwoFactor();

        return redirect()->route('two-factor.recovery')
            ->with('success', 'Email OTP 2FA enabled. Save your recovery codes now.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Disable 2FA
    // ─────────────────────────────────────────────────────────────────────────

    public function disable(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $user->disableTwoFactor();

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Recovery Codes
    // ─────────────────────────────────────────────────────────────────────────

    public function showRecovery(Request $request)
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('two-factor.setup');
        }

        return view('auth.two-factor.recovery-codes', [
            'codes' => $user->two_factor_recovery_codes ?? [],
        ]);
    }

    public function regenerateCodes(Request $request)
    {
        $request->validate(['password' => 'required']);

        $user = $request->user();

        if (! Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password.']);
        }

        $user->update([
            'two_factor_recovery_codes' => $user->generateRecoveryCodes(),
        ]);

        return back()->with('success', 'Recovery codes regenerated. The old codes are now invalid.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Login Challenge
    // ─────────────────────────────────────────────────────────────────────────

    public function showChallenge(Request $request)
    {
        $user = Auth::user();

        if (! $user?->hasTwoFactorEnabled()) {
            return redirect()->intended($this->defaultRedirect());
        }

        // For email OTP: auto-send code on first visit
        if ($user->usesEmailTwoFactor() && ! session('two_factor_code_sent')) {
            $this->dispatchEmailOtp($user);
            session(['two_factor_code_sent' => true]);
        }

        return view('auth.two-factor.challenge', [
            'method' => $user->two_factor_method,
        ]);
    }

    public function challenge(Request $request)
    {
        $request->validate(['code' => 'required|string']);

        $user = Auth::user();
        $code = trim($request->code);

        $valid = match ($user->two_factor_method) {
            'totp'  => app('pragmarx.google2fa')->verifyKey($user->two_factor_secret, $code),
            'email' => $user->verifyEmailOtp($code),
            default => false,
        };

        if (! $valid) {
            return back()->withErrors(['code' => 'Invalid or expired code. Please try again.']);
        }

        // Mark 2FA as passed for this session
        session(['two_factor_passed' => true]);
        session()->forget('two_factor_code_sent');

        return redirect()->intended($this->defaultRedirect());
    }

    public function resendCode(Request $request)
    {
        $user = Auth::user();

        if (! $user?->usesEmailTwoFactor()) {
            return back()->withErrors(['code' => 'Email OTP is not enabled for your account.']);
        }

        $this->dispatchEmailOtp($user);
        session(['two_factor_code_sent' => true]);

        return back()->with('success', 'A new code has been sent to your email.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Recovery Code Login
    // ─────────────────────────────────────────────────────────────────────────

    public function recovery(Request $request)
    {
        $request->validate(['recovery_code' => 'required|string']);

        $user = Auth::user();

        if (! $user->useRecoveryCode($request->recovery_code)) {
            return back()->withErrors(['recovery_code' => 'Invalid recovery code.']);
        }

        // Disable 2FA after using a recovery code — user must re-enrol
        $user->disableTwoFactor();
        session(['two_factor_passed' => true]);

        return redirect()->route('two-factor.setup')
            ->with('warning', 'Recovery code accepted. 2FA has been disabled — please set it up again to stay secure.');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function dispatchEmailOtp($user): void
    {
        $code = $user->generateEmailOtp();
        $user->notify(new TwoFactorCodeNotification($code));
    }

    private function defaultRedirect(): string
    {
        return Auth::user()->isSuperAdmin()
            ? route('admin.dashboard')
            : route('dashboard');
    }
}
