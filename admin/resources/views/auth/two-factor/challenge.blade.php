@extends('layouts.auth')
@section('title', 'Two-Factor Authentication')
@section('content')

<div class="max-w-sm w-full mx-auto" x-data="{ showRecovery: false }">
  <div class="text-center mb-8">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-velour-100 mb-4">
      <svg class="w-8 h-8 text-velour-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
      </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900">Verification required</h1>
    <p class="text-gray-500 mt-1 text-sm">
      @if($method === 'totp')
        Enter the 6-digit code from your authenticator app.
      @else
        Enter the 6-digit code sent to your email address.
      @endif
    </p>
  </div>

  @foreach(['success','info','warning'] as $f)
    @if(session($f))
    <div class="mb-4 px-4 py-3 rounded-xl text-sm
      {{ $f === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : ($f === 'warning' ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
      {{ session($f) }}
    </div>
    @endif
  @endforeach

  {{-- TOTP / Email OTP challenge --}}
  <div x-show="!showRecovery" class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
    <form method="POST" action="{{ route('two-factor.challenge') }}">
      @csrf
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">
          {{ $method === 'totp' ? 'Authenticator code' : 'Email verification code' }}
        </label>
        <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
               placeholder="000000" required autocomplete="one-time-code" autofocus
               class="w-full text-center text-3xl font-mono font-bold tracking-widest px-4 py-3 rounded-xl
                      border @error('code') border-red-400 @else border-gray-200 @enderror
                      focus:outline-none focus:ring-2 focus:ring-velour-500">
        @error('code')<p class="mt-1 text-sm text-red-600 text-center">{{ $message }}</p>@enderror
      </div>

      <button type="submit"
              class="w-full px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Verify
      </button>
    </form>

    @if($method === 'email')
    <form method="POST" action="{{ route('two-factor.resend') }}" class="text-center">
      @csrf
      <button type="submit" class="text-sm text-velour-600 hover:text-velour-700 font-medium">
        Resend code
      </button>
    </form>
    @endif

    <button @click="showRecovery=true" class="w-full text-center text-sm text-gray-400 hover:text-gray-600">
      Use a recovery code instead
    </button>
  </div>

  {{-- Recovery code fallback --}}
  <div x-show="showRecovery" x-cloak class="bg-white rounded-2xl border border-gray-200 p-6 space-y-4">
    <p class="text-sm font-medium text-gray-700">Enter a recovery code</p>
    <form method="POST" action="{{ route('two-factor.recovery') }}">
      @csrf
      <div class="space-y-3">
        <input type="text" name="recovery_code" required placeholder="XXXXX-XXXXX"
               class="w-full text-center font-mono text-lg px-4 py-3 rounded-xl border
                      @error('recovery_code') border-red-400 @else border-gray-200 @enderror
                      focus:outline-none focus:ring-2 focus:ring-velour-500 uppercase">
        @error('recovery_code')<p class="mt-1 text-sm text-red-600 text-center">{{ $message }}</p>@enderror
        <button type="submit"
                class="w-full px-5 py-2.5 text-sm font-semibold rounded-xl bg-gray-800 hover:bg-gray-900 text-white transition-colors">
          Use recovery code
        </button>
      </div>
    </form>
    <button @click="showRecovery=false" class="w-full text-center text-sm text-gray-400 hover:text-gray-600">
      ← Back
    </button>
  </div>

  <div class="mt-4 text-center">
    <form method="POST" action="{{ route('logout') }}" class="inline">
      @csrf
      <button type="submit" class="text-sm text-gray-400 hover:text-gray-600">Sign out</button>
    </form>
  </div>
</div>

@endsection
