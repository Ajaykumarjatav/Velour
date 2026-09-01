@extends('layouts.app')
@section('title', 'Support tickets')
@section('page-title', 'Support tickets')
@section('content')

@php
    $storeKey = \App\Support\SalonUrl::key($salon);
@endphp

<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <div class="min-w-0">
            <h1 class="text-base sm:text-lg font-bold text-heading leading-tight">Support tickets</h1>
            <p class="text-[11px] text-muted mt-0.5">Store issues go to EasyGrox support. You both get email updates.</p>
        </div>
        <x-unless-admin-browse>
            <a href="{{ route('support-tickets.create', ['store' => $storeKey]) }}" class="btn-primary btn-sm inline-flex items-center gap-1.5 shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                New ticket
            </a>
        </x-unless-admin-browse>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,68%)_minmax(17.5rem,32%)] gap-5 items-start">
        <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 overflow-hidden">
            @if($tickets->isEmpty())
                <div class="px-5 py-12 text-center">
                    <p class="text-sm font-semibold text-heading">No tickets yet</p>
                    <p class="text-xs text-muted mt-1 max-w-xs mx-auto">If booking, payments, or the storefront is broken, open a ticket and we’ll follow up by email.</p>
                    <x-unless-admin-browse>
                        <a href="{{ route('support-tickets.create', ['store' => $storeKey]) }}" class="btn-primary btn-sm inline-flex mt-4">Open a ticket</a>
                    </x-unless-admin-browse>
                </div>
            @else
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-[10px] uppercase tracking-wider text-muted border-b border-gray-100 dark:border-gray-800">
                                <th class="px-4 py-2.5 font-semibold">Ticket</th>
                                <th class="px-4 py-2.5 font-semibold">Subject</th>
                                <th class="px-4 py-2.5 font-semibold">Priority</th>
                                <th class="px-4 py-2.5 font-semibold">Status</th>
                                <th class="px-4 py-2.5 font-semibold">Opened</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                                @php
                                    $href = route('support-tickets.show', ['store' => $storeKey, 'ticket' => $ticket]);
                                @endphp
                                <tr class="border-b border-gray-100 dark:border-gray-800 last:border-0 hover:bg-gray-50/80 dark:hover:bg-gray-800/40">
                                    <td class="px-4 py-2.5 whitespace-nowrap">
                                        <a href="{{ $href }}" class="font-mono text-[11px] font-semibold text-velour-600 dark:text-velour-400 hover:underline">{{ $ticket->ticket_number }}</a>
                                    </td>
                                    <td class="px-4 py-2.5 min-w-0">
                                        <a href="{{ $href }}" class="font-medium text-heading hover:underline line-clamp-1">{{ $ticket->subject }}</a>
                                        <p class="text-[11px] text-muted mt-0.5">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}</p>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold border {{ $ticket->priorityColor() }} capitalize">{{ $ticket->priority }}</span>
                                    </td>
                                    <td class="px-4 py-2.5">
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize {{ $ticket->statusColor() }}">{{ str_replace('_', ' ', $ticket->status) }}</span>
                                    </td>
                                    <td class="px-4 py-2.5 text-[11px] text-muted whitespace-nowrap">{{ $ticket->created_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="md:hidden divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($tickets as $ticket)
                        <a href="{{ route('support-tickets.show', ['store' => $storeKey, 'ticket' => $ticket]) }}" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-800/40">
                            <div class="flex items-start justify-between gap-2">
                                <p class="font-medium text-heading text-sm leading-snug">{{ $ticket->subject }}</p>
                                <span class="shrink-0 inline-flex px-2 py-0.5 rounded-md text-[11px] font-semibold capitalize {{ $ticket->statusColor() }}">{{ str_replace('_', ' ', $ticket->status) }}</span>
                            </div>
                            <p class="mt-1 font-mono text-[11px] text-velour-600 dark:text-velour-400">{{ $ticket->ticket_number }}</p>
                            <p class="mt-1 text-[11px] text-muted">
                                {{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}
                                · <span class="capitalize">{{ $ticket->priority }}</span>
                                · {{ $ticket->created_at->diffForHumans() }}
                            </p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>

        <aside class="space-y-3 lg:sticky lg:top-24">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4">
                <h2 class="text-sm font-bold text-heading mb-3">Overview</h2>
                <dl class="space-y-2.5 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Open</dt>
                        <dd class="font-semibold text-heading">{{ $stats['open'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Waiting on you</dt>
                        <dd class="font-semibold text-heading">{{ $stats['waiting'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Closed</dt>
                        <dd class="font-semibold text-heading">{{ $stats['closed'] }}</dd>
                    </div>
                    <div class="flex justify-between gap-2 pt-2 border-t border-gray-100 dark:border-gray-800">
                        <dt class="text-muted">Total</dt>
                        <dd class="font-semibold text-heading">{{ $stats['total'] }}</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4">
                <h2 class="text-sm font-bold text-heading mb-2">How it works</h2>
                <ul class="space-y-2 text-xs text-muted">
                    <li>Open a ticket for this store only</li>
                    <li>Support usually replies within 24 hours</li>
                    <li>You’ll get an email when they reply</li>
                </ul>
            </div>
        </aside>
    </div>

    @if($tickets->hasPages())
        <div class="mt-4">{{ $tickets->links() }}</div>
    @endif
</div>
@endsection
