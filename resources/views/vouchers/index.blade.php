@extends('layouts.app')
@section('title', 'Vouchers')
@section('page-title', 'Vouchers & Gift Cards')
@section('content')

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
  <div class="stat-card text-center">
    <p class="text-2xl font-bold text-heading">{{ number_format($stats['total']) }}</p>
    <p class="stat-label mt-1">Total</p>
  </div>
  <div class="stat-card text-center">
    <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['active']) }}</p>
    <p class="stat-label mt-1">Active</p>
  </div>
  <div class="stat-card text-center">
    <p class="text-2xl font-bold text-velour-600 dark:text-velour-400">{{ number_format($stats['gift_cards']) }}</p>
    <p class="stat-label mt-1">Gift cards</p>
  </div>
  <div class="stat-card text-center">
    <p class="text-2xl font-bold text-heading">@money($stats['total_value'])</p>
    <p class="stat-label mt-1">Remaining value</p>
  </div>
</div>

<div class="flex flex-col lg:flex-row gap-4 mb-6 items-start">
  <form method="GET" action="{{ route('vouchers.index') }}" class="flex flex-1 flex-col gap-3 min-w-0 w-full">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
      <input type="search" name="search" value="{{ $search }}" placeholder="Search code or client…" class="form-input w-full min-w-0 xl:col-span-2">
      <select name="type" class="form-select w-full min-w-0">
        <option value="">All types</option>
        @foreach(['discount'=>'Discount (£)', 'gift_card'=>'Gift Card', 'free_service'=>'Free Service', 'percentage'=>'Percentage (%)'] as $v => $l)
        <option value="{{ $v }}" {{ $type === $v ? 'selected' : '' }}>{{ $l }}</option>
        @endforeach
      </select>
      <select name="status" class="form-select w-full min-w-0">
        <option value="active"   {{ $status === 'active'   ? 'selected' : '' }}>Active</option>
        <option value="expired"  {{ $status === 'expired'  ? 'selected' : '' }}>Expired</option>
        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
        <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All</option>
      </select>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <button type="submit" class="btn-secondary">Filter</button>
      <a href="{{ route('vouchers.index') }}" class="btn-outline">Clear</a>
    </div>
  </form>
  <a href="{{ route('vouchers.create') }}" class="btn-primary flex-shrink-0 w-full sm:w-auto text-center">+ New Voucher</a>
</div>

<div class="table-wrap">
  <table class="data-table data-table-fixed">
    <colgroup>
      <col class="w-[14%]">
      <col class="w-[18%]">
      <col class="w-[14%]">
      <col class="w-[12%]">
      <col class="w-[12%]">
      <col class="w-[14%]">
      <col class="w-[16%]">
    </colgroup>
    <thead>
    <tr>
      <th>Code</th>
      <th class="hidden sm:table-cell">Client</th>
      <th>Type</th>
      <th class="text-right">Value</th>
      <th class="hidden md:table-cell">Expires</th>
      <th class="text-center">Status</th>
      <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
    </tr>
    </thead>
    <tbody>
    @forelse($vouchers as $v)
    @php
      $typeLabel = ['discount'=>'£ Discount','gift_card'=>'Gift Card','free_service'=>'Free Service','percentage'=>'% Off'][$v->type] ?? $v->type;
      $isExpired = $v->expires_at && $v->expires_at->isPast();
    @endphp
    <tr class="{{ $isExpired ? 'opacity-70' : '' }}">
      <td class="max-w-0">
        <a href="{{ route('vouchers.show', $v->id) }}" class="font-mono font-semibold text-link truncate block">{{ $v->code }}</a>
        <p class="text-xs text-muted mt-0.5">Used {{ $v->usage_count }}×</p>
      </td>
      <td class="hidden sm:table-cell text-body max-w-0 truncate">
        @if($v->client)
        <a href="{{ route('clients.show', $v->client_id) }}" class="hover:underline">{{ $v->client->first_name }} {{ $v->client->last_name }}</a>
        @else
        <span class="text-muted">Any client</span>
        @endif
      </td>
      <td class="text-xs text-body">{{ $typeLabel }}</td>
      <td class="text-right font-semibold text-heading whitespace-nowrap">
        @if($v->type === 'percentage') {{ $v->value }}%
        @elseif($v->type === 'gift_card') @money($v->remaining_balance) <span class="text-xs text-muted font-normal">of @money($v->value)</span>
        @else @money($v->value)
        @endif
      </td>
      <td class="hidden md:table-cell text-xs {{ $isExpired ? 'text-red-500 dark:text-red-400' : 'text-muted' }} whitespace-nowrap">
        {{ $v->expires_at ? $v->expires_at->format('d M Y') : '—' }}
      </td>
      <td class="text-center">
        @if(!$v->is_active)
        <span class="badge-gray">Inactive</span>
        @elseif($isExpired)
        <span class="badge-red">Expired</span>
        @else
        <span class="badge-green">Active</span>
        @endif
      </td>
      <td class="text-right">
        <a href="{{ route('vouchers.show', $v->id) }}" class="text-xs text-link font-medium whitespace-nowrap">View</a>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="7" class="px-5 py-12 text-center text-sm text-muted">
        No vouchers yet. <a href="{{ route('vouchers.create') }}" class="text-link font-medium">Create one</a>
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
