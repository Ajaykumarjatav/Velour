@extends('layouts.admin')
@section('title', 'Tenant feedback')
@section('page-title', 'Tenant feedback')

@section('content')
<div class="space-y-5">
  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <p class="text-sm text-gray-500">Project feedback submitted from the salon admin panel popup.</p>
    </div>
    <div class="flex flex-wrap gap-3">
      <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-2.5 text-center min-w-[7rem]">
        <p class="text-xl font-semibold text-amber-400">{{ number_format($newCount) }}</p>
        <p class="text-[10px] uppercase tracking-wider text-gray-500">New</p>
      </div>
      <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-2.5 text-center min-w-[7rem]">
        <p class="text-xl font-semibold text-velour-400">{{ number_format($total) }}</p>
        <p class="text-[10px] uppercase tracking-wider text-gray-500">Total</p>
      </div>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.tenant-feedback.index') }}"
        class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3">
    <input type="search" name="search" value="{{ request('search') }}"
           placeholder="Search store, user, message…"
           class="flex-1 min-w-[180px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                  placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">

    <select name="status"
            class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All statuses</option>
      <option value="new" @selected(request('status') === 'new')>New</option>
      <option value="reviewed" @selected(request('status') === 'reviewed')>Reviewed</option>
    </select>

    <select name="rating"
            class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All ratings</option>
      @for($i = 5; $i >= 1; $i--)
        <option value="{{ $i }}" @selected((string) request('rating') === (string) $i)>{{ $i }} stars</option>
      @endfor
    </select>

    <button type="submit"
            class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
      Filter
    </button>
    <a href="{{ route('admin.tenant-feedback.index') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">Clear</a>
  </form>

  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-800 bg-gray-800/50">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Store</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">From</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Rating</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Message</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Status</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Received</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
          @forelse($rows as $row)
            <tr class="hover:bg-gray-800/30 transition-colors">
              <td class="px-5 py-3.5 align-top">
                <p class="font-semibold text-gray-200">{{ $row->salon?->name ?? '—' }}</p>
                <p class="text-xs text-gray-500">{{ $row->salon?->slug }}</p>
              </td>
              <td class="px-4 py-3.5 align-top hidden sm:table-cell">
                <p class="text-gray-300">{{ $row->user?->name ?? '—' }}</p>
                <p class="text-xs text-gray-500">{{ $row->user?->email }}</p>
              </td>
              <td class="px-4 py-3.5 align-top whitespace-nowrap">
                @if($row->rating)
                  <span class="text-amber-400 font-semibold">{{ $row->rating }}/5</span>
                @else
                  <span class="text-gray-600">—</span>
                @endif
              </td>
              <td class="px-4 py-3.5 align-top max-w-xs">
                <p class="text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($row->message, 120) }}</p>
              </td>
              <td class="px-4 py-3.5 align-top hidden md:table-cell">
                @if($row->status === 'new')
                  <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-amber-500/15 text-amber-300 border border-amber-500/30">New</span>
                @else
                  <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-500/30">Reviewed</span>
                @endif
              </td>
              <td class="px-4 py-3.5 align-top hidden lg:table-cell whitespace-nowrap">
                <span class="text-xs text-gray-500">{{ $row->created_at?->format('d M Y, H:i') }}</span>
              </td>
              <td class="px-4 py-3.5 align-top text-right">
                <a href="{{ route('admin.tenant-feedback.show', $row) }}"
                   class="text-xs font-semibold text-velour-400 hover:text-velour-300">View</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-16 text-center text-gray-500">No tenant feedback yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div>{{ $rows->links() }}</div>
</div>
@endsection
