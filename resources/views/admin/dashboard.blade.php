@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Platform Overview')
@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
  @foreach([
    ['label' => 'Total Salons',   'value' => $stats['total_tenants'],  'color' => 'text-white'],
    ['label' => 'Active Salons',  'value' => $stats['active_tenants'], 'color' => 'text-green-400'],
    ['label' => 'New This Month', 'value' => $stats['new_this_month'], 'color' => 'text-velour-400'],
    ['label' => 'Total Users',    'value' => $stats['total_users'],    'color' => 'text-white'],
    ['label' => 'Active Users',   'value' => $stats['active_users'],   'color' => 'text-green-400'],
  ] as $stat)
  <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5 text-center">
    <p class="text-2xl font-black {{ $stat['color'] }}">{{ number_format($stat['value']) }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider font-medium">{{ $stat['label'] }}</p>
  </div>
  @endforeach
</div>

<div class="grid lg:grid-cols-2 gap-6">

  {{-- Recent Tenants --}}
  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-300">Recent Salons</h2>
      <a href="{{ route('admin.tenants') }}" class="text-xs text-velour-400 hover:text-velour-300">View all →</a>
    </div>
    <table class="w-full text-sm">
      <tbody class="divide-y divide-gray-800/50">
      @foreach($recentTenants as $tenant)
      <tr class="hover:bg-gray-800/30 transition-colors">
        <td class="px-5 py-3">
          <a href="{{ route('admin.tenants.show', $tenant->id) }}" class="font-medium text-gray-200 hover:text-white">
            {{ $tenant->name }}
          </a>
          <p class="text-xs text-gray-500">{{ $tenant->subdomain }}.velour.app</p>
        </td>
        <td class="px-4 py-3 text-right">
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold
                {{ $tenant->is_active ? 'bg-green-900/50 text-green-400' : 'bg-red-900/50 text-red-400' }}">
            {{ $tenant->is_active ? 'Active' : 'Suspended' }}
          </span>
        </td>
        <td class="px-4 py-3 text-right text-xs text-gray-500">
          {{ $tenant->created_at->diffForHumans() }}
        </td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>

  {{-- Recent Users --}}
  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-300">Recent Users</h2>
      <a href="{{ route('admin.users') }}" class="text-xs text-velour-400 hover:text-velour-300">View all →</a>
    </div>
    <table class="w-full text-sm">
      <tbody class="divide-y divide-gray-800/50">
      @foreach($recentUsers as $user)
      <tr class="hover:bg-gray-800/30 transition-colors">
        <td class="px-5 py-3">
          <a href="{{ route('admin.users.show', $user->id) }}" class="font-medium text-gray-200 hover:text-white">
            {{ $user->name }}
          </a>
          <p class="text-xs text-gray-500">{{ $user->email }}</p>
        </td>
        <td class="px-4 py-3 text-right text-xs text-gray-500 uppercase">{{ $user->plan }}</td>
        <td class="px-4 py-3 text-right text-xs text-gray-500">{{ $user->created_at->diffForHumans() }}</td>
      </tr>
      @endforeach
      </tbody>
    </table>
  </div>

</div>

@endsection
