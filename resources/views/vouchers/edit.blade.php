@extends('layouts.app')
@section('title', 'Edit Voucher')
@section('page-title', 'Edit Voucher')
@section('content')

<div class="max-w-xl">
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <div class="mb-5 pb-4 border-b border-gray-100">
      <p class="text-xs text-gray-400">Code</p>
      <p class="text-xl font-mono font-bold text-gray-900 tracking-widest">{{ $voucher->code }}</p>
    </div>

    <form action="{{ route('vouchers.update', $voucher->id) }}" method="POST" class="space-y-5">
      @csrf @method('PUT')

      <x-relation-field-with-create
        label="Assign to client"
        name="client_id"
        select-id="voucher-edit-client"
        type="client"
        :required="false"
        select-class="w-full px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        <option value="">Any client</option>
        @foreach($clients as $c)
        <option value="{{ $c->id }}" {{ old('client_id', $voucher->client_id) == $c->id ? 'selected' : '' }}>
          {{ $c->first_name }} {{ $c->last_name }}
        </option>
        @endforeach
      </x-relation-field-with-create>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Expires</label>
          <input type="date" name="expires_at" value="{{ old('expires_at', $voucher->expires_at?->toDateString()) }}"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Usage limit</label>
          <input type="number" name="usage_limit" value="{{ old('usage_limit', $voucher->usage_limit) }}" min="{{ $voucher->usage_count }}" placeholder="Unlimited"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Minimum spend (£)</label>
        <input type="number" name="min_spend" value="{{ old('min_spend', $voucher->min_spend) }}" min="0" step="0.01" placeholder="No minimum"
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
      </div>

      <div class="flex items-center gap-3">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $voucher->is_active) ? 'checked' : '' }}
               class="rounded text-velour-600">
        <label for="is_active" class="text-sm text-gray-700 cursor-pointer">Active</label>
      </div>

      <div class="flex gap-3 pt-2 border-t border-gray-100">
        <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Save Changes
        </button>
        <a href="{{ route('vouchers.show', $voucher->id) }}"
           class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
