@extends('layouts.auth')
@section('title', 'Create Account')
@section('auth_container_class', 'max-w-full sm:max-w-lg md:max-w-xl')
@section('content')
<div class="mb-8 space-y-3">
    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-velour-600/90">Get started</p>
    <div class="space-y-1.5">
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-[1.75rem] sm:leading-snug">Create your account</h2>
        <p class="text-sm leading-relaxed text-slate-500">Onboard your salon and start booking in minutes—no clutter, just flow.</p>
    </div>
</div>

<form action="{{ route('register.submit') }}" method="POST" class="space-y-6 sm:space-y-7" id="register-form">
    @csrf
    <div class="grid grid-cols-1 gap-5 md:grid-cols-2 md:gap-x-6 md:gap-y-5">
        <div class="space-y-2 md:col-span-2">
            <label for="register-name" class="text-xs font-bold uppercase tracking-wide text-slate-600">Full name</label>
            <input id="register-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Alex Morgan"
                   class="w-full min-w-0 rounded-2xl border border-slate-200/90 bg-white/80 px-4 py-3.5 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0 @error('name') border-red-400 @enderror">
            @error('name')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-2 md:col-span-2">
            <label for="register-email" class="text-xs font-bold uppercase tracking-wide text-slate-600">Email</label>
            <input id="register-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@salon.com"
                   class="w-full min-w-0 rounded-2xl border border-slate-200/90 bg-white/80 px-4 py-3.5 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0 @error('email') border-red-400 @enderror">
            @error('email')<p class="text-xs font-medium text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="space-y-2">
            <label for="register-password" class="text-xs font-bold uppercase tracking-wide text-slate-600">Password</label>
            <div class="relative">
                <input id="register-password" type="password" name="password" required autocomplete="new-password" placeholder="Min. 8 characters"
                       class="w-full min-w-0 rounded-2xl border border-slate-200/90 bg-white/80 py-3.5 pl-4 pr-12 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password'])
            </div>
        </div>
        <div class="space-y-2">
            <label for="register-password-confirmation" class="text-xs font-bold uppercase tracking-wide text-slate-600">Confirm</label>
            <div class="relative">
                <input id="register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Repeat password"
                       class="w-full min-w-0 rounded-2xl border border-slate-200/90 bg-white/80 py-3.5 pl-4 pr-12 text-sm text-slate-900 shadow-auth-input placeholder:text-slate-400 transition-all duration-300 focus:border-velour-400 focus:bg-white focus:shadow-auth-input-focus focus:outline-none focus:ring-0">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password-confirmation'])
            </div>
        </div>
    </div>

    <p class="rounded-2xl border border-slate-100/80 bg-slate-50/50 px-4 py-3 text-xs leading-relaxed text-slate-500">
        By continuing, you agree to use Velour responsibly for your salon’s client and team data.
    </p>

    <button type="submit"
            class="auth-btn-primary group relative w-full overflow-hidden rounded-2xl py-4 text-sm font-bold text-white shadow-lg shadow-velour-600/30 transition-all duration-300 hover:shadow-xl hover:shadow-velour-600/35 focus:outline-none focus-visible:ring-2 focus-visible:ring-velour-500 focus-visible:ring-offset-2 active:scale-[0.985] touch-manipulation">
        <span class="absolute inset-0 bg-gradient-to-r from-velour-600 via-violet-600 to-purple-600 transition-opacity duration-300 group-hover:opacity-[0.92]" aria-hidden="true"></span>
        <span class="absolute inset-0 translate-x-[-100%] bg-gradient-to-r from-transparent via-white/25 to-transparent transition-transform duration-700 ease-out group-hover:translate-x-[100%]" aria-hidden="true"></span>
        <span class="relative z-10 tracking-wide">Create account</span>
    </button>
</form>

<p class="mt-9 border-t border-slate-200/60 pt-8 text-center text-sm text-slate-500">
    Already registered?
    <a href="{{ route('login') }}" class="auth-link-line font-semibold text-velour-700 decoration-transparent hover:text-velour-900">Sign in instead</a>
</p>
@endsection
