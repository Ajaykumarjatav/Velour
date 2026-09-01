@php
    $salonData = $data['salon'] ?? [];
    $categories = collect($data['service_categories'] ?? [])->filter(fn ($c) => count($c['services'] ?? []) > 0)->values();
    $serviceIcon = \App\Support\StorefrontAssets::assets($theme)['serviceIcon'] ?? 'noun-hair-cut-6384205 1.png';
    $currencySymbol = \App\Helpers\CurrencyHelper::symbol($salon->currency ?? \App\Helpers\CurrencyHelper::defaultCode());
    $homeBookEnabled = (bool) (($data['salon']['online_booking_enabled'] ?? null) ?? ($salon->online_booking_enabled ?? true));
@endphp
@if($salonData)
<section id="services"
         class="sf-home-services"
         :class="{ 'has-sticky-selection': selectedCount > 0 }"
         x-data="servicesSection(@js($categories->toArray()), @js($currencySymbol), @js($homeBookEnabled))">
    <div class="sf-home-services__inner">
        <header class="sf-home-services__header">
            <span class="sf-home-services__eyebrow">Services</span>
            <h2 class="sf-home-services__title">Find the right treatment for you</h2>
            <p class="sf-home-services__subtitle">Choose a category and select the service that suits you best.</p>
        </header>

        <div class="sf-home-services__cats-wrap"
             :class="{ 'has-scroll-left': canScrollCatLeft, 'has-scroll-right': canScrollCatRight }"
             x-show="categories.length > 0">
            <button type="button" class="sf-home-services__cat-nav sf-home-services__cat-nav--prev"
                    :disabled="!canScrollCatLeft" @click="scrollCats(-1)" aria-label="Previous categories">‹</button>
            <div x-ref="catTrack" role="region" aria-label="Service categories"
                 @mousedown="onCatMouseDown($event)" @mouseleave="endCatDrag()" @mouseup="endCatDrag()" @mousemove="onCatMouseMove($event)"
                 @scroll="updateCatScroll()"
                 :class="catGrabbing ? 'is-grabbing' : ''"
                 class="sf-home-services__cats">
                <template x-for="cat in categories" :key="cat.id">
                    <button type="button" @click="selectCategory(cat.id, $event)"
                            :class="isActiveCategory(cat.id) ? 'is-active' : ''"
                            class="sf-home-services__cat-btn"
                            :title="cat.name" x-text="cat.name"></button>
                </template>
            </div>
            <button type="button" class="sf-home-services__cat-nav sf-home-services__cat-nav--next"
                    :disabled="!canScrollCatRight" @click="scrollCats(1)" aria-label="Next categories">›</button>
        </div>

        <div x-show="activeServices.length > 0"
             :class="activeServices.length === 1 ? 'is-single' : 'is-multi'"
             class="sf-home-services__grid">
            <template x-for="service in activeServices" :key="service.id">
                <button type="button"
                        @click="toggleService(service.id)"
                        :class="isPicked(service.id) ? 'is-selected' : ''"
                        class="sf-home-services__card">
                    <div class="sf-home-services__card-top">
                        <span class="sf-home-services__icon">
                            <img src="{{ $asset($serviceIcon) }}" alt="">
                        </span>
                        <div class="sf-home-services__card-body">
                            <div class="sf-home-services__card-head">
                                <span class="sf-home-services__name" x-text="service.name"></span>
                                <span class="sf-home-services__price" x-text="service.price_formatted"></span>
                            </div>
                            <div class="sf-home-services__meta">
                                <span class="sf-home-services__pill" x-text="formatDuration(service.duration_minutes)"></span>
                                <span class="sf-home-services__dot" x-show="activeCategoryName">•</span>
                                <span class="sf-home-services__pill" x-show="activeCategoryName" x-text="activeCategoryName"></span>
                            </div>
                            <p class="sf-home-services__desc"
                               x-show="serviceDescription(service.description)"
                               x-text="serviceDescription(service.description)"></p>
                        </div>
                    </div>
                    <span class="sf-home-services__select">
                        <template x-if="isPicked(service.id)">
                            <span>✓ Selected</span>
                        </template>
                        <template x-if="!isPicked(service.id)">
                            <span>Select service</span>
                        </template>
                    </span>
                </button>
            </template>
        </div>

        <p x-show="categories.length > 0 && activeServices.length === 0" class="sf-home-services__empty">
            No services are listed for this category yet.
        </p>

        <div class="sf-home-services__cta" x-show="categories.length > 0 && selectedCount === 0">
            <p class="sf-home-services__cta-summary" x-show="selectedCount > 0" x-text="selectionSummary"></p>
            @if($homeBookEnabled)
            <button type="button" @click="bookFromHome()" class="sf-home-services__cta-btn">
                <span x-text="selectedCount > 0 ? 'Continue to booking →' : 'Book a service'"></span>
            </button>
            @else
            <span class="sf-home-services__cta-btn" style="opacity:0.55;cursor:not-allowed" title="Online booking is currently offline">Book a service</span>
            @endif
        </div>
    </div>

    <div class="sf-home-services__sticky" x-show="selectedCount > 0 && bookingEnabled" x-cloak>
        <div class="sf-home-services__sticky-inner">
            <div class="sf-home-services__sticky-summary min-w-0">
                <div class="sf-home-services__sticky-text" x-text="stickyCountLabel"></div>
                <div class="sf-home-services__sticky-meta" x-text="stickyDurationLabel"></div>
            </div>
            <div class="sf-home-services__sticky-price" x-text="stickyPriceLabel"></div>
            <button type="button" @click="bookFromHome()" class="sf-home-services__cta-btn sf-home-services__sticky-btn">
                Continue →
            </button>
        </div>
    </div>
</section>
@endif

@once
@push('scripts')
<script>
function servicesSection(categories, currencySymbol, bookingEnabled) {
    return {
        categories,
        currencySymbol,
        bookingEnabled,
        activeId: categories[0]?.id ?? null,
        selectedServiceIds: [],
        catGrabbing: false,
        catDragging: false,
        catDidDrag: false,
        catStartX: 0,
        catScrollStart: 0,
        canScrollCatLeft: false,
        canScrollCatRight: false,
        get activeServices() {
            const cat = this.categories.find(c => Number(c.id) === Number(this.activeId));
            return cat?.services ?? [];
        },
        get activeCategoryName() {
            return this.categories.find(c => Number(c.id) === Number(this.activeId))?.name ?? '';
        },
        get selectedCount() {
            return this.selectedServiceIds.length;
        },
        get selectedServices() {
            const ids = new Set(this.selectedServiceIds.map(Number));
            const out = [];
            for (const cat of this.categories) {
                for (const s of (cat.services || [])) {
                    if (ids.has(Number(s.id))) {
                        out.push({ ...s, categoryName: cat.name });
                    }
                }
            }
            return out;
        },
        get selectionSummary() {
            const n = this.selectedCount;
            if (!n) return '';
            return n + ' service' + (n === 1 ? '' : 's') + ' selected • ' + this.formatPrice(this.selectedTotal);
        },
        get selectedTotal() {
            return this.selectedServices.reduce((sum, s) => sum + this.servicePrice(s), 0);
        },
        get stickyCountLabel() {
            const n = this.selectedCount;
            if (!n) return '';
            return n + ' service' + (n === 1 ? '' : 's') + ' selected';
        },
        get stickyDurationLabel() {
            const mins = this.selectedServices.reduce((sum, s) => sum + Number(s.duration_minutes || 0), 0);
            return this.formatTotalDuration(mins);
        },
        get stickyPriceLabel() {
            return this.formatPrice(this.selectedTotal);
        },
        get stickyTitle() {
            const first = this.selectedServices[0];
            if (!first) return '';
            if (this.selectedCount === 1) return first.name;
            return first.name + ' +' + (this.selectedCount - 1) + ' more';
        },
        get stickyMeta() {
            const services = this.selectedServices;
            if (!services.length) return '';
            const mins = services.reduce((sum, s) => sum + Number(s.duration_minutes || 0), 0);
            return this.formatTotalDuration(mins) + ' • ' + this.formatPrice(this.selectedTotal);
        },
        servicePrice(service) {
            const price = Number(service?.price);
            return Number.isFinite(price) ? price : 0;
        },
        isActiveCategory(id) {
            return Number(this.activeId) === Number(id);
        },
        isPicked(id) {
            return this.selectedServiceIds.includes(Number(id));
        },
        formatDuration(mins) {
            const m = Number(mins) || 0;
            return m + ' min';
        },
        formatTotalDuration(mins) {
            const m = Number(mins) || 0;
            if (m < 60) return m + ' min total';
            const h = Math.floor(m / 60);
            const r = m % 60;
            if (r === 0) return h + ' hr total';
            return h + ' hr ' + r + ' min total';
        },
        formatPrice(amount) {
            const n = Number(amount) || 0;
            const formatted = n.toLocaleString(undefined, {
                minimumFractionDigits: Number.isInteger(n) ? 0 : 2,
                maximumFractionDigits: 2,
            });
            return this.currencySymbol + formatted;
        },
        serviceDescription(text) {
            const t = String(text || '').trim();
            if (t.length < 4) return '';
            if (/^(dfd|test|asdf|xxx|abc|demo|sample|lorem|qwerty|na|n\/a)$/i.test(t)) return '';
            if (/^[a-z]{2,4}$/i.test(t) && t.length <= 4) return '';
            return t;
        },
        selectCategory(id, event) {
            if (this.catDidDrag) {
                event?.preventDefault();
                return;
            }
            this.activeId = id;
        },
        toggleService(id) {
            if (window.storefrontHomeCart) window.storefrontHomeCart.toggleService(id);
            this.selectedServiceIds = window.storefrontHomeCart
                ? window.storefrontHomeCart.snapshot().serviceIds
                : [];
        },
        bookFromHome() {
            if (!this.bookingEnabled) return;
            if (window.storefrontHomeCart) window.storefrontHomeCart.bookSelected();
            else window.location.hash = 'book';
        },
        scrollCats(direction) {
            const el = this.$refs.catTrack;
            if (!el) return;
            const step = Math.max(200, el.clientWidth - 80);
            el.scrollBy({ left: direction * step, behavior: 'smooth' });
            setTimeout(() => this.updateCatScroll(), 350);
        },
        updateCatScroll() {
            const el = this.$refs.catTrack;
            if (!el) {
                this.canScrollCatLeft = false;
                this.canScrollCatRight = false;
                return;
            }
            const max = el.scrollWidth - el.clientWidth;
            this.canScrollCatLeft = el.scrollLeft > 4;
            this.canScrollCatRight = max > 4 && el.scrollLeft < max - 4;
        },
        init() {
            if (this.categories.length && (this.activeId === null || this.activeId === undefined)) {
                this.activeId = this.categories[0].id;
            }
            this._onCart = (e) => { this.selectedServiceIds = e.detail?.serviceIds ?? []; };
            window.addEventListener('storefront-home-cart-change', this._onCart);
            this.$nextTick(() => {
                const el = this.$refs.catTrack;
                if (!el) return;
                this.updateCatScroll();
                this._onCatWheel = (e) => this.onCatWheel(e);
                this._onCatResize = () => this.updateCatScroll();
                el.addEventListener('wheel', this._onCatWheel, { passive: false });
                window.addEventListener('resize', this._onCatResize);
            });
        },
        destroy() {
            if (this._onCart) window.removeEventListener('storefront-home-cart-change', this._onCart);
            if (this.$refs.catTrack && this._onCatWheel) {
                this.$refs.catTrack.removeEventListener('wheel', this._onCatWheel);
            }
            if (this._onCatResize) window.removeEventListener('resize', this._onCatResize);
        },
        onCatMouseDown(e) {
            if (e.button !== 0 || !this.$refs.catTrack) return;
            if (e.target.closest('button')) return;
            this.catDragging = true;
            this.catDidDrag = false;
            this.catStartX = e.pageX;
            this.catScrollStart = this.$refs.catTrack.scrollLeft;
            this.catGrabbing = true;
            document.addEventListener('mouseup', this._stopCat = () => this.endCatDrag());
        },
        onCatMouseMove(e) {
            if (!this.catDragging || !this.$refs.catTrack) return;
            const delta = e.pageX - this.catStartX;
            if (Math.abs(delta) > 4) this.catDidDrag = true;
            this.$refs.catTrack.scrollLeft = this.catScrollStart - delta;
            this.updateCatScroll();
        },
        onCatWheel(e) {
            const el = this.$refs.catTrack;
            if (!el) return;
            if (el.scrollWidth <= el.clientWidth + 1) return;
            if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
            const atStart = el.scrollLeft <= 0;
            const atEnd = el.scrollLeft + el.clientWidth >= el.scrollWidth - 1;
            if ((e.deltaY < 0 && atStart) || (e.deltaY > 0 && atEnd)) return;
            e.preventDefault();
            el.scrollLeft += e.deltaY;
            this.updateCatScroll();
        },
        endCatDrag() {
            this.catDragging = false;
            this.catGrabbing = false;
            if (this._stopCat) document.removeEventListener('mouseup', this._stopCat);
            this.updateCatScroll();
        },
    };
}
</script>
@endpush
@endonce
