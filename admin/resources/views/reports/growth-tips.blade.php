@extends('layouts.app')
@section('title', 'Growth Tips')
@section('page-title', 'Growth Tips')

@section('content')
<div class="max-w-3xl space-y-6" x-data="{ copied: null }">

    <div class="rounded-2xl border border-amber-200/70 dark:border-amber-800/50 bg-amber-50/50 dark:bg-amber-900/10 p-5 sm:p-6">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">Grow your salon</p>
                <h1 class="text-xl sm:text-2xl font-semibold text-heading mt-1">Actionable ideas to get more bookings</h1>
                <p class="text-sm text-muted mt-2">Share your booking link where clients already spend time — online and in-store.</p>
            </div>
            <a href="{{ route('go-live') }}" class="btn-outline btn-sm shrink-0 self-start">Go Live &amp; Share</a>
        </div>
    </div>

  <div class="rounded-xl border border-gray-200 dark:border-gray-800 bg-white/90 dark:bg-gray-900/50 p-4 sm:p-5"
       x-data>
    <p class="text-xs font-semibold uppercase tracking-wide text-muted mb-2">Your booking link</p>
    <div class="flex flex-col sm:flex-row gap-2">
      <input type="text" readonly value="{{ $bookingUrl }}"
             class="form-input flex-1 text-sm font-mono bg-gray-50 dark:bg-gray-950">
      <button type="button"
              class="btn-primary btn-sm shrink-0"
              @click="navigator.clipboard.writeText(@js($bookingUrl)); copied = 'booking'; setTimeout(() => copied = null, 2000)">
        <span x-text="copied === 'booking' ? 'Copied!' : 'Copy link'"></span>
      </button>
    </div>
  </div>

  <div class="space-y-4">
    @foreach([
      [
        'title' => 'Instagram bio link',
        'body' => 'Add your booking URL to your Instagram bio so profile visitors can book in one tap.',
        'action' => 'Paste your booking link in Instagram → Edit profile → Links.',
      ],
      [
        'title' => 'WhatsApp auto-reply',
        'body' => 'Send your booking link automatically when someone messages you outside business hours.',
        'action' => 'WhatsApp Business → Away message → include your booking URL.',
      ],
      [
        'title' => 'Window QR sticker',
        'body' => 'Convert walk-ins by placing a QR code on your salon window that opens your booking page.',
        'action' => 'Print a QR from Go Live & Share and place it at your entrance.',
        'link' => route('go-live'),
        'link_label' => 'Open Go Live',
      ],
      [
        'title' => 'Google Business profile',
        'body' => 'Add your booking URL to Google so searchers can book directly from your listing.',
        'action' => 'Google Business → Book → add your website or booking link.',
      ],
      [
        'title' => 'Website & SEO',
        'body' => 'Publish your salon website and make sure your booking page is easy to find from search.',
        'action' => 'Review your live site URL and meta details before sharing widely.',
        'link' => route('website-seo.index'),
        'link_label' => 'Website & SEO',
      ],
      [
        'title' => 'Email signature',
        'body' => 'Every staff email becomes a booking opportunity — add the link below your name.',
        'action' => 'Add “Book online: [your link]” to team email signatures.',
      ],
    ] as $tip)
    <article class="card p-5 border border-gray-100 dark:border-gray-800">
      <h2 class="text-base font-semibold text-heading">{{ $tip['title'] }}</h2>
      <p class="text-sm text-body mt-1.5">{{ $tip['body'] }}</p>
      <p class="text-xs text-muted mt-3">
        <span class="font-medium text-heading">How:</span> {{ $tip['action'] }}
      </p>
      @if(! empty($tip['link']))
      <a href="{{ $tip['link'] }}" class="inline-flex mt-3 text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline">
        {{ $tip['link_label'] }} →
      </a>
      @endif
    </article>
    @endforeach
  </div>

  <p class="text-xs text-muted text-center pb-2">
    Need deeper numbers? See
    <a href="{{ route('reports.analytics') }}" class="text-velour-600 dark:text-velour-400 font-medium hover:underline">Analytics</a>.
  </p>

</div>
@endsection
