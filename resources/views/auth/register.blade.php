@extends('layouts.auth')
@section('title', 'Create Account')
@section('auth_container_class', 'auth-container--wide')
@section('content')
<div class="auth-header">
    <p class="auth-eyebrow">Get started</p>
    <h2 class="auth-title">Create your account</h2>
    <p class="auth-subtitle">Onboard your salon and start booking in minutes—no clutter, just flow.</p>
</div>

<form action="{{ route('register.submit') }}" method="POST" class="auth-form" id="register-form"
      data-password-confirm-match="1">
    @csrf
    <div class="auth-grid">
        <div class="auth-field auth-field--full">
            <label for="register-name" class="auth-label">Full name</label>
            <input id="register-name" type="text" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name" placeholder="Alex Morgan"
                   class="auth-input @error('name') is-invalid @enderror">
            @error('name')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-field auth-field--full">
            <label for="register-email" class="auth-label">Email</label>
            <input id="register-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@salon.com"
                   class="auth-input @error('email') is-invalid @enderror">
            @error('email')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
        <div class="auth-field">
            <label for="register-password" class="auth-label">Password</label>
            <div class="auth-password-wrap">
                <input id="register-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min. 8 characters"
                       class="auth-input auth-input--password @error('password') is-invalid @enderror"
                       data-validation-message="Password">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password'])
            </div>
            @error('password')<p class="auth-error">{{ $message }}</p>@enderror
            <p class="auth-field-hint">At least 8 characters, with upper &amp; lower case letters and a number.</p>
        </div>
        <div class="auth-field">
            <label for="register-password-confirmation" class="auth-label">Confirm</label>
            <div class="auth-password-wrap">
                <input id="register-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="Repeat password"
                       class="auth-input auth-input--password @error('password_confirmation') is-invalid @enderror"
                       data-confirm-for="register-password"
                       data-validation-message="Confirm password">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password-confirmation'])
            </div>
            @error('password_confirmation')<p class="auth-error">{{ $message }}</p>@enderror
        </div>
    </div>

    <p class="auth-disclaimer">
        By continuing, you agree to use EasyGrox responsibly for your salon’s client and team data.
    </p>

    <button type="submit" class="auth-btn"><span>Create account</span></button>
</form>

<p class="auth-foot-link">
    Already registered?
    <a href="{{ route('login') }}" class="auth-link-line">Sign in instead</a>
</p>
@endsection
