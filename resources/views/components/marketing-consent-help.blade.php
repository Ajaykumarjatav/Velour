@props([
    /** tooltip: (i) icon | hint: helper paragraph under the label */
    'mode' => 'tooltip',
])
@php
    $tooltipText = 'Promotional marketing only (offers, newsletters, campaigns). Opted out means do not send marketing—only essential messages such as booking confirmations. Follow applicable law and your privacy policy; update with the client’s permission.';
    $hintText = 'Records whether this client agreed to receive promotional messages from your salon. This is separate from appointment confirmations and account notices. Change only with the client’s consent.';
@endphp
@if($mode === 'tooltip')
    <x-help-tip :text="$tooltipText" {{ $attributes }} />
@elseif($mode === 'hint')
    <p {{ $attributes->class(['form-hint']) }}>{{ $hintText }}</p>
@endif
