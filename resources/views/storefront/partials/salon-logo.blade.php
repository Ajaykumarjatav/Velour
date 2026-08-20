@php
    $salonData = $data['salon'] ?? [];
    $logoUrl = $data['branding']['logo_url'] ?? null;
    $salonName = $salonData['name'] ?? ($salon->name ?? '');
    $variant = $variant ?? 'header';
    $imageClass = $imageClass ?? match ($variant) {
        'footer' => 'sf-logo h-12 md:h-14 w-auto max-w-[320px] object-contain',
        default => 'sf-logo h-12 md:h-14 w-auto max-w-[400px] min-w-[160px] object-contain object-left',
    };
@endphp

@if($logoUrl)
    <img src="{{ $logoUrl }}" alt="{{ $salonName ? $salonName.' logo' : 'Salon logo' }}" class="{{ $imageClass }}"
         onerror="this.style.display='none';this.nextElementSibling?.classList.remove('hidden')">
    <span class="hidden {{ $variant === 'footer' ? 'font-bold text-xl md:text-[28px] text-white' : 'font-bold text-[22px] md:text-[32px] text-white' }}">{{ $salonName ?: 'EasyGrox' }}</span>
@else
    <span class="inline-flex items-center gap-2.5 md:gap-3" aria-label="EasyGrox logo">
        <img src="{{ $asset('easygrox-icon.png') }}" alt="" class="{{ $variant === 'footer' ? 'h-10 w-10 md:h-12 md:w-12' : 'h-11 w-11 md:h-14 md:w-14' }} shrink-0 object-contain mix-blend-screen">
        <span class="{{ $variant === 'footer' ? 'font-bold text-xl md:text-[28px] text-white' : 'font-bold text-[22px] md:text-[32px] text-white' }} leading-none tracking-tight">EasyGrox</span>
    </span>
@endif
