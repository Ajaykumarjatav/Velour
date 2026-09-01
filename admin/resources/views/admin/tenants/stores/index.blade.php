@extends('layouts.admin')
@section('title', $account->name . ' — Stores')
@section('page-title', 'Stores')

@section('content')
<div class="space-y-5" x-data="{ suspendStore: null, suspendStoreName: '', unsuspendStore: null, unsuspendStoreName: '' }">
  <div class="flex flex-wrap items-center justify-between gap-3">
  <div>
    <a href="{{ route('admin.tenants') }}" class="text-xs text-gray-500 hover:text-gray-300">← All tenants</a>
    <h2 class="text-lg font-bold text-white mt-1">{{ $account->name }}</h2>
    <p class="text-sm text-gray-500">{{ $account->email }} · {{ \App\Billing\Plan::labelFor($account->plan) }} plan</p>
  </div>
  <a href="{{ route('admin.tenants.owners.show', $account->id) }}"
     class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800">
    Account settings
  </a>
  </div>

  @if($autoOpenSalon)
  <div class="bg-velour-900/20 border border-velour-800/40 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3">
    <p class="text-sm text-velour-200">This account has one store. Open the panel to browse modules read-only.</p>
    <form method="POST" action="{{ route('admin.tenants.stores.enter', $autoOpenSalon->id) }}">
      @csrf
      <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 text-white hover:bg-velour-700">
        Open {{ $autoOpenSalon->name }} panel
      </button>
    </form>
  </div>
  @endif

  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @forelse($stores as $store)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-col">
      <div class="flex items-start justify-between gap-2 mb-3">
        <div class="min-w-0">
          <h3 class="font-semibold text-white truncate">{{ $store->name }}</h3>
          <p class="text-xs text-gray-500 font-mono">{{ $store->slug }}.easygrox.com</p>
          @if($store->city)<p class="text-xs text-gray-600 mt-0.5">{{ $store->city }}</p>@endif
        </div>
        @if($store->is_active)
          <span class="shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-lg bg-green-900/50 text-green-400">Active</span>
        @else
          <span class="shrink-0 px-2 py-0.5 text-[10px] font-bold rounded-lg bg-red-900/50 text-red-400">Suspended</span>
        @endif
      </div>

      <dl class="grid grid-cols-2 gap-2 text-xs mb-4">
        <div><dt class="text-gray-600">Staff</dt><dd class="text-gray-200 font-semibold">{{ $store->staff_count }}</dd></div>
        <div><dt class="text-gray-600">Clients</dt><dd class="text-gray-200 font-semibold">{{ number_format($store->clients_count) }}</dd></div>
        <div><dt class="text-gray-600">Appts</dt><dd class="text-gray-200 font-semibold">{{ number_format($store->appointments_count) }}</dd></div>
        <div><dt class="text-gray-600">Revenue (mo)</dt><dd class="text-gray-200 font-semibold">£{{ number_format((float) ($revenueBySalon[$store->id] ?? 0), 0) }}</dd></div>
      </dl>

      <div class="mt-auto flex flex-wrap gap-2">
        @if($store->is_active)
        <form method="POST" action="{{ route('admin.tenants.stores.enter', $store->id) }}" class="flex-1">
          @csrf
          <button type="submit" class="w-full px-3 py-2 text-xs font-semibold rounded-xl bg-velour-600 text-white hover:bg-velour-700">
            Open panel
          </button>
        </form>
        @endif
        <a href="{{ route('admin.tenants.show', $store->id) }}"
           class="px-3 py-2 text-xs font-medium rounded-xl border border-gray-700 text-gray-400 hover:text-white hover:bg-gray-800">
          Admin data
        </a>
        @if(!($isBlocked ?? false))
          @if($store->is_active)
          <button type="button"
                  @click="suspendStore = {{ $store->id }}; suspendStoreName = @js($store->name)"
                  class="px-3 py-2 text-xs font-semibold rounded-xl border border-red-800/60 text-red-400 hover:bg-red-900/30">
            Suspend
          </button>
          @else
          <button type="button"
                  @click="unsuspendStore = {{ $store->id }}; unsuspendStoreName = @js($store->name)"
                  class="px-3 py-2 text-xs font-semibold rounded-xl border border-green-800/60 text-green-400 hover:bg-green-900/30">
            Reactivate
          </button>
          @endif
        @endif
      </div>
    </div>
    @empty
    <p class="text-sm text-gray-500 col-span-full text-center py-12">No stores for this account.</p>
    @endforelse
  </div>

  @include('admin.tenants.partials.store-suspend-modals')
</div>
@endsection
