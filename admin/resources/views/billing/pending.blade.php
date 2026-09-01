@extends('layouts.auth')
@section('title', 'Payment processing')
@section('page-title', 'Payment processing')
@section('content')

<div class="max-w-lg mx-auto space-y-6 py-4">
  <div class="card p-8 text-center space-y-5">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-amber-100 dark:bg-amber-900/30">
      <svg class="w-10 h-10 text-amber-500 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </div>

    <div>
      <h1 class="text-2xl font-black text-heading">Processing payment</h1>
      <p class="text-sm text-muted mt-2">{{ $message }}</p>
    </div>

    <div class="flex flex-col gap-3 pt-2">
      <a href="{{ route('billing.dashboard') }}" class="btn-primary w-full">Check billing status</a>
      <a href="{{ route('billing.plans') }}" class="btn border border-gray-200 dark:border-gray-700 w-full">Back to plans</a>
    </div>
  </div>
</div>
@endsection
