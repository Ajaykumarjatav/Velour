@php
    use App\Support\StorefrontTheme;
    use App\Support\ThemeBranding;

    $readOnly  = $readOnly ?? ($adminStoreBrowse ?? false);
    $branding  = ThemeBranding::resolve($salon, $themeSlug);
    $defaults  = ThemeBranding::defaults($themeSlug);

    $badgeLabel = fn (string $element) => $branding['custom'][$element] ? 'Custom' : 'Theme default';
    $showUrl   = parse_url(route('go-live.theme-branding.show', ['theme' => '__THEME__']), PHP_URL_PATH);
    $resetUrl  = parse_url(route('go-live.theme-branding.reset', ['theme' => '__THEME__', 'element' => '__ELEMENT__']), PHP_URL_PATH);
    $updateUrl = parse_url(route('go-live.theme-branding.update'), PHP_URL_PATH);
@endphp

<div class="space-y-4"
     id="theme-branding"
     data-theme="{{ $themeSlug }}"
     data-show-url="{{ $showUrl }}"
     data-update-url="{{ $updateUrl }}"
     data-reset-url="{{ $resetUrl }}">

    <div class="flex items-start justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Theme branding</h3>
            <p class="text-xs text-muted mt-0.5">
                Applies to the <span class="font-medium text-gray-700 dark:text-gray-200" data-branding-theme-label>{{ StorefrontTheme::label($themeSlug) }}</span>
                theme of <span class="font-medium text-gray-700 dark:text-gray-200">{{ $salon->name }}</span> only.
                Your other locations keep their own branding. Anything you leave empty uses that theme's default.
            </p>
            @if($readOnly)
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">View-only — branding cannot be changed.</p>
            @endif
        </div>
    </div>

    <p data-branding-status class="text-xs text-amber-600 dark:text-amber-400 font-medium hidden" role="status"></p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        {{-- Logo --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Logo</span>
                <span data-branding-badge="logo"
                      class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $branding['custom']['logo'] ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300' }}">
                    {{ $badgeLabel('logo') }}
                </span>
            </div>
            <div class="h-20 rounded-lg bg-gray-900 flex items-center justify-center overflow-hidden">
                <img data-branding-preview="logo" src="{{ $branding['logo_url'] }}" alt=""
                     class="max-h-16 max-w-[80%] object-contain {{ $branding['logo_url'] ? '' : 'hidden' }}">
                {{-- Themes ship no logo file, so the EasyGrox lockup is their default. --}}
                <span data-branding-empty="logo"
                      class="inline-flex items-center gap-2 {{ $branding['logo_url'] ? 'hidden' : '' }}">
                    <img src="{{ asset('images/easygrox-icon.png') }}" alt="" class="h-8 w-8 object-contain">
                    <span class="text-lg font-bold tracking-tight text-white">EasyGrox</span>
                </span>
            </div>
            @unless($readOnly)
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-300 hover:text-amber-600 cursor-pointer">
                    Upload
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" data-branding-file="logo">
                </label>
                <button type="button" data-branding-reset="logo"
                        class="text-[11px] text-muted hover:text-amber-600 {{ $branding['custom']['logo'] ? '' : 'hidden' }}">
                    Reset to default
                </button>
            </div>
            <p class="text-[10px] text-muted">PNG, JPG or WebP · max 4 MB</p>
            @endunless
        </div>

        {{-- Banner --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-3 space-y-2">
            <div class="flex items-center justify-between gap-2">
                <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">Banner image</span>
                <span data-branding-badge="banner"
                      class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $branding['custom']['banner'] ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300' }}">
                    {{ $badgeLabel('banner') }}
                </span>
            </div>
            <div class="aspect-[16/9] rounded-lg bg-gray-900 overflow-hidden">
                <img data-branding-preview="banner" src="{{ $branding['banner_url'] }}" alt=""
                     class="w-full h-full object-cover {{ $branding['banner_url'] ? '' : 'hidden' }}">
            </div>
            @unless($readOnly)
            <div class="flex items-center gap-2">
                <label class="text-[11px] font-medium px-2.5 py-1.5 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-300 hover:text-amber-600 cursor-pointer">
                    Upload
                    <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" data-branding-file="banner">
                </label>
                <button type="button" data-branding-reset="banner"
                        class="text-[11px] text-muted hover:text-amber-600 {{ $branding['custom']['banner'] ? '' : 'hidden' }}">
                    Reset to default
                </button>
            </div>
            <p class="text-[10px] text-muted">JPG, PNG or WebP · max 5 MB · 1200px+ wide looks sharpest</p>
            @endunless
        </div>
    </div>

    {{-- Banner text --}}
    <div class="rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-3 space-y-3">
        <div>
            <div class="flex items-center justify-between gap-2 mb-1">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200" for="branding-heading">Banner heading</label>
                <div class="flex items-center gap-2">
                    <button type="button" data-branding-reset="heading"
                            class="text-[11px] text-muted hover:text-amber-600 {{ $branding['custom']['heading'] ? '' : 'hidden' }}">Reset</button>
                    <span data-branding-badge="heading"
                          class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $branding['custom']['heading'] ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300' }}">
                        {{ $badgeLabel('heading') }}
                    </span>
                </div>
            </div>
            <textarea id="branding-heading" rows="2" maxlength="80" data-branding-text="heading"
                      {{ $readOnly ? 'disabled' : '' }}
                      placeholder="{{ $defaults['heading'] }}"
                      class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 px-3 py-2 focus:ring-amber-500 focus:border-amber-500">{{ $branding['custom']['heading'] ? $branding['heading'] : '' }}</textarea>
            <p class="text-[10px] text-muted mt-1">Max 80 characters. Press Enter for a line break.</p>
        </div>

        <div>
            <div class="flex items-center justify-between gap-2 mb-1">
                <label class="text-xs font-semibold text-gray-700 dark:text-gray-200" for="branding-subheading">Banner text</label>
                <div class="flex items-center gap-2">
                    <button type="button" data-branding-reset="subheading"
                            class="text-[11px] text-muted hover:text-amber-600 {{ $branding['custom']['subheading'] ? '' : 'hidden' }}">Reset</button>
                    <span data-branding-badge="subheading"
                          class="text-[10px] font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full {{ $branding['custom']['subheading'] ? 'bg-amber-500 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300' }}">
                        {{ $badgeLabel('subheading') }}
                    </span>
                </div>
            </div>
            <textarea id="branding-subheading" rows="3" maxlength="220" data-branding-text="subheading"
                      {{ $readOnly ? 'disabled' : '' }}
                      placeholder="{{ $defaults['subheading'] }}"
                      class="w-full text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-100 px-3 py-2 focus:ring-amber-500 focus:border-amber-500">{{ $branding['custom']['subheading'] ? $branding['subheading'] : '' }}</textarea>
            <p class="text-[10px] text-muted mt-1">Max 220 characters. Leave empty to use the theme default.</p>
        </div>
    </div>
</div>

@unless($readOnly)
@push('scripts')
<script>
(function () {
    const root = document.getElementById('theme-branding');
    if (!root) return;

    const http = () => window.EasyGroxHttp;

    function setStatus(message, isError, keep) {
        const el = root.querySelector('[data-branding-status]');
        if (!el) return;
        el.textContent = message || '';
        el.classList.toggle('hidden', !message);
        el.classList.toggle('text-red-600', Boolean(isError));
        el.classList.toggle('text-amber-600', !isError);
        if (message && !isError && !keep) {
            setTimeout(function () { setStatus('', false); }, 3000);
        }
    }

    function applyPayload(data) {
        root.dataset.theme = data.theme;
        root.querySelectorAll('[data-branding-theme-label]').forEach(function (el) { el.textContent = data.label; });

        ['logo', 'banner'].forEach(function (element) {
            const img = root.querySelector('[data-branding-preview="' + element + '"]');
            const url = element === 'logo' ? data.logo_url : data.banner_url;
            if (img) {
                img.src = url || '';
                img.classList.toggle('hidden', !url);
            }
            const empty = root.querySelector('[data-branding-empty="' + element + '"]');
            if (empty) empty.classList.toggle('hidden', Boolean(url));
        });

        ['heading', 'subheading'].forEach(function (element) {
            const field = root.querySelector('[data-branding-text="' + element + '"]');
            if (!field) return;
            field.value = data.custom[element] ? data[element] : '';
            field.placeholder = data.defaults[element] || '';
        });

        Object.keys(data.custom).forEach(function (element) {
            const badge = root.querySelector('[data-branding-badge="' + element + '"]');
            if (badge) {
                badge.textContent = data.custom[element] ? 'Custom' : 'Theme default';
                badge.classList.toggle('bg-amber-500', data.custom[element]);
                badge.classList.toggle('text-white', data.custom[element]);
                badge.classList.toggle('bg-gray-100', !data.custom[element]);
                badge.classList.toggle('dark:bg-gray-700', !data.custom[element]);
                badge.classList.toggle('text-gray-500', !data.custom[element]);
                badge.classList.toggle('dark:text-gray-300', !data.custom[element]);
            }
            const reset = root.querySelector('[data-branding-reset="' + element + '"]');
            if (reset) reset.classList.toggle('hidden', !data.custom[element]);
        });
    }

    async function send(url, options) {
        const client = http();
        if (client) await client.refreshCsrf();

        const response = client
            ? await client.request(url, options)
            : await fetch(url, Object.assign({
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            }, options));

        const data = await response.json().catch(function () { return {}; });
        if (!response.ok) {
            // Laravel wraps validation errors, so dig out the field message the user can act on.
            const fieldError = data.errors ? Object.values(data.errors).flat()[0] : null;
            throw new Error(fieldError || data.message || 'Could not save branding.');
        }
        return data;
    }

    async function save(formData, pendingMessage) {
        setStatus(pendingMessage, false);
        formData.append('theme', root.dataset.theme);

        try {
            const data = await send(root.dataset.updateUrl, { method: 'POST', body: formData });
            applyPayload(data);
            setStatus(data.warning || data.message || 'Saved.', false, Boolean(data.warning));
        } catch (error) {
            setStatus(error.message, true);
        }
    }

    async function load(theme) {
        try {
            const data = await send(root.dataset.showUrl.replace('__THEME__', theme), { method: 'GET' });
            applyPayload(data);
        } catch (error) {
            setStatus(error.message, true);
        }
    }

    root.addEventListener('change', function (event) {
        const fileInput = event.target.closest('[data-branding-file]');
        if (fileInput && fileInput.files.length) {
            const fd = new FormData();
            fd.append(fileInput.dataset.brandingFile, fileInput.files[0]);
            save(fd, 'Uploading…');
            fileInput.value = '';
            return;
        }

        const textField = event.target.closest('[data-branding-text]');
        if (textField) {
            const fd = new FormData();
            fd.append(textField.dataset.brandingText, textField.value.trim());
            save(fd, 'Saving…');
        }
    });

    root.addEventListener('click', async function (event) {
        const button = event.target.closest('[data-branding-reset]');
        if (!button) return;

        const url = root.dataset.resetUrl
            .replace('__THEME__', root.dataset.theme)
            .replace('__ELEMENT__', button.dataset.brandingReset);

        setStatus('Resetting…', false);
        try {
            const data = await send(url, { method: 'DELETE' });
            applyPayload(data);
            setStatus(data.message || 'Reset.', false);
        } catch (error) {
            setStatus(error.message, true);
        }
    });

    // Follow the theme picker so this card always edits the live theme.
    document.addEventListener('storefront-theme-changed', function (event) {
        load(event.detail.theme);
    });
})();
</script>
@endpush
@endunless
