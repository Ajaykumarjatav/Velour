@php
    $salonData = $data['salon'] ?? [];
    $categories = collect($data['service_categories'] ?? [])->filter(fn ($c) => count($c['services'] ?? []) > 0)->values();
    $serviceIcon = \App\Support\StorefrontAssets::assets($theme)['serviceIcon'] ?? 'noun-hair-cut-6384205 1.png';
@endphp
@if($salonData)
<section id="services" class="w-full bg-testimonial-bg py-20 lg:py-24 overflow-hidden"
         x-data="servicesSection(@js($categories->toArray()))">
    <div class="max-w-[1360px] mx-auto px-4 min-w-0">
        <div class="mb-8 md:mb-10 max-w-2xl">
            <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Services</span>
            <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
                What we offer
            </h2>
            <p class="mt-3 text-text-muted font-inter text-sm md:text-base">Browse by category and pick the treatment that fits you.</p>
        </div>

        <div class="mb-12 min-w-0" x-show="categories.length > 0">
            <div class="w-full min-w-0 -mx-1 px-1">
                <div x-ref="catTrack" role="region" aria-label="Service categories"
                     @mousedown="onCatMouseDown($event)" @mouseleave="endCatDrag()" @mouseup="endCatDrag()" @mousemove="onCatMouseMove($event)"
                     :class="catGrabbing ? 'cursor-grabbing snap-none' : 'cursor-grab'"
                     class="flex items-center gap-3 md:gap-4 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 w-full min-w-0 touch-pan-x select-none">
                    <template x-for="cat in categories" :key="cat.id">
                        <button type="button" @click="selectCategory(cat.id, $event)"
                                :class="isActiveCategory(cat.id) ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-pill-inactive text-black/70 hover:bg-gray-200'"
                                class="shrink-0 snap-start px-5 md:px-7 py-2.5 rounded-full font-manrope font-semibold text-xs md:text-sm uppercase tracking-wider transition-all duration-300 outline-none whitespace-nowrap"
                                :title="cat.name" x-text="cat.name"></button>
                    </template>
                </div>
            </div>
        </div>

        <div x-show="activeServices.length > 0" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-5">
                <template x-for="service in activeServices" :key="service.id">
                    <button type="button"
                            @click="toggleService(service.id)"
                            :class="isPicked(service.id) ? 'border-primary ring-2 ring-primary/30 bg-primary/[0.04]' : 'border-black/5 hover:border-primary/25'"
                            class="flex items-start justify-between gap-4 p-5 md:p-6 rounded-2xl bg-white border shadow-[0_8px_24px_rgba(0,0,0,0.05)] hover:shadow-[0_12px_32px_rgba(0,0,0,0.09)] hover:-translate-y-0.5 transition-all duration-300 group/item text-left w-full cursor-pointer">
                        <div class="flex items-start gap-4 min-w-0">
                            <span class="mt-1 w-5 h-5 shrink-0 rounded border-2 flex items-center justify-center"
                                  :class="isPicked(service.id) ? 'bg-primary border-primary text-white' : 'border-black/20 bg-white'">
                                <svg x-show="isPicked(service.id)" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="w-12 h-12 md:w-14 md:h-14 flex-shrink-0 flex items-center justify-center bg-primary/10 rounded-xl p-2 transition-transform duration-300 group-hover/item:scale-105">
                                <img src="{{ $asset($serviceIcon) }}" alt="" class="w-full h-full object-contain">
                            </div>
                            <div class="flex flex-col gap-1.5 min-w-0">
                                <span class="font-manrope font-bold text-base md:text-lg text-black group-hover/item:text-primary transition-colors truncate" x-text="service.name"></span>
                                <span class="inline-flex self-start items-center rounded-full bg-section-light px-2.5 py-0.5 text-[11px] font-manrope font-semibold uppercase tracking-wide text-black/55"
                                      x-text="(service.duration_minutes || 0) + ' min'"></span>
                                <span class="font-inter text-xs md:text-sm text-text-muted line-clamp-2" x-show="service.description" x-text="service.description"></span>
                            </div>
                        </div>
                        <span class="font-manrope font-extrabold text-lg md:text-xl text-black whitespace-nowrap shrink-0" x-text="service.price_formatted"></span>
                    </button>
                </template>
        </div>

        <p x-show="categories.length > 0 && activeServices.length === 0" class="text-center text-text-muted font-inter text-sm py-8">No services are listed for this salon yet.</p>

        <template x-if="categories.length > 0">
            <div class="flex justify-center mt-12">
                @php $homeBookEnabled = (bool) (($data['salon']['online_booking_enabled'] ?? null) ?? ($salon->online_booking_enabled ?? true)); @endphp
                @if($homeBookEnabled)
                <button type="button" @click="bookFromHome()"
                        class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-dark text-white font-manrope font-semibold text-sm md:text-base rounded-full px-10 py-4 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    Book a service
                </button>
                @else
                <span class="inline-flex items-center justify-center gap-2 bg-primary text-white font-manrope font-semibold text-sm md:text-base rounded-full px-10 py-4 opacity-60 cursor-not-allowed" title="Online booking is currently offline">Book a service</span>
                @endif
            </div>
        </template>
    </div>
</section>
@endif

@once
@push('scripts')
<script>
function servicesSection(categories) {
    return {
        categories,
        activeId: categories[0]?.id ?? null,
        selectedServiceIds: [],
        catGrabbing: false,
        catDragging: false,
        catDidDrag: false,
        catStartX: 0,
        catScrollStart: 0,
        get activeServices() {
            const cat = this.categories.find(c => Number(c.id) === Number(this.activeId));
            return cat?.services ?? [];
        },
        isActiveCategory(id) {
            return Number(this.activeId) === Number(id);
        },
        isPicked(id) {
            return this.selectedServiceIds.includes(Number(id));
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
            this.selectedServiceIds = window.storefrontHomeCart ? window.storefrontHomeCart.snapshot().serviceIds : [];
        },
        bookFromHome() {
            if (window.storefrontHomeCart) window.storefrontHomeCart.bookSelected();
            else window.location.hash = 'book';
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
                this._onCatWheel = (e) => this.onCatWheel(e);
                el.addEventListener('wheel', this._onCatWheel, { passive: false });
            });
        },
        destroy() {
            if (this._onCart) window.removeEventListener('storefront-home-cart-change', this._onCart);
            if (this.$refs.catTrack && this._onCatWheel) {
                this.$refs.catTrack.removeEventListener('wheel', this._onCatWheel);
            }
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
        },
        endCatDrag() {
            this.catDragging = false;
            this.catGrabbing = false;
            if (this._stopCat) document.removeEventListener('mouseup', this._stopCat);
        },
    };
}
</script>
@endpush
@endonce
