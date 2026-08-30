@php
    $awardUrls = array_values(array_filter($data['salon']['awards_image_urls'] ?? []));
    $awardRaw = (string) ($data['salon']['awards_accolades'] ?? '');
    $awardHtml = \App\Support\AwardsHtml::safe($awardRaw);
    $hasInlineImages = str_contains(strtolower($awardHtml), '<img');
@endphp
@if(! \App\Support\AwardsHtml::isEmpty($awardRaw) || $awardUrls !== [])
<section id="awards" class="w-full bg-white py-14 md:py-16">
    <div class="max-w-[1360px] mx-auto px-4">
        <p class="text-primary font-manrope font-semibold text-sm uppercase tracking-widest mb-2">Recognition</p>
        <h2 class="font-manrope font-extrabold text-3xl md:text-4xl text-black mb-6 tracking-tight">Awards &amp; accolades</h2>
        @if($awardHtml !== '')
            <div class="awards-rich text-text-secondary font-inter text-base leading-relaxed max-w-3xl mb-8 [&_img]:max-w-full [&_img]:h-auto [&_img]:rounded-xl [&_img]:my-3 [&_ul]:list-disc [&_ul]:pl-5 [&_ol]:list-decimal [&_ol]:pl-5">
                {!! $awardHtml !!}
            </div>
        @endif
        @if($awardUrls !== [] && ! $hasInlineImages)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 md:gap-4">
                @foreach($awardUrls as $url)
                    <div class="aspect-[4/3] rounded-xl overflow-hidden border border-stone-200 bg-stone-50">
                        <img src="{{ $url }}" alt="Award" class="w-full h-full object-cover">
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
@endif
