@extends('layouts.auth')
@section('title', 'Reset Password')
@section('content')

<div class="max-w-sm w-full mx-auto">
  <div class="text-center mb-8">
    <a href="{{ route('login') }}" class="inline-block text-2xl font-black text-velour-700 tracking-tight mb-6">velour.</a>
    <h1 class="text-2xl font-bold text-gray-900">Set a new password</h1>
    <p class="text-gray-500 mt-1 text-sm">Must be at least 8 characters with uppercase and numbers.</p>
  </div>

  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <input type="hidden" name="email" value="{{ $email }}">

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="reset-password-new">New password</label>
        <div class="relative">
          <input id="reset-password-new" type="password" name="password" required autocomplete="new-password"
                 class="w-full pl-4 pr-11 py-2.5 rounded-xl border @error('password') border-red-400 @else border-gray-200 @enderror
                        text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
          @include('auth._password-visibility-toggle', ['targetId' => 'reset-password-new'])
        </div>
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5" for="reset-password-confirmation">Confirm new password</label>
        <div class="relative">
          <input id="reset-password-confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                 class="w-full pl-4 pr-11 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
          @include('auth._password-visibility-toggle', ['targetId' => 'reset-password-confirmation'])
        </div>
      </div>

      <button type="submit"
              class="w-full px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors mt-2">
        Reset password
      </button>
    </form>
  </div>
</div>

@endsection
