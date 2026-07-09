@php
    $navLinks = [
        ['label' => 'Who We are', 'href' => '#about'],
        ['label' => 'Services', 'href' => '#services'],
        ['label' => 'Packages', 'href' => '#packages'],
        ['label' => 'Amenities', 'href' => '#amenities'],
        ['label' => 'Our Staff', 'href' => '#staff'],
        ['label' => 'Reach us', 'href' => '#locations'],
        ['label' => 'Testimonials', 'href' => '#testimonials'],
    ];
@endphp
<nav
    x-data="{ active: 'Who We are', isSticky: false, menuOpen: false }"
    x-init="window.addEventListener('scroll', () => { isSticky = window.scrollY > 120 })"
    :class="isSticky
        ? 'fixed top-0 left-0 right-0 shadow-[0_4px_30px_rgba(0,0,0,0.5)] bg-black/90 backdrop-blur-md py-1'
        : 'relative bg-black py-3'"
    class="w-full z-50 transition-all duration-300 border-b border-white/5"
>
    <div class="max-w-[1360px] mx-auto px-4">
        <div class="flex items-center justify-between py-2 lg:hidden">
            <span class="font-manrope font-bold text-lg text-white tracking-wide uppercase">
                Menu
            </span>
            <button
                type="button"
                @click="menuOpen = !menuOpen"
                class="p-2.5 rounded-full hover:bg-white/10 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#9a031e]"
                aria-label="Toggle menu"
                :aria-expanded="menuOpen"
            >
                <svg x-show="!menuOpen" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white">
                    <line x1="3" y1="6" x2="21" y2="6" stroke-linecap="round" />
                    <line x1="3" y1="12" x2="21" y2="12" stroke-linecap="round" />
                    <line x1="3" y1="18" x2="21" y2="18" stroke-linecap="round" />
                </svg>
                <svg x-show="menuOpen" x-cloak xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-white">
                    <path d="M18 6L6 18M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <div
            :class="menuOpen
                ? 'flex max-h-[500px] opacity-100 py-4'
                : 'hidden max-h-0 opacity-0 lg:max-h-none lg:opacity-100'"
            class="lg:flex flex-col lg:flex-row items-center justify-between gap-2 lg:gap-1 lg:py-1 transition-all duration-300 ease-in-out overflow-hidden lg:overflow-visible"
        >
            <div class="flex flex-col lg:flex-row items-center w-full lg:justify-center gap-1.5 lg:gap-8">
                @foreach($navLinks as $link)
                <a
                    href="{{ $link['href'] }}"
                    @click="active = '{{ $link['label'] }}'; menuOpen = false"
                    :class="active === '{{ $link['label'] }}'
                        ? 'text-[#9a031e] bg-[#9a031e]/10 lg:bg-transparent'
                        : 'text-gray-400 hover:text-[#9a031e] hover:bg-white/5 lg:hover:bg-transparent'"
                    class="group relative py-2.5 lg:py-4 px-4 font-manrope font-semibold text-xs tracking-wider uppercase transition-all duration-300 w-full lg:w-auto text-center rounded-lg lg:rounded-none"
                >
                    <span class="relative z-10">{{ $link['label'] }}</span>
                    <span
                        :class="active === '{{ $link['label'] }}' ? 'w-full opacity-100' : 'w-0 opacity-0 group-hover:w-full'"
                        class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#9a031e] rounded-full transition-all duration-300 hidden lg:block"
                    ></span>
                </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>
