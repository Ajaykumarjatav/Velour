{{--
  Accessible inline help: shows an (i) control; full text in title + aria-label for screen readers.
  Use next to table headers or form labels where a short explanation is needed.
--}}
@props([
    'text' => '',
])
@if($text !== '')
<button
    type="button"
    class="inline-flex items-center justify-center shrink-0 rounded-full text-muted hover:text-velour-400 dark:hover:text-velour-300 focus:outline-none focus:ring-2 focus:ring-velour-400 focus:ring-offset-2 focus:ring-offset-white dark:focus:ring-offset-gray-900"
    title="{{ $text }}"
    aria-label="{{ $text }}"
>
    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
</button>
@endif
