@php
    $salonData = $data['salon'] ?? [];
    $packages = $data['packages'] ?? [];
    $packageImages = \App\Support\StorefrontAssets::assets($theme)['packageImages'] ?? ['Rectangle 46.png', 'Rectangle 46 (1).png', 'Rectangle 27 (1).png'];
    $pkgCount = count($packages);
@endphp
@if($salonData && $pkgCount > 0)
<section id="packages" class="w-full bg-white py-20 lg:py-24 overflow-hidden">
    <div class="max-w-[1360px] mx-auto px-4 min-w-0">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Packages</span>
            <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
                Explore Our Packages
            </h2>
        </div>

        @component('storefront.partials.horizontal-drag-scroll', [
            'ariaLabel' => 'Service packages',
            'gapClass' => 'gap-6 md:gap-8',
            'trackClass' => $pkgCount === 1 ? 'justify-center' : '',
        ])
            @foreach($packages as $i => $pkg)
            <div class="shrink-0 snap-start w-[min(88vw,340px)] sm:w-[360px] lg:w-[400px] flex">
                @include('storefront.partials.dynamic.package-card', [
                    'pkg' => $pkg,
                    'i' => $i,
                    'packageImages' => $packageImages,
                ])
            </div>
            @endforeach
        @endcomponent

        <div class="flex justify-center mt-10">
            <button type="button" onclick="window.storefrontHomeCart && window.storefrontHomeCart.bookSelected()"
                    class="inline-flex items-center justify-center bg-primary hover:bg-primary-dark text-white font-manrope font-semibold text-sm md:text-base rounded-full px-10 py-4">
                Book selected
            </button>
        </div>
    </div>
</section>
@endif
