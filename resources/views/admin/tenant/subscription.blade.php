@extends('layouts.app')
@section('title', 'Subscription')
@section('page-title', 'Subscription')
@section('content')

<div class="max-w-3xl space-y-6">

  @if(session('success'))
  <div class="px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">{{ session('success') }}</div>
  @endif

  {{-- Current plan card --}}
  <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-5 bg-gradient-to-r from-velour-600 to-velour-500 text-white">
      <p class="text-xs font-semibold uppercase tracking-widest opacity-75 mb-1">Current Plan</p>
      <div class="flex items-end justify-between gap-4 flex-wrap">
        <div>
          <p class="text-3xl font-black capitalize">{{ $user->plan ?? 'Free' }}</p>
          <p class="text-sm opacity-80 mt-0.5">
            {{ $plans[$user->plan ?? 'free']['name'] ?? 'Free' }} — {{ $plans[$user->plan ?? 'free']['tagline'] ?? 'Velour Salon Management' }}
          </p>
        </div>
        @php $sub = $user->subscription('default'); @endphp
        @if($sub)
        <div class="text-right">
          @if($sub->onTrial())
          <span class="px-3 py-1.5 bg-white/20 border border-white/30 text-white text-sm font-bold rounded-xl">
            Trial · {{ $sub->trial_ends_at->diffForHumans() }}
          </span>
          @elseif($sub->cancelled())
          <span class="px-3 py-1.5 bg-red-500/30 border border-red-300/30 text-white text-sm font-bold rounded-xl">
            Cancelled · ends {{ $sub->ends_at?->format('d M Y') }}
          </span>
          @else
          <span class="px-3 py-1.5 bg-white/20 border border-white/30 text-white text-sm font-bold rounded-xl">
            Active
          </span>
          @endif
        </div>
        @endif
      </div>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 border-t border-gray-100">
      @foreach([
        ['Staff', $plans[$user->plan ?? 'free']['staff'] ?? 1],
        ['Clients', $plans[$user->plan ?? 'free']['clients'] ?? 100],
        ['Price', '£'.($plans[$user->plan ?? 'free']['price'] ?? 0).'/mo'],
        ['Status', $sub ? ucfirst($sub->stripe_status ?? 'active') : 'No subscription'],
      ] as [$label, $val])
      <div class="px-5 py-4 text-center">
        <p class="text-lg font-bold text-gray-900">{{ $val }}</p>
        <p class="text-xs text-gray-400 mt-0.5">{{ $label }}</p>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Plan comparison --}}
  <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h2 class="font-semibold text-gray-900">All Plans</h2>
    </div>
    <div class="divide-y divide-gray-100">
      @foreach($plans as $key => $plan)
      <div class="px-6 py-4 flex items-center gap-4 {{ $key === ($user->plan ?? 'free') ? 'bg-velour-50' : '' }}">
        <div class="flex-1">
          <div class="flex items-center gap-2">
            <p class="font-semibold text-gray-900">{{ $plan['name'] }}</p>
            @if($key === ($user->plan ?? 'free'))
            <span class="px-2 py-0.5 text-xs font-bold bg-velour-100 text-velour-700 rounded-lg">Current</span>
            @endif
          </div>
          <p class="text-sm text-gray-500 mt-0.5">
            Up to {{ $plan['staff'] }} staff · {{ number_format($plan['clients']) }} clients
          </p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-lg font-bold text-gray-900">£{{ $plan['price'] }}<span class="text-sm font-normal text-gray-400">/mo</span></p>
          @if($key !== ($user->plan ?? 'free'))
          <a href="{{ route('billing.plans') }}"
             class="text-xs text-velour-600 hover:text-velour-700 font-medium">Change →</a>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Usage stats --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <h2 class="font-semibold text-gray-900 mb-4">Current Usage</h2>
    <div class="space-y-4">
      @php
        $limit = $plans[$user->plan ?? 'free']['staff'] ?? 1;
        $usedStaff = $salon->staff()->where('is_active', true)->count();
        $staffPct = $limit > 0 ? min(100, round(($usedStaff / $limit) * 100)) : 100;

        $clientLimit = $plans[$user->plan ?? 'free']['clients'] ?? 100;
        $usedClients = $salon->clients()->count();
        $clientPct = $clientLimit > 0 ? min(100, round(($usedClients / $clientLimit) * 100)) : 100;
      @endphp

      <div>
        <div class="flex justify-between text-sm mb-1">
          <span class="text-gray-600">Staff members</span>
          <span class="font-semibold {{ $staffPct >= 90 ? 'text-red-600' : 'text-gray-800' }}">
            {{ $usedStaff }} / {{ $limit }}
          </span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all {{ $staffPct >= 90 ? 'bg-red-500' : 'bg-velour-500' }}"
               style="width: {{ $staffPct }}%"></div>
        </div>
      </div>

      <div>
        <div class="flex justify-between text-sm mb-1">
          <span class="text-gray-600">Clients</span>
          <span class="font-semibold {{ $clientPct >= 90 ? 'text-red-600' : 'text-gray-800' }}">
            {{ number_format($usedClients) }} / {{ number_format($clientLimit) }}
          </span>
        </div>
        <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
          <div class="h-full rounded-full transition-all {{ $clientPct >= 90 ? 'bg-red-500' : 'bg-blue-500' }}"
               style="width: {{ $clientPct }}%"></div>
        </div>
      </div>
    </div>
  </div>

  {{-- Manage subscription --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-6 space-y-3">
    <h2 class="font-semibold text-gray-900 mb-2">Manage Subscription</h2>
    <div class="flex flex-wrap gap-3">
      <a href="{{ route('billing.plans') }}"
         class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Upgrade plan
      </a>
      <a href="{{ route('billing.dashboard') }}"
         class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
        View invoices
      </a>
      <a href="{{ route('billing.portal') }}"
         class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
        Stripe portal
      </a>
      @if($sub && !$sub->cancelled())
      <a href="{{ route('billing.cancel') }}"
         class="px-5 py-2.5 text-sm font-medium rounded-xl border border-red-200 hover:bg-red-50 text-red-600 transition-colors">
        Cancel subscription
      </a>
      @endif
    </div>
  </div>

</div>
@endsection
