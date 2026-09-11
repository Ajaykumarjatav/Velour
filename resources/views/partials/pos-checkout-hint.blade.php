{{-- First-visit coach on the till. Same dismiss as the dashboard callout. --}}
@if(!empty($showPosCheckoutHint) && !($adminStoreBrowse ?? \App\Support\AuthPanel::isAdminStoreBrowse()))
<div class="mb-3"
     x-data="{ open: true }"
     x-show="open"
     role="region"
     aria-label="How Point of Sale works">
    <div class="rounded-xl border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50 dark:bg-emerald-950/40 px-3.5 py-3 sm:px-4">
        <div class="flex items-start gap-3">
            <span class="shrink-0 mt-0.5 inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600 text-white" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-emerald-950 dark:text-emerald-100">Try a test checkout</p>
                <p class="text-xs sm:text-sm text-emerald-900/80 dark:text-emerald-200/90 mt-0.5 leading-snug">
                    Tap a service on the left. Leave the customer as <strong>Walk-in</strong> if you like.
                    Tick <strong>Payment received</strong>, then <strong>Complete sale</strong>.
                    This saves as a real sale on your dashboard.
                </p>
            </div>
            <form method="POST" action="{{ route('pos.intro.dismiss') }}" class="shrink-0"
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
                <button type="submit"
                        class="text-xs font-semibold text-emerald-800 dark:text-emerald-200 hover:underline px-1 py-1"
                        aria-label="Dismiss POS tip">
                    Got it
                </button>
            </form>
        </div>
    </div>
</div>
@endif
