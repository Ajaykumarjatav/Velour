@extends('layouts.app')
@section('title', 'Payment successful')
@section('page-title', 'Payment successful')
@section('content')

<div class="max-w-lg mx-auto space-y-6 py-4">
  <div class="card p-8 text-center space-y-5">
    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 dark:bg-green-900/30">
      <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
      </svg>
    </div>

    <div>
      <h1 class="text-2xl font-black text-heading">Payment successful</h1>
      <p class="text-sm text-muted mt-2">{{ $message }}</p>
    </div>

    @if($scheduled_plan && $activates_at)
    <div class="rounded-2xl border border-amber-200 dark:border-amber-800 bg-amber-50 dark:bg-amber-950/30 px-4 py-3 text-left text-sm">
      <p class="font-semibold text-heading">Current plan</p>
      <p class="text-body">{{ $plan?->name ?? 'Trial' }} — active now</p>
      <p class="font-semibold text-heading mt-3">Paid plan starts</p>
      <p class="text-body">{{ $scheduled_plan->name }} on {{ $activates_at->format('d M Y') }}</p>
    </div>
    @elseif($plan)
  <div class="rounded-2xl bg-velour-50 dark:bg-velour-950/30 px-4 py-3 text-sm text-body">
      Active plan: <strong class="text-heading">{{ $plan->name }}</strong>
    </div>
    @endif

    <div class="flex flex-col gap-3 pt-2">
      <a href="{{ route('billing.dashboard') }}" class="btn-primary w-full">View billing details</a>
      <a href="{{ route('dashboard') }}" class="btn border border-gray-200 dark:border-gray-700 w-full">Go to dashboard</a>
    </div>
  </div>
</div>
@endsection
