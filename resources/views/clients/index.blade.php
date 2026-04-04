@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('content')

<p class="text-sm text-muted mb-4">{{ number_format($clientTotal) }} total clients</p>

@if(!empty($loyaltyFilterTier))
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-xl border border-velour-200 dark:border-velour-800 bg-velour-50 dark:bg-velour-900/20 px-4 py-3 text-sm">
        <span class="text-body">Showing <strong class="text-heading">{{ $loyaltyFilterTier->name }}</strong> members</span>
        <a href="{{ route('clients.index', request()->except('loyalty_tier_id')) }}" class="text-link font-medium">Clear filter</a>
    </div>
@endif

<div class="flex flex-col lg:flex-row gap-4 mb-6 items-start">
    <form action="{{ route('clients.index') }}" method="GET" class="flex flex-1 flex-col sm:flex-row gap-3 min-w-0 w-full">
        @if(request('loyalty_tier_id'))
            <input type="hidden" name="loyalty_tier_id" value="{{ request('loyalty_tier_id') }}">
        @endif
        <input type="text" name="search" value="{{ $search }}" placeholder="Search name, email or phone…" class="form-input w-full min-w-0 flex-1">
        <div class="flex flex-wrap gap-2 shrink-0">
            <button type="submit" class="btn-secondary">Search</button>
            @if($search)<a href="{{ route('clients.index') }}" class="btn-outline">Clear</a>@endif
        </div>
    </form>
    <form action="{{ route('clients.import') }}" method="POST" enctype="multipart/form-data" class="inline shrink-0">
        @csrf
        <label for="client-csv-upload" class="btn-outline cursor-pointer w-full sm:w-auto">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            Import
        </label>
        <input id="client-csv-upload" type="file" name="file" accept=".csv,text/csv,text/plain,.txt" class="hidden" onchange="if(this.files.length)this.form.submit()">
    </form>
    <a href="{{ route('clients.export') }}" class="btn-outline flex-shrink-0 w-full sm:w-auto">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
        Export
    </a>
    <a href="{{ route('clients.create') }}" class="btn-primary flex-shrink-0 w-full sm:w-auto text-center">+ Add Client</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Name</th>
            <th class="hidden sm:table-cell">Contact</th>
            <th class="hidden md:table-cell">Added</th>
            <th class="hidden lg:table-cell">Marketing</th>
            <th class="text-right w-[1%] whitespace-nowrap">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($clients as $client)
        <tr>
            <td>
                <div class="flex items-center gap-3 min-h-[2.75rem]">
                    <div class="w-9 h-9 rounded-full bg-velour-100 dark:bg-velour-900/40 flex items-center justify-center text-velour-700 dark:text-velour-300 font-bold text-sm flex-shrink-0">
                        {{ strtoupper(substr($client->first_name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-heading">{{ $client->first_name }} {{ $client->last_name }}</p>
                        @if($client->email)<p class="text-xs text-muted">{{ $client->email }}</p>@endif
                    </div>
                </div>
            </td>
            <td class="hidden sm:table-cell text-body">{{ $client->phone ?? '—' }}</td>
            <td class="hidden md:table-cell text-muted text-xs">{{ $client->created_at->format('d M Y') }}</td>
            <td class="hidden lg:table-cell">
                @if($client->marketing_consent)
                    <span class="badge-green">Opted in</span>
                @else
                    <span class="badge-gray">Opted out</span>
                @endif
            </td>
            <td class="text-right">
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
