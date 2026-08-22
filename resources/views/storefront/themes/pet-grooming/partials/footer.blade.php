@if($data['salon'] ?? null)
<footer class="w-full bg-[#444a38] pt-16 md:pt-20">
    <div>
        <div class="max-w-[1360px] mx-auto px-4 py-8 md:py-12">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-6 mb-10">
                <a
                    href="#hero"
                    class="inline-flex items-center transition-opacity duration-300 hover:opacity-90 outline-none focus-visible:ring-2 focus-visible:ring-salmon"
                >
                    @include('storefront.partials.salon-logo', ['variant' => 'footer'])
                </a>

                @include('storefront.partials.social-icons')
            </div>

            <div class="w-full h-px bg-white/10 mb-8"></div>

            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 text-white/50 font-inter font-light text-xs md:text-sm text-center sm:text-left">
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-white transition-colors duration-200">Privacy Policy</a>
                    <a href="#" class="hover:text-white transition-colors duration-200">Terms & Conditions</a>
                </div>
                <span>© 2002 - 2026 Your Salon Ltd. All rights reserved</span>
            </div>
        </div>
    </div>
</footer>
@endif
