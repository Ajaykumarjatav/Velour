@extends('layouts.auth')
@section('title', 'You\'re Live!')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4" style="background: linear-gradient(135deg,#FAF8F5,#FFF8ED)">
  <div class="text-center max-w-lg">
    <div style="font-size:5rem" class="mb-4">🎉</div>
    <h1 class="text-4xl font-bold mb-3" style="font-family:'Playfair Display',serif;color:#0F0E0C">
      You're all set!
    </h1>
    <p class="text-gray-500 text-lg mb-8">
      <strong>{{ $salon->name }}</strong> is ready to take bookings. Share your link and start growing.
    </p>

    {{-- Booking link card --}}
    <div class="bg-white border border-amber-200 rounded-2xl p-6 mb-8 shadow-sm">
      <p class="text-sm text-gray-400 mb-2 font-medium uppercase tracking-wide">Your booking link</p>
      <div class="flex items-center gap-2">
        <code class="flex-1 bg-amber-50 text-amber-900 rounded-lg px-4 py-3 text-sm font-mono truncate">
          {{ config('app.url') }}/book/{{ $salon->slug }}
        </code>
        <button onclick="navigator.clipboard.writeText('{{ config('app.url') }}/book/{{ $salon->slug }}')"
          class="bg-amber-500 text-white rounded-lg px-4 py-3 text-sm font-semibold hover:bg-amber-600 transition">
          Copy
        </button>
      </div>
    </div>

    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="{{ route('dashboard') }}"
        class="bg-gray-900 text-white rounded-xl px-8 py-4 font-semibold hover:bg-gray-700 transition">
        Go to Dashboard
      </a>
      @if(config('billing.subscriptions_enabled'))
      <a href="{{ route('billing.plans') }}"
        class="border border-amber-400 text-amber-700 rounded-xl px-8 py-4 font-semibold hover:bg-amber-50 transition">
        View Plans
      </a>
      @endif
    </div>
  </div>
</div>
@endsection
