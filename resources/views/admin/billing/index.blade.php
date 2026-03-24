@extends('layouts.admin')
@section('title', 'Billing Overview')
@section('page-title', 'Billing & Revenue')
@section('content')

{{-- MRR / ARR Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  @foreach([
    ['label' => 'MRR',             'value' => '£'.number_format($mrr),          'color' => 'text-velour-400'],
    ['label' => 'ARR',             'value' => '£'.number_format($arr),           'color' => 'text-green-400'],
    ['label' => 'On Trial',        'value' => number_format($trialCount),        'color' => 'text-blue-400'],
    ['label' => 'Past Due',        'value' => number_format($pastDueCount),      'color' => $pastDueCount > 0 ? 'text-red-400' : 'text-gray-500'],
    ['label' => 'Cancelled (mo.)', 'value' => number_format($cancelledThisMonth),'color' => $cancelledThisMonth > 0 ? 'text-amber-400' : 'text-gray-500'],
  ] as $stat)
  <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5 text-center">
    <p class="text-2xl font-black {{ $stat['color'] }}">{{ $stat['value'] }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-medium">{{ $stat['label'] }}</p>
  </div>
  @endforeach
</div>

<div class="grid lg:grid-cols-5 gap-6 mb-6">

  {{-- Plan Distribution --}}
  <div class="lg:col-span-2 bg-gray-900 rounded-2xl border border-gray-800 p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4 uppercase tracking-wider">Plan Distribution</h2>
    <div class="space-y-3">
      @foreach($planDistribution as $item)
      @php
        $barColor = match($item['plan']->color) {
          'velour' => 'bg-velour-500',
          'blue'   => 'bg-blue-500',
          'amber'  => 'bg-amber-500',
          default  => 'bg-gray-600',
        };
      @endphp
      <div>
        <div class="flex justify-between items-center mb-1.5">
          <span class="text-sm text-gray-300 font-medium">{{ $item['plan']->name }}</span>
          <div class="flex items-center gap-3 text-xs">
            <span class="text-gray-400">{{ $item['count'] }} users</span>
            @if($item['mrr'] > 0)
            <span class="text-green-400 font-semibold">£{{ number_format($item['mrr']) }}/mo</span>
            @endif
          </div>
        </div>
        <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
          <div class="{{ $barColor }} h-full rounded-full transition-all"
               style="width: {{ $item['percent'] }}%"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Webhook Health --}}
  <div class="lg:col-span-3 bg-gray-900 rounded-2xl border border-gray-800 p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">Webhook Health</h2>
      <a href="{{ route('admin.billing.webhooks') }}" class="text-xs text-velour-400 hover:text-velour-300">View all →</a>
    </div>

    {{-- Status pills --}}
    <div class="flex flex-wrap gap-3 mb-5">
      @foreach([
        'processed' => ['bg-green-900/40 text-green-400 border-green-800/50',  '✓'],
        'received'  => ['bg-blue-900/40 text-blue-400 border-blue-800/50',     '⟳'],
        'failed'    => ['bg-red-900/40 text-red-400 border-red-800/50',        '✕'],
        'ignored'   => ['bg-gray-800 text-gray-500 border-gray-700',           '—'],
      ] as $status => [$cls, $icon])
      @php $count = $webhookStats[$status] ?? 0; @endphp
      <div class="flex items-center gap-2 px-3 py-2 rounded-xl border {{ $cls }}">
        <span class="text-xs font-bold">{{ $icon }}</span>
        <span class="text-sm font-bold">{{ number_format($count) }}</span>
        <span class="text-xs opacity-70 capitalize">{{ $status }}</span>
      </div>
      @endforeach
    </div>

    {{-- Recent webhook calls --}}
    <div class="space-y-1 max-h-48 overflow-y-auto">
      @foreach($recentWebhooks as $wh)
      @php
        $statusColor = match($wh->status) {
          'processed' => 'text-green-400',
          'failed'    => 'text-red-400',
          'received'  => 'text-blue-400',
          default     => 'text-gray-500',
        };
      @endphp
      <div class="flex items-center gap-3 px-3 py-2 rounded-xl hover:bg-gray-800/50 transition-colors">
        <span class="text-xs {{ $statusColor }} font-bold w-16 flex-shrink-0 uppercase">{{ $wh->status }}</span>
        <span class="text-xs text-gray-300 font-mono flex-1 truncate">{{ $wh->type }}</span>
        <span class="text-xs text-gray-600 flex-shrink-0">{{ \Carbon\Carbon::parse($wh->created_at)->diffForHumans() }}</span>
        @if($wh->status === 'failed')
        <form method="POST" action="{{ route('admin.billing.webhook.replay', $wh->id) }}" class="flex-shrink-0">
          @csrf
          <button type="submit" class="text-xs text-amber-400 hover:text-amber-300 font-medium">↺ Retry</button>
        </form>
        @endif
      </div>
      @endforeach
    </div>
  </div>

</div>

{{-- Recent Subscriptions --}}
<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
  <h2 class="px-5 py-4 text-sm font-semibold text-gray-300 border-b border-gray-800 uppercase tracking-wider">
    Recent Subscriptions
  </h2>
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/40">
      <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">User</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Plan</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Trial / Ends</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Created</th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($recentSubscriptions as $sub)
    @php
      $statusColors = [
        'active'   => 'bg-green-900/50 text-green-400',
        'trialing' => 'bg-blue-900/50 text-blue-400',
        'past_due' => 'bg-red-900/50 text-red-400',
        'canceled' => 'bg-gray-800 text-gray-500',
        'paused'   => 'bg-amber-900/50 text-amber-400',
      ];
      $planColors = [
        'free'       => 'text-gray-500',
        'starter'    => 'text-blue-400',
        'pro'        => 'text-velour-400',
        'enterprise' => 'text-amber-400',
      ];
    @endphp
    <tr class="hover:bg-gray-800/30 transition-colors">
      <td class="px-5 py-3.5">
        <a href="{{ route('admin.users.show', $sub->user_id) }}" class="font-medium text-gray-200 hover:text-white">
          {{ $sub->user_name }}
        </a>
        <p class="text-xs text-gray-500">{{ $sub->user_email }}</p>
      </td>
      <td class="px-4 py-3.5 hidden md:table-cell">
        <span class="text-sm font-semibold {{ $planColors[$sub->plan] ?? 'text-gray-400' }}">
          {{ ucfirst($sub->plan ?? 'free') }}
        </span>
      </td>
      <td class="px-4 py-3.5">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $statusColors[$sub->stripe_status] ?? 'bg-gray-800 text-gray-400' }}">
          {{ ucfirst(str_replace('_', ' ', $sub->stripe_status)) }}
        </span>
      </td>
      <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-500">
        @if($sub->trial_ends_at)
          Trial → {{ \Carbon\Carbon::parse($sub->trial_ends_at)->format('d M Y') }}
        @elseif($sub->ends_at)
          Ends {{ \Carbon\Carbon::parse($sub->ends_at)->format('d M Y') }}
        @else
          —
        @endif
      </td>
      <td class="px-4 py-3.5 hidden sm:table-cell text-xs text-gray-500">
        {{ \Carbon\Carbon::parse($sub->created_at)->format('d M Y') }}
      </td>
    </tr>
    @empty
    <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">No subscriptions yet</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

@endsection
