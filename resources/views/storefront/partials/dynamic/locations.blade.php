@php
    $salonData = $data['salon'] ?? [];
    $locations = $data['locations'] ?? [];
    if (empty($locations) && !empty($salonData['full_address'])) {
        $locations = [[
            'id' => $salonData['id'] ?? 0,
            'name' => $salonData['name'] ?? '',
            'address' => $salonData['full_address'],
            'is_current' => true,
            'map_embed_url' => null,
            'opening_hours_lines' => $salonData['opening_hours_lines'] ?? [],
            'photos' => [],
        ]];
    }
    $gallery = \App\Support\StorefrontAssets::assets($theme)['locationGallery'] ?? ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'];
@endphp
@if($salonData && count($locations) > 0)
<section id="locations" class="w-full bg-white py-20 lg:py-24"
         x-data="locationsSection(@js($locations), @js($salonData), @js(array_map(fn ($f) => $asset($f), $gallery)))">
    <div class="max-w-[1360px] mx-auto px-4">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Locations</span>
            <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
                Locate Your Nearest Store
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-[30px] lg:items-stretch">
            <div class="lg:col-span-3 flex flex-col gap-4 md:gap-5">
                <template x-for="loc in locations" :key="loc.id">
                    <button type="button" @click="activeId = loc.id"
                            :class="activeId === loc.id ? 'bg-deep-maroon text-white shadow-lg shadow-deep-maroon/25 scale-[1.01]' : 'bg-white text-black border border-gray-100 shadow-sm hover:shadow-md hover:border-primary/20'"
                            class="w-full text-left rounded-2xl p-6 md:p-7 transition-all duration-300 outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <h3 class="font-manrope font-bold text-lg md:text-xl mb-3" :class="activeId === loc.id ? 'text-white' : 'text-black'" x-text="loc.name"></h3>
                        <div class="flex items-start gap-2.5 font-inter text-sm leading-relaxed" :class="activeId === loc.id ? 'text-white/85' : 'text-text-muted'">
                            <span :class="activeId === loc.id ? 'text-white' : 'text-primary'">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="currentColor" class="shrink-0 mt-0.5" aria-hidden><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                            </span>
                            <span x-text="loc.address || 'Address coming soon'"></span>
                        </div>
                    </button>
                </template>
            </div>

            <div class="lg:col-span-5 flex flex-col gap-5 lg:min-h-[548px]">
                <div class="rounded-2xl overflow-hidden border border-gray-100 shadow-sm bg-section-light shrink-0">
                    <template x-if="mapSrc">
                        <iframe :title="'Map — ' + (activeLocation?.name || '')" :src="mapSrc" class="w-full h-[200px] md:h-[240px] lg:h-[286px] border-0" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                    </template>
                    <template x-if="!mapSrc">
                        <div class="w-full h-[200px] md:h-[240px] lg:h-[286px] flex items-center justify-center text-text-muted text-sm px-6 text-center">Map unavailable for this location</div>
                    </template>
                </div>
                <div>
                    <h3 class="font-manrope font-bold text-xl md:text-2xl text-black mb-4">Opening Hours</h3>
                    <div class="flex flex-col gap-3">
                        <template x-for="line in hourLines" :key="line">
                            <div class="inline-flex items-center gap-3 w-fit max-w-full bg-[#FFEFEF] text-black font-inter text-sm md:text-base rounded-full px-5 py-3">
                                <span class="text-primary shrink-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                </span>
                                <span x-text="line"></span>
                            </div>
                        </template>
                    </div>
                </div>
                @include('storefront.partials.book-button', [
                    'class' => 'inline-flex items-center justify-center gap-3 w-full bg-primary hover:bg-primary-dark text-white font-manrope font-bold text-sm md:text-base uppercase tracking-wider rounded-full px-8 py-4 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] shadow-md hover:shadow-primary/25 outline-none focus-visible:ring-2 focus-visible:ring-primary',
                    'label' => 'Book Your Transformation <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
                ])
            </div>

            <div class="lg:col-span-4 flex flex-col gap-5 lg:min-h-[548px]">
                <template x-for="(src, index) in galleryImages" :key="index">
                    <div class="rounded-2xl overflow-hidden shadow-md bg-section-light w-full aspect-[11/5] lg:aspect-auto lg:flex-1 lg:min-h-0">
                        <img :src="src" :alt="(activeLocation?.name || 'Location') + ' interior ' + (index + 1)" class="w-full h-full object-cover object-center transition-transform duration-700 hover:scale-105" loading="lazy">
                    </div>
                </template>
            </div>
        </div>
    </div>
</section>
@endif

@once
@push('scripts')
<script>
function locationsSection(locations, salon, galleryImages) {
    const current = locations.find(l => l.is_current)?.id ?? locations[0]?.id ?? null;
    return {
        locations,
        salon,
        galleryImages,
        activeId: current,
        get activeLocation() {
            return this.locations.find(l => l.id === this.activeId) ?? this.locations[0] ?? null;
        },
        get hourLines() {
            const loc = this.activeLocation;
            if (loc?.opening_hours_lines?.length) return loc.opening_hours_lines;
            if (this.salon.opening_hours_lines?.length) return this.salon.opening_hours_lines;
            return ['Mon – Fri: 9:00 AM – 9:00 PM', 'Sat – Sun: 10:00 AM – 7:00 PM'];
        },
        get mapSrc() {
            const loc = this.activeLocation;
            if (!loc) return null;
            if (loc.map_embed_url) return loc.map_embed_url;
            if (loc.address) return 'https://www.google.com/maps?q=' + encodeURIComponent(loc.address) + '&z=15&output=embed';
            return null;
        },
    };
}
</script>
@endpush
@endonce
