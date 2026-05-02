@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<div class="mb-8 space-y-3">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-velour-600/90">Password help</p>
    <div class="space-y-1.5">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-[1.75rem]">Reset your password</h2>
        <p class="text-sm leading-relaxed text-slate-500">We’ll email you a secure link—check spam if it’s slow to arrive.</p>
    </div>
</div>

<form action="{{ route('password.email') }}" method="POST" class="space-y-5">
    @csrf
    <div class="space-y-2">
        <label for="forgot-email" class="text-xs font-bold uppercase tracking-wide text-slate-600">Email</label>
        <input id="forgot-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@salon.com"
               class="w-full rounded-2xl border border-slate-200/90 bg-white/80 px-4 py-3.5 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0">
        @error('email')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror
    </div>
    <button type="submit"
            class="auth-btn-primary group relative w-full overflow-hidden rounded-2xl py-4 text-sm font-bold text-white shadow-lg shadow-velour-600/30 transition-all duration-300 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-velour-500 focus-visible:ring-offset-2 active:scale-[0.985]">
        <span class="absolute inset-0 bg-gradient-to-r from-velour-600 via-violet-600 to-purple-600 transition-opacity duration-300 group-hover:opacity-[0.92]" aria-hidden="true"></span>
        <span class="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 ease-out group-hover:translate-x-[100%]" aria-hidden="true"></span>
        <span class="relative z-10 tracking-wide">Send reset link</span>
    </button>
</form>

<p class="mt-9 border-t border-slate-200/60 pt-8 text-center text-sm text-slate-500">
    <a href="{{ route('login') }}" class="auth-link-line font-semibold text-velour-700 hover:text-velour-900">← Back to sign in</a>
</p>
@endsection
