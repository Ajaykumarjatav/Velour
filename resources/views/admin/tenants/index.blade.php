@extends('layouts.admin')
@section('title', 'Tenants')
@section('page-title', 'Tenant accounts')
@section('content')

<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
  @foreach([
    ['Accounts', $stats['total'], 'text-gray-200'],
    ['Blocked', $stats['blocked'], $stats['blocked'] > 0 ? 'text-red-400' : 'text-gray-500'],
    ['Active stores', $stats['active'], 'text-green-400'],
    ['Suspended stores', $stats['suspended'], $stats['suspended'] > 0 ? 'text-red-400' : 'text-gray-500'],
    ['New accounts (month)', $stats['new_month'], 'text-velour-400'],
  ] as [$label, $val, $color])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
    <p class="text-2xl font-black {{ $color }}">{{ number_format($val) }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $label }}</p>
  </div>
  @endforeach
</div>

<form method="GET" action="{{ route('admin.tenants') }}"
      class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-5 flex flex-wrap gap-3">
  <input type="search" name="search" value="{{ request('search') }}"
         placeholder="Search account, email, store name…"
         class="flex-1 min-w-[200px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">
  <select name="status" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
    <option value="">All statuses</option>
    <option value="active" @selected(request('status')==='active')>Active</option>
    <option value="blocked" @selected(request('status')==='blocked')>Blocked accounts</option>
    <option value="suspended" @selected(request('status')==='suspended')>Stores suspended only</option>
  </select>
  <select name="plan" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
    <option value="">All plans</option>
    @foreach($planOptions as $p)
    <option value="{{ $p }}" @selected(request('plan')===$p)>{{ \App\Billing\Plan::labelFor($p) }}</option>
    @endforeach
  </select>
  <select name="sort" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
    <option value="">Newest first</option>
    <option value="oldest" @selected(request('sort')==='oldest')>Oldest first</option>
    <option value="name" @selected(request('sort')==='name')>Name A–Z</option>
    <option value="stores" @selected(request('sort')==='stores')>Most stores</option>
  </select>
  <div class="flex gap-2">
    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white">Filter</button>
    <a href="{{ route('admin.tenants.export') }}" class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800">Export CSV</a>
  </div>
</form>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Account</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden sm:table-cell">Plan</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Stores</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden lg:table-cell">Clients</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden md:table-cell">Appts</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden sm:table-cell">Joined</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($accounts as $account)
    @php
      $agg = $aggregateStats[$account->id] ?? null;
      $planColor = match($account->plan) {
        'premium'  => 'text-amber-400 bg-amber-900/30', 'standard' => 'text-velour-400 bg-velour-900/30',
        'trial' => 'text-blue-400 bg-blue-900/30', default => 'text-gray-500 bg-gray-800',
      };
      $activeStores = (int) ($agg->active_stores ?? $account->salons->where('is_active', true)->count());
      $allSuspended = ! $account->is_active || ($account->stores_count > 0 && $activeStores === 0);
      $isBlocked = ! $account->is_active;
    @endphp
    <tr class="hover:bg-gray-800/30 transition-colors {{ $allSuspended ? 'opacity-60' : '' }}">
      <td class="px-4 py-3">
        <a href="{{ route('admin.tenants.owners.show', $account->id) }}" class="font-semibold text-gray-200 hover:text-white">{{ $account->name }}</a>
        <p class="text-xs text-gray-500">{{ $account->email }}</p>
      </td>
      <td class="px-4 py-3 hidden sm:table-cell">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $planColor }}">{{ \App\Billing\Plan::labelFor($account->plan) }}</span>
      </td>
      <td class="px-4 py-3 text-right text-gray-300">{{ $account->stores_count }}</td>
      <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-300">{{ number_format((int) ($agg->clients_total ?? 0)) }}</td>
      <td class="px-4 py-3 hidden md:table-cell text-right text-gray-300">{{ number_format((int) ($agg->appointments_total ?? 0)) }}</td>
      <td class="px-4 py-3">
        @if($isBlocked)
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-900/50 text-red-400">Blocked</span>
        @elseif($allSuspended)
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-amber-900/50 text-amber-400">Stores suspended</span>
        @else
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-green-900/50 text-green-400">Active</span>
        @endif
      </td>
      <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">{{ $account->created_at->format('d M Y') }}</td>
      <td class="px-4 py-3 text-right">
        <a href="{{ route('admin.tenants.stores', $account->id) }}" class="text-xs text-velour-400 hover:text-velour-300 font-medium">View stores →</a>
      </td>
    </tr>
    @empty
    <tr><td colspan="8" class="px-5 py-12 text-center text-sm text-gray-500">No tenant accounts found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $accounts->links() }}</div>
@endsection
