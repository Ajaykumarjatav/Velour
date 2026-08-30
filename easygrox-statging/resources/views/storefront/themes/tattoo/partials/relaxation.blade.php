@php
    $amenities = [
        ['iconFile' => 'equipment 1.png', 'title' => 'Sterile Equipment', 'desc' => 'Safe, hygienic tools sterilized for every tattoo session.'],
        ['iconFile' => 'design 1.png', 'title' => 'Design Consultation', 'desc' => 'Personalized tattoo concepts crafted around your vision.'],
        ['iconFile' => 'ink 1.png', 'title' => 'Premium Tattoo Inks', 'desc' => 'Rich, high-quality inks designed for lasting color and detail.'],
        ['iconFile' => 'ambiance 1.png', 'title' => 'Relaxing Ambience', 'desc' => 'A calm and creative environment for a comfortable experience.'],
        ['iconFile' => 'aftercare 1.png', 'title' => 'Aftercare Guidance', 'desc' => 'Professional healing support to keep your tattoo looking perfect.'],
        ['iconFile' => 'wifi 1.png', 'title' => 'High-Speed Wi-Fi', 'desc' => 'Stay connected or work comfortably during treatment.'],
        ['iconFile' => 'refreshment bar 1.png', 'title' => 'Refreshment Bar', 'desc' => 'Complimentary drinks and snacks for a relaxed visit.'],
        ['iconFile' => 'private room 1.png', 'title' => 'Private Tattoo Rooms', 'desc' => 'Comfortable private spaces for a more personal experience.'],
    ];
@endphp
<section id="amenities" class="w-full bg-black py-20 lg:py-24">
    <div class="max-w-[1360px] mx-auto px-4">
        <div class="text-center mb-16">
            <span class="text-gray-300 font-manrope font-medium text-sm block mb-3">Amenities</span>
            <h2 class="font-serif font-bold text-3xl md:text-[45px] md:leading-[55px] text-white mb-6 tracking-wide uppercase">
                Experience Premium Tattooing
            </h2>
            <p class="text-gray-400 font-inter font-light text-sm md:text-base max-w-[750px] mx-auto leading-relaxed">
                Beyond tattoo artistry, we provide a comfortable and hygienic studio experience designed to make every session relaxing, safe, and memorable.
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($amenities as $item)
            <div class="flex flex-col items-center text-center p-6 md:p-8 w-full bg-gradient-to-b from-zinc-800/40 to-zinc-900 rounded-2xl border border-white/5 shadow-lg hover:shadow-[0_0_20px_rgba(154,3,30,0.15)] hover:border-[#9a031e]/30 transition-all duration-300 hover:-translate-y-1 group cursor-default">
                <div class="mb-6 flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
                    <img
                        src="{{ $asset($item['iconFile']) }}"
                        alt="{{ $item['title'] }}"
                        class="w-10 h-10 md:w-12 md:h-12 object-contain opacity-90 group-hover:opacity-100 transition-opacity"
                    />
                </div>
                <h3 class="font-manrope font-semibold text-base md:text-lg text-white mb-2.5 transition-colors duration-300 group-hover:text-[#9a031e]">
                    {{ $item['title'] }}
                </h3>
                <p class="font-inter font-light text-xs md:text-sm text-gray-400 leading-[22px]">
                    {{ $item['desc'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>
