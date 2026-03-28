@extends('layouts.auth')
@section('title', 'Setup — {{ ucwords(str_replace('-',' ','salon-profile')) }}')
@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-12" style="background:#FAF8F5">
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 max-w-xl w-full p-10">
    <h2 class="text-2xl font-bold text-gray-900 mb-2" style="font-family:'Playfair Display',serif">
      {{ ucwords(str_replace('-', ' ', 'salon-profile')) }}
    </h2>
    <p class="text-gray-500 mb-6">Complete this step to continue setting up your salon.</p>
    <form method="POST" action="{{ route('onboarding.complete-step', ['step' => 'salon-profile']) }}">
      @csrf
      <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-amber-700 text-sm mb-6">
        This step can also be completed later from your <strong>Settings</strong> page.
      </div>
      <div class="flex gap-3">
        <button type="submit" class="flex-1 bg-gray-900 text-white rounded-xl py-3 font-semibold hover:bg-gray-700 transition">
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
