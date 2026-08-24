@php
    $img = $packageImages[$i % count($packageImages)];
@endphp
<article
    role="button"
    tabindex="0"
    data-home-package="{{ (int) $pkg['id'] }}"
    onclick="if (window.storefrontHomeCart) window.storefrontHomeCart.togglePackage({{ (int) $pkg['id'] }})"
    onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); if (window.storefrontHomeCart) window.storefrontHomeCart.togglePackage({{ (int) $pkg['id'] }}); }"
    class="{{ $cardClass ?? 'w-full h-full flex flex-col bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.06)] cursor-pointer hover:shadow-[0_8px_28px_rgba(0,0,0,0.1)] transition-shadow duration-300 text-left' }}"
>
    <div class="w-full h-[200px] md:h-[230px] overflow-hidden bg-section-light shrink-0">
        <img src="{{ $asset($img) }}" alt="{{ $pkg['name'] }}" class="w-full h-full object-cover" draggable="false">
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
            <span data-home-package-label class="block w-full text-center bg-[#FFEFEF] text-primary font-manrope font-bold text-sm uppercase tracking-wider rounded-full py-3.5 md:py-4 transition-colors duration-300 pointer-events-none">Select</span>
        </div>
    </div>
</article>
