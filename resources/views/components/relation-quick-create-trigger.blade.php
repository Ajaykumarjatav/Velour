@props([
    'type' => 'client',
    'selectId',
    'buttonClass' => 'inline-flex items-center justify-center h-10 w-10 flex-shrink-0 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-velour-600 dark:text-velour-400 hover:bg-velour-50 dark:hover:bg-velour-900/30 hover:border-velour-300 dark:hover:border-velour-600 transition-colors focus:outline-none focus:ring-2 focus:ring-velour-400',
    'title' => null,
])

@php
    $postUrl = match ($type) {
        'staff' => route('quick-create.staff'),
        'inventory_category' => route('quick-create.inventory-category'),
        'inventory_supplier' => '',
        default => route('quick-create.client'),
    };
    $cfg = [
        'type' => $type,
        'selectId' => $selectId,
        'postUrl' => $postUrl,
        'csrf' => csrf_token(),
    ];
    $btnTitle = $title ?? match ($type) {
        'staff' => 'Add staff member',
        'inventory_category' => 'Add category',
        'inventory_supplier' => 'Add supplier',
        default => 'Add client',
    };
@endphp

@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    if (window.__relationQuickCreateAlpine) return;
    window.__relationQuickCreateAlpine = true;
    Alpine.data('relationQuickCreate', (cfg) => ({
        modalOpen: false,
        loading: false,
        fieldErrors: {},
        qcFirst: '',
        qcLast: '',
        qcPhone: '',
        qcEmail: '',
        qcName: '',
        qcRole: 'stylist',
        qcStaffEmail: '',
        qcStaffPhone: '',
        qcInvCatName: '',
        qcInvSupplierName: '',
        cfg,
        openModal() {
            this.fieldErrors = {};
            this.qcFirst = '';
            this.qcLast = '';
            this.qcPhone = '';
            this.qcEmail = '';
            this.qcName = '';
            this.qcRole = 'stylist';
            this.qcStaffEmail = '';
            this.qcStaffPhone = '';
            this.qcInvCatName = '';
            this.qcInvSupplierName = '';
            this.modalOpen = true;
        },
        closeModal() {
            this.modalOpen = false;
        },
        err(key) {
            const e = this.fieldErrors[key];
            return Array.isArray(e) ? e[0] : (e || '');
        },
        applyOptionToSelect(data) {
            const sel = document.getElementById(this.cfg.selectId);
            if (!sel || data.id == null || !data.label) return;
            const idStr = String(data.id);
            const exists = Array.from(sel.options).some((o) => o.value === idStr);
            if (!exists) {
                const opt = document.createElement('option');
                opt.value = idStr;
                opt.textContent = data.label;
                sel.appendChild(opt);
            }
            sel.value = idStr;
            sel.dispatchEvent(new Event('change', { bubbles: true }));
        },
        successMessage() {
            if (this.cfg.type === 'client') return 'Client added';
            if (this.cfg.type === 'staff') return 'Staff member added';
            if (this.cfg.type === 'inventory_category') return 'Category added';
            return 'Record added';
        },
        async submitQuickCreate() {
            if (this.cfg.type === 'inventory_supplier') {
                this.fieldErrors = {};
                const name = (this.qcInvSupplierName || '').trim();
                if (!name) {
                    this.fieldErrors = { name: ['Supplier name is required.'] };
                    return;
                }
                if (name.length > 150) {
                    this.fieldErrors = { name: ['Max 150 characters.'] };
                    return;
                }
                this.applyOptionToSelect({ id: name, label: name });
                window.showToast?.('Supplier added', 'success');
                this.closeModal();
                return;
            }

            this.loading = true;
            this.fieldErrors = {};
            const fd = new FormData();
            fd.append('_token', this.cfg.csrf);
            if (this.cfg.type === 'client') {
                fd.append('first_name', this.qcFirst);
                fd.append('last_name', this.qcLast);
                fd.append('phone', this.qcPhone || '');
                fd.append('email', this.qcEmail || '');
            } else if (this.cfg.type === 'staff') {
                fd.append('name', this.qcName);
                fd.append('role', this.qcRole);
                fd.append('email', this.qcStaffEmail || '');
                fd.append('phone', this.qcStaffPhone || '');
            } else if (this.cfg.type === 'inventory_category') {
                fd.append('name', this.qcInvCatName);
            }
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
                    window.showToast?.(data.message || 'Could not create record', 'error');
                    this.loading = false;
                    return;
                }
                this.applyOptionToSelect(data);
                window.showToast?.(this.successMessage(), 'success');
                this.closeModal();
            } catch (e) {
                window.showToast?.('Network error', 'error');
            }
            this.loading = false;
        },
    }));
});
</script>
@endpush
@endonce

<div x-data="relationQuickCreate(@js($cfg))" {{ $attributes->class(['inline-flex flex-shrink-0 items-end']) }}>
    <button type="button"
            @click="openModal()"
            title="{{ $btnTitle }}"
            aria-label="{{ $btnTitle }}"
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
         :aria-labelledby="'rqc-title-' + cfg.selectId">
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-800"
             @click.outside="closeModal()">
            <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between gap-3">
                <h3 class="text-lg font-bold text-heading" :id="'rqc-title-' + cfg.selectId"
                    x-text="cfg.type === 'client' ? 'New client' : (cfg.type === 'staff' ? 'New staff member' : (cfg.type === 'inventory_category' ? 'New category' : (cfg.type === 'inventory_supplier' ? 'New supplier' : '')))"></h3>
                <button type="button" class="text-muted hover:text-heading p-1 rounded-lg" @click="closeModal()" aria-label="Close">&times;</button>
            </div>

            <div class="p-5 space-y-4">
                <template x-if="cfg.type === 'client'">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">First name <span class="text-red-500">*</span></label>
                                <input type="text" x-model="qcFirst" class="form-input" :class="err('first_name') ? 'form-input-error' : ''" autocomplete="given-name">
                                <p class="form-error text-xs mt-0.5" x-show="err('first_name')" x-text="err('first_name')"></p>
                            </div>
                            <div>
                                <label class="form-label">Last name <span class="text-red-500">*</span></label>
                                <input type="text" x-model="qcLast" class="form-input" :class="err('last_name') ? 'form-input-error' : ''" autocomplete="family-name">
                                <p class="form-error text-xs mt-0.5" x-show="err('last_name')" x-text="err('last_name')"></p>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Phone</label>
                            <input type="tel" x-model="qcPhone" class="form-input" :class="err('phone') ? 'form-input-error' : ''" autocomplete="tel">
                            <p class="form-error text-xs mt-0.5" x-show="err('phone')" x-text="err('phone')"></p>
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" x-model="qcEmail" class="form-input" :class="err('email') ? 'form-input-error' : ''" autocomplete="email">
                            <p class="form-error text-xs mt-0.5" x-show="err('email')" x-text="err('email')"></p>
                        </div>
                    </div>
                </template>

                <template x-if="cfg.type === 'staff'">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Full name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="qcName" class="form-input" :class="err('name') ? 'form-input-error' : ''" autocomplete="name">
                            <p class="form-error text-xs mt-0.5" x-show="err('name')" x-text="err('name')"></p>
                        </div>
                        <div>
                            <label class="form-label">Role <span class="text-red-500">*</span></label>
                            <select x-model="qcRole" class="form-select" :class="err('role') ? 'form-input-error' : ''">
                                @foreach(['stylist','therapist','manager','receptionist','junior','owner'] as $r)
                                    <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                @endforeach
                            </select>
                            <p class="form-error text-xs mt-0.5" x-show="err('role')" x-text="err('role')"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" x-model="qcStaffEmail" class="form-input" :class="err('email') ? 'form-input-error' : ''">
                                <p class="form-error text-xs mt-0.5" x-show="err('email')" x-text="err('email')"></p>
                            </div>
                            <div>
                                <label class="form-label">Phone</label>
                                <input type="tel" x-model="qcStaffPhone" class="form-input" :class="err('phone') ? 'form-input-error' : ''">
                                <p class="form-error text-xs mt-0.5" x-show="err('phone')" x-text="err('phone')"></p>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="cfg.type === 'inventory_category'">
                    <div>
                        <label class="form-label">Category name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="qcInvCatName" class="form-input" :class="err('name') ? 'form-input-error' : ''" autocomplete="off">
                        <p class="form-error text-xs mt-0.5" x-show="err('name')" x-text="err('name')"></p>
                    </div>
                </template>

                <template x-if="cfg.type === 'inventory_supplier'">
                    <div>
                        <label class="form-label">Supplier name <span class="text-red-500">*</span></label>
                        <input type="text" x-model="qcInvSupplierName" class="form-input" :class="err('name') ? 'form-input-error' : ''" maxlength="150" autocomplete="organization">
                        <p class="form-error text-xs mt-0.5" x-show="err('name')" x-text="err('name')"></p>
                        <p class="text-xs text-muted mt-2">Saved as text on this item; other items can reuse the name from the list once saved.</p>
                    </div>
                </template>
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
