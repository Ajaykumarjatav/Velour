@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
<div class="auth-header">
    <p class="auth-eyebrow">Account access</p>
    <h2 class="auth-title">Welcome back</h2>
    <p class="auth-subtitle">Sign in to run your calendar, clients, and team in one calm place.</p>
</div>

<form action="{{ route('login.submit') }}" method="POST" class="auth-form" autocomplete="on">
    @csrf
    <div class="auth-field">
        <label for="login-email" class="auth-label">Email</label>
        <input id="login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="you@business.com"
               class="auth-input @error('email') is-invalid @enderror">
        @error('email')<p class="auth-error">{{ $message }}</p>@enderror
    </div>
    <div class="auth-field">
        <div class="auth-label-row">
            <label for="login-password" class="auth-label">Password</label>
            <a href="{{ route('password.request') }}" class="auth-link">Forgot?</a>
        </div>
        <div class="auth-password-wrap">
            <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                   class="auth-input auth-input--password @error('password') is-invalid @enderror">
            @include('auth._password-visibility-toggle', ['targetId' => 'login-password'])
        </div>
        @error('password')<p class="auth-error">{{ $message }}</p>@enderror
    </div>

    <label class="auth-remember">
        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
        <span>Stay signed in on this device</span>
    </label>

    @if(config('captcha.turnstile.enabled') && filled(config('captcha.turnstile.site_key')))
        <div style="display:flex;justify-content:center">
            <div class="cf-turnstile" data-sitekey="{{ config('captcha.turnstile.site_key') }}" data-theme="light"></div>
        </div>
        @error('cf-turnstile-response')<p class="auth-error" style="text-align:center">{{ $message }}</p>@enderror
    @endif

    <button type="submit" class="auth-btn"><span>Sign in</span></button>
</form>

@if(config('captcha.turnstile.enabled') && filled(config('captcha.turnstile.site_key')))
    @push('scripts')
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var el = document.querySelector('.cf-turnstile');
                if (el) {
                    el.setAttribute('data-theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light');
                }
            });
        </script>
    @endpush
@endif

<p class="auth-foot-link">
    New to EasyGrox?
    <a href="{{ route('register') }}" class="auth-link-line">Create a free account</a>
</p>
@endsection
