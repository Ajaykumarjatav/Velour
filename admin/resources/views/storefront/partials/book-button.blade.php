@php
    $enabled = (bool) (($data['salon']['online_booking_enabled'] ?? null) ?? ($salon->online_booking_enabled ?? true));
    $label = $label ?? 'Book Now';
    $class = $class ?? '';
@endphp
@if($enabled)
<a href="#book" class="{{ $class }}">{!! $label !!}</a>
@else
<span class="{{ $class }} opacity-60 cursor-not-allowed" title="Online booking is currently offline" aria-disabled="true">{!! $label !!}</span>
@endif
