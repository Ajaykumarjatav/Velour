@props([
    'show' => 'open',
    'maxWidth' => 'max-w-xl',
])

<x-modal-overlay :show="$show" x-on:click.self="{{ $show }} = false">
    <div {{ $attributes->merge([
        'class' => 'bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-700 w-full shadow-2xl '
            . $maxWidth
            . ' max-h-[min(90vh,calc(100vh-2rem))] overflow-y-auto',
    ]) }} x-on:click.stop>
        {{ $slot }}
    </div>
</x-modal-overlay>
