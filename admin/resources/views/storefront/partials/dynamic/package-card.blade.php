@php
    $img = $packageImages[$i % count($packageImages)];
    $items = array_slice($pkg['items'] ?? [], 0, 3);
    $totalItems = $pkg['service_count'] ?? count($pkg['items'] ?? []);
@endphp
<article
    role="button"
    tabindex="0"
    data-home-package="{{ (int) $pkg['id'] }}"
    onclick="if (window.storefrontHomeCart) window.storefrontHomeCart.togglePackage({{ (int) $pkg['id'] }})"
    onkeydown="if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); if (window.storefrontHomeCart) window.storefrontHomeCart.togglePackage({{ (int) $pkg['id'] }}); }"
    class="sf-home-packages__card {{ $cardClass ?? '' }}"
>
    <div class="sf-home-packages__image-wrap">
        <img src="{{ $asset($img) }}" alt="{{ $pkg['name'] }}" draggable="false">
        @if(!empty($pkg['is_best_value']))
        <span class="sf-home-packages__badge">BEST VALUE ⭐</span>
        @endif
    </div>
    <div class="sf-home-packages__body">
        <h3 class="sf-home-packages__name">{{ $pkg['name'] }}</h3>
        <ul class="sf-home-packages__items">
            @foreach($items as $item)
            <li class="sf-home-packages__item">
                <span class="sf-home-packages__item-name">{{ $item['name'] }}</span>
                <span class="sf-home-packages__item-price">{{ $item['price'] }}</span>
            </li>
            @endforeach
        </ul>
        <p class="sf-home-packages__count">
            @if($totalItems > count($items))
                {{ count($items) }} shown • {{ $totalItems }} services included
            @else
                {{ $totalItems }} {{ $totalItems === 1 ? 'service' : 'services' }} included
            @endif
        </p>
        <div class="sf-home-packages__pricing">
            @if(($pkg['components_total'] ?? 0) > 0)
            <div class="sf-home-packages__value-row {{ !empty($pkg['has_savings']) ? 'is-struck' : '' }}">
                <span>Individual value</span>
                <span>{{ $pkg['components_formatted'] ?? '' }}</span>
            </div>
            @endif
            <div class="sf-home-packages__value-row">
                <span>Package price</span>
                <span class="sf-home-packages__price">{{ $pkg['price_formatted'] }}</span>
            </div>
            @if(!empty($pkg['has_savings']))
            <div class="sf-home-packages__save-row">
                <span class="sf-home-packages__save">SAVE {{ $pkg['savings_formatted'] }}</span>
            </div>
            @endif
        </div>
        <span data-home-package-label class="sf-home-packages__select">Select package</span>
    </div>
</article>
