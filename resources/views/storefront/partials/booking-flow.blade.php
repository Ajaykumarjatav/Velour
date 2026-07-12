@php
    $slug = $salon->slug;
    $currency = $data['salon']['currency_symbol'] ?? '£';
    $salonName = $data['salon']['name'] ?? $salon->name;
@endphp
<div x-data="storefrontBooking(@js([
    'slug' => $slug,
    'apiBase' => $apiBase,
    'currency' => $currency,
    'salonName' => $salonName,
]))"
     x-init="init()"
     @storefront-booking-toggle.window="open = $event.detail.open"
     x-show="open"
     x-cloak
     class="storefront-booking-overlay">
    <template x-if="step === 5 && bookingRef">
        <div class="min-h-screen">
            <header class="border-b border-white/10 px-4 py-4 max-w-2xl mx-auto flex items-center justify-between">
                <span class="font-manrope font-bold text-lg" x-text="salonName"></span>
                <button type="button" @click="close()" class="text-sm text-white/60 hover:text-white">← Back to site</button>
            </header>
            <main class="max-w-lg mx-auto px-4 py-12 text-center">
                <div class="w-20 h-20 rounded-full bg-primary flex items-center justify-center mx-auto mb-6 text-3xl shadow-lg shadow-primary/30">✓</div>
                <h1 class="text-2xl font-bold mb-2" x-text="bookingStatus === 'pending' ? 'Request received!' : 'You\'re all booked!'"></h1>
                <p class="text-white/70 text-sm mb-4" x-html="bookingStatus === 'pending' ? 'We\'ve received your booking request. You\'ll get a confirmation at <strong class=\'text-white\'>' + client.email + '</strong> once the salon approves it.' : 'Confirmation sent to <strong class=\'text-white\'>' + client.email + '</strong>'"></p>
                <p x-show="bookingRef" class="inline-block bg-white/10 rounded-full px-4 py-1 text-xs font-mono mb-8" x-text="'Ref: ' + bookingRef"></p>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-left text-sm space-y-3 mb-8">
                    <div class="flex justify-between gap-4"><span class="text-white/50">Services</span><span class="font-semibold text-right" x-text="selected.services.map(s => s.name).join(', ')"></span></div>
                    <div class="flex justify-between"><span class="text-white/50">Date</span><span class="font-semibold" x-text="confirmDisplay?.date_long || formatDate(selected.date)"></span></div>
                    <div class="flex justify-between"><span class="text-white/50">Time</span><span class="font-semibold" x-text="confirmDisplay?.time || selected.slot?.time"></span></div>
                    <div class="flex justify-between"><span class="text-white/50">With</span><span class="font-semibold" x-text="staffDisplayName()"></span></div>
                </div>
                <button type="button" @click="resetBooking()" class="bg-primary hover:bg-primary-dark text-white font-semibold rounded-full px-8 py-3">Book another appointment</button>
            </main>
        </div>
    </template>

    <template x-if="!(step === 5 && bookingRef)">
        <div class="min-h-screen">
            <header class="sticky top-0 z-50 bg-black/95 backdrop-blur border-b border-white/10 px-4 py-4">
                <div class="max-w-2xl mx-auto flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs text-white/50 uppercase tracking-wider">Book online</p>
                        <h1 class="font-manrope font-bold text-lg" x-text="salonName"></h1>
                    </div>
                    <button type="button" @click="close()" class="text-sm text-white/60 hover:text-white shrink-0">← Back</button>
                </div>
                <div class="max-w-2xl mx-auto mt-4 flex gap-1 overflow-x-auto pb-1">
                    <template x-for="(label, i) in steps" :key="label">
                        <span :class="i === step ? 'bg-primary text-white' : (i < step ? 'bg-white/20 text-white/80' : 'bg-white/5 text-white/40')"
                              class="text-[10px] uppercase tracking-wide px-2 py-1 rounded-full whitespace-nowrap" x-text="label"></span>
                    </template>
                </div>
            </header>

            <main class="max-w-2xl mx-auto px-4 py-8 pb-16">
                <p x-show="globalError" class="bg-red-500/20 border border-red-500/40 text-red-200 rounded-xl p-4 mb-6 text-sm" x-text="globalError"></p>

                <div x-show="loading && step === 0" class="text-white/60 text-center py-12">Loading services…</div>

                {{-- Step 0: Services --}}
                <div x-show="step === 0 && !loading" class="space-y-6">
                    <div class="rounded-2xl border border-white/10 bg-[#1a1f2e] p-5 sm:p-6">
                        <h2 class="font-manrope font-bold text-base text-white mb-4">Select Services</h2>
                        <template x-if="bookCategories.length === 0 && bookPackages.length === 0">
                            <p class="text-white/50 text-sm py-8 text-center">No services available for booking.</p>
                        </template>
                        <template x-if="bookCategories.length > 0 || bookPackages.length > 0">
                            <div class="overflow-y-auto scrollbar-none -mx-1 px-1 space-y-6" style="max-height:min(60vh,28rem)">
                                <template x-if="bookPackages.length > 0">
                                    <div>
                                        <div class="flex items-center justify-between gap-3 mb-3 pb-3 border-b border-white/10">
                                            <div class="min-w-0">
                                                <h3 class="font-manrope font-semibold text-base text-white leading-snug">Packages</h3>
                                                <p class="text-xs text-white/50 mt-0.5">Bundle deals — select a package or pick individual services below</p>
                                            </div>
                                            <span class="shrink-0 inline-flex items-center rounded-full bg-white/10 border border-white/10 px-2.5 py-0.5 text-[11px] font-medium text-white/70 tabular-nums"
                                                  x-text="bookPackages.length + ' ' + (bookPackages.length === 1 ? 'package' : 'packages')"></span>
                                        </div>
                                        <div class="divide-y divide-white/10">
                                            <template x-for="pkg in bookPackages" :key="'pkg-' + pkg.id">
                                                <label :class="isPackageSelected(pkg) ? 'bg-white/5' : 'hover:bg-white/[0.03]'"
                                                       class="flex items-center gap-3 py-3.5 cursor-pointer group transition-colors rounded-lg px-1 -mx-1">
                                                    <input type="checkbox" :checked="isPackageSelected(pkg)" @change="togglePackage(pkg)" class="h-4 w-4 shrink-0 rounded border-white/30 bg-transparent text-teal-500 focus:ring-teal-500/40 focus:ring-offset-0 accent-teal-500">
                                                    <span class="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-amber-500/90 to-orange-700/90 flex items-center justify-center text-white shadow-sm text-lg">📦</span>
                                                    <span class="flex-1 min-w-0">
                                                        <span class="block font-semibold text-sm text-white leading-snug" x-text="pkg.name"></span>
                                                        <span class="mt-0.5 block text-xs text-white/50" x-text="packageServiceNames(pkg)"></span>
                                                        <span class="mt-0.5 flex items-center gap-1.5 text-xs text-white/50" x-text="pkg.duration_minutes + ' min'"></span>
                                                    </span>
                                                    <span class="shrink-0 text-sm font-semibold text-white tabular-nums" x-text="formatPrice(pkg.price)"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template x-for="cat in bookCategories" :key="cat.id">
                                    <div>
                                        <div class="flex items-center justify-between gap-3 mb-3 pb-3 border-b border-white/10">
                                            <div class="min-w-0">
                                                <h3 class="font-manrope font-semibold text-base text-white leading-snug" x-text="cat.name"></h3>
                                                <p x-show="cat.business_type" class="text-xs text-white/50 mt-0.5" x-text="cat.business_type"></p>
                                            </div>
                                            <span class="shrink-0 inline-flex items-center rounded-full bg-white/10 border border-white/10 px-2.5 py-0.5 text-[11px] font-medium text-white/70 tabular-nums"
                                                  x-text="(cat.services?.length ?? 0) + ' ' + ((cat.services?.length ?? 0) === 1 ? 'service' : 'services')"></span>
                                        </div>
                                        <div class="divide-y divide-white/10">
                                            <template x-for="svc in cat.services" :key="svc.id">
                                                <label :class="isSelected(svc) ? 'bg-white/5' : 'hover:bg-white/[0.03]'"
                                                       class="flex items-center gap-3 py-3.5 cursor-pointer group transition-colors rounded-lg px-1 -mx-1">
                                                    <input type="checkbox" :checked="isSelected(svc)" @change="toggleService(svc)" class="h-4 w-4 shrink-0 rounded border-white/30 bg-transparent text-teal-500 focus:ring-teal-500/40 focus:ring-offset-0 accent-teal-500">
                                                    <span class="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-violet-500/90 to-purple-800/90 flex items-center justify-center text-white shadow-sm">✂</span>
                                                    <span class="flex-1 min-w-0">
                                                        <span class="block font-semibold text-sm text-white leading-snug" x-text="svc.name"></span>
                                                        <span class="mt-0.5 flex items-center gap-1.5 text-xs text-white/50" x-text="svc.duration_minutes + ' min'"></span>
                                                    </span>
                                                    <span class="shrink-0 text-sm font-semibold text-white tabular-nums" x-text="formatPrice(svc.price)"></span>
                                                </label>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                    <div x-show="selected.services.length > 0" class="sticky bottom-4 bg-black/90 backdrop-blur border border-white/10 rounded-2xl p-4 flex items-center justify-between gap-4">
                        <span class="text-sm" x-text="selected.services.length + ' selected · ' + currency + totalPrice().toFixed(2)"></span>
                        <button type="button" @click="step = 1" class="bg-primary hover:bg-primary-dark text-white font-semibold rounded-full px-6 py-2.5 text-sm">Continue</button>
                    </div>
                </div>

                {{-- Step 1: Date & time --}}
                <div x-show="step === 1" class="space-y-4">
                    <div class="text-center sm:text-left">
                        <h2 class="font-manrope font-bold text-base sm:text-lg">When would you like to visit?</h2>
                        <p class="text-xs sm:text-sm text-white/50 mt-0.5">Pick a date, then choose a time.</p>
                    </div>
                    <input type="date" :min="today" :max="maxDate" x-model="selected.date" @change="loadSlots()"
                           class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white">
                    <template x-if="selected.date">
                        <div class="rounded-xl border border-white/10 bg-[#1a1f2e]/80 p-3 sm:p-4">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <h3 class="font-manrope font-semibold text-sm text-white">Available times</h3>
                                <button type="button" @click="loadSlots()" :disabled="slotsLoading" class="text-[11px] font-semibold text-primary hover:text-primary-dark disabled:opacity-40" x-text="slotsLoading ? 'Loading…' : 'Refresh'"></button>
                            </div>
                            <p x-show="combinedInfo" class="text-[11px] text-white/50 mb-2.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/5" x-text="combinedInfo?.message || combinedInfo?.label"></p>
                            <p x-show="slotsError" class="text-red-300 text-xs sm:text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-2.5 mb-3" x-text="slotsError"></p>
                            <div x-show="slotsLoading" class="flex items-center justify-center gap-2 py-6 text-white/50 text-xs sm:text-sm">
                                <span class="inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-primary rounded-full animate-spin"></span>
                                Finding open slots…
                            </div>
                            <p x-show="!slotsLoading && !slotsError && slots.length === 0" class="text-white/50 text-xs sm:text-sm text-center py-5">No slots this day. Try another date.</p>
                            <template x-for="[period, periodSlots] in groupedSlots()" :key="period">
                                <div class="space-y-3 sm:space-y-4 mb-3">
                                    <p class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-1.5" x-text="period"></p>
                                    <div class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-1.5 sm:gap-2">
                                        <template x-for="slot in periodSlots" :key="slot.time">
                                            <button type="button" :disabled="!slot.available" @click="selectSlot(slot)"
                                                    :class="slot.available ? 'border-white/15 bg-white/5 text-white hover:border-primary hover:bg-primary/15 active:scale-[0.98]' : 'border-white/5 text-white/25 cursor-not-allowed'"
                                                    class="rounded-lg py-2 sm:py-2.5 text-xs sm:text-sm font-semibold border transition-colors duration-150" x-text="slot.time"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                    <button type="button" @click="step = 0" class="text-sm text-white/50 hover:text-white">← Back</button>
                </div>

                {{-- Step 2: Stylist --}}
                <div x-show="step === 2">
                    <p class="text-white/70 text-sm mb-4">Choose your stylist (or any available)</p>
                    <p x-show="selected.slot" class="text-xs text-white/50 mb-4" x-text="'Available for ' + formatDate(selected.date) + ' at ' + selected.slot.time"></p>
                    <div class="space-y-2">
                        <button type="button" @click="selected.staff = null; step = 3" class="w-full rounded-xl border-2 border-white/10 bg-white/5 p-4 text-left hover:border-primary">
                            <span class="font-semibold">Any available stylist</span>
                        </button>
                        <template x-for="member in availableStaff()" :key="member.id">
                            <button type="button" @click="selected.staff = member; step = 3"
                                    class="w-full rounded-xl border-2 border-white/10 bg-white/5 p-4 text-left hover:border-primary flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full bg-primary/30 flex items-center justify-center text-sm font-bold"
                                      x-text="((member.first_name || '')[0] || '') + ((member.last_name || '')[0] || '')"></span>
                                <span class="font-semibold" x-text="member.first_name + ' ' + member.last_name"></span>
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="step = 1" class="mt-6 text-sm text-white/50 hover:text-white">← Back</button>
                </div>

                {{-- Step 3: Details --}}
                <div x-show="step === 3" class="space-y-4">
                    <h2 class="font-bold text-lg">Your details</h2>
                    <p x-show="detailsError" class="text-red-300 text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-3" x-text="detailsError"></p>
                    <div class="grid grid-cols-2 gap-3">
                        <input placeholder="First name" x-model="client.first_name" class="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40">
                        <input placeholder="Last name" x-model="client.last_name" class="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40">
                    </div>
                    <input type="email" placeholder="Email" x-model="client.email" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40">
                    <input type="tel" placeholder="Phone" x-model="client.phone" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40">
                    <textarea placeholder="Notes (optional)" x-model="client.notes" rows="3" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"></textarea>
                    <label class="flex items-center gap-2 text-sm text-white/70">
                        <input type="checkbox" x-model="client.marketing_consent" class="rounded accent-primary">
                        Keep me updated with offers and news
                    </label>
                    <button type="button" @click="goToConfirm()" class="w-full bg-primary hover:bg-primary-dark text-white font-semibold rounded-full py-4">Review booking</button>
                    <button type="button" @click="step = 2" class="text-sm text-white/50 hover:text-white">← Back</button>
                </div>

                {{-- Step 4: Confirm --}}
                <div x-show="step === 4">
                    <h2 class="font-bold text-lg mb-4">Confirm your booking</h2>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm space-y-3 mb-6">
                        <div class="flex justify-between gap-4"><span class="text-white/50">Services</span><span class="font-semibold text-right" x-text="selected.services.map(s => s.name).join(', ')"></span></div>
                        <div class="flex justify-between"><span class="text-white/50">Total</span><span class="font-bold text-primary" x-text="currency + totalPrice().toFixed(2)"></span></div>
                        <div class="flex justify-between"><span class="text-white/50">When</span><span class="font-semibold" x-text="formatDate(selected.date) + ' at ' + (selected.slot?.time || '')"></span></div>
                        <div class="flex justify-between"><span class="text-white/50">With</span><span class="font-semibold" x-text="staffDisplayName()"></span></div>
                    </div>
                    <p x-show="bookingError" class="text-red-300 text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-3 mb-4" x-text="bookingError"></p>
                    <button type="button" :disabled="confirming" @click="handleConfirm()" class="w-full bg-primary hover:bg-primary-dark disabled:opacity-60 text-white font-semibold rounded-full py-4" x-text="confirming ? 'Confirming…' : 'Confirm booking'"></button>
                    <button type="button" @click="step = 3" class="mt-4 text-sm text-white/50 hover:text-white w-full text-center">← Edit details</button>
                </div>
            </main>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
function storefrontBooking(config) {
    return {
        ...config,
        open: window.location.hash === '#book',
        steps: ['Services', 'Date & time', 'Stylist', 'Your details', 'Confirm'],
        step: 0,
        loading: true,
        globalError: '',
        allServices: [],
        bookPackages: [],
        slotsLoading: false,
        slots: [],
        combinedInfo: null,
        slotsError: '',
        selected: { services: [], staff: null, date: '', slot: null },
        client: { first_name: '', last_name: '', email: '', phone: '', notes: '', marketing_consent: false },
        detailsError: '',
        bookingError: '',
        confirming: false,
        bookingRef: '',
        bookingStatus: '',
        confirmedStaff: null,
        confirmDisplay: null,
        get today() { return new Date().toISOString().slice(0, 10); },
        get maxDate() { const d = new Date(); d.setDate(d.getDate() + 60); return d.toISOString().slice(0, 10); },
        get bookCategories() { return this.allServices.filter(c => (c.services?.length ?? 0) > 0); },
        get flatServices() { return this.bookCategories.flatMap(c => c.services ?? []); },
        init() {
            this.syncBodyBookingState();
            this.$watch('open', () => this.syncBodyBookingState());
            this.fetchServices();
        },
        syncBodyBookingState() {
            document.body.classList.toggle('storefront-booking-active', this.open);
        },
        close() {
            if (history.length > 1) history.back();
            else { window.location.hash = ''; this.open = false; }
            this.syncBodyBookingState();
        },
        api(path, opts = {}) {
            const url = this.apiBase.replace(/\/$/, '') + '/api/v1/book/' + this.slug + path;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                ...(opts.headers ?? {}),
            };
            return fetch(url, { credentials: 'same-origin', headers, ...opts })
                .then(async r => { const d = await r.json().catch(() => ({})); if (!r.ok) throw new Error(d.message || 'Request failed'); return d; });
        },
        parseServicesResponse(data) {
            const raw = data?.services ?? data?.data?.services ?? {};
            if (Array.isArray(raw)) {
                return raw.filter(c => (c.services?.length ?? 0) > 0);
            }
            const cats = [];
            for (const [categoryId, svcs] of Object.entries(raw)) {
                if (!Array.isArray(svcs) || svcs.length === 0) continue;
                const parsedId = categoryId === '' || categoryId === 'null' ? 0 : Number(categoryId);
                const first = svcs[0];
                cats.push({
                    id: Number.isFinite(parsedId) ? parsedId : 0,
                    name: first?.category?.name ?? 'Services',
                    business_type: first?.category?.business_type?.name ?? null,
                    sort_order: first?.category?.sort_order ?? 999,
                    services: svcs,
                });
            }
            return cats.sort((a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name));
        },
        fetchServices() {
            this.loading = true;
            this.api('/services').then(d => {
                this.allServices = this.parseServicesResponse(d);
                this.bookPackages = Array.isArray(d.packages) ? d.packages : [];
            }).catch(() => { this.globalError = 'Failed to load services. Please refresh the page.'; })
              .finally(() => { this.loading = false; });
        },
        packageServiceIds(pkg) { return pkg.service_ids ?? (pkg.services ?? []).map(s => s.id); },
        packageServiceNames(pkg) { return (pkg.services ?? []).map(s => s.name).join(' · '); },
        isPackageSelected(pkg) {
            const ids = this.packageServiceIds(pkg);
            return ids.length > 0 && ids.every(id => this.selected.services.some(s => s.id === id));
        },
        togglePackage(pkg) {
            const ids = new Set(this.packageServiceIds(pkg));
            const svcs = this.flatServices.filter(s => ids.has(s.id));
            if (svcs.length === 0) return;
            if (this.isPackageSelected(pkg)) {
                this.selected.services = this.selected.services.filter(s => !ids.has(s.id));
            } else {
                const existing = new Set(this.selected.services.map(s => s.id));
                const merged = [...this.selected.services];
                svcs.forEach(s => { if (!existing.has(s.id)) merged.push(s); });
                this.selected.services = merged;
            }
            this.clearSlotSelection();
        },
        clearSlotSelection() {
            this.selected.staff = null; this.selected.date = ''; this.selected.slot = null;
            this.slots = []; this.combinedInfo = null;
        },
        isSelected(svc) { return this.selected.services.some(s => s.id === svc.id); },
        toggleService(svc) {
            const idx = this.selected.services.findIndex(s => s.id === svc.id);
            if (idx >= 0) {
                this.selected.services = this.selected.services.filter(s => s.id !== svc.id);
            } else {
                this.selected.services = [...this.selected.services, svc];
            }
            this.clearSlotSelection();
        },
        totalPrice() {
            let total = 0;
            const packageServiceIds = new Set();
            for (const pkg of this.bookPackages) {
                if (!this.isPackageSelected(pkg)) continue;
                total += parseFloat(pkg.price || 0);
                this.packageServiceIds(pkg).forEach(id => packageServiceIds.add(id));
            }
            for (const s of this.selected.services) {
                if (!packageServiceIds.has(s.id)) total += parseFloat(s.price || 0);
            }
            return total;
        },
        formatPrice(v) {
            const n = parseFloat(v || 0);
            if (this.currency === '₹') return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 2 });
            return this.currency + n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            try { return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
            catch { return dateStr; }
        },
        loadSlots() {
            if (!this.selected.date || !this.selected.services.length) return;
            let date = this.selected.date;
            if (date < this.today) date = this.today;
            this.slotsLoading = true; this.slots = []; this.combinedInfo = null; this.slotsError = '';
            const params = new URLSearchParams({ date });
            this.selected.services.forEach(s => params.append('service_ids[]', s.id));
            if (this.selected.staff?.id) params.append('staff_id', this.selected.staff.id);
            this.api('/availability?' + params).then(d => {
                this.slots = d.slots ?? d.data?.slots ?? [];
                this.combinedInfo = d.combined ?? d.data?.combined ?? null;
                if (date !== this.selected.date) this.selected.date = date;
            }).catch(e => { this.slots = []; this.slotsError = e.message || 'Could not load available times.'; })
              .finally(() => { this.slotsLoading = false; });
        },
        slotPeriod(time) {
            const hour = parseInt((time || '0').split(':')[0], 10);
            if (hour < 12) return 'Morning';
            if (hour < 17) return 'Afternoon';
            return 'Evening';
        },
        groupedSlots() {
            const groups = { Morning: [], Afternoon: [], Evening: [] };
            this.slots.forEach(s => groups[this.slotPeriod(s.time)].push(s));
            return Object.entries(groups).filter(([, list]) => list.length > 0);
        },
        selectSlot(slot) {
            this.selected.slot = slot;
            if (this.selected.staff && !slot.available_staff?.some(s => s.id === this.selected.staff.id)) {
                this.selected.staff = null;
            }
            this.step = 2;
        },
        availableStaff() {
            const list = this.selected.slot?.available_staff ?? [];
            return [...list].sort((a, b) => (`${a.first_name} ${a.last_name}`).localeCompare(`${b.first_name} ${b.last_name}`));
        },
        staffDisplayName() {
            if (this.confirmedStaff) return `${this.confirmedStaff.first_name || ''} ${this.confirmedStaff.last_name || ''}`.trim();
            if (this.selected.staff) return `${this.selected.staff.first_name || ''} ${this.selected.staff.last_name || ''}`.trim();
            const fb = this.selected.slot?.available_staff?.[0];
            if (fb) return `${fb.first_name || ''} ${fb.last_name || ''}`.trim();
            return 'Any available';
        },
        resolveHoldStaffId() {
            if (this.selected.staff?.id != null) return this.selected.staff.id;
            const first = this.selected.slot?.available_staff?.[0];
            return first?.id ?? null;
        },
        goToConfirm() {
            this.detailsError = '';
            if (!this.client.first_name || !this.client.last_name) { this.detailsError = 'Please enter your full name.'; return; }
            if (!this.client.email) { this.detailsError = 'Please enter your email address.'; return; }
            if (!this.client.phone) { this.detailsError = 'Please enter your phone number.'; return; }
            this.step = 4;
        },
        handleConfirm() {
            this.confirming = true; this.bookingError = '';
            this.api('/hold', { method: 'POST', body: JSON.stringify({
                service_ids: this.selected.services.map(s => s.id),
                staff_id: this.resolveHoldStaffId(),
                starts_at: `${this.selected.date} ${this.selected.slot.time}:00`,
            }) }).then(hold => this.api('/confirm', { method: 'POST', body: JSON.stringify({
                hold_token: hold.hold_token ?? hold.data?.hold_token,
                first_name: this.client.first_name,
                last_name: this.client.last_name,
                email: this.client.email,
                phone: this.client.phone,
                notes: this.client.notes,
                marketing_consent: this.client.marketing_consent,
            }) })).then(confirm => {
                this.bookingRef = confirm.reference ?? confirm.appointment?.reference ?? '';
                this.bookingStatus = confirm.status ?? confirm.appointment?.status ?? 'pending';
                this.confirmedStaff = confirm.appointment?.staff ?? null;
                this.confirmDisplay = confirm.display ?? null;
                this.step = 5;
            }).catch(e => { this.bookingError = e.message || 'Something went wrong. Please try again.'; })
              .finally(() => { this.confirming = false; });
        },
        resetBooking() {
            this.step = 0;
            this.selected = { services: [], staff: null, date: '', slot: null };
            this.client = { first_name: '', last_name: '', email: '', phone: '', notes: '', marketing_consent: false };
            this.bookingRef = ''; this.bookingStatus = ''; this.confirmDisplay = null;
        },
    };
}
</script>
@endpush
@endonce
