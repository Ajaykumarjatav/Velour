@extends('layouts.admin')
@section('title', 'Contact queries')
@section('page-title', 'Contact queries')

@section('content')
<div class="space-y-5">
  <div class="flex flex-wrap items-end justify-between gap-3">
    <div>
      <p class="text-sm text-gray-500">Website / contact-form submissions from <code class="text-gray-400">contact_us_query</code>.</p>
    </div>
    <div class="bg-gray-900 border border-gray-800 rounded-2xl px-4 py-2.5 text-center min-w-[7rem]">
      <p class="text-xl font-semibold text-velour-400">{{ number_format($total) }}</p>
      <p class="text-[10px] uppercase tracking-wider text-gray-500">Total</p>
    </div>
  </div>

  <form method="GET" action="{{ route('admin.contact-queries.index') }}"
        class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3">
    <input type="search" name="search" value="{{ request('search') }}"
           placeholder="Search name, email, business, message…"
           class="flex-1 min-w-[180px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl
                  placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">

    <select name="topic"
            class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All topics</option>
      @foreach($topics as $topic)
        <option value="{{ $topic }}" @selected(request('topic') === $topic)>{{ $topic }}</option>
      @endforeach
    </select>

    <button type="submit"
            class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
      Filter
    </button>
    <a href="{{ route('admin.contact-queries.index') }}" class="px-4 py-2 text-sm text-gray-400 hover:text-gray-200">Clear</a>
  </form>

  <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-hidden">
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-gray-800 bg-gray-800/50">
            <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Contact</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden sm:table-cell">Business</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden md:table-cell">Topics</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Message</th>
            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider hidden lg:table-cell">Received</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
          @forelse($queries as $row)
            <tr class="hover:bg-gray-800/30 transition-colors">
              <td class="px-5 py-3.5 align-top">
                <p class="font-semibold text-gray-200">{{ $row->full_name }}</p>
                <a href="mailto:{{ $row->email }}" class="text-xs text-velour-400 hover:text-velour-300">{{ $row->email }}</a>
              </td>
              <td class="px-4 py-3.5 align-top hidden sm:table-cell">
                <span class="text-gray-300">{{ $row->business_name ?: '—' }}</span>
              </td>
              <td class="px-4 py-3.5 align-top hidden md:table-cell">
                <div class="flex flex-wrap gap-1">
                  @forelse($row->topicList() as $t)
                    <span class="inline-flex px-2 py-0.5 rounded-lg text-[10px] font-medium bg-gray-800 text-gray-300 border border-gray-700">{{ $t }}</span>
                  @empty
                    <span class="text-xs text-gray-600">—</span>
                  @endforelse
                </div>
              </td>
              <td class="px-4 py-3.5 align-top max-w-xs">
                <p class="text-gray-400 line-clamp-2">{{ \Illuminate\Support\Str::limit($row->message ?: '—', 120) }}</p>
              </td>
              <td class="px-4 py-3.5 align-top hidden lg:table-cell whitespace-nowrap">
                <span class="text-xs text-gray-500">{{ $row->created_at?->format('d M Y, H:i') }}</span>
              </td>
              <td class="px-4 py-3.5 align-top text-right">
                <a href="{{ route('admin.contact-queries.show', $row) }}"
                   class="text-xs font-semibold text-velour-400 hover:text-velour-300">View</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-5 py-16 text-center text-gray-500">No contact queries yet.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div>{{ $queries->links() }}</div>
</div>
@endsection
