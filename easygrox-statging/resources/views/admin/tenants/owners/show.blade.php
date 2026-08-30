@extends('layouts.admin')
@section('title', $account->name)
@section('page-title', $account->name)

@section('content')
<div class="space-y-5" x-data="{ suspendStore: null, suspendStoreName: '', unsuspendStore: null, unsuspendStoreName: '' }">

  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <a href="{{ route('admin.tenants') }}" class="text-xs text-gray-500 hover:text-gray-300">← All tenants</a>
      <h2 class="text-xl font-bold text-white mt-1">{{ $account->name }}</h2>
      <p class="text-sm text-gray-500">{{ $account->email }}</p>
    </div>
    <a href="{{ route('admin.tenants.stores', $account->id) }}"
       class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 text-white hover:bg-velour-700">
      View stores →
    </a>
  </div>

  {{-- Block / Unblock account --}}
  <div class="bg-gray-900 border {{ $isBlocked ? 'border-red-800/60' : 'border-gray-800' }} rounded-2xl p-5">
    <div class="flex flex-wrap items-start justify-between gap-4">
      <div>
        <h3 class="text-sm font-semibold text-gray-200">Account access</h3>
        <p class="text-xs text-gray-500 mt-1">
          @if($isBlocked)
            This tenant is <strong class="text-red-400">blocked</strong> — cannot log in; all stores suspended.
          @else
            Account is <strong class="text-green-400">active</strong> — owner can log in and use stores.
          @endif
        </p>
      </div>
      @if($isBlocked)
      <form method="POST" action="{{ route('admin.tenants.owners.unblock', $account->id) }}" class="flex flex-wrap gap-2 items-end"
            onsubmit="return confirm('Unblock this tenant? They will be able to log in again.')">
        @csrf
        <div>
          <label class="block text-xs text-gray-500 mb-1">Note (optional)</label>
          <input type="text" name="unsuspend_reason" placeholder="Reason for unblock"
                 class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg w-48">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-green-700 hover:bg-green-600 text-white">
          Unblock account
        </button>
      </form>
      @else
      <form method="POST" action="{{ route('admin.tenants.owners.block', $account->id) }}" class="flex flex-wrap gap-2 items-end"
            onsubmit="return confirm('Block this tenant? Login will be disabled and all stores suspended.')">
        @csrf
        <div>
          <label class="block text-xs text-gray-500 mb-1">Reason *</label>
          <select name="reason" required class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg">
            <option value="payment_failure">Payment failure</option>
            <option value="policy_violation">Policy violation</option>
            <option value="fraud">Fraud</option>
            <option value="abuse">Abuse</option>
            <option value="requested">Requested by customer</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-500 mb-1">Internal note</label>
          <input type="text" name="notes" placeholder="Admin notes"
                 class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg w-40">
        </div>
        <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-red-700 hover:bg-red-600 text-white">
          Block account
        </button>
      </form>
      @endif
    </div>
  </div>

  <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
    @foreach([
      ['Stores', $aggregates['stores']],
      ['Active stores', $aggregates['active_stores']],
      ['Clients', number_format($aggregates['clients'])],
      ['Revenue (mo)', '£'.number_format($revenueThisMonth, 0)],
    ] as [$label, $value])
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
      <p class="text-xl font-bold text-white">{{ $value }}</p>
      <p class="text-xs text-gray-500 mt-1 uppercase">{{ $label }}</p>
    </div>
    @endforeach
  </div>

  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5" x-data="{ plan: '{{ old('plan', $account->plan ?? 'standard') }}' }">
    <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Plan & subscription</h3>
    <dl class="space-y-2 text-sm mb-4">
      <div class="flex justify-between"><dt class="text-gray-500">Current plan</dt><dd class="text-gray-200 font-medium">{{ \App\Billing\Plan::labelFor($account->plan) }}</dd></div>
      <div class="flex justify-between"><dt class="text-gray-500">Status</dt><dd class="text-gray-200">{{ $subscription?->stripe_status ?? ($account->trial_ends_at?->isFuture() ? 'Trial until '.$account->trial_ends_at->format('d M Y') : '—') }}</dd></div>
    </dl>
    <form method="POST" action="{{ route('admin.tenants.owners.plan', $account->id) }}" class="flex flex-wrap gap-2 items-end pt-4 border-t border-gray-800">
      @csrf
      <div class="flex-1 min-w-[140px]">
        <label class="block text-xs text-gray-500 mb-1">Assign plan</label>
        <select name="plan" x-model="plan" class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg">
          @foreach(\App\Billing\Plan::all() as $p)
          <option value="{{ $p->key }}" @selected($account->plan === $p->key)>{{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div x-show="plan === 'trial'" x-cloak class="w-24">
        <label class="block text-xs text-gray-500 mb-1">Days</label>
        <input type="number" name="trial_days" min="1" max="365" value="{{ config('billing.trial_days', 15) }}"
               class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-lg">
      </div>
      <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white">
        Apply
      </button>
    </form>
    <p class="text-[11px] text-gray-600 mt-2">No payment — tenant gets immediate access.</p>
    <form method="POST" action="{{ route('admin.users.impersonate', $account->id) }}" class="mt-4 pt-4 border-t border-gray-800">
      @csrf
      <button type="submit" class="text-xs text-gray-400 hover:text-white">Impersonate for full edit access →</button>
    </form>
  </div>

  @if($account->salons->isNotEmpty())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-4 py-3 border-b border-gray-800 flex flex-wrap items-center justify-between gap-2">
      <h3 class="text-xs font-semibold text-gray-400 uppercase">Stores</h3>
      <p class="text-[11px] text-gray-600">Suspend one store without blocking the whole account.</p>
    </div>
    <ul class="divide-y divide-gray-800/60">
      @foreach($account->salons as $salon)
      <li class="px-4 py-3 flex flex-wrap items-center justify-between gap-3 text-sm">
        <div class="min-w-0">
          <p class="text-gray-200 font-medium">{{ $salon->name }}</p>
          <p class="text-xs text-gray-500">{{ $salon->city ?? '—' }} ·
            @if($salon->is_active)
              <span class="text-green-400">Active</span>
            @else
              <span class="text-red-400">Suspended</span>
              @if($salon->suspension_reason)
                <span class="text-gray-600">· {{ str_replace('_', ' ', $salon->suspension_reason) }}</span>
              @endif
            @endif
          </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          @if($salon->is_active)
          <form method="POST" action="{{ route('admin.tenants.stores.enter', $salon->id) }}">
            @csrf
            <button type="submit" class="text-xs text-velour-400 hover:text-velour-300 font-semibold">Open panel</button>
          </form>
          @endif
          <a href="{{ route('admin.tenants.show', $salon->id) }}" class="text-xs text-gray-500 hover:text-gray-300">Admin data</a>
          @if(!$isBlocked)
            @if($salon->is_active)
            <button type="button"
                    @click="suspendStore = {{ $salon->id }}; suspendStoreName = @js($salon->name)"
                    class="text-xs px-2.5 py-1 rounded-lg border border-red-800/60 text-red-400 hover:bg-red-900/30 font-semibold">
              Suspend store
            </button>
            @else
            <button type="button"
                    @click="unsuspendStore = {{ $salon->id }}; unsuspendStoreName = @js($salon->name)"
                    class="text-xs px-2.5 py-1 rounded-lg border border-green-800/60 text-green-400 hover:bg-green-900/30 font-semibold">
              Reactivate store
            </button>
            @endif
          @else
            <span class="text-[11px] text-gray-600">Unblock account to manage stores</span>
          @endif
        </div>
      </li>
      @endforeach
    </ul>
  </div>
  @endif

  @if($overrides->isNotEmpty())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
    <h3 class="text-xs font-semibold text-gray-400 uppercase mb-3">Plan overrides</h3>
    <ul class="space-y-2 text-sm text-gray-300">
      @foreach($overrides as $override)
      <li>{{ $override->override_type }} · {{ $override->reason }} <span class="text-gray-600">({{ $override->created_at?->format('j M Y') }})</span></li>
      @endforeach
    </ul>
  </div>
  @endif

  @include('admin.tenants.partials.store-suspend-modals')
</div>
@endsection
