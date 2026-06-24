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

{{-- Platform reports (all tenants & stores) --}}
<div class="bg-gray-900 rounded-2xl border border-gray-800 p-5 sm:p-6 mb-8">
  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
    <div>
      <h2 class="text-sm font-semibold text-gray-200">Platform reports</h2>
      <p class="text-xs text-gray-500 mt-1 max-w-2xl leading-relaxed">
        Download CSV reports for every owner account and all stores. Each row includes owner and store context.
        Appointments, revenue, and expenses accept optional <code class="text-velour-300">?from=YYYY-MM-DD&amp;to=YYYY-MM-DD</code> date filters.
      </p>
    </div>
    <a href="{{ route('admin.reports.export-all') }}"
       class="inline-flex items-center justify-center gap-2 shrink-0 px-4 py-2.5 rounded-xl bg-velour-600 hover:bg-velour-500 text-white text-sm font-semibold transition-colors">
      <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
      Download all (ZIP)
    </a>
  </div>
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 mt-5">
    @foreach($platformReports as $key => $label)
    <a href="{{ route('admin.reports.export', $key) }}"
       class="group flex items-center gap-2 rounded-xl border border-gray-800 bg-gray-950/50 px-3 py-2.5 text-sm text-gray-300 hover:border-velour-700/60 hover:bg-velour-950/30 hover:text-white transition-colors">
      <svg class="w-4 h-4 shrink-0 text-gray-500 group-hover:text-velour-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
      <span class="truncate">{{ $label }}</span>
    </a>
    @endforeach
  </div>
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
          <a href="{{ route('admin.tenants.stores', $tenant->owner_id) }}" class="font-medium text-gray-200 hover:text-white">
            {{ $tenant->name }}
          </a>
          <p class="text-xs text-gray-500">{{ $tenant->subdomain }}.easygrox.com</p>
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
