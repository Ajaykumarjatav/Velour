@props([
    'listId' => 'appt-services-list',
    'buttonClass' => 'inline-flex items-center justify-center h-10 w-10 flex-shrink-0 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-velour-600 dark:text-velour-400 hover:bg-velour-50 dark:hover:bg-velour-900/30 hover:border-velour-300 dark:hover:border-velour-600 transition-colors focus:outline-none focus:ring-2 focus:ring-velour-400',
])

@php
    $cfg = [
        'postUrl' => route('quick-create.service'),
        'listId' => $listId,
        'csrf' => csrf_token(),
        'currencySymbol' => \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP'),
    ];
@endphp

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    if (window.__serviceQuickCreateAlpine) return;
    window.__serviceQuickCreateAlpine = true;
    Alpine.data('serviceQuickCreate', (cfg) => ({
        modalOpen: false,
        loading: false,
        fieldErrors: {},
        svcName: '',
        svcDuration: 60,
        svcPrice: '',
        cfg,
        err(key) {
            const e = this.fieldErrors[key];
            return Array.isArray(e) ? e[0] : (e || '');
        },
        openModal() {
            this.fieldErrors = {};
            this.svcName = '';
            this.svcDuration = 60;
            this.svcPrice = '';
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
        },
        async submitQuickCreate() {
            this.loading = true;
            this.fieldErrors = {};
            const fd = new FormData();
            fd.append('_token', this.cfg.csrf);
            fd.append('name', this.svcName);
            fd.append('duration_minutes', String(this.svcDuration));
            fd.append('price', String(this.svcPrice));
            try {
                const res = await fetch(this.cfg.postUrl, {
                    method: 'POST',
                    body: fd,
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });
                const data = await res.json().catch(() => ({}));
                if (res.status === 422) {
                    this.fieldErrors = data.errors || {};
                    if (data.message && typeof data.message === 'string' && !Object.keys(this.fieldErrors).length) {
                        window.showToast?.(data.message, 'error');
                    }
                    this.loading = false;
                    return;
                }
                if (res.status === 402) {
                    window.showToast?.(data.message || 'Plan limit reached', 'warning');
                    this.loading = false;
                    return;
                }
                if (!res.ok) {
                    window.showToast?.(data.message || 'Could not create service', 'error');
                    this.loading = false;
                    return;
                }
                window.appendApptServiceRow?.(data, this.cfg.listId);
                window.dispatchEvent(new CustomEvent('appt-services-changed'));
                window.showToast?.('Service added', 'success');
                this.closeModal();
            } catch (e) {
                window.showToast?.('Network error', 'error');
            }
            this.loading = false;
        },
    }));
});
window.appendApptServiceRow = function (detail, listId) {
    const list = document.getElementById(listId || 'appt-services-list');
    if (!list || !detail || detail.id == null) return;
    const esc = (s) => {
        const d = document.createElement('div');
        d.textContent = String(s ?? '');
        return d.innerHTML;
    };
    const wrap = document.createElement('div');
    wrap.className = 'rounded-lg border border-gray-100 dark:border-gray-800 p-2';
    wrap.innerHTML =
        '<label class="flex items-center gap-3 p-1 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20 cursor-pointer">' +
        '<input type="checkbox" name="services[]" value="' + String(detail.id) + '" checked class="rounded border-gray-300 dark:border-gray-600 text-velour-600">' +
        '<span class="flex-1 text-sm font-medium text-body">' + esc(detail.name) + '</span>' +
        '<span class="text-xs text-muted whitespace-nowrap">' + String(detail.duration_minutes) + ' min</span>' +
        '<span class="text-sm font-semibold text-heading whitespace-nowrap">' + esc(detail.price_formatted) + '</span>' +
        '</label>';
    list.appendChild(wrap);
    const cb = wrap.querySelector('input[name="services[]"]');
    if (cb) {
        cb.dispatchEvent(new Event('change', { bubbles: true }));
    }
};
</script>
@endpush
@endonce

<div x-data="serviceQuickCreate(@js($cfg))" {{ $attributes->class(['inline-flex flex-shrink-0 items-end']) }}>
    <button type="button"
            @click="openModal()"
            title="Add new service"
            aria-label="Add new service"
            class="{{ $buttonClass }}">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
    </button>

    <div x-show="modalOpen"
         x-cloak
         class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/50"
         @keydown.escape.window="closeModal()"
         role="dialog"
         aria-modal="true"
         aria-labelledby="svc-qc-title">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-800"
             @click.outside="closeModal()">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                <h3 id="svc-qc-title" class="text-lg font-bold text-heading">New service</h3>
                <button type="button" class="text-muted hover:text-heading p-1 rounded-lg" @click="closeModal()" aria-label="Close">&times;</button>
            </div>

            <div class="p-5 space-y-4">
                <p class="text-xs text-muted">Creates an active service in your catalog. You can edit details (category, staff, variants) under Services later.</p>
                <div>
                    <label class="form-label">Service name <span class="text-red-500">*</span></label>
                    <input type="text" x-model="svcName" class="form-input" :class="err('name') ? 'form-input-error' : ''" autocomplete="off" maxlength="150">
                    <p class="form-error text-xs mt-0.5" x-show="err('name')" x-text="err('name')"></p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="form-label">Duration (min) <span class="text-red-500">*</span></label>
                        <input type="number" x-model="svcDuration" min="5" max="480" step="1" class="form-input" :class="err('duration_minutes') ? 'form-input-error' : ''">
                        <p class="form-error text-xs mt-0.5" x-show="err('duration_minutes')" x-text="err('duration_minutes')"></p>
                    </div>
                    <div>
                        <label class="form-label">Price ({{ \App\Helpers\CurrencyHelper::symbol($currentSalon->currency ?? 'GBP') }}) <span class="text-red-500">*</span></label>
                        <input type="number" x-model="svcPrice" min="0" step="0.01" class="form-input" :class="err('price') ? 'form-input-error' : ''" placeholder="0.00">
                        <p class="form-error text-xs mt-0.5" x-show="err('price')" x-text="err('price')"></p>
                    </div>
                </div>
            </div>

            <div class="px-5 py-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50 flex justify-end gap-2">
                <button type="button" class="btn-outline" @click="closeModal()">Cancel</button>
                <button type="button" class="btn-primary" :disabled="loading" @click="submitQuickCreate()">
                    <span x-show="!loading">Create</span>
                    <span x-show="loading" x-cloak>Saving…</span>
                </button>
            </div>
        </div>
    </div>
</div>
