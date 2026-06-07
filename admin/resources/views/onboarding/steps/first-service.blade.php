@extends('layouts.auth')
@section('title', 'Setup — ' . ($meta['title'] ?? 'Service selection'))
@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12" style="background:#FAF8F5">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-xl w-full p-10">
    <h2 class="text-2xl font-bold text-gray-900 mb-2" style="font-family:'Playfair Display',serif">
      {{ $meta['title'] ?? 'Service selection' }}
    </h2>
    <p class="text-gray-500 mb-6">{{ $meta['description'] ?? 'Complete this step to continue setting up your salon.' }}</p>
    <a href="{{ $meta['action_url'] ?? route('settings.index', ['tab' => 'services']) }}" class="w-full mb-4 inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-700 hover:bg-gray-50">
      {{ $meta['action_label'] ?? 'Configure Services' }}
    </a>
    <form method="POST" action="{{ route('onboarding.complete-step', ['step' => 'first-service']) }}">
      @csrf
      <div class="{{ !empty($meta['done']) ? 'bg-green-50 border-green-200 text-green-700' : 'bg-amber-50 border-amber-100 text-amber-700' }} border rounded-xl p-4 text-sm mb-6">
        @if(!empty($meta['done']))
          Step completed. Continue to the next onboarding step.
        @else
          Configure this step in Settings first, then click continue.
        @endif
      </div>
      <div class="flex gap-3">
        <button type="submit" class="flex-1 {{ !empty($meta['done']) ? 'bg-gray-900 hover:bg-gray-700' : 'bg-gray-400 cursor-not-allowed' }} text-white rounded-xl py-3 font-semibold transition" {{ !empty($meta['done']) ? '' : 'disabled' }}>
          Continue →
        </button>
        <a href="{{ route('onboarding.skip') }}" class="px-6 py-3 text-gray-400 hover:text-gray-600 rounded-xl border border-gray-200 transition text-sm">
          Skip
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
