@extends('layouts.admin')
@section('title', 'Data Explorer')
@section('page-title', 'Cross-tenant explorer')

@section('content')
<div class="space-y-5">
    <p class="text-sm text-gray-400">Search clients and appointments across all stores.</p>

    <form method="GET" class="bg-gray-900 border border-gray-800 rounded-2xl p-4 flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="text-xs text-gray-500 block mb-1">Search</label>
            <input type="search" name="search" value="{{ $search }}" required
                   class="w-full px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Type</label>
            <select name="type" class="px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
                <option value="clients" @selected($type === 'clients')>Clients</option>
                <option value="appointments" @selected($type === 'appointments')>Appointments</option>
            </select>
        </div>
        <div>
            <label class="text-xs text-gray-500 block mb-1">Store</label>
            <select name="salon_id" class="px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl min-w-[10rem]">
                <option value="">All stores</option>
                @foreach($salons as $s)
                <option value="{{ $s->id }}" @selected($salonId == $s->id)>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2 text-sm font-semibold rounded-xl bg-velour-600 text-white">Search</button>
    </form>

    @if($search !== '')
    <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-800/50 text-gray-500 text-[11px] uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Store</th>
                    <th class="px-4 py-3 text-left">Record</th>
                    <th class="px-4 py-3 text-right"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800/60">
                @forelse($results as $row)
                <tr>
                    <td class="px-4 py-3 text-gray-400">{{ $row->salon?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-200">
                        @if($type === 'appointments')
                            {{ $row->reference }} · {{ $row->starts_at?->format('j M Y') }}
                        @else
                            {{ $row->full_name }} · {{ $row->phone ?? $row->email }}
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($type === 'clients')
                        <a href="{{ route('admin.tenants.clients.show', [$row->salon_id, $row->id]) }}" class="text-velour-400 text-xs font-semibold">View</a>
                        @else
                        <a href="{{ route('admin.tenants.appointments.show', [$row->salon_id, $row->id]) }}" class="text-velour-400 text-xs font-semibold">View</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="3" class="px-4 py-12 text-center text-gray-500">No matches.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
