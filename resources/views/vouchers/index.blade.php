@extends('layouts.app')
@section('title', 'Vouchers')
@section('page-title', 'Vouchers & Gift Cards')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
  <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
    <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total']) }}</p>
    <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Total</p>
  </div>
  <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
    <p class="text-2xl font-bold text-green-600">{{ number_format($stats['active']) }}</p>
    <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Active</p>
  </div>
  <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
    <p class="text-2xl font-bold text-velour-600">{{ number_format($stats['gift_cards']) }}</p>
    <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Gift Cards</p>
  </div>
  <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
    <p class="text-2xl font-bold text-gray-900">@money($stats['total_value'])</p>
    <p class="text-xs text-gray-400 mt-1 uppercase tracking-wide">Remaining Value</p>
  </div>
</div>

{{-- Filters + create --}}
<div class="flex flex-col sm:flex-row gap-3 mb-6">
  <form method="GET" action="{{ route('vouchers.index') }}" class="flex flex-1 gap-2 flex-wrap">
    <input type="search" name="search" value="{{ $search }}" placeholder="Search code or client…"
           class="flex-1 min-w-[180px] px-4 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-velour-500">

    <select name="type" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All types</option>
      @foreach(['discount'=>'Discount (£)', 'gift_card'=>'Gift Card', 'free_service'=>'Free Service', 'percentage'=>'Percentage (%)'] as $v => $l)
      <option value="{{ $v }}" {{ $type === $v ? 'selected' : '' }}>{{ $l }}</option>
      @endforeach
    </select>

    <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
      <option value="expired"  {{ $status === 'expired'  ? 'selected' : '' }}>Expired</option>
      <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
      <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All</option>
    </select>

    <button type="submit" class="px-4 py-2 text-sm font-medium rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700">Filter</button>
    <a href="{{ route('vouchers.index') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-600">Clear</a>
  </form>

  <a href="{{ route('vouchers.create') }}"
     class="flex-shrink-0 px-5 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
    + New Voucher
  </a>
</div>

{{-- Table --}}
<div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-100 bg-gray-50">
      <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Code</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Client</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Value</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider hidden md:table-cell">Expires</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
    @forelse($vouchers as $v)
    @php
      $typeLabel = ['discount'=>'£ Discount','gift_card'=>'Gift Card','free_service'=>'Free Service','percentage'=>'% Off'][$v->type] ?? $v->type;
      $isExpired = $v->expires_at && $v->expires_at->isPast();
    @endphp
    <tr class="hover:bg-gray-50 transition-colors {{ $isExpired ? 'opacity-60' : '' }}">
      <td class="px-5 py-3.5">
        <a href="{{ route('vouchers.show', $v->id) }}"
           class="font-mono font-semibold text-velour-700 hover:underline">{{ $v->code }}</a>
        <p class="text-xs text-gray-400 mt-0.5">Used {{ $v->usage_count }}x</p>
      </td>
      <td class="px-4 py-3.5 hidden sm:table-cell text-sm text-gray-600">
        @if($v->client)
        <a href="{{ route('clients.show', $v->client_id) }}" class="hover:text-velour-600">
          {{ $v->client->first_name }} {{ $v->client->last_name }}
        </a>
        @else
        <span class="text-gray-400">Any client</span>
        @endif
      </td>
      <td class="px-4 py-3.5 text-xs text-gray-600">{{ $typeLabel }}</td>
      <td class="px-4 py-3.5 text-right font-semibold text-gray-900">
        @if($v->type === 'percentage') {{ $v->value }}%
        @elseif($v->type === 'gift_card') @money($v->remaining_balance) <span class="text-xs text-gray-400">of @money($v->value)</span>
        @else @money($v->value)
        @endif
      </td>
      <td class="px-4 py-3.5 hidden md:table-cell text-xs {{ $isExpired ? 'text-red-500' : 'text-gray-500' }}">
        {{ $v->expires_at ? $v->expires_at->format('d M Y') : '—' }}
      </td>
      <td class="px-4 py-3.5">
        @if(!$v->is_active)
        <span class="px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-500 rounded-lg">Inactive</span>
        @elseif($isExpired)
        <span class="px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-600 rounded-lg">Expired</span>
        @else
        <span class="px-2 py-0.5 text-xs font-semibold bg-green-100 text-green-700 rounded-lg">Active</span>
        @endif
      </td>
      <td class="px-4 py-3.5 text-right">
        <a href="{{ route('vouchers.show', $v->id) }}" class="text-xs text-velour-600 hover:text-velour-700 font-medium">View →</a>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="7" class="px-5 py-12 text-center text-sm text-gray-400">
        No vouchers yet. <a href="{{ route('vouchers.create') }}" class="text-velour-600 hover:underline">Create one →</a>
      </td>
    </tr>
    @endforelse
    </tbody>
  </table>
</div>

@if($vouchers->hasPages())
<div class="mt-4">{{ $vouchers->links() }}</div>
@endif

@endsection
