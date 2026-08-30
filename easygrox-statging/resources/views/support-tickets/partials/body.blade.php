@php
    $bodyHtml = \App\Support\SupportTicketHtml::sanitize($ticket->body);
    $bodyIsHtml = \App\Support\SupportTicketHtml::looksLikeHtml($ticket->body);
    $files = $ticket->attachmentFiles();
@endphp
<div class="mt-3 text-sm text-body leading-relaxed @if($bodyIsHtml) st-ticket-body @else whitespace-pre-wrap @endif">
    @if($bodyIsHtml)
        {!! $bodyHtml !!}
    @else
        {{ $ticket->body }}
    @endif
</div>
@if($files !== [])
    <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-800">
        <p class="text-[10px] uppercase tracking-wide text-muted mb-2">Attachments</p>
        <div class="flex flex-wrap gap-2">
            @foreach($files as $file)
                @php
                    $isImage = str_starts_with((string) ($file['mime'] ?? ''), 'image/')
                        || preg_match('/\.(png|jpe?g|gif|webp)$/i', (string) ($file['name'] ?? ''));
                @endphp
                <a href="{{ $file['url'] ?: '#' }}" target="_blank" rel="noopener"
                   class="block w-[4.5rem] text-center group">
                    @if($isImage && !empty($file['url']))
                        <img src="{{ $file['url'] }}" alt="{{ $file['name'] }}"
                             class="w-[4.5rem] h-[4.5rem] rounded-lg object-cover border border-gray-200 dark:border-gray-700 bg-gray-100">
                    @else
                        <span class="w-[4.5rem] h-[4.5rem] rounded-lg bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-[10px] font-bold text-muted flex items-center justify-center">PDF</span>
                    @endif
                    <span class="mt-0.5 block text-[10px] text-muted truncate group-hover:text-velour-500">{{ $file['name'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
<style>
    .st-ticket-body ul { list-style: disc; padding-left: 1.25rem; }
    .st-ticket-body ol { list-style: decimal; padding-left: 1.25rem; }
    .st-ticket-body a { color: #7c3aed; text-decoration: underline; }
</style>
