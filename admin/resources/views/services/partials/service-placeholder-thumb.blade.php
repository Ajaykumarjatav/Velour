@php
    /** @var \App\Models\Service $service */
    /** @var \App\Models\ServiceCategory|null $category */
    $categoryName = $category?->name;
    $businessTypeName = ($category && $category->relationLoaded('businessType') && $category->businessType)
        ? $category->businessType->name
        : null;
    $paths = \App\Support\ServicePlaceholderGlyph::pathDs(
        (string) $service->name,
        $categoryName ? (string) $categoryName : null,
        $businessTypeName ? (string) $businessTypeName : null,
    );
    $gradients = [
        'linear-gradient(145deg, #5b21b6 0%, #7c3aed 45%, #a78bfa 100%)',
        'linear-gradient(145deg, #4c1d95 0%, #6d28d9 50%, #818cf8 100%)',
        'linear-gradient(145deg, #6d28d9 0%, #8b5cf6 40%, #c4b5fd 100%)',
        'linear-gradient(145deg, #2e1065 0%, #5b21b6 55%, #a78bfa 100%)',
    ];
    $gi = abs(crc32((string) $service->id)) % count($gradients);
    $thumbClass = $thumbClass ?? 'w-12 h-12';
    $iconClass = $iconClass ?? 'w-6 h-6';
@endphp
<div class="{{ $thumbClass }} rounded-xl flex-shrink-0 flex items-center justify-center text-white shadow-md ring-1 ring-black/10 dark:ring-white/10"
     style="background: {{ $gradients[$gi] }}">
    <svg class="{{ $iconClass }} opacity-95" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        @foreach($paths as $d)
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="{{ $d }}"/>
        @endforeach
    </svg>
</div>
