@php
    $salon = $data['salon'] ?? null;
    if (!$salon) return;
    $galleryImageFiles = [
        ['file' => 'Rectangle 31.png', 'alt' => 'Salon interior 1'],
        ['file' => 'Rectangle 27.png', 'alt' => 'Salon service 2'],
        ['file' => 'Rectangle 28.png', 'alt' => 'Hair styling 3'],
        ['file' => 'Rectangle 29.png', 'alt' => 'Grooming 4'],
        ['file' => 'Rectangle 30.png', 'alt' => 'Salon ambience 5'],
        ['file' => 'Rectangle 32.png', 'alt' => 'Professional care 6'],
    ];
@endphp

<section id="about" class="w-full bg-white relative overflow-hidden">
    <div class="relative w-full bg-section-light py-20 overflow-hidden">
        <div class="max-w-[1360px] mx-auto px-4 relative z-25">
            <div class="w-full lg:w-[55%] relative z-20 mx-auto text-center lg:text-left">
                @include('storefront.partials.dynamic.about-copy')
            </div>
        </div>

        <div class="absolute -left-24 top-10 w-[380px] h-[680px] pointer-events-none hidden lg:block opacity-100 transition-opacity">
            <img
                src="{{ $asset('Group 23.png') }}"
                alt="Decoration Left"
                class="w-full h-full object-contain"
            />
        </div>

        <div class="absolute -right-24 top-10 w-[380px] h-[680px] pointer-events-none hidden lg:block opacity-100 transition-opacity">
            <img
                src="{{ $asset('Group 25.png') }}"
                alt="Decoration Right"
                class="w-full h-full object-contain"
            />
        </div>
    </div>

    <div class="w-full overflow-hidden relative z-10 py-10 bg-white">
        <div class="max-w-[1360px] mx-auto px-4">
            <div class="flex justify-start md:justify-center items-center gap-5 overflow-x-auto scrollbar-none snap-x snap-mandatory pb-4">
                @foreach($galleryImageFiles as $img)
                    <div class="flex-shrink-0 w-[280px] sm:w-[300px] lg:w-[327px] h-[220px] md:h-[280px] lg:h-[345px] overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-500 snap-center group cursor-pointer">
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
