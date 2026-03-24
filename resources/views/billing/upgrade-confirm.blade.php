@extends('layouts.app')
@section('title', 'Change Plan')
@section('page-title', 'Change Plan')
@section('content')

<div class="max-w-lg space-y-5">

  @php
    $isUpgrade = $targetPlan->isUpgradeFrom($current->key);
    $symbol    = config('billing.currency_symbol', '£');
    $price     = $interval === 'monthly' ? $targetPlan->priceMonthly : $targetPlan->priceYearly;
    $period    = $interval === 'monthly' ? 'month' : 'year';
  @endphp

  <div class="card p-6 space-y-5">
    <div class="flex items-center justify-between gap-4">
      <div class="text-center flex-1 bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
        <p class="stat-label mb-1">Current</p>
        <p class="font-bold text-heading">{{ $current->name }}</p>
      </div>
      <div class="text-2xl text-muted">→</div>
      <div class="text-center flex-1 bg-velour-50 dark:bg-velour-900/20 rounded-2xl p-4 border border-velour-200 dark:border-velour-800">
        <p class="text-xs text-velour-500 dark:text-velour-400 uppercase tracking-wide mb-1">New</p>
        <p class="font-bold text-velour-700 dark:text-velour-300">{{ $targetPlan->name }}</p>
      </div>
    </div>

    <div class="border-t border-gray-100 dark:border-gray-800 pt-4 space-y-3 text-sm text-body">
      <div class="flex justify-between">
        <span>New price</span>
        <strong class="text-heading">{{ $symbol }}{{ number_format($price, 0) }}/{{ $period }}</strong>
      </div>
      @if($isUpgrade)
      <div class="flex justify-between text-green-700 dark:text-green-400">
        <span>Billing</span>
        <strong>Prorated — charged now</strong>
      </div>
      @else
      <div class="flex justify-between text-amber-700 dark:text-amber-400">
        <span>Billing</span>
        <strong>Takes effect at next renewal</strong>
      </div>
      @endif
      @if($sub?->onTrial())
      <div class="flex justify-between text-velour-600 dark:text-velour-400">
        <span>Trial</span>
        <strong>Continues until {{ $sub->trial_ends_at->format('d M Y') }}</strong>
      </div>
      @endif
    </div>

    <form method="POST" action="{{ route('billing.change') }}" class="space-y-3 pt-2 border-t border-gray-100 dark:border-gray-800">
      @csrf @method('PATCH')
      <input type="hidden" name="plan"     value="{{ $targetPlan->key }}">
      <input type="hidden" name="interval" value="{{ $interval }}">
      <div class="flex gap-3">
        <button type="submit"
                class="flex-1 btn {{ $isUpgrade ? 'bg-velour-600 hover:bg-velour-700 text-white focus:ring-velour-500' : 'bg-amber-500 hover:bg-amber-600 text-white focus:ring-amber-400' }}">
          Confirm {{ $isUpgrade ? 'upgrade' : 'downgrade' }}
        </button>
        <a href="{{ route('billing.plans') }}" class="btn-outline">Cancel</a>
      </div>
    </form>
  </div>

</div>

@endsection
