@extends('layouts.auth')
@section('title', 'Change Password')
@section('content')
<div class="auth-header">
    <p class="auth-eyebrow">Security</p>
    <h2 class="auth-title">Set a new password</h2>
    <p class="auth-subtitle">At least 8 characters, with upper &amp; lower case letters and a number.</p>
</div>

<form action="{{ route('password.force.update') }}" method="POST" class="auth-form" data-password-confirm-match="1">
    @csrf
    <div class="auth-field">
        <label for="force-password" class="auth-label">New password</label>
        <div class="auth-password-wrap">
            <input id="force-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="••••••••"
                   class="auth-input auth-input--password @error('password') is-invalid @enderror"
                   data-validation-message="Password">
            @include('auth._password-visibility-toggle', ['targetId' => 'force-password'])
        </div>
        @error('password')<p class="auth-error">{{ $message }}</p>@enderror
    </div>

    <div class="auth-field">
        <label for="force-password-confirmation" class="auth-label">Confirm</label>
        <div class="auth-password-wrap">
            <input id="force-password-confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password" placeholder="••••••••"
                   class="auth-input auth-input--password"
                   data-confirm-for="force-password"
                   data-validation-message="Confirm password">
            @include('auth._password-visibility-toggle', ['targetId' => 'force-password-confirmation'])
        </div>
    </div>

    <button type="submit" class="auth-btn"><span>Continue</span></button>
</form>
@endsection
