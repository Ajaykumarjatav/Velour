@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<div class="auth-header">
    <p class="auth-eyebrow">Password help</p>
    <h2 class="auth-title">Reset your password</h2>
    <p class="auth-subtitle">We’ll email you a secure link—check spam if it’s slow to arrive.</p>
</div>

<form action="{{ route('password.email') }}" method="POST" class="auth-form">
    @csrf
    <div class="auth-field">
        <label for="forgot-email" class="auth-label">Email</label>
        <input id="forgot-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@salon.com"
               class="auth-input @error('email') is-invalid @enderror">
        @error('email')<p class="auth-error">{{ $message }}</p>@enderror
    </div>
    <button type="submit" class="auth-btn"><span>Send reset link</span></button>
</form>

<p class="auth-foot-link">
    <a href="{{ route('login') }}" class="auth-link-line">← Back to sign in</a>
</p>
@endsection
