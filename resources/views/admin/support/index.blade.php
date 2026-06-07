@extends('layouts.admin')
@section('title', 'Support Tickets')
@section('page-title', 'Support Queue')
@section('content')

{{-- Stats strip --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-6">
  @foreach([
    ['Open',        $stats['open'],       $stats['open'] > 0 ? 'text-green-400' : 'text-gray-500'],
    ['Waiting',     $stats['waiting'],    $stats['waiting'] > 0 ? 'text-amber-400' : 'text-gray-500'],
    ['Unassigned',  $stats['unassigned'], $stats['unassigned'] > 0 ? 'text-red-400' : 'text-gray-500'],
    ['Urgent/High', $stats['urgent'],     $stats['urgent'] > 0 ? 'text-red-400' : 'text-gray-500'],
  ] as [$label, $val, $color])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
    <p class="text-2xl font-black {{ $color }}">{{ number_format($val) }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $label }}</p>
  </div>
  @endforeach
</div>

{{-- Health metrics --}}
<div class="grid sm:grid-cols-2 gap-3 mb-5">
  <div class="bg-gray-900 border border-gray-800 rounded-2xl px-5 py-3.5 flex items-center justify-between">
    <span class="text-sm text-gray-400">Avg first response time</span>
    <span class="text-sm font-semibold text-blue-400">
      {{ $avgResponseMinutes ? round($avgResponseMinutes / 60, 1).'h' : '—' }}
    </span>
  </div>
  <div class="bg-gray-900 border border-gray-800 rounded-2xl px-5 py-3.5 flex items-center justify-between">
    <span class="text-sm text-gray-400">Satisfaction score (30d)</span>
    <span class="text-sm font-semibold {{ $avgSatisfaction >= 4 ? 'text-green-400' : ($avgSatisfaction >= 3 ? 'text-amber-400' : 'text-red-400') }}">
      {{ $avgSatisfaction ? number_format($avgSatisfaction, 1).' / 5' : '—' }}
    </span>
  </div>
</div>

{{-- Filters + New Ticket --}}
<form method="GET" action="{{ route('admin.support.index') }}"
      class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-5 flex flex-wrap gap-3">
  <input type="search" name="search" value="{{ request('search') }}"
         placeholder="Search ticket number, subject…"
         class="flex-1 min-w-[160px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">

  <select name="status" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">Open & in-progress</option>
    @foreach(\App\Models\SupportTicket::STATUSES as $s)
    <option value="{{ $s }}" {{ request('status')===$s ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$s)) }}</option>
    @endforeach
  </select>

  <select name="priority" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All priorities</option>
    @foreach(\App\Models\SupportTicket::PRIORITIES as $p)
    <option value="{{ $p }}" {{ request('priority')===$p ? 'selected' : '' }}>{{ ucfirst($p) }}</option>
    @endforeach
  </select>

  <select name="category" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All categories</option>
    @foreach(\App\Models\SupportTicket::CATEGORIES as $c)
    <option value="{{ $c }}" {{ request('category')===$c ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$c)) }}</option>
    @endforeach
  </select>

  <select name="assigned_to" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All agents</option>
    <option value="unassigned" {{ request('assigned_to')==='unassigned' ? 'selected' : '' }}>Unassigned</option>
    @foreach($admins as $admin)
    <option value="{{ $admin->id }}" {{ request('assigned_to')==$admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
    @endforeach
  </select>

  <div class="flex gap-2">
    <button type="submit"
            class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
      Filter
    </button>
    <a href="{{ route('admin.support.index') }}"
       class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">Clear</a>
  </div>
</form>

{{-- Ticket table --}}
<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ticket</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Salon</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Category</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Priority</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Assignee</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Opened</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($tickets as $ticket)
    <tr class="hover:bg-gray-800/30 transition-colors">
      <td class="px-5 py-3.5">
        <a href="{{ route('admin.support.show', $ticket) }}"
           class="font-semibold text-gray-200 hover:text-white block">{{ $ticket->subject }}</a>
        <p class="text-xs font-mono text-gray-500 mt-0.5">{{ $ticket->ticket_number }}</p>
        @if($ticket->user)
        <p class="text-xs text-gray-600 mt-0.5">{{ $ticket->user->email }}</p>
        @endif
      </td>
      <td class="px-4 py-3.5 hidden sm:table-cell">
        @if($ticket->salon)
        <a href="{{ route('admin.tenants.show', $ticket->salon_id) }}"
           class="text-xs text-velour-400 hover:text-velour-300">{{ $ticket->salon->name }}</a>
        @else
        <span class="text-xs text-gray-600">—</span>
        @endif
      </td>
      <td class="px-4 py-3.5 hidden lg:table-cell">
        <span class="text-xs text-gray-400 capitalize">{{ str_replace('_',' ',$ticket->category) }}</span>
      </td>
      <td class="px-4 py-3.5">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold border {{ $ticket->priorityColor() }} capitalize">
          {{ $ticket->priority }}
        </span>
      </td>
      <td class="px-4 py-3.5">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $ticket->statusColor() }} capitalize">
          {{ str_replace('_',' ',$ticket->status) }}
        </span>
      </td>
      <td class="px-4 py-3.5 hidden md:table-cell">
        @if($ticket->assignee)
        <span class="text-xs text-gray-300">{{ $ticket->assignee->name }}</span>
        @else
        <span class="text-xs text-red-400 font-medium">Unassigned</span>
        @endif
      </td>
      <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-gray-500"
          title="{{ $ticket->created_at->toIso8601String() }}">
        {{ $ticket->created_at->diffForHumans() }}
      </td>
      <td class="px-4 py-3.5">
        <a href="{{ route('admin.support.show', $ticket) }}"
           class="text-xs text-velour-400 hover:text-velour-300 font-medium">Open →</a>
      </td>
    </tr>
    @empty
    <tr>
      <td colspan="8" class="px-5 py-16 text-center">
        <p class="text-gray-500 text-sm">No tickets match your filters.</p>
        <p class="text-gray-600 text-xs mt-1">All caught up 🎉</p>
      </td>
    </tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $tickets->links() }}</div>

@endsection
