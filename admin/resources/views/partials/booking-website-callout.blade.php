{{-- Additive dashboard callout. Hidden after this user opens Go Live once. --}}
@php
    $salon = $salon ?? null;
    $goLiveUrl = ($salon && Route::has('go-live'))
        ? route('go-live', ['store' => \App\Support\SalonUrl::key($salon)])
        : '';
@endphp
@if($salon && $goLiveUrl !== '')
<div
    class="relative overflow-hidden rounded-2xl border border-violet-300/80 dark:border-violet-500/40 bg-gradient-to-br from-violet-600 via-indigo-600 to-fuchsia-600 text-white shadow-lg mb-5"
    role="region"
    aria-label="Your website"
>
    <div class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/15 blur-2xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-12 -left-6 h-32 w-32 rounded-full bg-fuchsia-300/20 blur-2xl" aria-hidden="true"></div>

    <div class="relative p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex items-start gap-3 min-w-0 flex-1">
            <span class="shrink-0 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-white/80">Included with your account</p>
                <p class="text-base sm:text-lg font-semibold leading-snug mt-0.5">You already have a website</p>
                <p class="text-sm text-white/90 mt-1 leading-snug">
                    Open <a href="{{ $goLiveUrl }}" class="underline font-semibold text-white hover:text-white/90">Go Live &amp; Share</a>
                    to preview and share your public booking site.
                </p>
                <p class="mt-2 text-xs font-mono truncate text-white/80 bg-black/20 rounded-lg px-2.5 py-1.5" title="{{ $goLiveUrl }}">{{ $goLiveUrl }}</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:justify-end shrink-0">
            <a href="{{ $goLiveUrl }}"
               class="inline-flex items-center justify-center rounded-xl bg-white text-violet-800 text-sm font-semibold px-3.5 py-2 hover:bg-violet-50 transition-colors">
                Open website
            </a>
        </div>
    </div>
</div>
@endif
