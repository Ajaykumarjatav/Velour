@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('content')

<div class="flex flex-col sm:flex-row gap-3 mb-6">
    <form action="{{ route('clients.index') }}" method="GET" class="flex flex-1 gap-2">
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email or phone…" class="form-input flex-1">
        <button type="submit" class="btn-secondary">Search</button>
        @if($search)<a href="{{ route('clients.index') }}" class="btn-outline">Clear</a>@endif
    </form>
    <a href="{{ route('clients.create') }}" class="btn-primary flex-shrink-0">+ Add Client</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Name</th>
            <th class="hidden sm:table-cell">Contact</th>
            <th class="hidden md:table-cell">Added</th>
            <th class="hidden lg:table-cell">Marketing</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @forelse($clients as $client)
        <tr>
            <td class="px-5 py-3.5">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-full bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($client->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-heading">{{ $client->first_name }} {{ $client->last_name }}</p>
                        @if($client->email)<p class="text-xs text-muted">{{ $client->email }}</p>@endif
                    </div>
                </div>
            </td>
            <td class="px-4 py-3.5 hidden sm:table-cell text-body">{{ $client->phone ?? '—' }}</td>
            <td class="px-4 py-3.5 hidden md:table-cell text-muted text-xs">{{ $client->created_at->format('d M Y') }}</td>
            <td class="px-4 py-3.5 hidden lg:table-cell">
                @if($client->marketing_consent)
                    <span class="badge-green">Opted in</span>
                @else
                    <span class="badge-gray">Opted out</span>
                @endif
            </td>
            <td class="px-4 py-3.5 text-right">
                <a href="{{ route('clients.show', $client->id) }}" class="text-link text-xs font-medium">View</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-muted">No clients found</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $clients->links() }}</div>
@endsection
