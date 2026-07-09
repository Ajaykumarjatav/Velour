@php
    $salonData = $data['salon'] ?? [];
    $categories = collect($data['service_categories'] ?? [])->filter(fn ($c) => count($c['services'] ?? []) > 0)->values();
    $serviceIcon = \App\Support\StorefrontAssets::assets($theme)['serviceIcon'] ?? 'noun-hair-cut-6384205 1.png';
@endphp
@if($salonData)
<section id="services" class="w-full bg-section-light py-20 lg:py-24 overflow-hidden"
         x-data="servicesSection(@js($categories->toArray()))">
    <div class="max-w-[1360px] mx-auto px-4 min-w-0">
        <div class="mb-8 md:mb-10">
            <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Services</span>
            <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
                What we offer
            </h2>
        </div>

        <template x-if="categories.length > 0">
            <div class="mb-12 min-w-0">
                <div class="w-full min-w-0 -mx-1 px-1" x-data="categorySlider()" x-init="init()">
                    <div x-ref="track" role="region" aria-label="Service categories"
                         @mousedown="onMouseDown($event)" @mouseleave="endDrag()" @mouseup="endDrag()" @mousemove="onMouseMove($event)" @wheel.prevent="onWheel($event)"
                         :class="grabbing ? 'cursor-grabbing snap-none' : 'cursor-grab'"
                         class="flex items-center gap-3 md:gap-4 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 w-full min-w-0 touch-pan-x select-none">
                        <template x-for="cat in categories" :key="cat.id">
                            <button type="button" @click="handleSelect(cat.id)"
                                    :class="activeId === cat.id ? 'bg-primary text-white shadow-md shadow-primary/25' : 'bg-pill-inactive text-black/70 hover:bg-gray-200'"
                                    class="shrink-0 snap-start px-5 md:px-7 py-2.5 rounded-full font-manrope font-semibold text-xs md:text-sm uppercase tracking-wider transition-all duration-300 outline-none focus-visible:ring-2 focus-visible:ring-primary whitespace-nowrap max-w-[min(100%,20rem)] truncate"
                                    :title="cat.name" x-text="cat.name"></button>
                        </template>
                    </div>
                </div>
            </div>
        </template>

        <template x-if="activeServices.length > 0">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 lg:gap-x-8 gap-y-2">
                <template x-for="service in activeServices" :key="service.id">
                    <div class="flex items-center justify-between py-5 px-3 rounded-xl border-b border-border/40 hover:bg-white hover:shadow-md hover:border-transparent transition-all duration-300 gap-4 group/item cursor-default">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-[50px] h-[50px] md:w-[60px] md:h-[60px] flex-shrink-0 flex items-center justify-center bg-gray-50 rounded-lg p-1.5 transition-transform duration-300 group-hover/item:scale-105">
                                <img src="{{ $asset($serviceIcon) }}" alt="" class="w-full h-full object-contain">
                            </div>
                            <div class="flex flex-col gap-1 min-w-0">
                                <span class="font-manrope font-bold text-base md:text-lg text-black transition-colors duration-300 group-hover/item:text-primary truncate" x-text="service.name"></span>
                                <span class="font-inter font-normal text-xs md:text-sm text-text-muted line-clamp-2" x-text="service.description || (service.duration_minutes + ' min')"></span>
                            </div>
                        </div>
                        <span class="font-manrope font-bold text-xl md:text-2xl text-black whitespace-nowrap shrink-0" x-text="service.price_formatted"></span>
                    </div>
                </template>
            </div>
        </template>

        <template x-if="categories.length > 0 && activeServices.length === 0">
            <p class="text-center text-text-muted font-inter text-sm py-8">No services are listed for this salon yet.</p>
        </template>

        <template x-if="categories.length > 0">
            <div class="flex justify-center mt-12">
                <a href="#services" class="inline-flex items-center justify-center gap-2 border-2 border-primary text-primary hover:bg-primary hover:text-white font-manrope font-semibold text-sm md:text-base rounded-full px-10 py-4 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-primary">
                    View All Services
                </a>
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
        get activeServices() {
            const cat = this.categories.find(c => c.id === this.activeId);
            return cat?.services ?? [];
        },
        init() {
            if (this.categories.length && !this.activeId) {
                this.activeId = this.categories[0].id;
            }
        },
    };
}
function categorySlider() {
    return {
        grabbing: false,
        isDragging: false,
        didDrag: false,
        startX: 0,
        scrollStart: 0,
        activeId: null,
        init() {
            this.$watch('$root.activeId', v => { this.activeId = v; });
            this.activeId = this.$root.activeId;
        },
        handleSelect(id) {
            if (this.didDrag) return;
            this.$root.activeId = id;
        },
        onMouseDown(e) {
            if (e.button !== 0 || !this.$refs.track) return;
            this.isDragging = true;
            this.didDrag = false;
            this.startX = e.pageX;
            this.scrollStart = this.$refs.track.scrollLeft;
            this.grabbing = true;
            document.addEventListener('mouseup', this._stop = () => this.endDrag());
        },
        onMouseMove(e) {
            if (!this.isDragging || !this.$refs.track) return;
            const delta = e.pageX - this.startX;
            if (Math.abs(delta) > 4) this.didDrag = true;
            this.$refs.track.scrollLeft = this.scrollStart - delta;
        },
        onWheel(e) {
            const el = this.$refs.track;
            if (!el || el.scrollWidth <= el.clientWidth) return;
            if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return;
            el.scrollLeft += e.deltaY;
        },
        endDrag() {
            this.isDragging = false;
            this.grabbing = false;
            if (this._stop) document.removeEventListener('mouseup', this._stop);
        },
    };
}
</script>
@endpush
@endonce
