@extends('layouts.app')
@section('title', 'About Us')
@section('page-title', 'About Us')

@push('styles')
<style>
    .about-editor-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        margin-bottom: 0.5rem;
    }
    .about-editor-btn {
        min-width: 2.25rem;
        padding: 0.35rem 0.65rem;
        border-radius: 0.5rem;
        border: 1px solid rgb(226 232 240);
        background: #fff;
        color: inherit;
        font-size: 0.75rem;
        font-weight: 700;
        line-height: 1.25;
    }
    .dark .about-editor-btn {
        border-color: rgb(51 65 85);
        background: rgb(15 23 42 / 0.6);
    }
    .about-editor-surface {
        min-height: 6.5rem;
        max-height: 11rem;
        overflow-y: auto;
        max-width: 100%;
        overflow-x: hidden;
        padding: 0.75rem 0.875rem;
        border-radius: 0.75rem;
        border: 1px solid rgb(226 232 240);
        background: #fff;
        outline: none;
    }
    .dark .about-editor-surface {
        border-color: rgb(51 65 85);
        background: rgb(15 23 42 / 0.45);
        color: rgb(241 245 249);
    }
    .about-editor-surface:empty::before {
        content: attr(data-placeholder);
        color: rgb(148 163 184);
        pointer-events: none;
    }
    .about-editor-surface p { margin: 0 0 0.65rem; }
    .about-editor-surface p:last-child { margin-bottom: 0; }
    .about-editor-surface ul,
    .about-editor-surface ol { margin: 0 0 0.65rem; padding-left: 1.25rem; }
    .about-editor-surface ul { list-style: disc; }
    .about-editor-surface ol { list-style: decimal; }
    .about-us-form .form-error,
    .about-us-form [data-cv-msg] {
        display: none !important;
    }
</style>
@endpush

@section('content')
<div class="space-y-4 max-w-6xl">
    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-4 sm:p-5">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-2xl font-semibold tracking-tight text-gray-900 dark:text-white leading-tight">About Us</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Editing <span class="font-semibold text-gray-900 dark:text-white">{{ $themeLabel }}</span>
                    @if($themeSlug === $liveTheme)
                        <span class="ml-1 text-xs font-medium text-amber-700 dark:text-amber-300">· live on booking site</span>
                    @else
                        <span class="ml-1 text-xs text-muted">· booking site currently uses {{ \App\Support\StorefrontTheme::label($liveTheme) }}</span>
                    @endif
                </p>
            </div>
            <a href="{{ $websiteUrl }}" target="_blank" rel="noopener" class="btn-outline btn-sm shrink-0">Preview site</a>
        </div>
        <div class="mt-4">
            <p class="text-xs font-semibold uppercase tracking-wide text-muted mb-2">Theme</p>
            <div class="flex flex-wrap gap-2" role="tablist" aria-label="About Us theme">
                @foreach($themes as $slug => $meta)
                    <a href="{{ \App\Support\AppUrl::path('website-about.index', ['theme' => $slug]) }}"
                       role="tab"
                       aria-selected="{{ $slug === $themeSlug ? 'true' : 'false' }}"
                       class="inline-flex items-center rounded-full px-3 py-1.5 text-xs font-semibold border transition
                         {{ $slug === $themeSlug
                            ? 'bg-gray-900 text-white border-gray-900 dark:bg-white dark:text-gray-900 dark:border-white'
                            : 'border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:border-amber-400' }}">
                        {{ $meta['label'] ?? $slug }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <x-unless-admin-browse>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-4 items-start">
        <form method="POST" action="{{ \App\Support\AppUrl::path('website-about.update') }}" class="about-us-form salon-write-ui card p-4 sm:p-5 space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="theme" value="{{ $themeSlug }}">

            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-heading">Copy · {{ $themeLabel }}</h2>
                    <p class="text-xs text-muted mt-0.5">Saved only for this theme in this store.</p>
                </div>
                <button type="submit" class="btn-primary btn-sm shrink-0">Save</button>
            </div>

            <div>
                <label class="form-label" for="about-eyebrow">Small heading</label>
                <input id="about-eyebrow" name="eyebrow" class="form-input" maxlength="80" value="{{ old('eyebrow', $about['eyebrow'] ?? '') }}">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="min-w-0">
                    <label class="form-label" for="about-prefix">Before highlight</label>
                    <input id="about-prefix" name="heading_prefix" class="form-input" maxlength="160" value="{{ old('heading_prefix', $about['heading_prefix'] ?? '') }}">
                </div>
                <div class="min-w-0">
                    <label class="form-label" for="about-highlight">Highlight word</label>
                    <input id="about-highlight" name="heading_highlight" class="form-input" maxlength="80" value="{{ old('heading_highlight', $about['heading_highlight'] ?? '') }}">
                </div>
                <div class="min-w-0">
                    <label class="form-label" for="about-suffix">After highlight</label>
                    <input id="about-suffix" name="heading_suffix" class="form-input" maxlength="160" value="{{ old('heading_suffix', $about['heading_suffix'] ?? '') }}">
                </div>
            </div>

            <div data-about-editor>
                <label class="form-label">About text</label>
                <div class="about-editor-toolbar">
                    <button type="button" class="about-editor-btn" data-cmd="bold" title="Bold">B</button>
                    <button type="button" class="about-editor-btn" data-cmd="italic" title="Italic"><em>I</em></button>
                    <button type="button" class="about-editor-btn" data-cmd="underline" title="Underline">U</button>
                    <button type="button" class="about-editor-btn" data-cmd="insertUnorderedList" title="Bullet list">List</button>
                </div>
                <div class="about-editor-surface"
                     contenteditable="true"
                     data-placeholder="Tell guests who you are and what makes the visit special…"
                     role="textbox">{!! $bodyHtml !!}</div>
                <textarea name="body" class="hidden" aria-hidden="true">{{ old('body', $about['body'] ?? '') }}</textarea>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="min-w-0">
                    <label class="form-label" for="about-stat1-value">Stat 1 value</label>
                    <input id="about-stat1-value" name="stat_one_value" class="form-input" maxlength="40" value="{{ old('stat_one_value', $about['stat_one_value'] ?? '') }}">
                </div>
                <div class="min-w-0">
                    <label class="form-label" for="about-stat1-label">Stat 1 label</label>
                    <input id="about-stat1-label" name="stat_one_label" class="form-input" maxlength="80" value="{{ old('stat_one_label', $about['stat_one_label'] ?? '') }}">
                </div>
                <div class="min-w-0">
                    <label class="form-label" for="about-stat2-value">Stat 2 value</label>
                    <input id="about-stat2-value" name="stat_two_value" class="form-input" maxlength="40" value="{{ old('stat_two_value', $about['stat_two_value'] ?? '') }}">
                </div>
                <div class="min-w-0">
                    <label class="form-label" for="about-stat2-label">Stat 2 label</label>
                    <input id="about-stat2-label" name="stat_two_label" class="form-input" maxlength="80" value="{{ old('stat_two_label', $about['stat_two_label'] ?? '') }}">
                </div>
            </div>
        </form>

        <div class="card p-4 sm:p-5 space-y-3">
            <div>
                <h2 class="text-base font-semibold text-heading">Images · {{ $themeLabel }}</h2>
                <p class="text-xs text-muted mt-0.5">Uploads stay on this theme only. Other themes are unchanged.</p>
                @error('image')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                @error('index')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @foreach($gallerySlots as $slot)
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-gray-50 dark:bg-gray-900/40">
                        <div class="aspect-[4/3] overflow-hidden bg-gray-100 dark:bg-gray-800">
                            <img src="{{ $slot['preview_url'] }}" alt="About image {{ $slot['index'] + 1 }}" class="w-full h-full object-cover">
                        </div>
                        <div class="p-2 space-y-1.5">
                            <p class="text-[11px] text-muted">{{ $slot['is_custom'] ? 'Custom' : 'Theme default' }}</p>
                            <form method="POST" action="{{ \App\Support\AppUrl::path('website-about.gallery.update') }}" enctype="multipart/form-data" class="salon-write-ui">
                                @csrf
                                <input type="hidden" name="theme" value="{{ $themeSlug }}">
                                <input type="hidden" name="index" value="{{ $slot['index'] }}">
                                <label for="about-gallery-{{ $slot['index'] }}" class="btn-outline btn-sm w-full text-center cursor-pointer block">Replace</label>
                                <input id="about-gallery-{{ $slot['index'] }}" type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" class="hidden" onchange="if (this.files && this.files.length) this.form.submit()">
                            </form>
                            @if($slot['is_custom'])
                                <form method="POST" action="{{ \App\Support\AppUrl::path('website-about.gallery.reset') }}" class="salon-write-ui">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="theme" value="{{ $themeSlug }}">
                                    <input type="hidden" name="index" value="{{ $slot['index'] }}">
                                    <button type="submit" class="text-[11px] text-muted hover:text-heading">Use default</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    </x-unless-admin-browse>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var root = document.querySelector('[data-about-editor]');
    if (!root) return;
    var surface = root.querySelector('.about-editor-surface');
    var textarea = root.querySelector('textarea[name="body"]');
    var form = root.closest('form');
    if (!surface || !textarea) return;

    function sync() {
        textarea.value = surface.innerHTML.trim();
    }

    root.querySelectorAll('[data-cmd]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            surface.focus();
            document.execCommand(btn.getAttribute('data-cmd'), false, null);
            sync();
        });
    });

    surface.addEventListener('input', sync);
    surface.addEventListener('blur', sync);
    if (form) form.addEventListener('submit', sync);
    sync();
})();
</script>
@endpush
