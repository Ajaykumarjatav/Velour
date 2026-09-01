@extends('layouts.admin-tenant')

@section('tenant_module_content')
@include('admin.tenants.partials.breadcrumb', ['salon' => $salon, 'items' => [['label' => 'Deleted items']]])

@if(empty($groups))
<p class="text-sm text-gray-500 bg-gray-900 border border-gray-800 rounded-2xl p-8 text-center">No deleted items in this store.</p>
@else
<div class="space-y-4">
    @foreach($groups as $group)
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <h3 class="px-4 py-3 text-xs font-semibold text-gray-400 uppercase border-b border-gray-800">{{ $group['label'] }}</h3>
        <ul class="divide-y divide-gray-800/60">
            @foreach($group['items'] as $item)
            <li class="px-4 py-3 flex items-center justify-between gap-3 text-sm">
                <div>
                    <p class="text-gray-200">{{ $item['name'] }}</p>
                    <p class="text-xs text-gray-500">Deleted {{ $item['deleted_at']?->diffForHumans() }}</p>
                </div>
                <form method="POST" action="{{ route('admin.tenants.actions.deleted.restore', [$salon->id, $group['key'], $item['id']]) }}"
                      onsubmit="return confirm('Restore this item?')">
                    @csrf
                    <button type="submit" class="text-xs text-emerald-400 hover:text-emerald-300 font-semibold">Restore</button>
                </form>
            </li>
            @endforeach
        </ul>
    </div>
    @endforeach
</div>
@endif
@endsection
