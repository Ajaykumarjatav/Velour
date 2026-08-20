@php
    $salon = $data['salon'] ?? null;
    if (!$salon) return;
    $galleryImageFiles = [
        ['file' => 'Rectangle 31.png', 'alt' => 'Salon interior 1'],
        ['file' => 'Rectangle 27.png', 'alt' => 'Salon service 2'],
        ['file' => 'Rectangle 28.png', 'alt' => 'Hair styling 3'],
        ['file' => 'Rectangle 30.png', 'alt' => 'Grooming 4'],
        ['file' => 'Rectangle 32.png', 'alt' => 'Salon ambience 5'],
        ['file' => 'Rectangle 29.png', 'alt' => 'Professional care 6'],
    ];
@endphp

<section
    id="about"
    class="w-full bg-[#F8F8F8] relative overflow-hidden"
>
    <div class="relative w-full bg-[#F8F8F8] py-20 overflow-hidden">
        <div class="max-w-[1360px] mx-auto px-4 relative z-25">
            <div class="w-full lg:w-[55%] relative z-20 mx-auto text-center lg:text-left">
                <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest mb-3 block">
                    Who we are
                </span>

                <h2 class="font-manrope font-extrabold text-4xl md:text-5xl lg:text-[60px] lg:leading-[69px] text-black mb-6 tracking-tight">
                    Made With Love
                    <span class="text-deep-maroon font-pacifico font-normal lowercase tracking-normal">
                        & Happy
                    </span>
                    Paws.
                </h2>

                <p class="text-text-secondary font-inter font-light text-base md:text-lg leading-relaxed mb-12 max-w-[777px] mx-auto lg:mx-0">
                    We believe every pet deserves gentle care, comforting cuddles, and
                    a grooming experience that feels safe, relaxing, and full of love.
                    From playful pups to curious little cats, our team creates calm
                    and happy moments that leave every furry friend looking adorable
                    and feeling their absolute best.
                </p>

                <div class="flex items-center justify-center lg:justify-start gap-8 md:gap-14">
                    <div class="flex flex-col items-center text-center gap-1.5">
                        <span class="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-deep-maroon tracking-tight">
                            2015
                        </span>
                        <span class="font-manrope font-semibold text-xs md:text-sm text-black uppercase tracking-wider">
                            Founded
                        </span>
                    </div>

                    <div class="w-px h-[90px] md:h-[110px] bg-[#F8F8F8]/50"></div>

                    <div class="flex flex-col items-center text-center gap-1.5">
                        <span class="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-deep-maroon tracking-tight">
                            300+
                        </span>
                        <span class="font-manrope font-semibold text-xs md:text-sm text-black uppercase tracking-wider">
                            Trusted Clients
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute -left-0 top-0 w-[380px] h-[680px] pointer-events-none hidden lg:block opacity-100 transition-opacity">
            <img
                src="{{ $asset('1-removebg-preview.png') }}"
                alt="Decoration Left"
                class="w-full h-full object-contain"
            />
        </div>

        <div class="absolute -right-0 top-0 w-[380px] h-[680px] pointer-events-none hidden lg:block opacity-100 transition-opacity">
            <img
                src="{{ $asset('2-removebg-preview.png') }}"
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
                            src="{{ $asset($img['file']) }}"
                            alt="{{ $img['alt'] }}"
                            class="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-out"
                        />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>
