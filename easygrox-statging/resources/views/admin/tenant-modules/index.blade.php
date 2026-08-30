@extends('layouts.admin')
@section('title', 'Tenant tabs')
@section('page-title', 'Tenant tabs')
@section('content')

<div class="max-w-4xl space-y-5">
  <div>
    <p class="text-sm text-gray-400">
      These switches apply to <span class="text-gray-200 font-medium">every salon</span>.
      Turn a tab off and it disappears for all tenant admins. Turn it on and it shows again (role permissions still apply for staff).
      Dashboard stays available so owners always have a home page.
    </p>
    @if($disabledCount > 0)
    <p class="mt-2 text-xs text-amber-400">{{ $disabledCount }} tab{{ $disabledCount === 1 ? '' : 's' }} currently hidden from tenants.</p>
    @endif
  </div>

  <form method="POST" action="{{ route('admin.tenant-modules.update') }}" class="space-y-5">
    @csrf
    @method('PUT')

    @foreach($groups as $group => $items)
    <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden">
      <div class="px-4 py-3 border-b border-gray-800">
        <h2 class="text-sm font-semibold text-white">{{ $group }}</h2>
      </div>
      <ul class="divide-y divide-gray-800/80">
        @foreach($items as $item)
        <li class="flex items-center justify-between gap-4 px-4 py-3">
          <div class="min-w-0">
            <p class="text-sm font-medium text-gray-100">{{ $item['label'] }}</p>
            @if($item['always_on'])
            <p class="text-[11px] text-gray-500 mt-0.5">Always on</p>
            @endif
          </div>
          @if($item['always_on'])
          <span class="text-[11px] font-semibold uppercase tracking-wide text-emerald-400">Enabled</span>
          @else
          <label class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer items-center">
            <input type="hidden" name="modules[{{ $item['key'] }}]" value="0">
            <input type="checkbox" name="modules[{{ $item['key'] }}]" value="1" class="peer sr-only"
                   @checked($item['enabled'])>
            <span class="h-6 w-11 rounded-full bg-gray-700 peer-checked:bg-emerald-500 transition-colors"></span>
            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
          </label>
          @endif
        </li>
        @endforeach
      </ul>
    </div>
    @endforeach

    <div class="rounded-2xl border border-gray-800 bg-gray-900 overflow-hidden {{ $settingsEnabled ? '' : 'opacity-60' }}">
      <div class="px-4 py-3 border-b border-gray-800">
        <h2 class="text-sm font-semibold text-white">Settings tabs</h2>
        <p class="text-xs text-gray-500 mt-0.5">Inner tabs on Settings. Hidden if Settings itself is off.</p>
      </div>
      <ul class="divide-y divide-gray-800/80">
        @foreach($settingsTabs as $item)
        <li class="flex items-center justify-between gap-4 px-4 py-3">
          <p class="text-sm font-medium text-gray-100">{{ $item['label'] }}</p>
          <label class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer items-center">
            <input type="hidden" name="modules[{{ $item['key'] }}]" value="0">
            <input type="checkbox" name="modules[{{ $item['key'] }}]" value="1" class="peer sr-only"
                   @checked($item['enabled'])>
            <span class="h-6 w-11 rounded-full bg-gray-700 peer-checked:bg-emerald-500 transition-colors"></span>
            <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></span>
          </label>
        </li>
        @endforeach
      </ul>
    </div>

    <button type="submit"
            class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-semibold bg-velour-600 hover:bg-velour-500 text-white">
      Save for all tenants
    </button>
  </form>
</div>

@endsection
