@extends('layouts.app')
@section('title', 'New Voucher')
@section('page-title', 'Create Voucher')
@section('content')

<div class="max-w-xl" x-data="{ type: '{{ old('type', 'discount') }}' }">
  <div class="bg-white rounded-2xl border border-gray-200 p-6">
    <form action="{{ route('vouchers.store') }}" method="POST" class="space-y-5">
      @csrf

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Type <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-2 gap-3">
          @foreach(['discount'=>['£ Discount','Fixed amount off'], 'gift_card'=>['Gift Card','Reloadable balance'], 'percentage'=>['% Off','Percentage discount'], 'free_service'=>['Free Service','Complimentary service']] as $v => [$label, $desc])
          <label class="cursor-pointer border-2 rounded-xl p-3 transition-colors
                        {{ old('type', 'discount') === $v ? 'border-velour-500 bg-velour-50' : 'border-gray-200 hover:border-velour-200' }}"
                 x-bind:class="type==='{{ $v }}' ? 'border-velour-500 bg-velour-50' : 'border-gray-200 hover:border-velour-200'">
            <input type="radio" name="type" value="{{ $v }}" @click="type='{{ $v }}'"
                   {{ old('type', 'discount') === $v ? 'checked' : '' }} class="sr-only">
            <p class="text-sm font-semibold text-gray-900">{{ $label }}</p>
            <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
          </label>
          @endforeach
        </div>
        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">
            Value <span class="text-red-500">*</span>
            <span class="text-gray-400 font-normal" x-text="type === 'percentage' ? '(%)' : '(£)'"></span>
          </label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" x-show="type !== 'percentage'">£</span>
            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm" x-show="type === 'percentage'" x-cloak>%</span>
            <input type="number" name="value" value="{{ old('value') }}" step="0.01" min="0.01" required
                   class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500"
                   x-bind:class="type !== 'percentage' ? 'pl-7' : 'pr-7'">
          </div>
          @error('value')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Custom code</label>
          <input type="text" name="code" value="{{ old('code') }}" placeholder="Auto-generated"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-mono uppercase
                        focus:outline-none focus:ring-2 focus:ring-velour-500">
          @error('code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Assign to client <span class="text-gray-400 font-normal">(optional)</span></label>
        <select name="client_id" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
          <option value="">Any client</option>
          @foreach($clients as $c)
          <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
            {{ $c->first_name }} {{ $c->last_name }}
          </option>
          @endforeach
        </select>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Expires</label>
          <input type="date" name="expires_at" value="{{ old('expires_at') }}" min="{{ today()->addDay()->toDateString() }}"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Usage limit</label>
          <input type="number" name="usage_limit" value="{{ old('usage_limit') }}" min="1" placeholder="Unlimited"
                 class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1.5">Minimum spend (£)</label>
        <input type="number" name="min_spend" value="{{ old('min_spend') }}" min="0" step="0.01" placeholder="No minimum"
               class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-velour-500">
      </div>

      <div class="flex gap-3 pt-2 border-t border-gray-100">
        <button type="submit"
                class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
          Create Voucher
        </button>
        <a href="{{ route('vouchers.index') }}"
           class="px-5 py-2.5 text-sm font-medium rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-600 transition-colors">
          Cancel
        </a>
      </div>
    </form>
  </div>
</div>
@endsection
