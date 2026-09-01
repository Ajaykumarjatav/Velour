@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<div class="auth-header">
    <p class="auth-eyebrow">New credentials</p>
    <h2 class="auth-title">Choose a new password</h2>
    <p class="auth-subtitle">At least 8 characters, with upper &amp; lower case letters and a number.</p>
</div>

<form method="POST" action="{{ route('password.update') }}" class="auth-form" data-password-confirm-match="1">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="auth-field">
        <label for="reset-email" class="auth-label">Email</label>
        <input id="reset-email" type="email" name="email" value="{{ old('email', $email) }}" required autocomplete="username"
               class="auth-input @error('email') is-invalid @enderror">
        @error('email')<p class="auth-error">{{ $message }}</p>@enderror
    </div>

    <div class="auth-field">
        <label for="reset-password-new" class="auth-label">New password</label>
        <div class="auth-password-wrap">
            <input id="reset-password-new" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="••••••••"
                   class="auth-input auth-input--password @error('password') is-invalid @enderror"
                   data-validation-message="Password">
            @include('auth._password-visibility-toggle', ['targetId' => 'reset-password-new'])
        </div>
        @error('password')<p class="auth-error">{{ $message }}</p>@enderror
    </div>

    <div class="auth-field">
        <label for="reset-password-confirmation" class="auth-label">Confirm</label>
        <div class="auth-password-wrap">
            <input id="reset-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="••••••••"
                   class="auth-input auth-input--password"
                   data-confirm-for="reset-password-new"
                   data-validation-message="Confirm password">
            @include('auth._password-visibility-toggle', ['targetId' => 'reset-password-confirmation'])
        </div>
    </div>

    <button type="submit" class="auth-btn"><span>Update password</span></button>
</form>

<p class="auth-foot-link">
    <a href="{{ route('login') }}" class="auth-link-line">← Back to sign in</a>
</p>
@endsection
