@extends('layouts.app')
@section('title', $loyaltyTier->name.' — Members')
@section('page-title', $loyaltyTier->name.' — Members')
@section('content')

<div class="mb-6 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('marketing.growth', ['tab' => 'loyalty']) }}" class="text-sm text-link font-medium">← Back to Marketing</a>
    <a href="{{ route('clients.index', ['loyalty_tier_id' => $loyaltyTier->id]) }}" class="btn-outline btn-sm">Open in Clients list</a>
</div>

<div class="table-wrap">
    <table class="data-table">
        <thead>
        <tr>
            <th>Client</th>
            <th class="hidden sm:table-cell">Phone</th>
            <th class="text-right w-[1%]">Actions</th>
        </tr>
        </thead>
        <tbody>
        @forelse($clients as $client)
        <tr>
            <td class="font-medium text-heading">{{ $client->full_name }}</td>
            <td class="hidden sm:table-cell text-muted">{{ $client->phone ?? '—' }}</td>
            <td class="text-right">
                <a href="{{ route('clients.show', $client) }}" class="text-link text-xs font-medium">View</a>
            </td>
        </tr>
        @empty
        <tr><td colspan="3" class="px-5 py-12 text-center text-sm text-muted">No members on this plan yet.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">{{ $clients->links() }}</div>

@endsection
