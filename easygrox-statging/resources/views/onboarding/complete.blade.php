@extends('layouts.auth')
@section('title', 'You\'re Live!')
@section('auth_container_class', 'auth-container--wide')

@section('content')
<div class="auth-header" style="text-align:center">
    <div class="text-5xl mb-3" aria-hidden="true">🎉</div>
    <p class="auth-eyebrow">All done</p>
    <h2 class="auth-title">You're all set!</h2>
    <p class="auth-subtitle">
        <strong class="auth-email-strong">{{ $salon->name }}</strong> is ready to take bookings.
        Share your link and start growing.
    </p>
</div>

<div class="rounded-xl border border-amber-200 dark:border-amber-500/30 bg-amber-50/90 dark:bg-amber-950/40 p-4 sm:p-5 mb-6 text-left">
    <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400 mb-2">Your booking link</p>
    <div class="flex flex-col sm:flex-row gap-2">
        <code class="flex-1 min-w-0 rounded-lg bg-white dark:bg-slate-900/70 border border-amber-100 dark:border-amber-800/40 px-3 py-2.5 text-xs sm:text-sm font-mono text-amber-900 dark:text-amber-100 break-all">
            {{ \App\Support\StorefrontUrl::booking($salon) }}
        </code>
        <button type="button"
                onclick="navigator.clipboard.writeText(@js(\App\Support\StorefrontUrl::booking($salon)))"
                class="shrink-0 inline-flex items-center justify-center rounded-lg bg-amber-500 hover:bg-amber-600 text-white px-4 py-2.5 text-sm font-semibold transition-colors">
            Copy
        </button>
    </div>
</div>

<div class="flex flex-col gap-2.5">
    <a href="{{ route('dashboard', ['store' => \App\Support\SalonUrl::key($salon)]) }}" class="auth-btn" style="text-decoration:none;text-align:center">
        <span>Go to Dashboard</span>
    </a>
    <a href="{{ route('setup-progress', ['store' => \App\Support\SalonUrl::key($salon)]) }}"
       class="inline-flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-600 px-4 py-3 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
        View Setup Progress
    </a>
    <a href="{{ route('go-live') }}"
       class="inline-flex items-center justify-center rounded-xl border border-amber-300 dark:border-amber-600/50 px-4 py-3 text-sm font-semibold text-amber-800 dark:text-amber-200 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors">
        Open Go Live
    </a>
    @if(config('billing.subscriptions_enabled'))
    <a href="{{ route('billing.plans') }}"
       class="inline-flex items-center justify-center rounded-xl border border-amber-300 dark:border-amber-600/50 px-4 py-3 text-sm font-semibold text-amber-800 dark:text-amber-200 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition-colors">
        View Plans
    </a>
    @endif
</div>
@endsection
