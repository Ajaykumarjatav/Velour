@extends('layouts.app')
@section('title', 'Tasks')
@section('page-title', 'Tasks')

@php
    $priorityBadge = function (\App\Models\SalonActionItem $item): string {
        return match ($item->priority) {
            'high' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
            'low' => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300',
            default => 'bg-sky-100 text-sky-800 dark:bg-sky-900/40 dark:text-sky-200',
        };
    };
    $priorityLabel = fn (\App\Models\SalonActionItem $item): string => match ($item->priority) {
        'high' => 'High',
        'low' => 'Low',
        default => 'Medium',
    };
    $isOverdue = function (\App\Models\SalonActionItem $item) use ($tz, $todayLocal): bool {
        if (! in_array($item->status, ['open', 'in_progress'], true) || ! $item->due_at) {
            return false;
        }
        $dueDay = \Carbon\Carbon::parse($item->due_at)->timezone($tz)->startOfDay();

        return $dueDay->lt($todayLocal);
    };
@endphp

@section('content')
<div class="max-w-7xl mx-auto space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <p class="text-sm text-muted mt-1 max-w-xl">Assign and track work across your team. Tasks stay in sync with the dashboard action center.</p>
        </div>
        @if($canManage)
        <button type="button" onclick="document.getElementById('new-task-panel')?.classList.toggle('hidden')" class="btn-primary shrink-0">
            + New task
        </button>
        @endif
    </div>

    @if(! $canManage)
    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 px-4 py-3 text-sm text-body">
        You can view tasks assigned to you or that you submitted. Ask management to update status or edit details.
    </div>
    @endif

    @if($canManage)
    <div id="new-task-panel" class="card hidden overflow-hidden">
        <div class="card-header border-b border-gray-100 dark:border-gray-800">
            <h2 class="section-title text-base">New task</h2>
        </div>
        <div class="p-5 sm:p-6">
            <form action="{{ route('action-items.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                @csrf
                <input type="hidden" name="redirect_after" value="tasks">
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Type</label>
                    <select name="kind" class="form-select text-sm">
                        <option value="{{ \App\Models\SalonActionItem::KIND_ADMIN_TODO }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_ADMIN_TODO] ?? 'To-do' }}</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_STAFF_SUGGESTION }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_STAFF_SUGGESTION] ?? 'Suggestion' }}</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_INVENTORY_REQUEST }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_INVENTORY_REQUEST] ?? 'Product' }}</option>
                        <option value="{{ \App\Models\SalonActionItem::KIND_GENERAL }}">{{ $deskKindLabels[\App\Models\SalonActionItem::KIND_GENERAL] ?? 'General' }}</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Priority</label>
                    <select name="priority" class="form-select text-sm">
                        <option value="normal">Normal</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Assign <span class="text-muted font-normal">(optional)</span></label>
                    <select name="assigned_staff_id" class="form-select text-sm">
                        <option value="">—</option>
                        @foreach($staffForAssign as $st)
                            <option value="{{ $st->id }}">{{ $st->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="md:col-span-3">
                    <label class="form-label text-xs">Due <span class="text-muted font-normal">(optional)</span></label>
                    <input type="date" name="due_at" class="form-input text-sm">
                </div>
                <div class="md:col-span-12">
                    <label class="form-label text-xs">Title</label>
                    <input type="text" name="title" required maxlength="200" class="form-input text-sm" placeholder="What needs to happen?">
                </div>
                <div class="md:col-span-12">
                    <label class="form-label text-xs">Details <span class="text-muted font-normal">(optional)</span></label>
                    <textarea name="body" rows="2" maxlength="5000" class="form-textarea text-sm" placeholder="Context, links, locations…"></textarea>
                </div>
                <div class="md:col-span-12 flex flex-wrap gap-2">
                    <button type="submit" class="btn-primary text-sm">Create task</button>
                    <button type="button" class="btn-outline text-sm" onclick="document.getElementById('new-task-panel')?.classList.add('hidden')">Cancel</button>
                </div>
                @foreach(['kind','title','body','priority','assigned_staff_id','due_at'] as $f)
                    @error($f)<p class="md:col-span-12 text-xs text-red-600">{{ $message }}</p>@enderror
                @endforeach
            </form>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="card p-4 sm:p-5 border-l-4 border-l-sky-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Open</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $countOpen }}</p>
        </div>
        <div class="card p-4 sm:p-5 border-l-4 border-l-amber-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">In progress</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $countInProgress }}</p>
        </div>
        <div class="card p-4 sm:p-5 border-l-4 border-l-emerald-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Done</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $countDone }}</p>
        </div>
        <div class="card p-4 sm:p-5 border-l-4 border-l-red-500">
            <p class="text-[10px] font-semibold uppercase tracking-wide text-muted">Overdue</p>
            <p class="text-2xl font-semibold text-heading mt-1 tabular-nums">{{ $countOverdue }}</p>
        </div>
    </div>

    @php
        $columns = [
            ['key' => 'todo', 'title' => 'To do', 'dot' => 'bg-sky-500', 'items' => $columnTodo],
            ['key' => 'progress', 'title' => 'In progress', 'dot' => 'bg-amber-500', 'items' => $columnProgress],
            ['key' => 'done', 'title' => 'Done', 'dot' => 'bg-emerald-500', 'items' => $columnDone],
        ];
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 lg:gap-5">
        @foreach($columns as $col)
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50/80 dark:bg-gray-950/40 min-h-[200px] flex flex-col">
            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200/80 dark:border-gray-800">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="w-2 h-2 rounded-full {{ $col['dot'] }} shrink-0" aria-hidden="true"></span>
                    <h2 class="text-sm font-semibold text-heading truncate">{{ $col['title'] }}</h2>
                </div>
                <span class="text-xs text-muted tabular-nums">{{ $col['items']->count() }}</span>
            </div>
            <div class="p-3 space-y-3 flex-1">
                @forelse($col['items'] as $item)
                <article class="card p-4 shadow-sm border-gray-200 dark:border-gray-800">
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-semibold text-heading leading-snug">{{ $item->title }}</h3>
                        <span class="text-[10px] font-bold uppercase px-1.5 py-0.5 rounded shrink-0 {{ $priorityBadge($item) }}">{{ $priorityLabel($item) }}</span>
                    </div>
                    @if($item->body)
                        <p class="text-xs text-body/90 mt-2 whitespace-pre-line">{{ $item->body }}</p>
                    @endif
                    @if($isOverdue($item))
                        <p class="text-[11px] font-medium text-red-600 dark:text-red-400 mt-2">Overdue</p>
                    @endif
                    <div class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-muted">
                        <span class="inline-flex items-center gap-1 min-w-0">
                            <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span class="truncate">{{ $item->assignedStaff?->name ?? 'Unassigned' }}</span>
                        </span>
                        @if($item->due_at)
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            {{ $item->due_at->timezone($tz)->format('n/j/Y') }}
                        </span>
                        @endif
                    </div>
                    <p class="text-[10px] text-muted mt-2">{{ $deskKindLabels[$item->kind] ?? $item->kind }}@if($item->staff) · from {{ $item->staff->name }}@endif</p>

                    @if($canManage)
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if($col['key'] === 'todo')
                            <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="rounded-full border border-gray-200 dark:border-gray-700 px-2.5 py-1 text-[11px] font-medium text-body hover:bg-gray-100 dark:hover:bg-gray-800">In progress</button>
                            </form>
                            <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="done">
                                <button type="submit" class="rounded-full border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-200 px-2.5 py-1 text-[11px] font-medium hover:bg-emerald-50 dark:hover:bg-emerald-950/40">Done</button>
                            </form>
                        @elseif($col['key'] === 'progress')
                            <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="rounded-full border border-gray-200 dark:border-gray-700 px-2.5 py-1 text-[11px] font-medium text-body hover:bg-gray-100 dark:hover:bg-gray-800">To do</button>
                            </form>
                            <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="done">
                                <button type="submit" class="rounded-full border border-emerald-200 dark:border-emerald-900 text-emerald-800 dark:text-emerald-200 px-2.5 py-1 text-[11px] font-medium hover:bg-emerald-50 dark:hover:bg-emerald-950/40">Done</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="open">
                                <button type="submit" class="rounded-full border border-gray-200 dark:border-gray-700 px-2.5 py-1 text-[11px] font-medium text-body hover:bg-gray-100 dark:hover:bg-gray-800">To do</button>
                            </form>
                            <form method="POST" action="{{ route('action-items.update', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="in_progress">
                                <button type="submit" class="rounded-full border border-amber-200 dark:border-amber-900 text-amber-800 dark:text-amber-200 px-2.5 py-1 text-[11px] font-medium hover:bg-amber-50 dark:hover:bg-amber-950/40">In progress</button>
                            </form>
                        @endif

                        <details class="relative group">
                            <summary class="list-none cursor-pointer rounded-full border border-gray-200 dark:border-gray-700 p-1.5 text-muted hover:bg-gray-100 dark:hover:bg-gray-800 [&::-webkit-details-marker]:hidden inline-flex" title="Edit">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </summary>
                            <div class="mt-2 p-3 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 text-left space-y-2">
                                <form method="POST" action="{{ route('tasks.update', $item) }}" class="space-y-2">
                                    @csrf @method('PATCH')
                                    <div>
                                        <label class="form-label text-[10px]">Type</label>
                                        <select name="kind" class="form-select text-xs">
                                            @foreach([
                                                \App\Models\SalonActionItem::KIND_ADMIN_TODO,
                                                \App\Models\SalonActionItem::KIND_STAFF_SUGGESTION,
                                                \App\Models\SalonActionItem::KIND_INVENTORY_REQUEST,
                                                \App\Models\SalonActionItem::KIND_GENERAL,
                                            ] as $k)
                                                <option value="{{ $k }}" @selected($item->kind === $k)>{{ $deskKindLabels[$k] ?? $k }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label text-[10px]">Title</label>
                                        <input type="text" name="title" value="{{ $item->title }}" required maxlength="200" class="form-input text-xs">
                                    </div>
                                    <div>
                                        <label class="form-label text-[10px]">Details</label>
                                        <textarea name="body" rows="2" maxlength="5000" class="form-textarea text-xs">{{ $item->body }}</textarea>
                                    </div>
                                    <div class="grid grid-cols-2 gap-2">
                                        <div>
                                            <label class="form-label text-[10px]">Priority</label>
                                            <select name="priority" class="form-select text-xs">
                                                <option value="low" @selected($item->priority === 'low')>Low</option>
                                                <option value="normal" @selected($item->priority === 'normal')>Normal</option>
                                                <option value="high" @selected($item->priority === 'high')>High</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label text-[10px]">Status</label>
                                            <select name="status" class="form-select text-xs">
                                                <option value="open" @selected($item->status === 'open')>Open</option>
                                                <option value="in_progress" @selected($item->status === 'in_progress')>In progress</option>
                                                <option value="done" @selected($item->status === 'done')>Done</option>
                                                <option value="dismissed" @selected($item->status === 'dismissed')>Dismissed</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label text-[10px]">Assign</label>
                                        <select name="assigned_staff_id" class="form-select text-xs">
                                            <option value="">—</option>
                                            @foreach($staffForAssign as $st)
                                                <option value="{{ $st->id }}" @selected((int) $item->assigned_staff_id === (int) $st->id)>{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="form-label text-[10px]">Due</label>
                                        <input type="date" name="due_at" value="{{ $item->due_at?->timezone($tz)->format('Y-m-d') }}" class="form-input text-xs">
                                    </div>
                                    <button type="submit" class="btn-primary text-xs w-full">Save</button>
                                </form>
                            </div>
                        </details>

                        <form method="POST" action="{{ route('tasks.destroy', $item) }}" class="inline" onsubmit="return confirm('Delete this task?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded-full border border-red-200 dark:border-red-900/60 p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-950/30" title="Delete">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                    @endif
                </article>
                @empty
                <p class="text-sm text-muted text-center py-8">No tasks</p>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
