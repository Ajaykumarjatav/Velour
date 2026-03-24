@extends('layouts.admin')
@section('title', 'Usage Analytics')
@section('page-title', 'Usage Analytics')
@section('content')

{{-- Platform totals --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
  @foreach([
    ['Total Salons',   number_format($totals['salons']),         'text-white'],
    ['Active Salons',  number_format($totals['active_salons']),  'text-green-400'],
    ['Total Users',    number_format($totals['users']),          'text-white'],
    ['Appointments',   number_format($totals['appointments']),   'text-velour-400'],
    ['Total Clients',  number_format($totals['clients']),        'text-blue-400'],
    ['Booked Today',   number_format($totals['bookings_today']), 'text-amber-400'],
  ] as [$label, $val, $color])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
    <p class="text-xl font-black {{ $color }}">{{ $val }}</p>
    <p class="text-xs text-gray-500 mt-0.5 uppercase tracking-wider leading-tight">{{ $label }}</p>
  </div>
  @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-5 mb-5">

  {{-- Salon growth bar chart --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">New Salons — 12 Month Growth</h2>
    @php $maxSG = max(array_values($salonGrowth)) ?: 1; @endphp
    <div class="flex items-end gap-1.5 h-36">
      @foreach($salonGrowth as $label => $count)
      @php $h = max(3, round(($count / $maxSG) * 100)); @endphp
      <div class="flex-1 flex flex-col items-center gap-1 group">
        <div class="w-full bg-velour-600/80 hover:bg-velour-500 rounded-t transition-colors relative"
             style="height: {{ $h }}%"
             title="{{ $label }}: {{ $count }} salons">
          <div class="absolute -top-6 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-700 text-gray-100
                      text-xs px-1.5 py-0.5 rounded whitespace-nowrap z-10">
            {{ $count }}
          </div>
        </div>
        <span class="text-gray-600 text-[9px] whitespace-nowrap">{{ substr($label,0,3) }}</span>
      </div>
      @endforeach
    </div>
    <div class="flex justify-between mt-2 text-xs text-gray-600">
      <span>{{ array_key_first($salonGrowth) }}</span>
      <span>{{ array_key_last($salonGrowth) }}</span>
    </div>
  </div>

  {{-- Appointment volume --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">Appointment Volume — 12 Months</h2>
    @php $maxAV = max(array_values($appointmentVolume)) ?: 1; @endphp
    <div class="flex items-end gap-1.5 h-36">
      @foreach($appointmentVolume as $label => $count)
      @php $h = max(3, round(($count / $maxAV) * 100)); @endphp
      <div class="flex-1 flex flex-col items-center gap-1 group">
        <div class="w-full bg-blue-600/70 hover:bg-blue-500 rounded-t transition-colors relative"
             style="height: {{ $h }}%"
             title="{{ $label }}: {{ number_format($count) }} appts">
          <div class="absolute -top-6 left-1/2 -translate-x-1/2 hidden group-hover:block bg-gray-700 text-gray-100
                      text-xs px-1.5 py-0.5 rounded whitespace-nowrap z-10">
            {{ number_format($count) }}
          </div>
        </div>
        <span class="text-gray-600 text-[9px] whitespace-nowrap">{{ substr($label,0,3) }}</span>
      </div>
      @endforeach
    </div>
    <div class="flex justify-between mt-2 text-xs text-gray-600">
      <span>{{ array_key_first($appointmentVolume) }}</span>
      <span>{{ array_key_last($appointmentVolume) }}</span>
    </div>
  </div>

</div>

<div class="grid lg:grid-cols-3 gap-5 mb-5">

  {{-- Feature adoption --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">Feature Adoption</h2>
    <div class="space-y-3">
      @foreach($adoption as $item)
      <div>
        <div class="flex justify-between items-center mb-1">
          <span class="text-xs text-gray-400">{{ $item['label'] }}</span>
          <span class="text-xs font-semibold text-gray-200">
            {{ number_format($item['count']) }}
            <span class="text-gray-500">({{ $item['percent'] }}%)</span>
          </span>
        </div>
        <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
          <div class="h-full bg-velour-500/80 rounded-full transition-all"
               style="width: {{ $item['percent'] }}%"></div>
        </div>
      </div>
      @endforeach
    </div>
  </div>

  {{-- Plan conversion funnel --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">Conversion Funnel</h2>
    @php
      $funnelMax = $planFunnel['registered'] ?: 1;
      $funnelSteps = [
        ['Registered',   $planFunnel['registered'], 'bg-gray-600'],
        ['Started trial',$planFunnel['trialing'],   'bg-blue-600'],
        ['Paid',         $planFunnel['paid'],        'bg-green-600'],
        ['Churned',      $planFunnel['churned'],     'bg-red-600'],
      ];
    @endphp
    <div class="space-y-3">
      @foreach($funnelSteps as [$label, $count, $barColor])
      @php $pct = round(($count / $funnelMax) * 100); @endphp
      <div>
        <div class="flex justify-between items-center mb-1">
          <span class="text-xs text-gray-400">{{ $label }}</span>
          <span class="text-xs font-semibold text-gray-200">{{ number_format($count) }}</span>
        </div>
        <div class="h-3 bg-gray-800 rounded-full overflow-hidden">
          <div class="h-full {{ $barColor }}/70 rounded-full" style="width: {{ $pct }}%"></div>
        </div>
      </div>
      @endforeach
    </div>
    @php
      $trialConv = $planFunnel['trialing'] > 0
        ? round(($planFunnel['paid'] / $planFunnel['trialing']) * 100, 1) : 0;
    @endphp
    <p class="mt-4 text-xs text-gray-500">
      Trial → Paid conversion:
      <span class="font-semibold {{ $trialConv >= 20 ? 'text-green-400' : 'text-amber-400' }}">
        {{ $trialConv }}%
      </span>
    </p>
  </div>

  {{-- Geographic distribution --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h2 class="text-sm font-semibold text-gray-300 mb-4">Top Cities</h2>
    @if($cityDistribution->isEmpty())
    <p class="text-sm text-gray-600 text-center py-4">No location data available.</p>
    @else
    @php $maxCity = $cityDistribution->max() ?: 1; @endphp
    <div class="space-y-2.5">
      @foreach($cityDistribution as $city => $count)
      <div class="flex items-center gap-2">
        <span class="text-xs text-gray-400 w-28 truncate">{{ $city }}</span>
        <div class="flex-1 h-2.5 bg-gray-800 rounded-full overflow-hidden">
          <div class="h-full bg-amber-500/70 rounded-full"
               style="width: {{ round(($count / $maxCity) * 100) }}%"></div>
        </div>
        <span class="text-xs text-gray-400 w-6 text-right">{{ $count }}</span>
      </div>
      @endforeach
    </div>
    @endif
  </div>

</div>

<div class="grid lg:grid-cols-2 gap-5">

  {{-- Top active salons --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-4 text-sm font-semibold text-gray-300 border-b border-gray-800">
      Most Active Salons This Month
    </h2>
    @if($topActiveSalons->isEmpty())
    <p class="px-5 py-8 text-sm text-gray-600 text-center">No appointment data for this month.</p>
    @else
    <div class="divide-y divide-gray-800/50">
      @foreach($topActiveSalons as $i => $row)
      <div class="flex items-center gap-3 px-5 py-3">
        <span class="text-xs font-bold text-gray-600 w-5 flex-shrink-0">{{ $i + 1 }}</span>
        <div class="flex-1 min-w-0">
          <a href="{{ route('admin.tenants.show', $row->id) }}"
             class="text-sm font-medium text-gray-200 hover:text-white truncate block">
            {{ $row->name }}
          </a>
          <p class="text-xs text-gray-500 font-mono">{{ $row->slug }}.velour.app</p>
        </div>
        <div class="text-right flex-shrink-0">
          <span class="text-sm font-bold text-velour-400">{{ number_format($row->count) }}</span>
          <span class="text-xs text-gray-500"> appts</span>
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- Retention cohorts --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-800">
      <h2 class="text-sm font-semibold text-gray-300">Retention by Signup Cohort</h2>
      <p class="text-xs text-gray-500 mt-0.5">% of salons from each month still active today</p>
    </div>
    @if(empty($cohorts))
    <p class="px-5 py-8 text-sm text-gray-600 text-center">Not enough data yet.</p>
    @else
    <div class="divide-y divide-gray-800/50">
      @foreach($cohorts as $label => $data)
      @php
        $retColor = $data['percent'] >= 80 ? 'text-green-400'
            : ($data['percent'] >= 50 ? 'text-amber-400' : 'text-red-400');
        $barColor = $data['percent'] >= 80 ? 'bg-green-500'
            : ($data['percent'] >= 50 ? 'bg-amber-500' : 'bg-red-500');
      @endphp
      <div class="flex items-center gap-3 px-5 py-3">
        <span class="text-xs text-gray-500 w-16 flex-shrink-0">{{ $label }}</span>
        <div class="flex-1 h-2.5 bg-gray-800 rounded-full overflow-hidden">
          <div class="{{ $barColor }}/70 h-full rounded-full"
               style="width: {{ $data['percent'] }}%"></div>
        </div>
        <span class="text-xs {{ $retColor }} font-semibold w-10 text-right">{{ $data['percent'] }}%</span>
        <span class="text-xs text-gray-600 w-20 text-right">
          {{ $data['active'] }} / {{ $data['total'] }}
        </span>
      </div>
      @endforeach
    </div>
    @endif
  </div>

</div>

@endsection
