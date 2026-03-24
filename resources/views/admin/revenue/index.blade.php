@extends('layouts.admin')
@section('title', 'Revenue')
@section('page-title', 'Revenue & Finance')
@section('content')

{{-- KPI strip --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  @foreach([
    ['MRR',          '£'.number_format($mrr),          'text-velour-400', 'Monthly Recurring Revenue'],
    ['ARR',          '£'.number_format($arr),           'text-green-400',  'Annual Run Rate'],
    ['ARPU',         '£'.number_format($arpu,2),        'text-blue-400',   'Avg Revenue per User'],
    ['LTV',          $ltv ? '£'.number_format($ltv,2) : '—', 'text-amber-400', 'Estimated Lifetime Value'],
  ] as [$label, $val, $color, $tooltip])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 text-center" title="{{ $tooltip }}">
    <p class="text-3xl font-black {{ $color }}">{{ $val }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $label }}</p>
  </div>
  @endforeach
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
  @foreach([
    ['Paying customers', number_format($totalPaying),      'text-white'],
    ['On trial',         number_format($trialCount),       'text-blue-400'],
    ['Past due',         number_format($pastDueCount),     $pastDueCount > 0 ? 'text-red-400' : 'text-gray-500'],
    ['Churn rate (mo.)', $churnRate.'%',                   $churnRate > 5 ? 'text-red-400' : 'text-amber-400'],
  ] as [$label, $val, $color])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 text-center">
    <p class="text-2xl font-bold {{ $color }}">{{ $val }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $label }}</p>
  </div>
  @endforeach
</div>

<div class="grid lg:grid-cols-5 gap-5 mb-5">

  {{-- MRR History bar chart --}}
  <div class="lg:col-span-3 bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-semibold text-gray-300">MRR – 12 Month History</h2>
      <a href="{{ route('admin.revenue.export') }}"
         class="text-xs text-velour-400 hover:text-velour-300">Export CSV ↓</a>
    </div>
    @php $maxMrr = max(array_values($mrrHistory)) ?: 1; @endphp
    <div class="flex items-end gap-1.5 h-40">
      @foreach($mrrHistory as $label => $value)
      @php $h = max(4, round(($value / $maxMrr) * 100)); @endphp
      <div class="flex-1 flex flex-col items-center gap-1 group">
        <div class="w-full bg-velour-600/80 hover:bg-velour-500 rounded-t transition-colors relative"
             style="height: {{ $h }}%"
             title="{{ $label }}: £{{ number_format($value) }}">
          <div class="absolute -top-6 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-700 text-gray-100 text-xs px-1.5 py-0.5 rounded whitespace-nowrap">
            £{{ number_format($value) }}
          </div>
        </div>
        <span class="text-gray-600 text-[9px] rotate-45 origin-left whitespace-nowrap">{{ substr($label, 0, 6) }}</span>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Plan distribution --}}
  <div class="lg:col-span-2 bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">Plan Distribution</h2>
    <div class="space-y-3">
      @foreach($planDistribution as $item)
      @php
        $barColor = match($item['plan']->key) {
          'enterprise' => 'bg-purple-500', 'pro' => 'bg-blue-500',
          'starter'    => 'bg-green-500',  default => 'bg-gray-600',
        };
      @endphp
      <div>
        <div class="flex justify-between items-center mb-1">
          <span class="text-sm text-gray-300">{{ $item['plan']->name }}</span>
          <div class="flex items-center gap-2 text-xs">
            <span class="text-gray-500">{{ $item['count'] }}</span>
            @if($item['mrr'] > 0)
              <span class="text-green-400 font-semibold">£{{ number_format($item['mrr']) }}/mo</span>
            @endif
          </div>
        </div>
        <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
          <div class="{{ $barColor }} h-full rounded-full" style="width: {{ $item['percent'] }}%"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">

  {{-- New vs Churned by month --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">New vs Churned (12 months)</h2>
    <div class="space-y-2">
      @foreach($monthlyGrowth as $label => $data)
      @php $max = max(array_max(array_column($monthlyGrowth, 'new')), array_max(array_column($monthlyGrowth, 'churned'))) ?: 1; @endphp
      <div class="flex items-center gap-2 text-xs">
        <span class="text-gray-500 w-14 flex-shrink-0">{{ substr($label,0,6) }}</span>
        <div class="flex-1 relative h-4">
          <div class="absolute inset-y-0 left-0 bg-green-500/60 rounded-l"
               style="width: {{ round(($data['new'] / $max) * 100) }}%"></div>
          <div class="absolute inset-y-0 right-0 bg-red-500/50 rounded-r"
               style="width: {{ round(($data['churned'] / $max) * 100) }}%"></div>
        </div>
        <span class="text-green-400 w-6 text-right">+{{ $data['new'] }}</span>
        <span class="text-red-400 w-6 text-right">-{{ $data['churned'] }}</span>
      </div>
      @endforeach
    </div>
    <div class="flex gap-4 mt-3 text-xs text-gray-500">
      <span class="flex items-center gap-1.5"><span class="w-3 h-2 bg-green-500/60 rounded inline-block"></span>New signups</span>
      <span class="flex items-center gap-1.5"><span class="w-3 h-2 bg-red-500/50 rounded inline-block"></span>Churned</span>
    </div>
  </div>

  {{-- Top revenue tenants --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-4 text-sm font-semibold text-gray-300 border-b border-gray-800">Top Revenue Accounts</h2>
    <div class="divide-y divide-gray-800/50">
      @forelse($topTenants as $i => $item)
      <div class="flex items-center gap-3 px-5 py-3">
        <span class="text-xs font-bold text-gray-600 w-5">{{ $i+1 }}</span>
        <div class="flex-1 min-w-0">
          <a href="{{ route('admin.tenants.show', $item['salon']?->id ?? 0) }}"
             class="text-sm font-medium text-gray-200 hover:text-white truncate block">
            {{ $item['salon']?->name ?? $item['user']->name }}
          </a>
          <p class="text-xs text-gray-500">{{ $item['user']->email }}</p>
        </div>
        <div class="text-right flex-shrink-0">
          <p class="text-sm font-bold text-green-400">£{{ number_format($item['mrr']) }}<span class="text-xs text-gray-500">/mo</span></p>
          <p class="text-xs text-gray-500 capitalize">{{ $item['user']->plan }}</p>
        </div>
      </div>
      @empty
      <p class="px-5 py-8 text-sm text-gray-600 text-center">No paying customers yet.</p>
      @endforelse
    </div>
  </div>

</div>

@endsection

@php
function array_max(array $arr): int { return count($arr) ? (int) max($arr) : 0; }
@endphp
