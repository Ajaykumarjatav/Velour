@props([
    'type' => 'client',
    'selectId',
    'buttonClass' => 'inline-flex items-center justify-center h-10 w-10 flex-shrink-0 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-velour-600 dark:text-velour-400 hover:bg-velour-50 dark:hover:bg-velour-900/30 hover:border-velour-300 dark:hover:border-velour-600 transition-colors focus:outline-none focus:ring-2 focus:ring-velour-400',
    'title' => null,
    'clientLoyaltyTiers' => null,
    /** @var array<string, list<array{id:int,name:string}>>|null */
    'staffServicesByRole' => null,
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
    if ($type === 'staff') {
        $cfg['staffServicesByRole'] = $staffServicesByRole ?? [];
    }
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
        qcDateOfBirth: '',
        qcGender: '',
        qcAddress: '',
        qcNotes: '',
        qcLoyaltyTierId: '',
        qcMarketingConsent: false,
        qcStaffCommission: '0',
        qcStaffColor: '#7C3AED',
        qcStaffBio: '',
        qcStaffServiceIds: [],
        cfg,
        servicesForRole() {
            const m = this.cfg.staffServicesByRole || {};
            return m[this.qcRole] || [];
        },
        toggleStaffService(id, on) {
            id = Number(id);
            if (on) {
                if (!this.qcStaffServiceIds.includes(id)) {
                    this.qcStaffServiceIds.push(id);
                }
            } else {
                this.qcStaffServiceIds = this.qcStaffServiceIds.filter((x) => x !== id);
            }
        },
        openModal() {
            this.fieldErrors = {};
            this.qcFirst = '';
            this.qcLast = '';
            this.qcPhone = '';
            this.qcEmail = '';
            this.qcDateOfBirth = '';
            this.qcGender = '';
            this.qcAddress = '';
            this.qcNotes = '';
            this.qcLoyaltyTierId = '';
            this.qcMarketingConsent = false;
            this.qcName = '';
            this.qcRole = 'stylist';
            this.qcStaffEmail = '';
            this.qcStaffPhone = '';
            this.qcInvCatName = '';
            this.qcInvSupplierName = '';
            this.qcStaffCommission = '0';
            this.qcStaffColor = '#7C3AED';
            this.qcStaffBio = '';
            this.qcStaffServiceIds = [];
            this.modalOpen = true;
            this.$nextTick(() => {
                const el = this.$refs.staffAvatarFile;
                if (el) {
                    el.value = '';
                }
            });
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
                fd.append('date_of_birth', this.qcDateOfBirth || '');
                fd.append('gender', this.qcGender || '');
                fd.append('address', this.qcAddress || '');
                fd.append('notes', this.qcNotes || '');
                fd.append('loyalty_tier_id', this.qcLoyaltyTierId || '');
                if (this.qcMarketingConsent) {
                    fd.append('marketing_consent', '1');
                }
            } else if (this.cfg.type === 'staff') {
                fd.append('name', this.qcName);
                fd.append('role', this.qcRole);
                fd.append('email', this.qcStaffEmail || '');
                fd.append('phone', this.qcStaffPhone || '');
                fd.append('bio', this.qcStaffBio || '');
                fd.append('color', this.qcStaffColor || '#7C3AED');
                fd.append('commission_rate', this.qcStaffCommission !== '' ? this.qcStaffCommission : '0');
                (this.qcStaffServiceIds || []).forEach((id) => fd.append('services[]', String(id)));
                const avatarEl = this.$refs.staffAvatarFile;
                const file = avatarEl && avatarEl.files && avatarEl.files[0];
                if (file) {
                    fd.append('avatar', file);
                }
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
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-xl w-full max-h-[90vh] overflow-y-auto border border-gray-200 dark:border-gray-800"
             :class="(cfg.type === 'client' || cfg.type === 'staff') ? 'max-w-lg' : 'max-w-md'"
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
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" x-model="qcEmail" class="form-input" :class="err('email') ? 'form-input-error' : ''" autocomplete="email">
                                <p class="form-error text-xs mt-0.5" x-show="err('email')" x-text="err('email')"></p>
                            </div>
                            <div>
                                <label class="form-label">Phone</label>
                                <input type="tel" x-model="qcPhone" class="form-input" :class="err('phone') ? 'form-input-error' : ''" autocomplete="tel">
                                <p class="form-error text-xs mt-0.5" x-show="err('phone')" x-text="err('phone')"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Date of birth</label>
                                <input type="date" x-model="qcDateOfBirth" class="form-input" :class="err('date_of_birth') ? 'form-input-error' : ''">
                                <p class="form-error text-xs mt-0.5" x-show="err('date_of_birth')" x-text="err('date_of_birth')"></p>
                            </div>
                            <div>
                                <label class="form-label">Gender</label>
                                <select x-model="qcGender" class="form-select" :class="err('gender') ? 'form-input-error' : ''">
                                    <option value="">Prefer not to say</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="non_binary">Non-binary</option>
                                </select>
                                <p class="form-error text-xs mt-0.5" x-show="err('gender')" x-text="err('gender')"></p>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Address</label>
                            <input type="text" x-model="qcAddress" class="form-input" :class="err('address') ? 'form-input-error' : ''" autocomplete="street-address">
                            <p class="form-error text-xs mt-0.5" x-show="err('address')" x-text="err('address')"></p>
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea x-model="qcNotes" rows="3" class="form-textarea" :class="err('notes') ? 'form-input-error' : ''"></textarea>
                            <p class="form-error text-xs mt-0.5" x-show="err('notes')" x-text="err('notes')"></p>
                        </div>
                        @if($type === 'client' && $clientLoyaltyTiers && count($clientLoyaltyTiers))
                        <div>
                            <label class="form-label">Loyalty plan</label>
                            <select x-model="qcLoyaltyTierId" class="form-select" :class="err('loyalty_tier_id') ? 'form-input-error' : ''">
                                <option value="">— None —</option>
                                @foreach($clientLoyaltyTiers as $tier)
                                <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                                @endforeach
                            </select>
                            <p class="form-error text-xs mt-0.5" x-show="err('loyalty_tier_id')" x-text="err('loyalty_tier_id')"></p>
                        </div>
                        @endif
                        <div>
                            <label class="flex items-start gap-3 cursor-pointer">
                                <input type="checkbox" x-model="qcMarketingConsent" class="rounded border-gray-300 dark:border-gray-600 text-velour-600 mt-1 shrink-0">
                                <span class="flex-1 min-w-0">
                                    <span class="inline-flex items-center gap-1.5 text-sm font-medium text-body">
                                        Marketing consent
                                        <x-marketing-consent-help mode="tooltip" />
                                    </span>
                                    <x-marketing-consent-help mode="hint" class="mt-1.5" />
                                </span>
                            </label>
                        </div>
                    </div>
                </template>

                <template x-if="cfg.type === 'staff'">
                    <div class="space-y-4">
                        <div>
                            <label class="form-label">Photo <span class="text-red-500">*</span></label>
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" x-ref="staffAvatarFile"
                                   class="form-input text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-velour-50 file:text-velour-700 dark:file:bg-velour-900/40 dark:file:text-velour-200">
                            <p class="form-hint">JPG, PNG or WebP · max 2&nbsp;MB</p>
                            <p class="form-error text-xs mt-0.5" x-show="err('avatar')" x-text="err('avatar')"></p>
                        </div>
                        <div>
                            <label class="form-label">Full name <span class="text-red-500">*</span></label>
                            <input type="text" x-model="qcName" class="form-input" :class="err('name') ? 'form-input-error' : ''" autocomplete="name">
                            <p class="form-error text-xs mt-0.5" x-show="err('name')" x-text="err('name')"></p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" x-model="qcStaffEmail" class="form-input" :class="err('email') ? 'form-input-error' : ''" autocomplete="email">
                                <p class="form-error text-xs mt-0.5" x-show="err('email')" x-text="err('email')"></p>
                            </div>
                            <div>
                                <label class="form-label">Phone</label>
                                <input type="tel" x-model="qcStaffPhone" class="form-input" :class="err('phone') ? 'form-input-error' : ''" autocomplete="tel">
                                <p class="form-error text-xs mt-0.5" x-show="err('phone')" x-text="err('phone')"></p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="form-label">Role <span class="text-red-500">*</span></label>
                                <select x-model="qcRole" @change="qcStaffServiceIds = []" class="form-select" :class="err('role') ? 'form-input-error' : ''">
                                    @foreach(['stylist','therapist','manager','receptionist','junior','owner'] as $r)
                                        <option value="{{ $r }}">{{ ucfirst($r) }}</option>
                                    @endforeach
                                </select>
                                <p class="form-error text-xs mt-0.5" x-show="err('role')" x-text="err('role')"></p>
                            </div>
                            <div>
                                <label class="form-label">Commission %</label>
                                <input type="number" x-model="qcStaffCommission" min="0" max="100" step="0.1" class="form-input" :class="err('commission_rate') ? 'form-input-error' : ''">
                                <p class="form-error text-xs mt-0.5" x-show="err('commission_rate')" x-text="err('commission_rate')"></p>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Calendar colour</label>
                            <input type="color" x-model="qcStaffColor" class="w-full h-10 px-2 py-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer">
                            <p class="form-error text-xs mt-0.5" x-show="err('color')" x-text="err('color')"></p>
                        </div>
                        <div>
                            <label class="form-label">Bio</label>
                            <textarea x-model="qcStaffBio" rows="3" class="form-textarea" :class="err('bio') ? 'form-input-error' : ''"></textarea>
                            <p class="form-error text-xs mt-0.5" x-show="err('bio')" x-text="err('bio')"></p>
                        </div>
                        <div x-show="servicesForRole().length > 0" x-cloak>
                            <label class="form-label">Services offered</label>
                            <p class="form-hint mb-2">Only services permitted for the role selected above are listed.</p>
                            <p class="form-error text-xs mb-1" x-show="err('services')" x-text="err('services')"></p>
                            <div class="grid grid-cols-2 gap-2 border border-gray-200 dark:border-gray-700 rounded-xl p-3 max-h-40 overflow-y-auto bg-white dark:bg-gray-800">
                                <template x-for="svc in servicesForRole()" :key="svc.id">
                                    <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20">
                                        <input type="checkbox"
                                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600"
                                               :value="svc.id"
                                               :checked="qcStaffServiceIds.includes(Number(svc.id))"
                                               @change="toggleStaffService(svc.id, $event.target.checked)">
                                        <span class="text-sm text-body truncate" x-text="svc.name"></span>
                                    </label>
                                </template>
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
