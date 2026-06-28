@extends('layouts.app')
@section('title', 'Billing')
@section('page-title', 'Billing & Subscription')
@section('content')

@php $sym = config('billing.currency_symbol', '₹'); @endphp

<div class="max-w-4xl space-y-6">

  <div class="card overflow-hidden">
    <div class="px-6 py-5 bg-gradient-to-r from-velour-600 to-velour-500 text-white">
      <p class="text-xs font-semibold uppercase tracking-widest opacity-75 mb-1">Current Plan</p>
      <div class="flex items-end justify-between flex-wrap gap-3">
        <div>
          <p class="text-3xl font-black">{{ $current->name }}</p>
          <p class="text-sm opacity-80 mt-0.5">{{ $current->tagline }}</p>
          @if($user->trial_ends_at?->isFuture())
          <p class="text-xs opacity-90 mt-1">Trial ends {{ $user->trial_ends_at->format('d M Y') }}</p>
          @endif
        </div>
        @if($sub)
        <div class="text-right">
          @if($sub->onTrial())
          <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-bold rounded-xl border border-white/30">Trial ends {{ $sub->trial_ends_at?->format('d M Y') }}</span>
          @elseif($sub->canceled())
          <span class="px-3 py-1.5 bg-red-500/80 text-white text-sm font-bold rounded-xl">Cancels {{ $sub->ends_at?->format('d M Y') }}</span>
          @elseif($sub->pastDue())
          <span class="px-3 py-1.5 bg-amber-500/80 text-white text-sm font-bold rounded-xl">Payment overdue</span>
          @else
          <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-bold rounded-xl border border-white/30">Active</span>
          @endif
        </div>
        @endif
      </div>
    </div>
    <div class="px-6 py-5 space-y-4">
      @php $storeLimit = $current->limit('stores'); @endphp
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="text-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
          <p class="text-xl font-black text-heading">{{ $storeLimit === -1 ? '∞' : $storeLimit }}</p>
          <p class="stat-label mt-0.5">Stores allowed</p>
        </div>
        <div class="text-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
          <p class="text-xl font-black text-heading">{{ $transactions->where('status', 'success')->count() }}</p>
          <p class="stat-label mt-0.5">Successful payments</p>
        </div>
      </div>
      <div class="flex flex-wrap gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
        <a href="{{ route('billing.plans') }}" class="btn-primary">{{ $sub && !$sub->canceled() ? 'Change plan' : 'View plans' }}</a>
        @if($sub && $sub->onGracePeriod())
        <form method="POST" action="{{ route('billing.resume') }}">@csrf
          <button type="submit" class="btn border border-green-300 dark:border-green-700 text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20">Resume subscription</button>
        </form>
        @elseif($sub && !$sub->canceled())
        <a href="{{ route('billing.cancel') }}" class="btn border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Cancel subscription</a>
        @endif
      </div>
    </div>
  </div>

  @if($scheduledPlan && $user->scheduled_plan_starts_at)
  <div class="card p-6 border-amber-200 dark:border-amber-800 bg-amber-50/50 dark:bg-amber-950/20">
    <h2 class="font-semibold text-heading mb-2">Upcoming paid plan</h2>
    <p class="text-sm text-body">
      <strong class="text-heading">{{ $scheduledPlan->name }}</strong>
      ({{ $user->scheduled_plan_interval === 'yearly' ? 'Yearly' : 'Monthly' }})
      activates on <strong>{{ $user->scheduled_plan_starts_at->format('d M Y') }}</strong>.
    </p>
    <p class="text-xs text-muted mt-2">Your current plan stays active until that date. Payment is already received.</p>
  </div>
  @endif

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-4">Payment history</h2>
    @if($transactions->isEmpty())
    <p class="text-sm text-muted">No billing transactions yet.</p>
    @else
    <div class="overflow-x-auto -mx-2">
      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-muted border-b border-gray-100 dark:border-gray-800">
            <th class="py-2 px-2 font-medium">Date</th>
            <th class="py-2 px-2 font-medium">Plan</th>
            <th class="py-2 px-2 font-medium">Amount</th>
            <th class="py-2 px-2 font-medium">Status</th>
            <th class="py-2 px-2 font-medium">Activates</th>
          </tr>
        </thead>
        <tbody>
          @foreach($transactions as $tx)
          @php $txPlan = $tx->plan(); @endphp
          <tr class="border-b border-gray-50 dark:border-gray-800/80 last:border-0">
            <td class="py-3 px-2 text-body whitespace-nowrap">{{ $tx->created_at->format('d M Y, H:i') }}</td>
            <td class="py-3 px-2 text-heading">
              {{ $txPlan?->name ?? ucfirst($tx->plan_key) }}
              <span class="text-muted text-xs">· {{ ucfirst($tx->interval) }}</span>
            </td>
            <td class="py-3 px-2 tabular-nums text-heading">{{ $sym }}{{ number_format($tx->amount) }}</td>
            <td class="py-3 px-2">
              @if($tx->status === 'success')
              <span class="text-green-600 dark:text-green-400 font-medium">Paid</span>
              @elseif($tx->status === 'failed')
              <span class="text-red-600 dark:text-red-400 font-medium" title="{{ $tx->failure_reason }}">Failed</span>
              @else
              <span class="text-amber-600 dark:text-amber-400 font-medium">Pending</span>
              @endif
            </td>
            <td class="py-3 px-2 text-body whitespace-nowrap">
              {{ $tx->activates_at?->format('d M Y') ?? '—' }}
            </td>
          </tr>
          @if($tx->status === 'failed' && $tx->failure_reason)
          <tr>
            <td colspan="5" class="pb-3 px-2 text-xs text-red-600 dark:text-red-400">{{ $tx->failure_reason }}</td>
          </tr>
          @endif
          @endforeach
        </tbody>
      </table>
    </div>
    @endif
  </div>

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-4">Plan Features</h2>
    @php
      $featureLabels = [
        'online_booking'=>['Online booking','book'],'marketing'=>['Email & SMS marketing','mail'],
        'reports'=>['Advanced reports','chart'],'api_access'=>['API access','api'],
        'custom_domain'=>['Custom domain','globe'],'priority_support'=>['Priority support','star'],
        'white_label'=>['White-label branding','tag'],'remove_branding'=>['Remove EasyGrox branding','clean'],
      ];
    @endphp
    <div class="grid sm:grid-cols-2 gap-2">
      @foreach($featureLabels as $key => [$label, $icon])
      @php $has = $current->allows($key); @endphp
      <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ $has ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-800/60' }}">
        <span class="text-sm {{ $has ? 'text-heading font-medium' : 'text-muted line-through' }}">{{ $label }}</span>
        @if($has)
        <span class="ml-auto text-xs text-green-600 dark:text-green-400 font-bold">ok</span>
        @else
        <a href="{{ route('billing.plans') }}" class="ml-auto text-xs text-link font-medium">Upgrade</a>
        @endif
      </div>
      @endforeach
    </div>
  </div>

</div>
@endsection
