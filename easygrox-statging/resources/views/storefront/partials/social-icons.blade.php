@php
    $outUrls = $data['salon']['social_out_urls'] ?? [];
    $linkClass = $linkClass ?? 'w-10 h-10 flex items-center justify-center text-white hover:opacity-80 hover:scale-110 active:scale-95 transition-all duration-300 outline-none focus-visible:ring-2 focus-visible:ring-salmon';
    $iconClass = $iconClass ?? 'w-6 h-6';
    $order = ['instagram', 'whatsapp', 'facebook', 'google', 'tiktok', 'email', 'youtube', 'linkedin', 'twitter', 'pinterest'];
    $labels = \App\Support\SocialLinkPlatforms::all();
@endphp
<div class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 max-w-full">
    @foreach($order as $platform)
        @php
            $href = $outUrls[$platform] ?? null;
            $label = $labels[$platform]['label'] ?? ucfirst($platform);
        @endphp
        @if($href)
        <a
            href="{{ $href }}"
            target="_blank"
            rel="noopener noreferrer"
            class="{{ $linkClass }}"
            aria-label="{{ $label }}"
        >
            @include('partials.social-platform-icon', ['platform' => $platform, 'class' => $iconClass])
        </a>
        @endif
    @endforeach
</div>
