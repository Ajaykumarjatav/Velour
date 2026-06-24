@extends('layouts.admin')
@section('title', 'Plan Management')
@section('page-title', 'Plan Management')
@section('content')

@php
  $currency = config('billing.currency_symbol', '₹');
@endphp

<div class="space-y-5" x-data="{ plan: '{{ old('plan', 'standard') }}' }">

  {{-- Plan cards --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @foreach($planData as $item)
    @php
      $planKey = $item['plan']->key;
      $accentBg = match($planKey) {
        'premium'  => 'border-amber-700/60 bg-amber-900/10',
        'standard' => 'border-velour-700/60 bg-velour-900/10',
        'trial'    => 'border-blue-700/60 bg-blue-900/10',
        default    => 'border-gray-700 bg-gray-900',
      };
      $accentText = match($planKey) {
        'premium' => 'text-amber-400', 'standard' => 'text-velour-400',
        'trial' => 'text-blue-400', default => 'text-gray-400',
      };
    @endphp
    <div class="border rounded-2xl p-5 {{ $accentBg }}">
      <div class="flex items-start justify-between mb-3">
        <div>
          <h3 class="text-base font-bold text-white">{{ $item['plan']->name }}</h3>
          <p class="{{ $accentText }} text-sm font-semibold mt-0.5">
            @if($item['plan']->priceMonthly === 0)
              Free<span class="text-gray-500 text-xs font-normal"> · {{ $item['plan']->trialDays }}-day trial</span>
            @else
              {{ $currency }}{{ number_format($item['plan']->priceMonthly) }}<span class="text-gray-500 text-xs">/mo</span>
            @endif
          </p>
        </div>
        <span class="text-2xl font-black text-gray-200">{{ number_format($item['count']) }}</span>
      </div>
      <div class="space-y-1.5 text-xs text-gray-500">
        <div class="flex justify-between">
          <span>Monthly MRR</span>
          <span class="text-gray-300 font-medium">{{ $currency }}{{ number_format($item['mrr']) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Annual ARR</span>
          <span class="text-gray-300 font-medium">{{ $currency }}{{ number_format($item['arr']) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Store limit</span>
          <span class="text-gray-300">{{ $item['plan']->limit('stores') }}</span>
        </div>
        <div class="flex justify-between">
          <span>Features</span>
          <span class="text-green-400 font-medium">All included</span>
        </div>
      </div>
      @if($planKey === 'trial')
      <p class="mt-3 pt-3 border-t border-gray-700/50 text-xs text-blue-400/80">Default plan for new registrations</p>
      @endif
    </div>
    @endforeach
  </div>

  {{-- Assign plan (main action) --}}
  <div class="bg-gray-900 border border-velour-800/40 rounded-2xl p-5">
    <h2 class="text-base font-semibold text-white mb-1">Assign plan to tenant</h2>
    <p class="text-sm text-gray-500 mb-5">Instant access — no Cashfree payment. Updates plan + subscription in the system.</p>

    <form method="POST" action="{{ route('admin.plans.assign') }}" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
      @csrf
      <div class="sm:col-span-2">
        <label class="block text-xs text-gray-400 mb-1.5">Tenant *</label>
        <select name="user_id" required
                class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
          <option value="">Select owner account…</option>
          @foreach($tenants as $tenant)
          <option value="{{ $tenant->id }}" @selected(old('user_id') == $tenant->id)>
            {{ $tenant->name }} — {{ $tenant->email }}
            ({{ \App\Billing\Plan::labelFor($tenant->plan) }}@if($tenant->trial_ends_at) · trial {{ $tenant->trial_ends_at->format('d M Y') }}@endif)
          </option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">Plan *</label>
        <select name="plan" required x-model="plan"
                class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
          @foreach(\App\Billing\Plan::all() as $p)
          <option value="{{ $p->key }}">{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div x-show="plan === 'trial'" x-cloak>
        <label class="block text-xs text-gray-400 mb-1.5">Trial days</label>
        <input type="number" name="trial_days" min="1" max="365" value="{{ old('trial_days', config('billing.trial_days', 15)) }}"
               class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      </div>
      <div class="sm:col-span-2 lg:col-span-3">
        <label class="block text-xs text-gray-400 mb-1.5">Note (optional)</label>
        <input type="text" name="note" value="{{ old('note') }}" placeholder="e.g. Partner deal, manual renewal…"
               class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500 placeholder-gray-600">
      </div>
      <div class="sm:col-span-2 lg:col-span-1">
        <button type="submit" class="w-full px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Assign plan
        </button>
      </div>
    </form>
  </div>

  {{-- Recent --}}
  @if($recentAssignments->isNotEmpty())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-3 text-xs font-semibold text-gray-400 uppercase border-b border-gray-800">Recent assignments</h2>
    <ul class="divide-y divide-gray-800/50 text-sm">
      @foreach($recentAssignments as $log)
      <li class="px-5 py-2.5 flex justify-between gap-3">
        <span class="text-gray-300">{{ $log->description }}</span>
        <span class="text-xs text-gray-600 shrink-0">{{ $log->occurred_at->diffForHumans() }}</span>
      </li>
      @endforeach
    </ul>
  </div>
  @endif

  {{-- Advanced: bulk only --}}
  <details class="bg-gray-900 border border-gray-800 rounded-2xl">
    <summary class="px-5 py-3 text-sm text-gray-500 cursor-pointer hover:text-gray-300 select-none">Advanced — bulk move all tenants on one plan to another</summary>
    <form method="POST" action="{{ route('admin.plans.bulk-migrate') }}" class="px-5 pb-5 pt-2 flex flex-wrap gap-3 items-end border-t border-gray-800"
          onsubmit="return confirm('Move ALL tenants from one plan to another?')">
      @csrf
      <div>
        <label class="block text-xs text-gray-500 mb-1">From</label>
        <select name="from_plan" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg">
          @foreach(\App\Billing\Plan::all() as $p)<option value="{{ $p->key }}">{{ $p->name }}</option>@endforeach
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-500 mb-1">To</label>
        <select name="to_plan" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg">
          @foreach(\App\Billing\Plan::all() as $p)<option value="{{ $p->key }}">{{ $p->name }}</option>@endforeach
        </select>
      </div>
      <label class="flex items-center gap-2 text-xs text-gray-400 pb-2">
        <input type="checkbox" name="confirm" value="1" required class="rounded"> I confirm
      </label>
      <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-lg bg-amber-800/80 hover:bg-amber-700 text-white">Bulk assign</button>
    </form>
  </details>

</div>
@endsection
