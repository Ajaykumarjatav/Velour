@extends('layouts.auth')
@section('title', 'Create Account')
@section('auth_container_class', 'max-w-full sm:max-w-xl md:max-w-2xl')
@section('content')
<h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-4 sm:mb-6">Create your free account</h2>

<form action="{{ route('register.submit') }}" method="POST" class="space-y-4 sm:space-y-5" id="register-form">
    @csrf
    <div class="grid grid-cols-1 gap-4 sm:gap-5">
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Your full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                   class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="w-full min-w-0 px-4 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1.5" for="register-password">Password</label>
            <div class="relative">
                <input id="register-password" type="password" name="password" required autocomplete="new-password"
                       class="w-full min-w-0 pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password'])
            </div>
        </div>
        <div class="min-w-0">
            <label class="block text-sm font-medium text-gray-700 mb-1.5" for="register-password-confirmation">Confirm password</label>
            <div class="relative">
                <input id="register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full min-w-0 pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-base sm:text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password-confirmation'])
            </div>
        </div>
    </div>

    <button type="submit"
            class="w-full bg-velour-600 hover:bg-velour-700 active:bg-velour-800 text-white font-semibold py-3 sm:py-2.5 px-4 rounded-xl text-base sm:text-sm transition-colors shadow-sm mt-2 touch-manipulation">
        Create account
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Already have an account?
    <a href="{{ route('login') }}" class="text-velour-600 hover:text-velour-700 font-medium">Sign in</a>
</p>
@endsection
