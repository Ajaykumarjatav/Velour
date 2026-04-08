@extends('layouts.app')
@section('title', 'Website & Online Presence')
@section('page-title', 'Website & SEO')

@section('content')
<div class="space-y-6"
     x-data="{ tab: 'builder' }">

    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
            <div>
                <h1 class="text-3xl sm:text-4xl font-semibold tracking-tight text-gray-900 dark:text-white leading-tight">Website &amp; Online Presence</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Builder, booking widget &amp; reviews</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ $bookingUrl }}" target="_blank" rel="noopener" class="btn-outline btn-sm">Preview Site</a>
                <form method="POST" action="{{ route('website-seo.publish') }}">
                    @csrf
                    <input type="hidden" name="published" value="{{ $stats['published'] ? 0 : 1 }}">
                    <button type="submit" class="btn-primary btn-sm">{{ $stats['published'] ? 'Unpublish' : 'Publish' }}</button>
                </form>
            </div>
        </div>

        <div class="mt-6 inline-flex flex-wrap gap-1 p-1.5 rounded-full bg-stone-100 dark:bg-gray-800">
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='builder' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='builder'">Builder</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='widget' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='widget'">Widget</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='reviews' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='reviews'">Reviews</button>
            <button type="button" class="px-4 py-2 rounded-full text-sm font-semibold" :class="tab==='seo' ? 'bg-velour-600 text-white' : 'text-muted'" @click="tab='seo'">SEO &amp; Reviews</button>
        </div>
    </div>

    <div x-show="tab==='builder'" x-cloak class="card p-5 sm:p-6">
        <h2 class="text-xl font-semibold text-heading mb-4">Website Builder</h2>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="h-8 bg-[#191120] px-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-yellow-400"></span>
                <span class="w-2.5 h-2.5 rounded-full bg-green-400"></span>
                <span class="text-[11px] text-gray-300 ml-2 truncate">{{ parse_url($bookingUrl, PHP_URL_HOST) }}/your-salon</span>
            </div>
            <div class="p-4 sm:p-5 bg-white dark:bg-gray-900">
                <div class="rounded-xl bg-[#F7F2F4] dark:bg-gray-800/50 p-6 text-center mb-4">
                    <h3 class="text-2xl font-semibold text-heading">{{ $salon->name }}</h3>
                    <p class="text-sm text-muted mt-1">{{ $salon->description ?: ($salon->city ? $salon->city . '\'s premium salon for hair, skin & wellness' : 'Your brand showcase website') }}</p>
                    <div class="mt-4 flex items-center justify-center gap-2">
                        <a href="{{ $bookingUrl }}" target="_blank" class="btn-primary btn-sm">Book Now</a>
                        <a href="{{ route('services.index') }}" class="btn-outline btn-sm">Our Services</a>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-3 mb-4">
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3 text-center"><p class="font-semibold text-heading">Hair</p></div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3 text-center"><p class="font-semibold text-heading">Skin</p></div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3 text-center"><p class="font-semibold text-heading">Nails</p></div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Theme</p><p class="font-semibold text-heading">{{ $stats['theme'] }}</p></div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Domain</p><p class="font-semibold text-heading">{{ $stats['domain_status'] }}</p></div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Pages</p><p class="font-semibold text-heading">{{ $stats['pages'] }}</p></div>
                    <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3"><p class="text-xs text-muted">Mobile</p><p class="font-semibold text-heading">{{ $stats['mobile'] }}</p></div>
                </div>
            </div>
        </div>
    </div>

    <div x-show="tab==='widget'" x-cloak class="card p-5 sm:p-6">
        <h2 class="text-xl font-semibold text-heading mb-2">Booking Widget</h2>
        <p class="text-sm text-muted mb-4">Use this embed on your website:</p>
        <pre class="rounded-xl bg-gray-900 text-green-300 text-xs p-4 overflow-x-auto">&lt;iframe src="{{ $widgetUrl }}" width="100%" height="700" frameborder="0"&gt;&lt;/iframe&gt;</pre>
        <div class="mt-4">
            <a href="{{ $widgetUrl }}" target="_blank" class="btn-outline btn-sm">Open Widget</a>
        </div>
    </div>

    <div x-show="tab==='reviews'" x-cloak class="card p-5 sm:p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">Reviews</h2>
        <div class="grid grid-cols-2 gap-3 max-w-xl">
            <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3">
                <p class="text-xs text-muted">Public Reviews</p>
                <p class="text-2xl font-bold text-heading">{{ number_format($stats['reviews_count']) }}</p>
            </div>
            <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3">
                <p class="text-xs text-muted">Average Rating</p>
                <p class="text-2xl font-bold text-heading">{{ $stats['avg_rating'] ? $stats['avg_rating'].' ★' : 'N/A' }}</p>
            </div>
        </div>
        <div class="mt-4">
            <a href="{{ route('reviews.index') }}" class="btn-outline btn-sm">Manage Reviews</a>
        </div>
    </div>

    <div x-show="tab==='seo'" x-cloak class="card p-5 sm:p-6">
        <h2 class="text-xl font-semibold text-heading mb-3">SEO &amp; Reviews</h2>
        <div class="space-y-3 text-sm">
            <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3">
                <p class="text-muted">Meta title</p>
                <p class="text-heading font-semibold">{{ $salon->name }}{{ $salon->city ? ' | '.$salon->city : '' }}</p>
            </div>
            <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3">
                <p class="text-muted">Meta description</p>
                <p class="text-heading">{{ $salon->description ?: 'Book appointments online for services, staff and real-time slots.' }}</p>
            </div>
            <div class="rounded-lg bg-stone-50 dark:bg-gray-800/50 p-3">
                <p class="text-muted">Canonical URL</p>
                <p class="text-heading">{{ $bookingUrl }}</p>
            </div>
        </div>
        <p class="text-xs text-muted mt-4">Need custom SEO fields? Use <a href="{{ route('settings.index') }}" class="text-link">Settings</a>.</p>
    </div>
</div>
@endsection

