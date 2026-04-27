@extends('layouts.auth')
@section('title', 'Change Password')
@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-2">Change your password</h2>
<p class="text-sm text-gray-500 mb-6">For security, you must set a new password before continuing.</p>

<form action="{{ route('password.force.update') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="force-password">New password</label>
        <div class="relative">
            <input id="force-password" type="password" name="password" required autocomplete="new-password"
                   class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent @error('password') border-red-400 @enderror">
            @include('auth._password-visibility-toggle', ['targetId' => 'force-password'])
        </div>
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="force-password-confirmation">Confirm new password</label>
        <div class="relative">
            <input id="force-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                   class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
            @include('auth._password-visibility-toggle', ['targetId' => 'force-password-confirmation'])
        </div>
    </div>

    <button type="submit"
            class="w-full bg-velour-600 hover:bg-velour-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm">
        Update password
    </button>
</form>
@endsection
