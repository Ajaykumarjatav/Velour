@extends('layouts.app')
@section('title', 'Help Centre')

@section('content')
<div class="max-w-5xl mx-auto px-4 py-12">

  {{-- Header --}}
  <div class="text-center mb-12">
    <h1 class="text-4xl font-bold text-gray-900 mb-3" style="font-family:'Playfair Display',serif">
      How can we help?
    </h1>
    <form method="GET" action="{{ route('help.index') }}" class="max-w-xl mx-auto mt-4">
      <div class="relative">
        <input name="q" value="{{ $search }}" placeholder="Search articles..."
          class="w-full pl-12 pr-4 py-4 rounded-2xl border border-gray-200 shadow-sm text-base focus:ring-2 focus:ring-amber-400 focus:border-transparent outline-none">
        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">🔍</span>
        <button type="submit"
          class="absolute right-3 top-1/2 -translate-y-1/2 bg-amber-500 text-white px-4 py-2 rounded-xl text-sm font-semibold hover:bg-amber-600 transition">
          Search
        </button>
      </div>
    </form>
  </div>

  {{-- Category filters --}}
  <div class="flex flex-wrap gap-2 mb-8 justify-center">
    <a href="{{ route('help.index') }}"
      class="px-4 py-2 rounded-xl text-sm font-medium border transition
        {{ ! $category ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300' }}">
      All
    </a>
    @foreach($categories as $cat => $count)
    <a href="{{ route('help.index', ['category' => $cat]) }}"
      class="px-4 py-2 rounded-xl text-sm font-medium border transition
        {{ $category === $cat ? 'bg-amber-500 text-white border-amber-500' : 'bg-white text-gray-600 border-gray-200 hover:border-amber-300' }}">
      {{ ucwords(str_replace('-', ' ', $cat)) }}
      <span class="ml-1 opacity-60">({{ $count }})</span>
    </a>
    @endforeach
  </div>

  {{-- Articles grid --}}
  @if($articles->isEmpty())
    <div class="text-center py-16 text-gray-400">
      <p class="text-5xl mb-4">🔎</p>
      <p class="text-lg font-medium">No articles found</p>
      <p class="text-sm">Try a different search or <a href="mailto:support@velour.app" class="text-amber-600 underline">contact support</a>.</p>
    </div>
  @else
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
      @foreach($articles as $article)
      <a href="{{ route('help.article', $article->slug) }}"
        class="bg-white rounded-2xl border border-gray-100 p-5 hover:shadow-md hover:border-amber-200 transition block group">
        <div class="flex items-start justify-between mb-2">
          <span class="text-xs font-semibold uppercase tracking-wide text-amber-600 bg-amber-50 px-2 py-1 rounded-lg">
            {{ str_replace('-', ' ', $article->category) }}
          </span>
          @if($article->is_featured)
          <span class="text-xs text-gray-400">★ Featured</span>
          @endif
        </div>
        <h3 class="font-semibold text-gray-800 mb-2 group-hover:text-amber-600 transition">
          {{ $article->title }}
        </h3>
        <p class="text-sm text-gray-500 leading-relaxed line-clamp-2">
          {{ $article->excerpt }}
        </p>
        <div class="mt-3 flex items-center gap-3 text-xs text-gray-400">
          <span>👁 {{ number_format($article->view_count) }} views</span>
          <span>👍 {{ $article->helpful_count }}</span>
        </div>
      </a>
      @endforeach
    </div>
  @endif

  {{-- Support CTA --}}
  <div class="mt-16 bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl border border-amber-100 p-8 text-center">
    <h2 class="text-xl font-bold text-gray-800 mb-2">Still need help?</h2>
    <p class="text-gray-500 mb-5">Our support team typically responds within 2 hours during business hours.</p>
    <div class="flex flex-col sm:flex-row gap-3 justify-center">
      <a href="mailto:support@velour.app"
        class="bg-gray-900 text-white px-6 py-3 rounded-xl font-semibold hover:bg-gray-700 transition text-sm">
        ✉ Email Support
      </a>
      <a href="{{ route('admin.support.index') }}"
        class="border border-gray-200 text-gray-600 px-6 py-3 rounded-xl font-semibold hover:bg-gray-50 transition text-sm">
        🎟 View My Tickets
      </a>
    </div>
  </div>

</div>
@endsection
