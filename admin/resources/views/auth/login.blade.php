@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
<div class="mb-8 space-y-3">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-velour-600/90">Account access</p>
    <div class="space-y-1.5">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-[1.75rem] sm:leading-snug">Welcome back</h2>
        <p class="text-sm leading-relaxed text-slate-500">Sign in to run your calendar, clients, and team in one calm place.</p>
    </div>
</div>

<form action="{{ route('login.submit') }}" method="POST" class="space-y-5">
    @csrf
    <div class="space-y-2">
        <label for="login-email" class="text-xs font-bold uppercase tracking-wide text-slate-600">Email</label>
        <input id="login-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@salon.com"
               class="w-full rounded-2xl border border-slate-200/90 bg-white/80 px-4 py-3.5 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0 @error('email') border-red-400 focus:border-red-400 @enderror">
        @error('email')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="space-y-2">
        <div class="flex items-center justify-between gap-2">
            <label for="login-password" class="text-xs font-bold uppercase tracking-wide text-slate-600">Password</label>
            <a href="{{ route('password.request') }}" class="text-xs font-semibold text-velour-600 transition-colors hover:text-velour-800">Forgot?</a>
        </div>
        <div class="relative">
            <input id="login-password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                   class="w-full rounded-2xl border border-slate-200/90 bg-white/80 py-3.5 pl-4 pr-12 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0">
            @include('auth._password-visibility-toggle', ['targetId' => 'login-password'])
        </div>
    </div>
    <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-slate-200/60 bg-gradient-to-br from-slate-50/90 to-white/50 px-4 py-3 text-sm text-slate-600 transition-all duration-300 hover:border-velour-200/50 hover:from-velour-50/30 focus-within:border-velour-300/40 focus-within:ring-2 focus-within:ring-velour-500/20">
        <input type="checkbox" name="remember" class="h-4 w-4 rounded-md border-slate-300 text-velour-600 shadow-sm focus:ring-velour-500">
        <span class="font-medium leading-snug">Stay signed in on this device</span>
    </label>
    <button type="submit"
            class="auth-btn-primary group relative mt-1 w-full overflow-hidden rounded-2xl py-4 text-sm font-bold text-white shadow-lg shadow-velour-600/30 transition-all duration-300 hover:shadow-xl hover:shadow-velour-600/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-velour-500 focus-visible:ring-offset-2 active:scale-[0.985]">
        <span class="absolute inset-0 bg-gradient-to-r from-velour-600 via-violet-600 to-purple-600 transition-opacity duration-300 group-hover:opacity-[0.92]" aria-hidden="true"></span>
        <span class="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 ease-out group-hover:translate-x-[100%]" aria-hidden="true"></span>
        <span class="relative z-10 tracking-wide">Sign in</span>
    </button>
</form>

<p class="mt-9 border-t border-slate-200/60 pt-8 text-center text-sm text-slate-500">
    New to Velour?
    <a href="{{ route('register') }}" class="auth-link-line font-semibold text-velour-700 decoration-transparent hover:text-velour-900">Create a free account</a>
</p>
@endsection
