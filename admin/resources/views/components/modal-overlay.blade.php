@props([
    'show' => 'open',
])

{{-- Teleport to body so overlay covers sidebar + header (stacking context). --}}
<template x-teleport="body">
    <div
        x-show="{{ $show }}"
        x-cloak
        {{ $attributes->merge([
            'class' => 'fixed inset-0 z-[250] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm',
        ]) }}
        role="dialog"
        aria-modal="true"
    >
        {{ $slot }}
    </div>
</template>
