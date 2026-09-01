@extends('layouts.app')
@section('title', $ticket->ticket_number)
@section('page-title', $ticket->ticket_number)
@section('content')

<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <a href="{{ route('support-tickets.index') }}" class="text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline">← All tickets</a>
        <p class="text-[11px] text-muted">Opened {{ $ticket->created_at->diffForHumans() }}</p>
    </div>

    <div class="grid lg:grid-cols-[minmax(0,68%)_minmax(17.5rem,32%)] gap-5 items-start">
        <div class="space-y-3">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4">
                <div class="flex flex-wrap items-center gap-1.5 mb-2">
                    <span class="font-mono text-[11px] text-muted">{{ $ticket->ticket_number }}</span>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-semibold border {{ $ticket->priorityColor() }} capitalize">{{ $ticket->priority }}</span>
                    <span class="px-2 py-0.5 rounded-lg text-[11px] font-semibold {{ $ticket->statusColor() }} capitalize">{{ str_replace('_', ' ', $ticket->status) }}</span>
                    <span class="text-[11px] text-muted">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}</span>
                </div>
                <h1 class="text-base sm:text-lg font-bold text-heading leading-snug">{{ $ticket->subject }}</h1>
                @include('support-tickets.partials.body')
            </div>

            @foreach($ticket->publicReplies as $reply)
                <div class="rounded-2xl border p-3.5 {{ $reply->is_admin_reply ? 'border-velour-200 dark:border-velour-800 bg-velour-50/40 dark:bg-velour-950/20' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80' }}">
                    <p class="text-[11px] text-muted mb-1.5">
                        <span class="font-semibold text-heading">{{ $reply->author?->name ?? 'Support' }}</span>
                        @if($reply->is_admin_reply)
                            <span class="text-velour-600 dark:text-velour-400">· EasyGrox support</span>
                        @endif
                        · {{ $reply->created_at->diffForHumans() }}
                    </p>
                    <p class="text-sm text-body whitespace-pre-wrap leading-relaxed">{{ $reply->body }}</p>
                </div>
            @endforeach

            @if(! $ticket->isClosed())
                <x-unless-admin-browse>
                    <form method="POST" action="{{ \App\Support\AppUrl::path('support-tickets.reply', ['store' => \App\Support\SalonUrl::key($salon), 'ticket' => $ticket]) }}"
                          class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4 space-y-2">
                        @csrf
                        <label class="form-label mb-0.5">Reply <span class="required-asterisk">*</span></label>
                        <textarea name="body" rows="4" required maxlength="10000"
                                  class="form-textarea @error('body') form-input-error @enderror"
                                  placeholder="Add more detail for the support team…">{{ old('body') }}</textarea>
                        @error('body')<p class="form-error">{{ $message }}</p>@enderror
                        <div class="flex justify-end">
                            <button type="submit" class="btn-primary btn-sm">Send reply</button>
                        </div>
                    </form>
                </x-unless-admin-browse>
            @else
                <p class="text-sm text-muted rounded-2xl border border-gray-200 dark:border-gray-800 px-4 py-3">This ticket is closed. Open a new ticket if you still need help.</p>
            @endif
        </div>

        <aside class="space-y-3 lg:sticky lg:top-24">
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4">
                <h2 class="text-sm font-bold text-heading mb-3">Ticket summary</h2>
                <dl class="space-y-2.5 text-xs">
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Status</dt>
                        <dd class="font-semibold capitalize {{ $ticket->statusColor() }} px-2 py-0.5 rounded-md">{{ str_replace('_', ' ', $ticket->status) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Priority</dt>
                        <dd class="font-semibold capitalize">{{ $ticket->priority }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Category</dt>
                        <dd class="text-heading text-right">{{ \App\Models\SupportTicket::categoryLabel($ticket->category) }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted">Opened</dt>
                        <dd class="text-heading">{{ $ticket->created_at->diffForHumans() }}</dd>
                    </div>
                </dl>
            </div>
            <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/80 p-4">
                <h2 class="text-sm font-bold text-heading mb-2">What happens next</h2>
                <ul class="space-y-2 text-xs text-muted">
                    <li>🕐 Support usually replies within 24 hours</li>
                    <li>🔔 You’ll get an email when they reply</li>
                    <li>🎫 Track updates on this page</li>
                </ul>
            </div>
        </aside>
    </div>
</div>
@endsection
