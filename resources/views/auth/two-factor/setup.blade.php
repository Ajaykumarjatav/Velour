@extends('layouts.app')
@section('title', 'Two-Factor Authentication')
@section('page-title', 'Two-Factor Authentication')
@section('content')

<div class="max-w-2xl space-y-5">

  @if($enabled)
  {{-- 2FA is ON --}}
  <div class="bg-green-50 border border-green-200 rounded-2xl p-5 flex items-center gap-4">
    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
    </div>
    <div>
      <p class="font-semibold text-green-800">Two-factor authentication is active</p>
      <p class="text-sm text-green-600">Method: {{ $method === 'totp' ? 'Authenticator app (TOTP)' : 'Email OTP' }}</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
    <h2 class="font-semibold text-gray-900">Recovery codes</h2>
    <p class="text-sm text-gray-500">Recovery codes let you access your account if you lose your device.</p>
    <a href="{{ route('two-factor.recovery') }}"
       class="inline-block px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
      View recovery codes
    </a>
  </div>

  <div class="bg-white rounded-2xl border border-red-100 p-6">
    <h2 class="font-semibold text-gray-900 mb-2">Disable 2FA</h2>
    <p class="text-sm text-gray-500 mb-4">This will remove the extra layer of security from your account.</p>
    <form method="POST" action="{{ route('two-factor.disable') }}" x-data="{ open: false }">
      @csrf @method('DELETE')
      <button type="button" @click="open=!open"
              class="px-5 py-2.5 text-sm font-medium rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
        Disable two-factor authentication
      </button>
      <div x-show="open" x-cloak class="mt-4 space-y-3 border-t border-gray-100 pt-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm your password</label>
          <input type="password" name="password" required
                 class="w-full sm:w-64 px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
          @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit"
                class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-red-600 hover:bg-red-700 text-white transition-colors">
          Yes, disable 2FA
        </button>
      </div>
    </form>
  </div>

  @else
  {{-- 2FA is OFF — show setup options --}}
  <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 flex items-start gap-4">
    <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
      <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
      </svg>
    </div>
    <div>
      <p class="font-semibold text-amber-800">Two-factor authentication is not enabled</p>
      <p class="text-sm text-amber-700 mt-1">Add an extra layer of security to protect your account from unauthorised access.</p>
    </div>
  </div>

  <div class="grid sm:grid-cols-2 gap-5">

    {{-- TOTP Option --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col gap-4">
      <div class="w-12 h-12 rounded-2xl bg-velour-100 flex items-center justify-center text-2xl">📱</div>
      <div>
        <h3 class="font-semibold text-gray-900">Authenticator App</h3>
        <p class="text-sm text-gray-500 mt-1">Use Google Authenticator, Authy, or any TOTP app. Most secure option.</p>
      </div>
      <form method="POST" action="{{ route('two-factor.totp.setup') }}" class="mt-auto">
        @csrf
        <button type="submit"
                class="w-full px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Set up authenticator app
        </button>
      </form>
    </div>

    {{-- Email OTP Option --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col gap-4">
      <div class="w-12 h-12 rounded-2xl bg-blue-50 flex items-center justify-center text-2xl">📧</div>
      <div>
        <h3 class="font-semibold text-gray-900">Email OTP</h3>
        <p class="text-sm text-gray-500 mt-1">Receive a 6-digit code via email each time you sign in. Easier but less secure.</p>
      </div>
      <form method="POST" action="{{ route('two-factor.email.setup') }}" class="mt-auto"
            onsubmit="return confirm('Enable email OTP two-factor authentication?')">
        @csrf
        <button type="submit"
                class="w-full px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
          Set up email OTP
        </button>
      </form>
    </div>
  </div>
  @endif

</div>

@endsection
