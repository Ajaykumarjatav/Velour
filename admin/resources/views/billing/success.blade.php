@extends('layouts.auth')
@section('title', 'Subscription Active')
@section('content')

<div class="max-w-md w-full mx-auto text-center">
  <div class="bg-white rounded-3xl border border-gray-200 p-10 space-y-6">

    {{-- Animated checkmark --}}
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100">
      <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
      </svg>
    </div>

    <div>
      <h1 class="text-2xl font-black text-gray-900">You're all set! 🎉</h1>
      <p class="text-gray-500 mt-2 text-sm">
        Your <strong class="text-gray-800">{{ $plan->name }}</strong> subscription is now active.
        @if($plan->trialDays)
        Your {{ $plan->trialDays }}-day free trial has started.
        @endif
      </p>
    </div>

    <div class="bg-velour-50 rounded-2xl p-4 text-left space-y-2.5">
      <p class="text-xs font-semibold text-velour-700 uppercase tracking-wider">What's included</p>
      @foreach(['online_booking'=>'Online booking widget','marketing'=>'Email & SMS marketing','reports'=>'Advanced reports','api_access'=>'API access'] as $f => $label)
      @if($plan->allows($f))
      <div class="flex items-center gap-2 text-sm text-velour-700">
        <span class="text-green-500 font-bold">✓</span> {{ $label }}
      </div>
      @endif
      @endforeach
    </div>

    <div class="flex flex-col gap-3">
      <a href="{{ route('dashboard') }}"
         class="w-full px-6 py-3 font-semibold rounded-2xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Open my dashboard →
      </a>
      <a href="{{ route('billing.dashboard') }}"
         class="w-full px-6 py-3 text-sm font-medium rounded-2xl border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors">
        View billing & invoices
      </a>
    </div>

  </div>
</div>

@endsection
