@php
    use App\Support\StorefrontTheme;
    $readOnly = $readOnly ?? ($adminStoreBrowse ?? false);
@endphp

<div class="space-y-3" id="storefront-theme-picker"
     data-theme-action="{{ parse_url($action ?? route('go-live.theme'), PHP_URL_PATH) }}"
     data-csrf-cookie-url="{{ parse_url(route('sanctum.csrf-cookie'), PHP_URL_PATH) }}">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Website theme</h3>
            <p class="text-xs text-muted mt-0.5">
                Active:
                <span class="font-medium text-gray-700 dark:text-gray-200" id="storefront-theme-active-label">{{ $themeLabel }}</span>
            </p>
            @if($readOnly)
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">View-only — theme cannot be changed.</p>
            @endif
        </div>
        <span id="storefront-theme-badge"
              class="text-[11px] px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800 font-medium">
            {{ $themeLabel }}
        </span>
    </div>

    <p id="storefront-theme-status"
       class="text-xs text-amber-600 dark:text-amber-400 font-medium hidden"
       role="status"></p>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach($themes as $slug => $theme)
        @php
            $isActive = $themeSlug === $slug;
            $previewUrl = StorefrontTheme::previewImageUrl($slug);
            $accent = StorefrontTheme::accentColor($slug);
        @endphp

        @if($readOnly)
        <div class="rounded-2xl border overflow-hidden {{ $isActive ? 'border-amber-500 ring-2 ring-amber-400/40 shadow-md' : 'border-gray-200 dark:border-gray-600 opacity-80' }}">
        @else
        <label class="storefront-theme-card group cursor-pointer rounded-2xl border overflow-hidden transition-all {{ $isActive ? 'border-amber-500 ring-2 ring-amber-400/40 shadow-md is-active' : 'border-gray-200 dark:border-gray-600 hover:border-amber-300 dark:hover:border-amber-600' }}"
               data-theme-slug="{{ $slug }}"
               data-theme-label="{{ $theme['label'] }}">
        @endif
            <div class="relative aspect-[16/10] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                @if($previewUrl)
                    <img src="{{ $previewUrl }}" alt="{{ $theme['label'] }} theme preview"
                         class="w-full h-full object-cover object-center {{ $readOnly ? '' : 'transition-transform duration-300 group-hover:scale-[1.02]' }}">
                @else
                    <div class="w-full h-full flex items-center justify-center text-white text-2xl font-semibold"
                         style="background: linear-gradient(135deg, {{ $accent }}, #1a1a1a);">
                        {{ $theme['label'] }}
                    </div>
                @endif
                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/70 to-transparent"></div>
                <span class="theme-active-badge absolute top-2 right-2 text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded-full bg-amber-500 text-white shadow {{ $isActive ? '' : 'hidden' }}">
                    Active
                </span>
            </div>
            <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800">
                @if($readOnly)
                <span class="w-4 h-4 rounded-full border-2 flex-shrink-0 {{ $isActive ? 'border-amber-500 bg-amber-500' : 'border-gray-300 dark:border-gray-600 bg-transparent' }}"
                      aria-hidden="true"></span>
                @else
                <input type="radio" name="storefront_theme" value="{{ $slug }}"
                       class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500"
                       {{ $isActive ? 'checked' : '' }}
                       data-theme-picker-radio>
                @endif
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $theme['label'] }}</p>
                    <p class="text-[11px] text-muted truncate">{{ $slug }}</p>
                </div>
            </div>
        @if($readOnly)
        </div>
        @else
        </label>
        @endif
        @endforeach
    </div>
</div>

@unless($readOnly)
@once
@push('scripts')
<script>
(function () {
    function setStatus(message, isError) {
        const el = document.getElementById('storefront-theme-status');
        if (!el) return;

        el.textContent = message || '';
        el.classList.toggle('hidden', !message);
        el.classList.toggle('text-red-600', Boolean(isError));
        el.classList.toggle('dark:text-red-400', Boolean(isError));
        el.classList.toggle('text-amber-600', !isError);
        el.classList.toggle('dark:text-amber-400', !isError);
    }

    function markActiveCard(slug, label) {
        document.querySelectorAll('.storefront-theme-card').forEach(function (card) {
            const active = card.dataset.themeSlug === slug;
            card.classList.toggle('border-amber-500', active);
            card.classList.toggle('ring-2', active);
            card.classList.toggle('ring-amber-400/40', active);
            card.classList.toggle('shadow-md', active);
            card.classList.toggle('is-active', active);
            card.classList.toggle('border-gray-200', !active);
            card.classList.toggle('dark:border-gray-600', !active);
            card.querySelector('.theme-active-badge')?.classList.toggle('hidden', !active);

            const radio = card.querySelector('[data-theme-picker-radio]');
            if (radio) radio.checked = active;
        });

        const activeLabel = document.getElementById('storefront-theme-active-label');
        const badge = document.getElementById('storefront-theme-badge');
        if (activeLabel) activeLabel.textContent = label;
        if (badge) badge.textContent = label;
    }

    async function saveTheme(picker, slug, label, input) {
        setStatus('Saving theme…', false);
        input.disabled = true;

        try {
            const http = window.EasyGroxHttp;
            const fd = new FormData();
            fd.append('theme', slug);

            if (http) {
                await http.refreshCsrf();
            }

            const response = http
                ? await http.post(picker.dataset.themeAction, fd)
                : await fetch(picker.dataset.themeAction, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                    body: fd,
                });

            const data = await response.json().catch(function () { return {}; });

            if (!response.ok) {
                if (response.status === 401 || response.status === 419) {
                    throw new Error('Session expired. Refresh this page and try again.');
                }
                throw new Error(data.message || 'Could not save theme.');
            }

            markActiveCard(slug, data.label || label);
            document.dispatchEvent(new CustomEvent('storefront-theme-changed', {
                detail: { theme: slug, label: data.label || label },
            }));
            setStatus(data.message || 'Theme updated.', false);
            setTimeout(function () { setStatus('', false); }, 3000);
        } catch (error) {
            setStatus(error.message || 'Could not save theme.', true);
            const current = document.querySelector('.storefront-theme-card.is-active [data-theme-picker-radio]');
            if (current) current.checked = true;
        } finally {
            input.disabled = false;
        }
    }

    document.addEventListener('change', function (event) {
        const input = event.target;
        if (!input.matches('[data-theme-picker-radio]')) return;

        const picker = input.closest('#storefront-theme-picker');
        const card = input.closest('.storefront-theme-card');
        if (!picker || !card || card.classList.contains('is-active')) return;

        saveTheme(picker, card.dataset.themeSlug, card.dataset.themeLabel, input);
    });
})();
</script>
@endpush
@endonce
@endunless
