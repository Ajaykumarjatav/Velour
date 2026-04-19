@extends('layouts.auth')
@section('title', 'Sign In')
@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-6">Sign in to your account</h2>

<form action="{{ route('login.submit') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
        <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
               class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="login-password">Password</label>
        <div class="relative">
            <input id="login-password" type="password" name="password" required autocomplete="current-password"
                   class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
            @include('auth._password-visibility-toggle', ['targetId' => 'login-password'])
        </div>
    </div>
    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
            <input type="checkbox" name="remember" class="rounded border-gray-300 text-velour-600">
            Remember me
        </label>
        <a href="{{ route('password.request') }}" class="text-sm text-velour-600 hover:text-velour-700 font-medium">Forgot password?</a>
    </div>
    <button type="submit"
            class="w-full bg-velour-600 hover:bg-velour-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm">
        Sign in
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Don't have an account?
    <a href="{{ route('register') }}" class="text-velour-600 hover:text-velour-700 font-medium">Create one free</a>
</p>
@endsection
