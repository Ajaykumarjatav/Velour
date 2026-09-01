@extends('layouts.auth')
@section('title', 'Payment failed')
@section('page-title', 'Payment failed')
@section('content')

<div class="max-w-lg mx-auto space-y-6 py-4">
  <div class="card p-8 text-center space-y-5">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 dark:bg-red-900/30">
      <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </div>

    <div>
      <h1 class="text-2xl font-black text-heading">Payment not completed</h1>
      <p class="text-sm text-muted mt-2">{{ $message ?? 'Something went wrong during checkout. No plan was changed.' }}</p>
    </div>

    <div class="flex flex-col gap-3 pt-2">
      <a href="{{ route('billing.plans') }}" class="btn-primary w-full">Try again</a>
      <a href="{{ route('billing.dashboard') }}" class="btn border border-gray-200 dark:border-gray-700 w-full">Billing overview</a>
    </div>
  </div>
</div>
@endsection
