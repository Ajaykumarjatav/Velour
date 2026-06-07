@extends('layouts.app')
@section('title', 'Recovery Codes')
@section('page-title', 'Recovery Codes')
@section('content')

<div class="max-w-lg space-y-5">

  @if(session('success'))
  <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm">{{ session('success') }}</div>
  @endif

  <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
    <p class="text-sm font-semibold text-amber-800 mb-1">⚠ Save these codes now</p>
    <p class="text-sm text-amber-700">Each code can only be used once. Store them in a password manager or print them. If you use a code, your 2FA will be disabled and you'll need to re-enrol.</p>
  </div>

  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="grid grid-cols-2 gap-2 font-mono text-sm">
      @foreach($codes as $code)
      <div class="bg-gray-50 px-4 py-2.5 rounded-xl text-center font-semibold text-gray-800 select-all tracking-wider">
        {{ $code }}
      </div>
      @endforeach
    </div>

    <button onclick="
      const text = {{ json_encode(implode("\n", $codes)) }};
      navigator.clipboard.writeText(text).then(() => alert('Codes copied!'));
    " class="mt-4 w-full px-5 py-2 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
      📋 Copy all codes
    </button>
  </div>

  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <h3 class="font-semibold text-gray-900 mb-3">Regenerate codes</h3>
    <p class="text-sm text-gray-500 mb-4">This will permanently invalidate all existing recovery codes.</p>
    <form method="POST" action="{{ route('two-factor.recovery.regenerate') }}"
          onsubmit="return confirm('This will permanently invalidate your existing recovery codes. Continue?')"
          x-data="{ show: false }">
      @csrf
      <button type="button" @click="show=!show"
              class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
        Regenerate recovery codes
      </button>
      <div x-show="show" x-cloak class="mt-4 space-y-3 border-t border-gray-100 pt-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Confirm your password</label>
          <input type="password" name="password" required
                 class="w-full sm:w-64 px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
          @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <button type="submit"
                class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-gray-800 hover:bg-gray-900 text-white transition-colors">
          Regenerate now
        </button>
      </div>
    </form>
  </div>

  <a href="{{ route('two-factor.setup') }}"
     class="inline-block text-sm text-gray-400 hover:text-gray-600">← Back to 2FA settings</a>

</div>

@endsection
