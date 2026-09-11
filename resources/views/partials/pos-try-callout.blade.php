{{-- Dashboard POS intro. Hidden after first real sale or this user dismisses. --}}
@php
    $salon = $salon ?? ($currentSalon ?? null);
    $posCreateUrl = ($salon && Route::has('pos.create'))
        ? route('pos.create', ['store' => \App\Support\SalonUrl::key($salon)])
        : '';
    $posIndexUrl = ($salon && Route::has('pos.index'))
        ? route('pos.index', ['store' => \App\Support\SalonUrl::key($salon)])
        : '';
@endphp
@if($salon && $posCreateUrl !== '' && !($adminStoreBrowse ?? \App\Support\AuthPanel::isAdminStoreBrowse()))
<div
    class="relative overflow-hidden rounded-2xl border border-emerald-300/80 dark:border-emerald-500/40 bg-gradient-to-br from-emerald-700 via-teal-700 to-cyan-800 text-white shadow-lg mb-5"
    role="region"
    aria-label="Point of Sale"
    x-data="{ open: true }"
    x-show="open"
>
    <div class="pointer-events-none absolute -right-8 -top-10 h-40 w-40 rounded-full bg-white/15 blur-2xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -bottom-12 -left-6 h-32 w-32 rounded-full bg-cyan-300/20 blur-2xl" aria-hidden="true"></div>

    <div class="relative p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
        <div class="flex items-start gap-3 min-w-0 flex-1">
            <span class="shrink-0 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 ring-1 ring-white/25" aria-hidden="true">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </span>
            <div class="min-w-0">
                <p class="text-[11px] font-bold uppercase tracking-wider text-white/80">Your till — try it once</p>
                <p class="text-base sm:text-lg font-semibold leading-snug mt-0.5">Point of Sale takes the payment</p>
                <p class="text-sm text-white/90 mt-1 leading-snug">
                    Checkout for services, packages, and retail. Walk-in is fine if nobody is on the books.
                    Tap an item, tick <span class="font-semibold">Payment received</span>, then Complete sale.
                </p>
                <ol class="mt-2.5 grid grid-cols-1 sm:grid-cols-3 gap-1.5 text-xs text-white/90">
                    <li class="rounded-lg bg-black/20 px-2.5 py-1.5">1. Pick a service</li>
                    <li class="rounded-lg bg-black/20 px-2.5 py-1.5">2. Walk-in is OK</li>
                    <li class="rounded-lg bg-black/20 px-2.5 py-1.5">3. Complete sale</li>
                </ol>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2 sm:justify-end shrink-0">
            <a href="{{ $posCreateUrl }}"
               class="inline-flex items-center justify-center rounded-xl bg-white text-emerald-800 text-sm font-semibold px-3.5 py-2 hover:bg-emerald-50 transition-colors">
                Try a test sale
            </a>
            @if($posIndexUrl !== '')
            <a href="{{ $posIndexUrl }}"
               class="inline-flex items-center justify-center rounded-xl bg-white/10 ring-1 ring-white/30 text-white text-sm font-semibold px-3.5 py-2 hover:bg-white/15 transition-colors">
                Open POS
            </a>
            @endif
            <form method="POST" action="{{ route('pos.intro.dismiss') }}" class="inline"
                  @submit.prevent="
                    open = false;
                    fetch($el.action, {
                        method: 'POST',
                        headers: window.EasyGroxHttp
                            ? window.EasyGroxHttp.csrfHeaders()
                            : { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    }).catch(function () {});
                  ">
                @csrf
                <button type="submit" class="text-xs font-medium text-white/75 hover:text-white underline underline-offset-2 px-1 py-2">
                    Not now
                </button>
            </form>
        </div>
    </div>
</div>
@endif
