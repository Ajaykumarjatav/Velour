@props([
    'fromName' => 'from',
    'toName' => 'to',
    'fromValue' => '',
    'toValue' => '',
    'fromId' => null,
    'toId' => null,
    'fromLabel' => 'From',
    'toLabel' => 'To',
    'required' => false,
])

@php
    $fromId = $fromId ?? ($fromName . '-field');
    $toId = $toId ?? ($toName . '-field');
@endphp

<div {{ $attributes->merge(['class' => 'grid grid-cols-2 gap-3']) }}>
    <div class="min-w-0">
        <label for="{{ $fromId }}" class="form-label text-xs mb-1">{{ $fromLabel }}</label>
        <div class="form-date-wrap">
            <input type="date"
                   id="{{ $fromId }}"
                   name="{{ $fromName }}"
                   value="{{ $fromValue }}"
                   class="form-date-input text-sm w-full"
                   @if($required) required @endif>
            <span class="form-date-icon" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
        </div>
    </div>
    <div class="min-w-0">
        <label for="{{ $toId }}" class="form-label text-xs mb-1">{{ $toLabel }}</label>
        <div class="form-date-wrap">
            <input type="date"
                   id="{{ $toId }}"
                   name="{{ $toName }}"
                   value="{{ $toValue }}"
                   class="form-date-input text-sm w-full"
                   @if($required) required @endif>
            <span class="form-date-icon" aria-hidden="true">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </span>
        </div>
    </div>
</div>
