@extends('layouts.admin-tenant')

@section('tenant_module_content')
@include('admin.tenants.partials.breadcrumb', ['salon' => $salon, 'items' => [['label' => 'Activity log']]])

<form method="GET" class="flex gap-2 mb-4">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search events…"
           class="flex-1 px-4 py-2 text-sm bg-gray-900 border border-gray-800 text-gray-200 rounded-xl">
    <button type="submit" class="px-4 py-2 text-sm rounded-xl bg-gray-800 text-gray-200">Search</button>
</form>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden divide-y divide-gray-800/60">
    @forelse($logs as $log)
    <div class="px-4 py-3 text-sm">
        <p class="text-gray-200">{{ $log->description }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ $log->event }} · {{ $log->occurred_at?->format('j M Y H:i') }}</p>
    </div>
    @empty
    <p class="px-4 py-12 text-center text-gray-500">No activity logged for this store.</p>
    @endforelse
</div>
@if($logs->hasPages())
<div class="mt-4">{{ $logs->links() }}</div>
@endif
@endsection
