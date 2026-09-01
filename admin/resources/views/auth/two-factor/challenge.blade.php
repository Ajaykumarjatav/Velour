@extends('layouts.auth')
@section('title', 'Two-Factor Authentication')
@section('content')

<div class="auth-header">
    <p class="auth-eyebrow">Security check</p>
    <h2 class="auth-title">Verification required</h2>
    <p class="auth-subtitle">
      @if($method === 'totp')
        Enter the 6-digit code from your authenticator app.
      @else
        Enter the 6-digit code sent to your email address.
      @endif
    </p>
</div>

<div x-data="{ showRecovery: {{ $errors->has('recovery_code') ? 'true' : 'false' }} }">
  {{-- TOTP / Email OTP challenge --}}
  <div x-show="!showRecovery" class="auth-form" style="gap:1rem">
    <form method="POST" action="{{ route('two-factor.challenge.submit') }}" class="auth-form">
      @csrf
      <div class="auth-field">
        <label for="two-factor-code" class="auth-label">
          {{ $method === 'totp' ? 'Authenticator code' : 'Email verification code' }}
        </label>
        <input id="two-factor-code" type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
               placeholder="000000" required autocomplete="one-time-code" autofocus
               data-pattern-message="Enter the 6-digit code."
               class="auth-input @error('code') is-invalid @enderror"
               style="text-align:center;font-family:ui-monospace,monospace;font-size:1.5rem;letter-spacing:0.35em;font-weight:700">
        @error('code')<p class="auth-error">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="auth-btn"><span>Verify</span></button>
    </form>

    @if($method === 'email')
    <form method="POST" action="{{ route('two-factor.resend') }}" class="auth-form" data-no-client-validation>
      @csrf
      <button type="submit" class="auth-link" style="background:none;border:0;cursor:pointer;padding:0;font:inherit">
        Resend code
      </button>
    </form>
    @endif

    <button type="button" @click="showRecovery=true" class="auth-link-muted" style="background:none;border:0;cursor:pointer;padding:0;font:inherit;width:100%;text-align:center">
      Use a recovery code instead
    </button>
  </div>

  {{-- Recovery code fallback --}}
  <div x-show="showRecovery" x-cloak class="auth-form" style="gap:1rem">
    <p class="auth-subtitle" style="margin:0">Enter a recovery code</p>
    <form method="POST" action="{{ route('two-factor.challenge.recovery') }}" class="auth-form">
      @csrf
      <div class="auth-field">
        <label for="recovery-code" class="auth-label">Recovery code</label>
        <input id="recovery-code" type="text" name="recovery_code" required placeholder="XXXXX-XXXXX"
               value="{{ old('recovery_code') }}"
               class="auth-input @error('recovery_code') is-invalid @enderror"
               style="text-align:center;font-family:ui-monospace,monospace;font-size:1.05rem;text-transform:uppercase">
        @error('recovery_code')<p class="auth-error">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="auth-btn"><span>Use recovery code</span></button>
    </form>
    <button type="button" @click="showRecovery=false" class="auth-link-muted" style="background:none;border:0;cursor:pointer;padding:0;font:inherit;width:100%;text-align:center">
      ← Back
    </button>
  </div>

  <div class="auth-foot-link" style="margin-top:1.5rem;padding-top:1.25rem">
    <form method="POST" action="{{ route('logout') }}" data-no-client-validation>
      @csrf
      <button type="submit" class="auth-link-line" style="background:none;border:0;cursor:pointer;padding:0;font:inherit">Sign out</button>
    </form>
  </div>
</div>

@endsection
