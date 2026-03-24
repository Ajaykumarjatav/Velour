@extends('layouts.app')
@section('title', 'Voucher '.$voucher->code)
@section('page-title', 'Voucher Details')
@section('content')

<div class="max-w-xl space-y-5">

  {{-- Header card --}}
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="flex items-start justify-between mb-4">
      <div>
        <p class="text-2xl font-mono font-bold text-gray-900 tracking-widest">{{ $voucher->code }}</p>
        @php
          $typeLabel = ['discount'=>'Fixed Discount','gift_card'=>'Gift Card','free_service'=>'Free Service','percentage'=>'Percentage Off'][$voucher->type] ?? $voucher->type;
          $isExpired = $voucher->expires_at && $voucher->expires_at->isPast();
        @endphp
        <p class="text-sm text-gray-500 mt-0.5">{{ $typeLabel }}</p>
      </div>
      @if(!$voucher->is_active)
      <span class="px-3 py-1.5 text-xs font-bold bg-gray-100 text-gray-500 rounded-xl">Inactive</span>
      @elseif($isExpired)
      <span class="px-3 py-1.5 text-xs font-bold bg-red-100 text-red-600 rounded-xl">Expired</span>
      @else
      <span class="px-3 py-1.5 text-xs font-bold bg-green-100 text-green-700 rounded-xl">Active</span>
      @endif
    </div>

    <div class="grid grid-cols-2 gap-4 text-sm">
      <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Value</p>
        <p class="font-bold text-gray-900 text-lg">
          @if($voucher->type === 'percentage')
            {{ $voucher->value }}%
          @elseif($voucher->type === 'gift_card')
            £{{ number_format($voucher->remaining_balance, 2) }}
            <span class="text-sm text-gray-400 font-normal">of £{{ number_format($voucher->value, 2) }}</span>
          @else
            £{{ number_format($voucher->value, 2) }}
          @endif
        </p>
      </div>
      <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Usage</p>
        <p class="font-semibold text-gray-800">
          {{ $voucher->usage_count }}
          @if($voucher->usage_limit) / {{ $voucher->usage_limit }} @else <span class="text-gray-400 font-normal">/ unlimited</span> @endif
        </p>
      </div>
      <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Expires</p>
        <p class="font-semibold {{ $isExpired ? 'text-red-600' : 'text-gray-800' }}">
          {{ $voucher->expires_at ? $voucher->expires_at->format('d M Y') : 'Never' }}
        </p>
      </div>
      <div>
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Min Spend</p>
        <p class="font-semibold text-gray-800">{{ $voucher->min_spend ? '£'.number_format($voucher->min_spend, 2) : 'None' }}</p>
      </div>
      @if($voucher->client)
      <div class="col-span-2">
        <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Assigned Client</p>
        <a href="{{ route('clients.show', $voucher->client_id) }}"
           class="font-semibold text-velour-600 hover:underline">
          {{ $voucher->client->first_name }} {{ $voucher->client->last_name }}
        </a>
      </div>
      @endif
    </div>

    <div class="flex flex-wrap gap-2 mt-5 pt-5 border-t border-gray-100">
      <a href="{{ route('vouchers.edit', $voucher->id) }}"
         class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 transition-colors">
        Edit
      </a>

      <form method="POST" action="{{ route('vouchers.toggle', $voucher->id) }}">
        @csrf @method('PATCH')
        <button type="submit"
                class="px-4 py-2 text-sm font-medium rounded-xl border transition-colors
                       {{ $voucher->is_active ? 'border-amber-200 text-amber-700 hover:bg-amber-50' : 'border-green-200 text-green-700 hover:bg-green-50' }}">
          {{ $voucher->is_active ? 'Deactivate' : 'Activate' }}
        </button>
      </form>

      <form method="POST" action="{{ route('vouchers.destroy', $voucher->id) }}"
            onsubmit="return confirm('Delete this voucher?')">
        @csrf @method('DELETE')
        <button type="submit"
                class="px-4 py-2 text-sm font-medium rounded-xl border border-red-200 text-red-600 hover:bg-red-50 transition-colors">
          Delete
        </button>
      </form>
    </div>
  </div>

  <a href="{{ route('vouchers.index') }}" class="inline-block text-sm text-gray-500 hover:text-gray-700">← Back</a>
</div>
@endsection
