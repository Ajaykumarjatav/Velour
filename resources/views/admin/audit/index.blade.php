@extends('layouts.admin')
@section('title', 'Security Audit Log')
@section('page-title', 'Security Audit Log')
@section('content')

{{-- Stats strip --}}
<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
  @php
    $statDefs = [
      ['label'=>'Total Events',  'value'=>$stats['total'],    'color'=>'text-gray-300'],
      ['label'=>'Critical',      'value'=>$stats['critical'],  'color'=>$stats['critical']>0?'text-red-400':'text-gray-500'],
      ['label'=>'Warnings',      'value'=>$stats['warning'],   'color'=>$stats['warning']>0?'text-amber-400':'text-gray-500'],
      ['label'=>'Auth Events',   'value'=>$stats['auth'],      'color'=>'text-blue-400'],
      ['label'=>'Security',      'value'=>$stats['security'],  'color'=>$stats['security']>0?'text-orange-400':'text-gray-500'],
    ];
  @endphp
  @foreach($statDefs as $s)
  <div class="bg-gray-900 rounded-2xl border border-gray-800 p-4 text-center">
    <p class="text-2xl font-black {{ $s['color'] }}">{{ number_format($s['value']) }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $s['label'] }}</p>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.audit.index') }}"
      class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-5">
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="Search events, email, IP…"
           class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                  placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500 col-span-full lg:col-span-1">

    <select name="category" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All categories</option>
      @foreach($categories as $cat)
      <option value="{{ $cat }}" {{ request('category')===$cat?'selected':'' }}>{{ ucfirst($cat) }}</option>
      @endforeach
    </select>

    <select name="severity" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All severities</option>
      @foreach($severities as $sev)
      <option value="{{ $sev }}" {{ request('severity')===$sev?'selected':'' }}>{{ ucfirst($sev) }}</option>
      @endforeach
    </select>

    <div class="flex gap-2">
      <input type="date" name="from" value="{{ request('from') }}"
             class="flex-1 px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <input type="date" name="to" value="{{ request('to') }}"
             class="flex-1 px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    </div>
  </div>
  <div class="flex gap-2 mt-3 justify-end">
    <a href="{{ route('admin.audit.index') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">Clear</a>
    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
      Filter
    </button>
    <a href="{{ route('admin.audit.export', request()->query()) }}"
       class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800 transition-colors">
      Export CSV ↓
    </a>
  </div>
</form>

{{-- Log table --}}
<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden" x-data>
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Severity</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Category</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Event</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">User</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">IP</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Time</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/60">
    @forelse($logs as $log)
    @php
      $sevConfig = match($log->severity) {
        'critical' => ['dot' => 'bg-red-500',    'row' => 'bg-red-950/10'],
        'warning'  => ['dot' => 'bg-amber-500',  'row' => 'bg-amber-950/10'],
        default    => ['dot' => 'bg-blue-500/50','row' => ''],
      };
    @endphp
    <tr class="hover:bg-gray-800/40 transition-colors {{ $sevConfig['row'] }}">
      <td class="px-5 py-3">
        <span class="inline-flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full {{ $sevConfig['dot'] }} flex-shrink-0"></span>
          <span class="text-xs text-gray-400 capitalize">{{ $log->severity }}</span>
        </span>
      </td>
      <td class="px-4 py-3 hidden sm:table-cell">
        <span class="text-lg leading-none">{{ $log->categoryIcon() }}</span>
        <span class="text-xs text-gray-400 ml-1 capitalize">{{ $log->event_category }}</span>
      </td>
      <td class="px-4 py-3">
        <p class="font-mono text-xs text-gray-200 font-semibold">{{ $log->event }}</p>
        @if($log->description)
        <p class="text-xs text-gray-500 mt-0.5 truncate max-w-[200px]">{{ $log->description }}</p>
        @endif
      </td>
      <td class="px-4 py-3 hidden md:table-cell">
        @if($log->user_email)
        <a href="{{ route('admin.users.show', $log->user_id ?? 0) }}"
           class="text-xs text-gray-300 hover:text-white">{{ $log->user_email }}</a>
        @else
        <span class="text-xs text-gray-600">—</span>
        @endif
      </td>
      <td class="px-4 py-3 hidden lg:table-cell font-mono text-xs text-gray-500">
        {{ $log->ip_address ?? '—' }}
      </td>
      <td class="px-4 py-3 text-xs text-gray-500" title="{{ $log->occurred_at->toIso8601String() }}">
        {{ $log->occurred_at->diffForHumans() }}
      </td>
      <td class="px-4 py-3">
        <a href="{{ route('admin.audit.show', $log) }}"
           class="text-xs text-velour-400 hover:text-velour-300 font-medium">
          Detail →
        </a>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="7" class="px-5 py-16 text-center">
        <p class="text-gray-500 text-sm">No audit events match your filters.</p>
      </td>
    </tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $logs->links() }}</div>

@endsection
