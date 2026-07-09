@php
    $salonData = $data['salon'] ?? [];
    $packages = $data['packages'] ?? [];
    $packageImages = \App\Support\StorefrontAssets::assets($theme)['packageImages'] ?? ['Rectangle 46.png', 'Rectangle 46 (1).png', 'Rectangle 27 (1).png'];
@endphp
@if($salonData && count($packages) > 0)
<section id="packages" class="w-full bg-white py-20 lg:py-24 overflow-hidden">
    <div class="max-w-[1360px] mx-auto px-4 min-w-0">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Packages</span>
            <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
                Explore Our Packages
            </h2>
        </div>

        @component('storefront.partials.horizontal-drag-scroll', ['ariaLabel' => 'Service packages', 'gapClass' => 'gap-6 md:gap-8'])
            @foreach($packages as $i => $pkg)
            <article class="shrink-0 snap-start w-[min(88vw,340px)] sm:w-[360px] lg:w-[400px] flex flex-col bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.06)]">
                <div class="w-full h-[200px] md:h-[230px] overflow-hidden bg-section-light shrink-0">
                    <img src="{{ $asset($packageImages[$i % count($packageImages)]) }}" alt="{{ $pkg['name'] }}" class="w-full h-full object-cover" draggable="false">
                </div>
                <div class="p-6 md:p-7 flex flex-col flex-1">
                    <h3 class="font-manrope font-bold text-xl md:text-2xl text-primary mb-5 leading-tight">{{ $pkg['name'] }}</h3>
                    <ul class="flex flex-col flex-1 mb-6">
                        @foreach($pkg['items'] ?? [] as $item)
                        <li class="flex items-center justify-between gap-4 py-3 border-b border-gray-100 last:border-b-0 font-inter text-sm md:text-[15px]">
                            <span class="text-text-secondary font-normal">{{ $item['name'] }}</span>
                            <span class="text-black font-semibold whitespace-nowrap shrink-0">{{ $item['price'] }}</span>
                        </li>
                        @endforeach
                    </ul>
                    <div class="mt-auto pt-2">
                        <div class="flex items-end justify-between gap-3 mb-6">
                            <span class="font-manrope font-bold text-sm md:text-base text-black uppercase tracking-wide">Total</span>
                            <div class="flex items-center gap-2 md:gap-3 flex-wrap justify-end">
                                @if(!empty($pkg['discount_percent']))
                                <span class="text-xs md:text-sm text-text-faded line-through font-inter">{{ $pkg['components_formatted'] ?? '' }}</span>
                                <span class="text-[10px] md:text-[11px] font-bold text-primary bg-[#FFEFEF] px-2 py-1 rounded">{{ $pkg['discount_percent'] }}</span>
                                @endif
                                <span class="font-manrope font-extrabold text-2xl md:text-[28px] text-black leading-none">{{ $pkg['price_formatted'] }}</span>
                            </div>
                        </div>
                        @include('storefront.partials.book-button', [
                            'class' => 'block w-full text-center bg-primary hover:bg-primary-dark text-white font-manrope font-bold text-sm uppercase tracking-wider rounded-full py-3.5 md:py-4 transition-colors duration-300',
                            'label' => 'Book Now',
                        ])
                    </div>
                </div>
            </article>
            @endforeach
        @endcomponent
    </div>
</section>
@endif
