@props([
    'staff' => null,
    'url' => null,
    'initials' => null,
    'color' => null,
    'size' => 'md',
    'rounded' => 'full',
])

@php
    $sizeClasses = match ($size) {
        'xs' => 'w-9 h-9 text-xs',
        'sm' => 'w-11 h-11 text-xs',
        'lg' => 'w-16 h-16 text-2xl',
        'xl' => 'w-20 h-20 text-3xl',
        default => 'w-12 h-12 text-sm',
    };
    $roundedClass = $rounded === '2xl' ? 'rounded-2xl' : 'rounded-full';

    if ($staff) {
        $url = $staff->avatar_url ?? null;
        $initials = $staff->display_initials ?? '?';
        $color = $staff->color ?? '#7C3AED';
    } else {
        $url = $url ?? null;
        $initials = $initials ?? '?';
        $color = $color ?? '#7C3AED';
    }
@endphp

<div {{ $attributes->merge(['class' => "relative shrink-0 {$sizeClasses}"]) }}>
    <div class="absolute inset-0 {{ $roundedClass }} flex items-center justify-center text-white font-bold ring-2 ring-white dark:ring-gray-900"
         style="background-color: {{ $color }}"
         aria-hidden="true">
        {{ $initials }}
    </div>
    @if($url)
        <img src="{{ $url }}"
             alt=""
             width="48"
             height="48"
             class="absolute inset-0 w-full h-full {{ $roundedClass }} object-cover border border-gray-200/80 dark:border-gray-700/80 bg-gray-100 dark:bg-gray-800"
             loading="lazy"
             decoding="async"
             onerror="this.remove()">
    @endif
</div>
