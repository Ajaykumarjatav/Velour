@php
    $reviews = collect($data['reviews'] ?? [])->filter(fn ($r) => $r && (!empty($r['text']) || !empty($r['title'])))->values();
@endphp
@if($reviews->isNotEmpty())
<section id="testimonials" class="w-full bg-white py-20 lg:py-24" x-data="{ current: 0, animate: true, items: @js($reviews->toArray()), go(dir) { this.animate = false; setTimeout(() => { this.current = (this.current + dir + this.items.length) % this.items.length; this.animate = true; }, 150); } }">
    <div class="max-w-[1360px] mx-auto px-4">
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-20 items-center">
            <div class="w-full lg:w-[450px] text-center lg:text-left">
                <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Testimonials</span>
                <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight mb-6">
                    Where Style Meets Satisfaction
                </h2>
                <p class="text-text-muted font-inter font-light text-base md:text-lg leading-relaxed mb-4 lg:mb-0">
                    Discover real experiences and honest feedback from people who have visited us.
                </p>
            </div>
            <div class="w-full lg:flex-1 relative">
                <template x-for="(review, i) in items" :key="i">
                    <div x-show="current === i" :class="animate ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'"
                         class="bg-section-light rounded-3xl p-8 md:p-12 shadow-sm border border-border/30 transition-all duration-300">
                        <div class="flex gap-1 mb-4">
                            <template x-for="s in [...Array(review.rating || 5)]" :key="s">
                                <span class="text-[#FFC700] text-lg">★</span>
                            </template>
                        </div>
                        <h3 class="font-manrope font-bold text-xl md:text-2xl text-black mb-4" x-text="review.title"></h3>
                        <p class="text-text-secondary font-inter text-sm md:text-base leading-relaxed mb-6" x-text="review.text"></p>
                        <p class="font-manrope font-semibold text-black">— <span x-text="review.author"></span></p>
                    </div>
                </template>
                <div x-show="items.length > 1" class="flex justify-center gap-4 mt-6">
                    <button type="button" @click="go(-1)" class="w-10 h-10 rounded-full border border-border flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-colors" aria-label="Previous review">←</button>
                    <button type="button" @click="go(1)" class="w-10 h-10 rounded-full border border-border flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-colors" aria-label="Next review">→</button>
                </div>
            </div>
        </div>
    </div>
</section>
@endif
