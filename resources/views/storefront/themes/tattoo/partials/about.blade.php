@php
    $galleryImages = [
        ['file' => 'Rectangle 31.png', 'alt' => 'Salon interior 1'],
        ['file' => 'Rectangle 27.png', 'alt' => 'Salon service 2'],
        ['file' => 'Rectangle 28.png', 'alt' => 'Hair styling 3'],
        ['file' => 'Rectangle 30.png', 'alt' => 'Grooming 4'],
        ['file' => 'Rectangle 32.png', 'alt' => 'Salon ambience 5'],
        ['file' => 'Rectangle 29.png', 'alt' => 'Professional care 6'],
    ];
@endphp
@if($data['salon'] ?? null)
<section
    id="about"
    class="w-full bg-black relative overflow-hidden"
>
    <div class="relative w-full bg-black py-20 overflow-hidden">
        <div class="max-w-[1360px] mx-auto px-4 relative z-20">
            <div class="w-full lg:w-[55%] relative z-20 mx-auto text-center lg:text-left">
                @include('storefront.partials.dynamic.about-copy', [
                    'eyebrowClass' => 'text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest mb-3 block',
                    'headingClass' => 'font-manrope font-extrabold text-4xl md:text-5xl lg:text-[60px] lg:leading-[69px] text-white mb-6 tracking-tight',
                    'highlightClass' => 'text-[#9a031e] font-pacifico font-normal lowercase tracking-normal',
                    'bodyClass' => 'text-gray-400 font-inter font-light text-base md:text-lg leading-relaxed mb-12 max-w-[777px] mx-auto lg:mx-0',
                    'statValueClass' => 'font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-[#9a031e] tracking-tight',
                    'statLabelClass' => 'font-manrope font-semibold text-xs md:text-sm text-gray-300 uppercase tracking-wider',
                    'dividerClass' => 'w-px h-[90px] md:h-[110px] bg-white/10',
                ])
            </div>
        </div>

        <div class="absolute -left-0 top-0 w-[380px] h-[680px] pointer-events-none hidden lg:block opacity-60 transition-opacity">
            <img
                src="{{ $asset('tatto-removebg-preview 1.png') }}"
                alt=""
                class="w-full h-full object-contain"
                aria-hidden="true"
            />
        </div>

        <div class="absolute -right-0 top-0 w-[380px] h-[680px] pointer-events-none hidden lg:block opacity-60 transition-opacity">
            <img
                src="{{ $asset('tatto-removebg-preview 2.png') }}"
                alt=""
                class="w-full h-full object-contain"
                aria-hidden="true"
            />
        </div>
    </div>

    <div class="w-full overflow-hidden relative z-10 py-10 bg-black">
        <div class="max-w-[1360px] mx-auto px-4">
            <div class="flex justify-start md:justify-center items-center gap-5 overflow-x-auto scrollbar-none snap-x snap-mandatory pb-4">
                @foreach($galleryImages as $img)
                <div class="flex-shrink-0 w-[280px] sm:w-[300px] lg:w-[327px] h-[220px] md:h-[280px] lg:h-[345px] overflow-hidden rounded-2xl shadow-lg border border-white/5 hover:border-[#9a031e]/30 hover:shadow-[0_0_20px_rgba(154,3,30,0.3)] transition-all duration-500 snap-center group cursor-pointer">
                    <img
                        src="{{ ($data['about_gallery'][$loop->index] ?? null) ?: $asset($img['file']) }}"
                        alt="{{ $img['alt'] }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-out"
                    />
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endif
