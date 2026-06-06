@props([
    'id',
    'name' => null,
    'label' => null,
    'required' => false,
    'errorName' => null,
    /** Remote JSON: GET ?q= → { results: [{ id, label }] }. Empty = filter local <option>s only. */
    'searchUrl' => null,
    'searchPlaceholder' => 'Search…',
    'hint' => null,
    'triggerClass' => 'form-select w-full',
    /** Tailwind classes for outer wrapper (width/flex in toolbars vs full-width fields). */
    'wrapperClass' => 'flex-1 min-w-0 relative w-full',
    /**
     * bottom: open below the trigger (default).
     * top: open above the trigger — use at the bottom of a clipped/scrollable panel (e.g. POS bill).
     */
    'panelPlacement' => 'bottom',
    /** Plain menu list (no search bar) — e.g. report filters. */
    'hideSearch' => false,
])

@php
    $errorName = $errorName ?? $name;
    $hasErr = $errorName && $errors->has($errorName);
    $remoteUrl = $searchUrl ? (string) $searchUrl : '';
    $panelPlacement = in_array($panelPlacement, ['top', 'bottom'], true) ? $panelPlacement : 'bottom';
    $panelPositionClasses = $panelPlacement === 'top'
        ? 'bottom-full mb-1'
        : 'top-full mt-1';
@endphp

    <div class="searchable-select-root relative {{ $wrapperClass }}"
     data-searchable-select
     data-select-id="{{ $id }}"
     data-search-remote="{{ $remoteUrl !== '' ? '1' : '0' }}"
     @if($remoteUrl !== '') data-search-url="{{ $remoteUrl }}" @endif>
    @if($label)
        <label class="form-label" for="{{ $id }}-trigger">
            {{ $label }}
            @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif
    <button type="button"
            id="{{ $id }}-trigger"
            class="{{ $triggerClass }} flex items-center justify-between gap-2 text-left font-normal {{ $hasErr ? 'form-input-error' : '' }}"
            aria-haspopup="listbox"
            aria-expanded="false"
            aria-controls="{{ $id }}-panel">
        <span id="{{ $id }}-label" class="truncate text-body"></span>
        <svg class="w-5 h-5 shrink-0 text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div id="{{ $id }}-panel"
         class="hidden absolute left-0 z-[100] min-w-full w-full {{ $panelPositionClasses }} rounded-lg border border-gray-200/90 dark:border-gray-600 bg-white dark:bg-gray-900 shadow-[0_4px_16px_rgba(0,0,0,0.12)] dark:shadow-[0_4px_20px_rgba(0,0,0,0.45)] overflow-hidden"
         role="listbox"
         data-hide-search="{{ $hideSearch ? '1' : '0' }}">
        @unless($hideSearch)
        <div class="p-2 border-b border-gray-100 dark:border-gray-800">
            <input type="search"
                   id="{{ $id }}-search"
                   class="form-input text-sm w-full"
                   placeholder="{{ $searchPlaceholder }}"
                   autocomplete="off">
        </div>
        @else
        <input type="search"
               id="{{ $id }}-search"
               class="sr-only"
               tabindex="-1"
               autocomplete="off"
               aria-hidden="true">
        @endunless
        <ul id="{{ $id }}-list" class="max-h-60 overflow-y-auto overflow-x-hidden py-1" role="presentation"></ul>
        @if($hint)
            <p class="text-xs text-muted px-3 py-2 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                {{ $hint }}
            </p>
        @endif
    </div>

    <select id="{{ $id }}"
            @if($name !== null && $name !== '') name="{{ $name }}" @endif
            @if($required) required @endif
            class="sr-only"
            tabindex="-1"
            {{ $attributes }}>
        {{ $slot }}
    </select>

    @if($hasErr)
        @error($errorName)
            <p class="form-error">{{ $message }}</p>
        @enderror
    @endif
</div>

@once
@push('scripts')
<script>
(function () {
    function getStickyChoices(select) {
        return Array.from(select.options)
            .filter((o) => o.value === '' || o.hasAttribute('data-sticky'))
            .map((o) => ({ id: o.value, label: o.textContent.trim() }));
    }

    function syncTriggerLabel(select, labelEl) {
        const opt = select.selectedOptions[0];
        const text = opt && opt.textContent ? opt.textContent.trim() : '';
        labelEl.textContent = text || 'Select…';
    }

    function ensureOption(select, id, label) {
        const idStr = id === '' || id === null ? '' : String(id);
        if (idStr === '') {
            select.value = '';
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            return;
        }
        const exists = Array.from(select.options).some((o) => o.value === idStr);
        if (!exists) {
            const opt = document.createElement('option');
            opt.value = idStr;
            opt.textContent = label;
            select.appendChild(opt);
        }
        select.value = idStr;
        select.dispatchEvent(new Event('input', { bubbles: true }));
        select.dispatchEvent(new Event('change', { bubbles: true }));
    }

    window.initSearchableSelectRoot = function (root) {
        const selectId = root.dataset.selectId;
        if (!selectId || root.dataset.ssReady === '1') return;
        root.dataset.ssReady = '1';

        const select = document.getElementById(selectId);
        const trigger = document.getElementById(selectId + '-trigger');
        const panel = document.getElementById(selectId + '-panel');
        const searchInput = document.getElementById(selectId + '-search');
        const listEl = document.getElementById(selectId + '-list');
        const labelEl = document.getElementById(selectId + '-label');
        if (!select || !trigger || !panel || !searchInput || !listEl || !labelEl) return;

        const remote = root.dataset.searchRemote === '1';
        const searchUrl = root.dataset.searchUrl || '';
        let debounceHandle = null;
        let activeController = null;
        let open = false;

        const hideSearch = panel.dataset.hideSearch === '1';
        const rowBtnClass = (selected) => [
            'w-full text-left px-4 py-2.5 text-sm truncate',
            'text-gray-800 dark:text-gray-100',
            'hover:bg-blue-50 dark:hover:bg-blue-950/40',
            'focus:bg-blue-50 dark:focus:bg-blue-950/40 focus:outline-none',
            selected ? 'bg-blue-50 dark:bg-blue-950/40 font-medium text-blue-900 dark:text-blue-100' : '',
        ].join(' ');

        const renderRows = (items) => {
            listEl.innerHTML = '';
            if (!items.length) {
                const li = document.createElement('li');
                li.className = 'px-4 py-2.5 text-sm text-muted';
                li.textContent = remote ? 'No matches.' : 'No matching options.';
                listEl.appendChild(li);
                return;
            }
            items.forEach((row) => {
                const li = document.createElement('li');
                const btn = document.createElement('button');
                btn.type = 'button';
                const selected = String(select.value) === String(row.id);
                btn.className = rowBtnClass(selected);
                btn.textContent = row.label;
                btn.title = row.label;
                btn.addEventListener('click', () => {
                    ensureOption(select, row.id, row.label);
                    syncTriggerLabel(select, labelEl);
                    close();
                });
                li.appendChild(btn);
                listEl.appendChild(li);
            });
        };

        const localFilter = () => {
            const q = searchInput.value.trim().toLowerCase();
            const rows = Array.from(select.options)
                .filter((o) => !o.disabled)
                .map((o) => ({
                    id: o.value,
                    label: o.textContent.trim(),
                }))
                .filter((row) => {
                    if (!q) return true;
                    return row.label.toLowerCase().includes(q) || String(row.id).toLowerCase().includes(q);
                });
            renderRows(rows);
        };

        const fetchRemote = async () => {
            const q = searchInput.value.trim();
            const sticky = (q === '' && remote) ? getStickyChoices(select) : [];
            if (activeController) activeController.abort();
            activeController = new AbortController();
            try {
                const url = new URL(searchUrl, window.location.origin);
                if (q) url.searchParams.set('q', q);
                const res = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: activeController.signal,
                });
                if (!res.ok) return;
                const data = await res.json();
                const raw = data.results || data.clients || [];
                const api = Array.isArray(raw) ? raw.map((r) => ({
                    id: r.id,
                    label: r.label || r.text || String(r.id),
                })) : [];
                const seen = new Set(sticky.map((s) => String(s.id)));
                const merged = sticky.concat(api.filter((r) => !seen.has(String(r.id))));
                renderRows(merged);
            } catch (e) {
                if (e.name !== 'AbortError') { /* no-op */ }
            }
        };

        const refreshList = () => {
            if (remote && searchUrl) {
                fetchRemote();
            } else {
                localFilter();
            }
        };

        const openPanel = () => {
            open = true;
            panel.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            searchInput.value = '';
            refreshList();
            if (!hideSearch) {
                requestAnimationFrame(() => searchInput.focus());
            }
        };

        const close = () => {
            open = false;
            panel.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
        };

        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (open) close();
            else openPanel();
        });

        searchInput.addEventListener('input', () => {
            clearTimeout(debounceHandle);
            debounceHandle = setTimeout(refreshList, remote ? 250 : 0);
        });

        searchInput.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.stopPropagation();
                close();
                trigger.focus();
            }
        });

        document.addEventListener('click', (e) => {
            if (!open) return;
            const t = e.target;
            if (trigger.contains(t) || panel.contains(t)) return;
            close();
        });

        select.addEventListener('change', () => {
            syncTriggerLabel(select, labelEl);
        });

        syncTriggerLabel(select, labelEl);
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('[data-searchable-select]').forEach((el) => window.initSearchableSelectRoot(el));
    });
})();
</script>
@endpush
@endonce
