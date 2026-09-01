@extends('layouts.app')
@section('title', 'Subscription')
@section('page-title', 'Subscription')
@section('content')

@php
  $currency = config('billing.currency_symbol', '₹');
  $currentKey = $user->plan ?? config('billing.default_plan', 'trial');
  $currentPlan = $plans[$currentKey] ?? null;
  $sub = $user->subscription('default');
  $storeLimit = (int) ($currentPlan['stores'] ?? 0);
  $usedStores = $user->salons()->count();
  $storePct = $storeLimit > 0 ? min(100, round(($usedStores / $storeLimit) * 100)) : 100;
  $statusLabel = $sub
      ? ucfirst(str_replace('_', ' ', $sub->stripe_status ?? 'active'))
      : ($currentKey === 'trial' ? 'Trial' : 'No subscription');
@endphp

<div class="max-w-4xl space-y-6">

  <div class="card overflow-hidden">
    <div class="px-6 py-5 bg-gradient-to-r from-velour-600 to-velour-500 text-white">
      <p class="text-xs font-semibold uppercase tracking-widest opacity-75 mb-1">Current Plan</p>
      <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
          <p class="text-3xl font-black">{{ $currentPlan['name'] ?? ucfirst($currentKey) }}</p>
          <p class="text-sm opacity-80 mt-0.5">
            Up to {{ $currentPlan['stores'] ?? '—' }} stores · All features included
          </p>
        </div>
        @if($user->trial_ends_at?->isFuture() && $currentKey === 'trial')
        <span class="px-3 py-1.5 bg-white/20 border border-white/30 text-white text-sm font-bold rounded-xl">
          Trial ends {{ $user->trial_ends_at->format('d M Y') }}
        </span>
        @elseif($sub)
        <div class="text-right">
          @if($sub->onTrial())
          <span class="px-3 py-1.5 bg-white/20 border border-white/30 text-white text-sm font-bold rounded-xl">
            Trial ends {{ $sub->trial_ends_at?->format('d M Y') }}
          </span>
          @elseif($sub->canceled())
          <span class="px-3 py-1.5 bg-red-500/80 text-white text-sm font-bold rounded-xl">
            Cancels {{ $sub->ends_at?->format('d M Y') }}
          </span>
          @elseif(method_exists($sub, 'pastDue') && $sub->pastDue())
          <span class="px-3 py-1.5 bg-amber-500/80 text-white text-sm font-bold rounded-xl">Payment overdue</span>
          @else
          <span class="px-3 py-1.5 bg-white/20 border border-white/30 text-white text-sm font-bold rounded-xl">Active</span>
          @endif
        </div>
        @endif
      </div>
    </div>

    <div class="px-6 py-5">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="text-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
          <p class="text-xl font-black text-heading">{{ $currentPlan['stores'] ?? '—' }}</p>
          <p class="stat-label mt-0.5">Stores allowed</p>
        </div>
        <div class="text-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
          <p class="text-xl font-black text-heading">
            @if(($currentPlan['price'] ?? 0) > 0)
              {{ $currency }}{{ $currentPlan['price'] }}<span class="text-sm font-semibold text-muted">/mo</span>
            @else
              Free
            @endif
          </p>
          <p class="stat-label mt-0.5">Price</p>
        </div>
        <div class="text-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
          <p class="text-xl font-black text-heading">{{ $statusLabel }}</p>
          <p class="stat-label mt-0.5">Status</p>
        </div>
      </div>
    </div>
  </div>

  <div class="card overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800">
      <h2 class="font-semibold text-heading">All Plans</h2>
      <p class="text-sm text-muted mt-0.5">Compare tiers, then change plan from billing.</p>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-gray-800">
      @foreach($plans as $key => $plan)
      <div class="px-6 py-4 flex items-center gap-4 {{ $key === $currentKey ? 'bg-velour-50 dark:bg-velour-900/20' : '' }}">
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <p class="font-semibold text-heading">{{ $plan['name'] }}</p>
            @if($key === $currentKey)
            <span class="px-2 py-0.5 text-xs font-bold bg-velour-100 dark:bg-velour-900/50 text-velour-700 dark:text-velour-300 rounded-lg">Current</span>
            @endif
          </div>
          <p class="text-sm text-muted mt-0.5">
            Up to {{ $plan['stores'] }} stores · All features
          </p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-lg font-bold text-heading">
            @if(($plan['price'] ?? 0) > 0)
              {{ $currency }}{{ $plan['price'] }}<span class="text-sm font-normal text-muted">/mo</span>
            @else
              Free
            @endif
          </p>
          @if($key !== $currentKey)
          <a href="{{ route('billing.plans') }}" class="text-xs text-link font-medium">Change →</a>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-4">Current Usage</h2>
    <div class="flex justify-between text-sm mb-2">
      <span class="text-body">Stores</span>
      <span class="font-semibold {{ $storePct >= 90 ? 'text-red-600 dark:text-red-400' : 'text-heading' }}">
        {{ $usedStores }} / {{ $storeLimit > 0 ? $storeLimit : '∞' }}
      </span>
    </div>
    <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
      <div class="h-full rounded-full transition-all {{ $storePct >= 90 ? 'bg-red-500' : 'bg-velour-500' }}"
           style="width: {{ $storePct }}%"></div>
    </div>
    <p class="text-xs text-muted mt-2">Usage is counted across locations on this account.</p>
  </div>

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-1">Manage Subscription</h2>
    <p class="text-sm text-muted mb-4">Upgrade, invoices, billing home, or cancel from here.</p>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('billing.plans') }}" class="btn-primary">Upgrade plan</a>
      <a href="{{ route('billing.dashboard') }}#invoices" class="btn-outline">View invoices</a>
      <a href="{{ route('billing.dashboard') }}" class="btn-outline">Billing dashboard</a>
      @if($sub && !$sub->canceled())
      <a href="{{ route('billing.cancel') }}" class="btn border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Cancel subscription</a>
      @endif
    </div>
  </div>

</div>
@endsection
