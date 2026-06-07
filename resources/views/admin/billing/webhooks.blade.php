@extends('layouts.admin')
@section('title', 'Webhook Log')
@section('page-title', 'Stripe Webhook Log')
@section('content')

<form method="GET" action="{{ route('admin.billing.webhooks') }}" class="flex gap-3 mb-6 flex-wrap">
  <input type="text" name="type" value="{{ request('type') }}" placeholder="Filter by event type…"
         class="flex-1 min-w-0 px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">
  <select name="status" onchange="this.form.submit()"
          class="px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <option value="">All statuses</option>
    @foreach(['processed','received','failed','ignored'] as $s)
    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
    @endforeach
  </select>
  <button type="submit" class="px-5 py-2 text-sm font-medium rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
    Filter
  </button>
</form>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
  <table class="w-full text-sm">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Event</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Event ID</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Error</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Received</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($webhooks as $wh)
    @php
      $statusColors = [
        'processed' => 'bg-green-900/50 text-green-400',
        'failed'    => 'bg-red-900/50 text-red-400',
        'received'  => 'bg-blue-900/50 text-blue-400',
        'ignored'   => 'bg-gray-800 text-gray-500',
      ];
    @endphp
    <tr class="hover:bg-gray-800/30 transition-colors {{ $wh->status === 'failed' ? 'bg-red-950/20' : '' }}"
        x-data="{ open: false }">
      <td class="px-5 py-3.5">
        <span class="font-mono text-xs text-gray-300">{{ $wh->type }}</span>
      </td>
      <td class="px-4 py-3.5">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $statusColors[$wh->status] ?? 'bg-gray-800 text-gray-400' }}">
          {{ ucfirst($wh->status) }}
        </span>
      </td>
      <td class="px-4 py-3.5 hidden md:table-cell font-mono text-xs text-gray-500 truncate max-w-[140px]">
        {{ $wh->stripe_event_id ?? '—' }}
      </td>
      <td class="px-4 py-3.5 hidden lg:table-cell text-xs text-red-400 truncate max-w-[200px]">
        @if($wh->exception)
        <button @click="open=!open" class="underline text-left">
          {{ Str::limit($wh->exception, 60) }}
        </button>
        <div x-show="open" x-cloak
             class="mt-2 p-3 bg-red-950/30 border border-red-900/50 rounded-xl text-xs text-red-300 font-mono break-all whitespace-pre-wrap max-w-lg">
          {{ $wh->exception }}
        </div>
        @else
        —
        @endif
      </td>
      <td class="px-4 py-3.5 text-xs text-gray-500">
        {{ \Carbon\Carbon::parse($wh->created_at)->format('d M H:i:s') }}
      </td>
      <td class="px-4 py-3.5 text-right">
        @if($wh->status === 'failed')
        <form method="POST" action="{{ route('admin.billing.webhook.replay', $wh->id) }}">
          @csrf
          <button type="submit" class="text-xs text-amber-400 hover:text-amber-300 font-semibold">
            ↺ Retry
          </button>
        </form>
        @endif
      </td>
    </tr>
    @empty
    <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-gray-500">No webhooks recorded yet.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $webhooks->links() }}</div>

@endsection
