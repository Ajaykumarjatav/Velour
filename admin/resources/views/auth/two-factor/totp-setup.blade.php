@extends('layouts.app')
@section('title', 'Set up Authenticator App')
@section('page-title', 'Set up Authenticator App')
@section('content')

<div class="max-w-lg">
  <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-6">

    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-velour-100 flex items-center justify-center text-velour-600 font-bold text-lg">1</div>
      <div>
        <p class="font-semibold text-gray-900">Scan the QR code</p>
        <p class="text-sm text-gray-500">Open your authenticator app and scan this code.</p>
      </div>
    </div>

    {{-- QR code rendered as an inline SVG via the QR code URL --}}
    <div class="flex justify-center">
      <div class="p-4 bg-white border-2 border-gray-200 rounded-2xl inline-block">
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrCodeUrl) }}"
             alt="QR Code" class="w-44 h-44">
      </div>
    </div>

    <div class="bg-gray-50 rounded-xl p-4 text-center">
      <p class="text-xs text-gray-400 mb-1">Can't scan? Enter this key manually:</p>
      <p class="font-mono text-lg font-bold text-gray-900 tracking-widest select-all">
        {{ implode(' ', str_split($secret, 4)) }}
      </p>
    </div>

    <div class="flex items-center gap-3 mt-2">
      <div class="w-10 h-10 rounded-xl bg-velour-100 flex items-center justify-center text-velour-600 font-bold text-lg">2</div>
      <div>
        <p class="font-semibold text-gray-900">Enter the 6-digit code</p>
        <p class="text-sm text-gray-500">Confirm setup by entering the code from your app.</p>
      </div>
    </div>

    <form method="POST" action="{{ route('two-factor.totp.confirm') }}" class="space-y-4">
      @csrf
      <div>
        <input type="text" name="code" inputmode="numeric" pattern="\d{6}" maxlength="6"
               placeholder="000000" required autocomplete="one-time-code"
               autofocus
               class="w-full text-center text-3xl font-mono font-bold tracking-widest px-4 py-3 rounded-xl border
                      @error('code') border-red-400 @else border-gray-200 @enderror
                      focus:outline-none focus:ring-2 focus:ring-velour-500">
        @error('code')<p class="mt-1 text-sm text-red-600 text-center">{{ $message }}</p>@enderror
      </div>

      <div class="flex gap-3">
        <button type="submit"
                class="flex-1 px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Confirm & enable
        </button>
        <a href="{{ route('two-factor.setup') }}"
           class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors">
          Cancel
        </a>
      </div>
    </form>

  </div>
</div>

@endsection
