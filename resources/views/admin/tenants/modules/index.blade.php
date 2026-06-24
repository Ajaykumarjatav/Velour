@extends('layouts.admin-tenant')

@section('tenant_module_content')
@php
    $showRoute = fn ($id) => route('admin.tenants.'.$module.'.show', [$salon->id, $id]);
@endphp

<form method="GET" class="flex gap-2 mb-4">
    <input type="search" name="search" value="{{ $search }}" placeholder="Search…"
           class="flex-1 px-4 py-2 text-sm bg-gray-900 border border-gray-800 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-gray-800 text-gray-200 hover:bg-gray-700">Search</button>
</form>

<div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-800/50 text-[11px] uppercase tracking-wide text-gray-500">
                <tr>
                    @foreach($columns as $col)
                        <th class="px-4 py-3 text-left font-semibold">{{ $col['label'] }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-right w-20"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($records as $row)
                <tr class="hover:bg-gray-800/30">
                    @foreach($columns as $col)
                    <td class="px-4 py-3 text-gray-300">
                        @include('admin.tenants.partials.cell', ['module' => $module, 'col' => $col['key'], 'row' => $row, 'salon' => $salon])
                    </td>
                    @endforeach
                    <td class="px-4 py-3 text-right">
                        <a href="{{ $showRoute($row->id) }}" class="text-xs text-velour-400 hover:text-velour-300 font-semibold">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="px-4 py-12 text-center text-gray-500">No records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())
    <div class="px-4 py-3 border-t border-gray-800">{{ $records->links() }}</div>
    @endif
</div>
@endsection
