@php
    $staff = $data['staff'] ?? [];
    $visibleCount = 4;
@endphp
<section id="staff" class="w-full bg-section-lighter py-20 lg:py-24 overflow-hidden">
    <div class="max-w-[1360px] mx-auto px-4 min-w-0">
        <div class="text-center mb-12 md:mb-16">
            <span class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Team</span>
            <h2 class="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight mb-4">
                Meet Our Staff
            </h2>
            <p class="text-text-muted font-inter font-light text-sm md:text-lg max-w-[600px] mx-auto leading-relaxed">
                We are a hand-picked group of artists who believe that great hair happens when we listen.
            </p>
        </div>

        @if(count($staff) === 0)
            <p class="text-center text-text-muted">Team profiles coming soon.</p>
        @else
            @component('storefront.partials.horizontal-drag-scroll', ['ariaLabel' => 'Our staff', 'gapClass' => 'gap-6 md:gap-8', 'class' => 'pt-16 pb-4'])
                @foreach($staff as $member)
                @php
                    $labels = $member['service_labels'] ?? [];
                    if (empty($labels) && !empty($member['specialisms'])) {
                        $labels = array_filter(array_map('trim', explode('|', $member['specialisms'])));
                    }
                    $visible = count($labels) >= $visibleCount ? array_slice($labels, 0, $visibleCount) : $labels;
                    $moreCount = count($labels) - count($visible);
                    $initials = strtoupper(substr($member['first_name'] ?? '', 0, 1).substr($member['last_name'] ?? '', 0, 1));
                @endphp
                <article class="group shrink-0 snap-start w-[240px] sm:w-[252px] h-[288px] relative flex flex-col items-center bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.08)] pt-20 pb-10 px-6 mt-16 transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-[0_18px_50px_rgba(0,0,0,0.14)] hover:ring-1 hover:ring-primary/15">
                    <div class="absolute -top-16 left-1/2 -translate-x-1/2 w-[128px] h-[128px] rounded-full overflow-hidden border-[5px] border-white shadow-lg bg-gray-100 transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl group-hover:border-primary/20">
                        @if(!empty($member['photo_url']))
                            <img src="{{ $member['photo_url'] }}" alt="{{ $member['name'] }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-primary/20 text-primary font-bold text-2xl">{{ $initials ?: '?' }}</div>
                        @endif
                    </div>
                    <h3 class="font-manrope font-bold text-lg md:text-xl text-black text-center mb-3 leading-tight transition-colors duration-300 group-hover:text-primary">
                        {{ $member['name'] }}
                    </h3>
                    @if(count($visible) > 0)
                        <div class="flex flex-col items-center px-1 w-full">
                            <p class="text-text-muted font-inter text-xs md:text-sm text-center leading-relaxed">
                                @foreach($visible as $index => $label)
                                    @if($index > 0)<span class="text-text-muted/45"> | </span>@endif
                                    <span class="inline whitespace-normal">{{ $label }}</span>
                                @endforeach
                            </p>
                            @if($moreCount > 0)
                                <p class="text-[10px] md:text-xs text-text-muted/55 mt-2 text-center group-hover:text-primary/70 transition-colors duration-300">+{{ $moreCount }} more</p>
                            @endif
                        </div>
                    @else
                        <p class="text-text-muted font-inter text-xs md:text-sm text-center leading-relaxed px-1">
                            {{ $member['role_label'] ?? $member['bio'] ?? 'Stylist' }}
                        </p>
                    @endif
                </article>
                @endforeach
            @endcomponent
        @endif
    </div>
</section>
