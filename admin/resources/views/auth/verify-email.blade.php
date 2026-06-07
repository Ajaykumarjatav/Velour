@extends('layouts.auth')
@section('title', 'Verify Your Email')
@section('content')
<div class="mb-8 text-center">
    <div class="mx-auto mb-5 flex h-[4.25rem] w-[4.25rem] items-center justify-center rounded-2xl bg-gradient-to-br from-velour-100 via-violet-50 to-fuchsia-50 text-velour-700 shadow-inner ring-1 ring-velour-200/50">
        <svg class="h-9 w-9" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <p class="mb-2 text-[11px] font-bold uppercase tracking-[0.22em] text-velour-600/90">Almost there</p>
    <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-[1.75rem]">Check your inbox</h2>
    <p class="mt-3 text-sm leading-relaxed text-slate-500">
        We sent a link to<br>
        <strong class="font-semibold text-slate-800">{{ Auth::user()->email }}</strong>
    </p>
</div>

@if(session('success'))
    <div class="mb-5 rounded-2xl border border-emerald-200/70 bg-gradient-to-br from-emerald-50/95 to-teal-50/40 px-4 py-3 text-sm text-emerald-900 shadow-sm">
        {{ session('success') }}
    </div>
@endif

@if(session('email_error'))
    <div class="mb-5 rounded-2xl border border-amber-200/90 bg-amber-50/95 px-4 py-3 text-sm text-amber-950 shadow-sm" role="alert">
        {{ session('email_error') }}
    </div>
@endif

<div class="space-y-5 text-center">
    <p class="text-sm leading-relaxed text-slate-500">
        Nothing yet? Peek in spam—or resend below.
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit"
                class="auth-btn-primary group relative w-full overflow-hidden rounded-2xl py-4 text-sm font-bold text-white shadow-lg shadow-velour-600/30 transition-all duration-300 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-velour-500 focus-visible:ring-offset-2 active:scale-[0.985]">
            <span class="absolute inset-0 bg-gradient-to-r from-velour-600 via-violet-600 to-purple-600 transition-opacity duration-300 group-hover:opacity-[0.92]" aria-hidden="true"></span>
            <span class="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 ease-out group-hover:translate-x-[100%]" aria-hidden="true"></span>
            <span class="relative z-10 tracking-wide">Resend verification email</span>
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-sm font-medium text-slate-400 transition-colors hover:text-slate-700">
            Sign out
        </button>
    </form>
</div>
@endsection
