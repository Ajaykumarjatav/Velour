@extends('layouts.admin')

@section('title', $salon->name . ' — ' . ($moduleLabel ?? 'Data'))
@section('page-title', $salon->name)

@section('content')
@include('admin.tenants.partials.context-bar', ['salon' => $salon, 'activeModule' => $module ?? null])

<div class="space-y-4">
    @include('admin.tenants.partials.breadcrumb', [
        'salon' => $salon,
        'items' => [['label' => $moduleLabel ?? 'Module']],
    ])

    <div class="flex items-center justify-between gap-3 flex-wrap">
        <span class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg bg-gray-800 text-gray-400 border border-gray-700">
            Read-only
        </span>
        @if($salon->owner)
        <form method="POST" action="{{ route('admin.users.impersonate', $salon->owner->id) }}">
            @csrf
            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg border border-gray-700 text-gray-300 hover:bg-gray-800">
                Edit as owner
            </button>
        </form>
        @endif
    </div>

    @yield('tenant_module_content')
</div>
@endsection
