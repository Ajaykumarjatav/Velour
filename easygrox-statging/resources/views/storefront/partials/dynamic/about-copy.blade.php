@php
    $about = $data['about'] ?? [];
    $eyebrowClass = $eyebrowClass ?? 'text-primary font-manrope font-semibold text-sm uppercase tracking-widest mb-3 block';
    $headingClass = $headingClass ?? 'font-manrope font-extrabold text-4xl md:text-5xl lg:text-[60px] lg:leading-[69px] text-black mb-6 tracking-tight';
    $highlightClass = $highlightClass ?? 'text-deep-maroon font-pacifico font-normal lowercase tracking-normal';
    $bodyClass = $bodyClass ?? 'text-text-secondary font-inter font-light text-base md:text-lg leading-relaxed mb-12 max-w-[777px] mx-auto lg:mx-0';
    $statValueClass = $statValueClass ?? 'font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-deep-maroon tracking-tight';
    $statLabelClass = $statLabelClass ?? 'font-manrope font-semibold text-xs md:text-sm text-black uppercase tracking-wider';
    $dividerClass = $dividerClass ?? 'w-px h-[90px] md:h-[110px] bg-gray-200';
@endphp

<span class="{{ $eyebrowClass }}">{{ $about['eyebrow'] ?? 'Who we are' }}</span>

<h2 class="{{ $headingClass }}">
    {{ $about['heading_prefix'] ?? '' }}
    @if(!empty($about['heading_highlight']))
        <span class="{{ $highlightClass }}">{{ $about['heading_highlight'] }}</span>
    @endif
    @if(!empty($about['heading_suffix']))
        {!! nl2br(e($about['heading_suffix'])) !!}
    @endif
</h2>

<div class="{{ $bodyClass }} [&_p]:mb-3 last:[&_p]:mb-0 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5 [&_strong]:font-semibold [&_b]:font-semibold [&_u]:underline">
    {!! \App\Support\AwardsHtml::safe($about['body'] ?? '') !!}
</div>

<div class="flex items-center justify-center lg:justify-start gap-8 md:gap-14">
    <div class="flex flex-col items-center text-center gap-1.5">
        <span class="{{ $statValueClass }}">{{ $about['stat_one_value'] ?? '' }}</span>
        <span class="{{ $statLabelClass }}">{{ $about['stat_one_label'] ?? '' }}</span>
    </div>
    <div class="{{ $dividerClass }}"></div>
    <div class="flex flex-col items-center text-center gap-1.5">
        <span class="{{ $statValueClass }}">{{ $about['stat_two_value'] ?? '' }}</span>
        <span class="{{ $statLabelClass }}">{{ $about['stat_two_label'] ?? '' }}</span>
    </div>
</div>
