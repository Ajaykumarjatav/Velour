@php
    use App\Support\StorefrontTheme;
    $readOnly = $readOnly ?? ($adminStoreBrowse ?? false);
@endphp

<div class="space-y-3">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Website theme</h3>
            <p class="text-xs text-muted mt-0.5">Active: <span class="font-medium text-gray-700 dark:text-gray-200">{{ $themeLabel }}</span></p>
            @if($readOnly)
            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">View-only — theme cannot be changed.</p>
            @endif
        </div>
        <span class="text-[11px] px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-800 font-medium">
            {{ $themeLabel }}
        </span>
    </div>

    @if($readOnly)
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
        @foreach($themes as $slug => $theme)
        @php
            $isActive = $themeSlug === $slug;
            $previewUrl = StorefrontTheme::previewImageUrl($slug);
            $accent = StorefrontTheme::accentColor($slug);
        @endphp
        <div class="rounded-2xl border overflow-hidden {{ $isActive ? 'border-amber-500 ring-2 ring-amber-400/40 shadow-md' : 'border-gray-200 dark:border-gray-600 opacity-80' }}">
            <div class="relative aspect-[16/10] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                @if($previewUrl)
                    <img src="{{ $previewUrl }}" alt="{{ $theme['label'] }} theme preview"
                         class="w-full h-full object-cover object-center">
                @else
                    <div class="w-full h-full flex items-center justify-center text-white text-2xl font-semibold"
                         style="background: linear-gradient(135deg, {{ $accent }}, #1a1a1a);">
                        {{ $theme['label'] }}
                    </div>
                @endif
                <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/70 to-transparent"></div>
                @if($isActive)
                    <span class="absolute top-2 right-2 text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded-full bg-amber-500 text-white shadow">
                        Active
                    </span>
                @endif
            </div>
            <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800">
                <span class="w-4 h-4 rounded-full border-2 flex-shrink-0 {{ $isActive ? 'border-amber-500 bg-amber-500' : 'border-gray-300 dark:border-gray-600 bg-transparent' }}" aria-hidden="true"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $theme['label'] }}</p>
                    <p class="text-[11px] text-muted truncate">{{ $slug }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <form method="POST" action="{{ $action }}" class="space-y-3">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
            @foreach($themes as $slug => $theme)
            @php
                $isActive = $themeSlug === $slug;
                $previewUrl = StorefrontTheme::previewImageUrl($slug);
                $accent = StorefrontTheme::accentColor($slug);
            @endphp
            <label class="group cursor-pointer rounded-2xl border overflow-hidden transition-all {{ $isActive ? 'border-amber-500 ring-2 ring-amber-400/40 shadow-md' : 'border-gray-200 dark:border-gray-600 hover:border-amber-300 dark:hover:border-amber-600' }}">
                <div class="relative aspect-[16/10] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    @if($previewUrl)
                        <img src="{{ $previewUrl }}" alt="{{ $theme['label'] }} theme preview"
                             class="w-full h-full object-cover object-center transition-transform duration-300 group-hover:scale-[1.02]">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white text-2xl font-semibold"
                             style="background: linear-gradient(135deg, {{ $accent }}, #1a1a1a);">
                            {{ $theme['label'] }}
                        </div>
                    @endif
                    <div class="absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-black/70 to-transparent"></div>
                    @if($isActive)
                        <span class="absolute top-2 right-2 text-[10px] font-semibold uppercase tracking-wide px-2 py-1 rounded-full bg-amber-500 text-white shadow">
                            Active
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-3 p-3 bg-white dark:bg-gray-800">
                    <input type="radio" name="theme" value="{{ $slug }}"
                        class="w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500"
                        {{ $isActive ? 'checked' : '' }}
                        onchange="this.form.submit()">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">{{ $theme['label'] }}</p>
                        <p class="text-[11px] text-muted truncate">{{ $slug }}</p>
                    </div>
                </div>
            </label>
            @endforeach
        </div>
    </form>
    @endif
</div>
