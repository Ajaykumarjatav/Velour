@extends('layouts.admin')
@section('title', 'Tenants')
@section('page-title', 'All Salons')
@section('content')

{{-- Stats strip --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
  @foreach([
    ['Total',     $stats['total'],     'text-gray-200'],
    ['Active',    $stats['active'],    'text-green-400'],
    ['Suspended', $stats['suspended'], $stats['suspended'] > 0 ? 'text-red-400' : 'text-gray-500'],
    ['New this month', $stats['new_month'], 'text-velour-400'],
  ] as [$label, $val, $color])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
    <p class="text-2xl font-black {{ $color }}">{{ number_format($val) }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $label }}</p>
  </div>
  @endforeach
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.tenants') }}"
      class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-5 flex flex-wrap gap-3">
  <input type="search" name="search" value="{{ request('search') }}"
         placeholder="Search name, slug, email, city…"
         class="flex-1 min-w-[200px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">
  <select name="status" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All statuses</option>
    <option value="active"    {{ request('status')==='active'    ? 'selected' : '' }}>Active</option>
    <option value="suspended" {{ request('status')==='suspended' ? 'selected' : '' }}>Suspended</option>
  </select>
  <select name="plan" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All plans</option>
    @foreach($planOptions as $p)
    <option value="{{ $p }}" {{ request('plan')===$p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
    @endforeach
  </select>
  <select name="sort" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">Newest first</option>
    <option value="oldest"  {{ request('sort')==='oldest'  ? 'selected' : '' }}>Oldest first</option>
    <option value="name"    {{ request('sort')==='name'    ? 'selected' : '' }}>Name A–Z</option>
    <option value="clients" {{ request('sort')==='clients' ? 'selected' : '' }}>Most clients</option>
    <option value="staff"   {{ request('sort')==='staff'   ? 'selected' : '' }}>Most staff</option>
  </select>
  <div class="flex gap-2">
    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">Filter</button>
    <a href="{{ route('admin.tenants.export') }}" class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800 transition-colors">Export CSV ↓</a>
  </div>
</form>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden" x-data="{ selected: [] }">

  {{-- Bulk bar --}}
  <div x-show="selected.length > 0" x-cloak
       class="flex flex-wrap items-center gap-3 px-5 py-3 bg-amber-900/20 border-b border-amber-800/50">
    <span class="text-sm text-amber-300 font-semibold" x-text="selected.length + ' selected'"></span>
    <form method="POST" action="{{ route('admin.tenants.bulk-suspend') }}" class="flex flex-wrap items-center gap-2" @submit="$el.querySelectorAll('[name=\'salon_ids[]\']').forEach(el => el.disabled = false)">
      @csrf
      <template x-for="id in selected" :key="id">
        <input type="hidden" name="salon_ids[]" :value="id" disabled>
      </template>
      <select name="reason" class="px-3 py-1.5 text-xs bg-gray-800 border border-gray-700 text-gray-200 rounded-lg">
        <option value="policy_violation">Policy violation</option>
        <option value="payment_failure">Payment failure</option>
        <option value="fraud">Fraud</option>
        <option value="abuse">Abuse</option>
        <option value="other">Other</option>
      </select>
      <label class="flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer">
        <input type="checkbox" name="notify_owners" value="1" class="rounded"> Notify owners
      </label>
      <button type="submit" onclick="return confirm('Suspend ' + selected.length + ' salon(s)?')"
              class="px-3 py-1.5 text-xs font-bold rounded-lg bg-red-700 hover:bg-red-600 text-white transition-colors">
        Suspend selected
      </button>
    </form>
  </div>

  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="px-4 py-3 w-8"><input type="checkbox" @change="selected = $event.target.checked ? {{ $tenants->pluck('id') }} : []" class="rounded border-gray-600"></th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Salon</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Plan</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Staff</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Clients</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Appts</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Joined</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($tenants as $tenant)
    @php
      $planColor = match($tenant->owner?->plan) {
        'enterprise' => 'text-purple-400 bg-purple-900/30', 'pro' => 'text-blue-400 bg-blue-900/30',
        'starter'    => 'text-green-400 bg-green-900/30',    default => 'text-gray-500 bg-gray-800',
      };
    @endphp
    <tr class="hover:bg-gray-800/30 transition-colors {{ !$tenant->is_active ? 'opacity-60' : '' }}">
      <td class="px-4 py-3">
        <input type="checkbox" :value="{{ $tenant->id }}"
               @change="selected.includes({{ $tenant->id }}) ? selected = selected.filter(i=>i!=={{ $tenant->id }}) : selected.push({{ $tenant->id }})"
               class="rounded border-gray-600">
      </td>
      <td class="px-4 py-3">
        <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="font-semibold text-gray-200 hover:text-white">{{ $tenant->name }}</a>
        <p class="text-xs text-gray-500 font-mono">{{ $tenant->slug }}.velour.app</p>
        @if($tenant->owner)<p class="text-xs text-gray-600">{{ $tenant->owner->email }}</p>@endif
      </td>
      <td class="px-4 py-3 hidden sm:table-cell">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $planColor }}">{{ ucfirst($tenant->owner?->plan ?? 'free') }}</span>
      </td>
      <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-300">{{ $tenant->staff_count }}</td>
      <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-300">{{ number_format($tenant->clients_count) }}</td>
      <td class="px-4 py-3 hidden md:table-cell text-right text-gray-300">{{ number_format($tenant->appointments_count) }}</td>
      <td class="px-4 py-3">
        @if($tenant->is_active)
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-green-900/50 text-green-400">Active</span>
        @else
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-900/50 text-red-400">Suspended</span>
          @if($tenant->suspended_at)<p class="text-xs text-gray-600 mt-0.5">{{ $tenant->suspended_at->diffForHumans() }}</p>@endif
        @endif
      </td>
      <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">{{ $tenant->created_at->format('d M Y') }}</td>
      <td class="px-4 py-3">
        <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="text-xs text-velour-400 hover:text-velour-300 font-medium">View →</a>
      </td>
    </tr>
    @empty
    <tr><td colspan="9" class="px-5 py-12 text-center text-sm text-gray-500">No tenants found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $tenants->links() }}</div>
@endsection
