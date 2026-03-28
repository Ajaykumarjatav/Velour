@extends('layouts.app')
@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('content')

<div class="space-y-5">

  {{-- Filters --}}
  <form method="GET" action="{{ route('activity.index') }}"
        class="bg-white border border-gray-200 rounded-2xl p-4 flex flex-wrap gap-3">

    <select name="subject_type"
            class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All resource types</option>
      @foreach($subjectTypes as $type)
      <option value="{{ $type }}" {{ request('subject_type')===$type?'selected':'' }}>{{ $type }}</option>
      @endforeach
    </select>

    <select name="event"
            class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <option value="">All events</option>
      @foreach(['created','updated','deleted','restored'] as $ev)
      <option value="{{ $ev }}" {{ request('event')===$ev?'selected':'' }}>{{ ucfirst($ev) }}</option>
      @endforeach
    </select>

    <div class="flex gap-2 flex-1 min-w-0">
      <input type="date" name="from" value="{{ request('from') }}"
             class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
      <input type="date" name="to" value="{{ request('to') }}"
             class="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    </div>

    <div class="flex gap-2">
      <a href="{{ route('activity.index') }}"
         class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
      <button type="submit"
              class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Filter
      </button>
    </div>
  </form>

  {{-- Activity timeline --}}
  @if($activities->isEmpty())
  <div class="bg-white border border-gray-200 rounded-2xl p-12 text-center">
    <p class="text-gray-400 text-sm">No activity recorded yet.</p>
  </div>
  @else
  <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden">
    <div class="divide-y divide-gray-100">
      @foreach($activities as $activity)
      @php
        $eventColor = match($activity->event) {
          'created'  => 'bg-green-100 text-green-700 border-green-200',
          'updated'  => 'bg-blue-100 text-blue-700 border-blue-200',
          'deleted'  => 'bg-red-100 text-red-700 border-red-200',
          'restored' => 'bg-amber-100 text-amber-700 border-amber-200',
          default    => 'bg-gray-100 text-gray-700 border-gray-200',
        };
        $props  = $activity->properties;
        $old    = $props['old'] ?? [];
        $new    = $props['new'] ?? [];
        $hasDiff = !empty($old) || !empty($new);
        $causer = $activity->causer;
      @endphp
      <div class="px-5 py-4 hover:bg-gray-50 transition-colors" x-data="{ open: false }">
        <div class="flex items-start justify-between gap-4">

          <div class="flex items-start gap-3 min-w-0">
            {{-- Event badge --}}
            <span class="mt-0.5 px-2 py-0.5 text-xs font-bold rounded border {{ $eventColor }} flex-shrink-0">
              {{ strtoupper($activity->event) }}
            </span>

            <div class="min-w-0">
              {{-- Subject --}}
              <p class="text-sm font-medium text-gray-900">
                {{ class_basename($activity->subject_type ?? '') }}
                @if($activity->subject_id)
                  <span class="text-gray-400 font-normal">#{{ $activity->subject_id }}</span>
                @endif
              </p>

              {{-- By whom --}}
              <p class="text-xs text-gray-500 mt-0.5">
                by
                <span class="font-medium text-gray-700">
                  {{ $causer?->name ?? 'System' }}
                </span>
                ·
                {{ $activity->created_at->diffForHumans() }}
                @if($props['ip'] ?? null)
                  · <span class="font-mono">{{ $props['ip'] }}</span>
                @endif
              </p>
            </div>
          </div>

          @if($hasDiff)
          <button @click="open=!open"
                  class="flex-shrink-0 text-xs text-velour-600 font-medium hover:text-velour-700"
                  x-text="open ? 'Hide changes' : 'Show changes'">
          </button>
          @endif
        </div>

        {{-- Diff viewer --}}
        @if($hasDiff)
        <div x-show="open" x-cloak class="mt-4 border border-gray-100 rounded-xl overflow-hidden">
          <table class="w-full text-xs">
            <thead>
            <tr class="bg-gray-50 border-b border-gray-100">
              <th class="text-left px-4 py-2 font-semibold text-gray-400 uppercase tracking-wider">Field</th>
              @if($old)<th class="text-left px-4 py-2 font-semibold text-red-400 uppercase tracking-wider">Before</th>@endif
              @if($new)<th class="text-left px-4 py-2 font-semibold text-green-500 uppercase tracking-wider">After</th>@endif
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
            @foreach($new ?: $old as $field => $value)
            <tr class="hover:bg-gray-50">
              <td class="px-4 py-2 font-mono text-gray-600 font-semibold">{{ $field }}</td>
              @if($old)
              <td class="px-4 py-2 font-mono text-red-600 bg-red-50 max-w-[200px] truncate">
                {{ is_array($old[$field] ?? null) ? json_encode($old[$field]) : ($old[$field] ?? '—') }}
              </td>
              @endif
              @if($new)
              <td class="px-4 py-2 font-mono text-green-700 bg-green-50 max-w-[200px] truncate">
                {{ is_array($new[$field] ?? null) ? json_encode($new[$field]) : ($new[$field] ?? '—') }}
              </td>
              @endif
            </tr>
            @endforeach
            </tbody>
          </table>
        </div>
        @endif
      </div>
      @endforeach
    </div>
  </div>

  <div>{{ $activities->links() }}</div>
  @endif

</div>

@endsection
