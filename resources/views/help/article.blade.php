@extends('layouts.app')
@section('title', $article->title . ' — Help Centre')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-12">

  {{-- Breadcrumb --}}
  <nav class="text-sm text-gray-400 mb-6 flex items-center gap-2">
    <a href="{{ route('help.index') }}" class="hover:text-gray-600">Help Centre</a>
    <span>/</span>
    <a href="{{ route('help.index', ['category' => $article->category]) }}" class="hover:text-gray-600 capitalize">
      {{ str_replace('-', ' ', $article->category) }}
    </a>
    <span>/</span>
    <span class="text-gray-600 truncate max-w-xs">{{ $article->title }}</span>
  </nav>

  <div class="grid lg:grid-cols-[1fr_280px] gap-10">

    {{-- Article content --}}
    <article>
      <h1 class="text-3xl font-bold text-gray-900 mb-3" style="font-family:'Playfair Display',serif">
        {{ $article->title }}
      </h1>
      <div class="flex items-center gap-4 text-sm text-gray-400 mb-8 pb-6 border-b border-gray-100">
        <span class="capitalize bg-amber-50 text-amber-700 px-3 py-1 rounded-full text-xs font-medium">
          {{ str_replace('-', ' ', $article->category) }}
        </span>
        <span>👁 {{ number_format($article->view_count) }} views</span>
        <span>Updated {{ \Carbon\Carbon::parse($article->updated_at)->diffForHumans() }}</span>
      </div>

      {{-- Rendered Markdown content --}}
      <div class="prose prose-gray max-w-none">
        {!! \Illuminate\Support\Str::markdown($article->content) !!}
      </div>

      {{-- Feedback --}}
      <div class="mt-12 pt-6 border-t border-gray-100">
        <p class="text-sm font-medium text-gray-700 mb-3">Was this article helpful?</p>
        <div class="flex gap-3" id="feedback-btns">
          <button onclick="sendFeedback({{ $article->id }}, true)"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-green-50 hover:border-green-300 transition">
            👍 Yes ({{ $article->helpful_count }})
          </button>
          <button onclick="sendFeedback({{ $article->id }}, false)"
            class="flex items-center gap-2 px-4 py-2 rounded-xl border border-gray-200 text-sm hover:bg-red-50 hover:border-red-300 transition">
            👎 No ({{ $article->not_helpful_count }})
          </button>
        </div>
        <div id="feedback-thanks" class="hidden text-sm text-green-600 mt-2">Thanks for your feedback!</div>
      </div>
    </article>

    {{-- Sidebar --}}
    <aside class="space-y-6">
      {{-- Related articles --}}
      @if($related->isNotEmpty())
      <div class="bg-gray-50 rounded-2xl p-5">
        <h3 class="font-semibold text-gray-700 mb-3 text-sm uppercase tracking-wide">Related</h3>
        <ul class="space-y-2">
          @foreach($related as $rel)
          <li>
            <a href="{{ route('help.article', $rel->slug) }}"
              class="text-sm text-amber-700 hover:underline leading-snug block">
              {{ $rel->title }}
            </a>
          </li>
          @endforeach
        </ul>
      </div>
      @endif

      {{-- Support CTA --}}
      <div class="bg-white border border-gray-100 rounded-2xl p-5 text-center">
        <p class="text-2xl mb-2">💬</p>
        <p class="text-sm font-medium text-gray-700 mb-1">Still stuck?</p>
        <p class="text-xs text-gray-400 mb-3">Our team replies within 2 hours</p>
        <a href="mailto:support@velour.app"
          class="block bg-amber-500 text-white rounded-xl py-2.5 text-sm font-semibold hover:bg-amber-600 transition">
          Contact Support
        </a>
      </div>
    </aside>

  </div>
</div>

<script>
async function sendFeedback(id, helpful) {
  await fetch(`/help/${id}/feedback`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content },
    body: JSON.stringify({ helpful })
  });
  document.getElementById('feedback-btns').style.display = 'none';
  document.getElementById('feedback-thanks').classList.remove('hidden');
}
</script>
@endsection
