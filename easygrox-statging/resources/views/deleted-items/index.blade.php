@extends('layouts.app')
@section('title', 'Deleted Items')
@section('page-title', 'Deleted Items')

@section('content')
<div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
    <p class="text-xs text-muted leading-relaxed max-w-2xl">
        Soft-deleted records from your salon. Restore them here or permanently remove them (admins only).
        Items may still be purged automatically after your retention period.
    </p>
    @if($typeCounts->isNotEmpty())
    <p class="text-[13px] font-semibold text-heading tabular-nums shrink-0">
        {{ $items->count() }} item{{ $items->count() === 1 ? '' : 's' }}
    </p>
    @endif
</div>

@if($typeCounts->isNotEmpty())
<div class="flex flex-wrap gap-2 mb-4">
    <a href="{{ route('deleted-items.index') }}"
       class="px-2.5 py-1 rounded-lg text-[12px] font-medium border transition-colors
              {{ ! $filter ? 'border-velour-400 bg-velour-50 text-velour-700 dark:bg-velour-950/40 dark:text-velour-300' : 'border-gray-200 dark:border-gray-700 text-muted hover:text-heading' }}">
        All ({{ $typeCounts->sum() }})
    </a>
    @foreach($types as $typeKey => $config)
        @php $count = (int) ($typeCounts[$typeKey] ?? 0); @endphp
        @if($count > 0)
        <a href="{{ route('deleted-items.index', ['type' => $typeKey]) }}"
           class="px-2.5 py-1 rounded-lg text-[12px] font-medium border transition-colors
                  {{ $filter === $typeKey ? 'border-velour-400 bg-velour-50 text-velour-700 dark:bg-velour-950/40 dark:text-velour-300' : 'border-gray-200 dark:border-gray-700 text-muted hover:text-heading' }}">
            {{ $config['plural'] }} ({{ $count }})
        </a>
        @endif
    @endforeach
</div>
@endif

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>Type</th>
                <th>Name</th>
                <th class="hidden sm:table-cell">Deleted</th>
                @unless($adminStoreBrowse ?? false)
                <th class="text-right">Actions</th>
                @endunless
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>
                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        {{ $item['type_label'] }}
                    </span>
                </td>
                <td class="font-medium text-heading">{{ $item['name'] }}</td>
                <td class="hidden sm:table-cell text-muted text-[12px]">{{ $item['deleted_at'] }}</td>
                @unless($adminStoreBrowse ?? false)
                <td class="text-right">
                    <div class="inline-flex items-center gap-2 justify-end">
                        @if($item['can_restore'])
                        <form action="{{ route('deleted-items.restore', ['type' => $item['type'], 'id' => $item['id']]) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn-outline btn-sm">Restore</button>
                        </form>
                        @endif
                        @if($item['can_force_delete'])
                        <form action="{{ route('deleted-items.force-delete', ['type' => $item['type'], 'id' => $item['id']]) }}"
                              method="POST"
                              onsubmit="return confirm('Permanently delete this item? This cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-outline btn-sm text-red-600 dark:text-red-400 border-red-200 dark:border-red-900/50 hover:bg-red-50 dark:hover:bg-red-950/30">
                                Delete forever
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
                @endunless
            </tr>
            @empty
            <tr>
                <td colspan="{{ ($adminStoreBrowse ?? false) ? 3 : 4 }}" class="px-5 py-12 text-center">
                    <div class="empty-state py-6">
                        <svg class="empty-state-icon w-10 h-10 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 01-1 1v3M4 7h16"/>
                        </svg>
                        <p class="empty-state-title">Trash is empty</p>
                        <p class="empty-state-sub">Deleted clients, staff, services and other records will appear here.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
