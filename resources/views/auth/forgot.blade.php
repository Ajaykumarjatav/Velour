@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')
<h2 class="text-xl font-semibold text-gray-900 mb-2">Reset your password</h2>
<p class="text-sm text-gray-500 mb-6">Enter your email and we'll send you a reset link.</p>

<form action="{{ route('password.email') }}" method="POST" class="space-y-4">
    @csrf
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
        <input type="email" name="email" value="{{ old('email') }}" required
               class="w-full px-4 py-2.5 rounded-xl border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <button type="submit"
            class="w-full bg-velour-600 hover:bg-velour-700 text-white font-semibold py-2.5 px-4 rounded-xl text-sm transition-colors">
        Send reset link
    </button>
</form>

<p class="mt-6 text-center text-sm text-gray-500">
    <a href="{{ route('login') }}" class="text-velour-600 hover:text-velour-700 font-medium">← Back to sign in</a>
</p>
@endsection
