@extends('layouts.auth')
@section('title', 'Welcome to EasyGrox')
@section('auth_container_class', 'auth-container--wide')

@section('content')
<div class="auth-header" style="text-align:center">
    <div class="text-4xl sm:text-5xl mb-3" aria-hidden="true">✨</div>
    <p class="auth-eyebrow">Get started</p>
    <h2 class="auth-title">Welcome, {{ auth()->user()->name }}</h2>
    <p class="auth-subtitle" style="max-width:28rem;margin-left:auto;margin-right:auto">
        You're on your <strong class="auth-email-strong">14-day free trial</strong>.
        Let's get your business live in under 5 minutes.
    </p>
</div>

<div class="rounded-xl border border-amber-200/80 dark:border-amber-500/25 bg-amber-50 dark:bg-amber-950/40 px-4 py-3.5 mb-6 flex items-start gap-3">
    <span class="text-xl leading-none shrink-0" aria-hidden="true">⏳</span>
    <div class="min-w-0 text-left">
        <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">14 days free — no card required</p>
        <p class="text-xs text-amber-700 dark:text-amber-300/90 mt-0.5">Trial ends {{ now()->addDays(14)->format('j F Y') }}</p>
    </div>
</div>

<ul class="space-y-2.5 mb-7 text-left">
    @foreach([
        'Business profile — name, address & contact',
        'Opening hours — when you\'re open',
        'Your first service — what you offer',
        'Invite your team — optional',
    ] as $text)
    <li class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300">
        <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-md bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 text-xs font-bold" aria-hidden="true">✓</span>
        <span>{{ $text }}</span>
    </li>
    @endforeach
</ul>

<div class="auth-form" style="gap:0.75rem">
    <a href="{{ $startUrl }}" class="auth-btn" style="text-decoration:none;text-align:center">
        <span>Let's get started →</span>
    </a>
    <a href="{{ route('onboarding.skip') }}" class="auth-link-muted" style="text-align:center">
        Skip setup, I'll do it later
    </a>
</div>
@endsection
