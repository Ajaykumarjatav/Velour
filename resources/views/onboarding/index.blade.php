@extends('layouts.auth')
@section('title', 'Welcome to Velour')

@push('styles')
<style>
  :root {
    --gold: #B8943A;
    --gold-light: #D4AF5A;
    --dark: #0F0E0C;
    --cream: #FAF8F5;
    --muted: #8A8070;
  }
  body { background: var(--cream); font-family: 'DM Sans', sans-serif; }
  .step-dot { width: 10px; height: 10px; border-radius: 50%; background: #D4C9B5; transition: background .3s; }
  .step-dot.active { background: var(--gold); transform: scale(1.3); }
  .step-dot.done { background: #2D7A4F; }
  .onboard-card { background: #fff; border: 1px solid #EDE9E2; border-radius: 20px;
    box-shadow: 0 8px 40px rgba(0,0,0,.06); max-width: 560px; }
  .btn-gold { background: var(--gold); color: #fff; border: none; border-radius: 10px;
    padding: .875rem 2rem; font-weight: 600; letter-spacing: .02em; transition: background .2s; }
  .btn-gold:hover { background: var(--gold-light); }
  .progress-bar-fill { background: linear-gradient(90deg, var(--gold), var(--gold-light));
    height: 4px; border-radius: 2px; transition: width .4s ease; }
  @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
  .fade-up { animation: fadeUp .5s ease both; }
</style>
@endpush

@section('content')
<div class="min-h-screen flex flex-col items-center justify-center px-4 py-12">

  {{-- Progress bar --}}
  <div class="w-full max-w-xl mb-8">
    <div class="w-full bg-gray-200 rounded-full h-1 mb-4">
      <div class="progress-bar-fill" style="width: 0%"></div>
    </div>
    <div class="flex justify-between">
      @foreach(['Profile','Hours','Service','Team','Live'] as $i => $label)
      <div class="flex flex-col items-center gap-1">
        <div class="step-dot {{ $i === 0 ? 'active' : '' }}" id="dot-{{ $i }}"></div>
        <span class="text-xs text-gray-400 hidden sm:block">{{ $label }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Card --}}
  <div class="onboard-card w-full mx-auto p-10 fade-up">

    {{-- Welcome step --}}
    <div id="step-welcome" class="step-panel">
      <div class="text-center mb-8">
        <div class="text-5xl mb-4">✨</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-3" style="font-family:'Playfair Display',serif">
          Welcome, {{ auth()->user()->name }}
        </h1>
        <p class="text-gray-500 text-lg leading-relaxed">
          You're on your <strong class="text-gray-800">14-day free trial</strong>. Let's get your salon
          live in under 5 minutes.
        </p>
      </div>

      {{-- Trial badge --}}
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-8 flex items-center gap-3">
        <span class="text-2xl">⏳</span>
        <div>
          <p class="font-semibold text-amber-800">14 days free — no card required</p>
          <p class="text-sm text-amber-600">Trial ends {{ now()->addDays(14)->format('j F Y') }}</p>
        </div>
      </div>

      {{-- Steps preview --}}
      <div class="space-y-3 mb-8">
        @foreach([
          ['✅','Salon profile — name, address & contact'],
          ['✅','Opening hours — when you\'re open'],
          ['✅','Your first service — what you offer'],
          ['✅','Invite your team — optional'],
        ] as [$icon, $text])
        <div class="flex items-center gap-3 text-gray-600 text-sm">
          <span class="text-green-500 font-bold">{{ $icon }}</span> {{ $text }}
        </div>
        @endforeach
      </div>

      <button class="btn-gold w-full text-base" onclick="goToStep('salon-profile')">
        Let's get started →
      </button>
      <p class="text-center mt-4">
        <a href="{{ route('onboarding.skip') }}" class="text-sm text-gray-400 hover:text-gray-600">
          Skip setup, I'll do it later
        </a>
      </p>
    </div>

  </div>

  {{-- Velour branding --}}
  <p class="text-xs text-gray-400 mt-6">
    Need help? <a href="{{ route('help.index') }}" class="underline">Visit our Help Centre</a>
  </p>
</div>

<script>
function goToStep(step) {
  window.location.href = '{{ route("onboarding.step", ["step" => ""]) }}' + step;
}
</script>
@endsection
