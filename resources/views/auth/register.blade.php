@extends('layouts.auth')
@section('title', 'Create Account')
@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-6">Create your free account</h2>

<form action="{{ route('register.submit') }}" method="POST" class="space-y-4">
    @csrf
    <div class="grid grid-cols-1 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Your full name</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Salon name</label>
            <input type="text" name="salon_name" value="{{ old('salon_name') }}" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('salon_name') border-red-400 @enderror">
            @error('salon_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Salon phone <span class="text-gray-400">(optional)</span></label>
            <input type="tel" name="salon_phone" value="{{ old('salon_phone') }}"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('email') border-red-400 @enderror">
            @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5" for="register-password">Password</label>
            <div class="relative">
                <input id="register-password" type="password" name="password" required autocomplete="new-password"
                       class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password'])
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1.5" for="register-password-confirmation">Confirm password</label>
            <div class="relative">
                <input id="register-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
                @include('auth._password-visibility-toggle', ['targetId' => 'register-password-confirmation'])
            </div>
        </div>
    </div>

    <button type="submit"
            class="w-full bg-velour-600 hover:bg-velour-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm mt-2">
        Create account
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    Already have an account?
    <a href="{{ route('login') }}" class="text-velour-600 hover:text-velour-700 font-medium">Sign in</a>
</p>
@endsection
