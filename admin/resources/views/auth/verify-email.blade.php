@extends('layouts.auth')
@section('title', 'Verify Your Email')
@section('content')
<div class="auth-header" style="text-align:center">
    <div class="auth-verify-icon" aria-hidden="true">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <p class="auth-eyebrow">Almost there</p>
    <h2 class="auth-title">Check your inbox</h2>
    <p class="auth-subtitle">
        We sent a link to<br>
        <strong class="auth-email-strong">{{ Auth::user()->email }}</strong>
    </p>
</div>

@if(session('email_error'))
    <div class="auth-alert auth-alert--warn" role="alert">
        {{ session('email_error') }}
    </div>
@endif

<div class="auth-form" style="text-align:center">
    <p class="auth-subtitle" style="margin:0">
        Nothing yet? Peek in spam—or resend below.
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="auth-btn"><span>Resend verification email</span></button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="auth-link-muted">Sign out</button>
    </form>
</div>
@endsection
