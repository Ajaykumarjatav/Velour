@extends('layouts.app')
@section('title', 'Go Live & Share')
@section('page-title', 'Go Live & Share')

@push('styles')
<style>
  .stat-card   { @apply bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-5 shadow-sm hover:shadow-md transition-shadow; }
  .tab-btn     { @apply px-4 py-2 text-sm font-medium rounded-xl transition-all; }
  .tab-btn.on  { @apply bg-amber-500 text-white shadow-sm; }
  .tab-btn.off { @apply text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700; }
  .bar-fill    { transition: width .8s cubic-bezier(.4,0,.2,1); }
  .pulse-dot   { animation: pulse 2s cubic-bezier(.4,0,.6,1) infinite; }
  @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.4} }
  .copy-btn    { @apply inline-flex items-center gap-1.5 text-xs font-medium px-3 py-1.5 rounded-lg border transition-all; }
  .badge-high  { @apply bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-800 text-xs px-2 py-0.5 rounded-full font-medium; }
  .badge-med   { @apply bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400 border border-amber-100 dark:border-amber-800 text-xs px-2 py-0.5 rounded-full font-medium; }
  .badge-low   { @apply bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 border border-gray-200 dark:border-gray-600 text-xs px-2 py-0.5 rounded-full font-medium; }
  .toggle-track{ @apply relative inline-flex h-6 w-11 items-center rounded-full transition-colors cursor-pointer; }
  .toggle-thumb{ @apply inline-block h-4 w-4 transform rounded-full bg-white shadow-md transition-transform; }
  .sparkline   { fill: none; stroke: #f59e0b; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
  .embed-code  { @apply font-mono text-xs bg-gray-900 text-green-300 rounded-xl p-4 overflow-x-auto whitespace-pre select-all leading-relaxed; }
  [x-cloak]   { display:none !important; }
</style>
@endpush

@section('content')
{{-- ══════════════════════════════════════════════════════════════════════════
     ALPINE ROOT — fetches all live data from the API on mount
     ══════════════════════════════════════════════════════════════════════════ --}}
<div
  x-data="goLivePage()"
  x-init="init()"
  class="max-w-7xl mx-auto px-4 sm:px-6 pb-16 space-y-8"
>

  {{-- ── HEADER ─────────────────────────────────────────────────────────── --}}
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
    <div>
      <div class="flex items-center gap-2">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white" style="font-family:'Playfair Display',serif">
          Go Live &amp; Share
        </h1>
        <span x-show="salon.online_booking_enabled"
          class="flex items-center gap-1.5 bg-green-50 border border-green-200 text-green-700 text-xs font-semibold px-2.5 py-1 rounded-full">
          <span class="w-1.5 h-1.5 bg-green-500 rounded-full pulse-dot"></span>
          Live
        </span>
        <span x-show="!salon.online_booking_enabled" x-cloak
          class="flex items-center gap-1.5 bg-gray-100 border border-gray-200 text-gray-500 text-xs font-semibold px-2.5 py-1 rounded-full">
          <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
          Offline
        </span>
      </div>
      <p class="text-sm text-gray-400 dark:text-gray-500 mt-0.5">
        Share your booking page · track visits · grow your clientele
      </p>
    </div>

    {{-- Master on/off toggle --}}
    <div class="flex items-center gap-3 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl px-5 py-3 shadow-sm">
      <div>
        <p class="text-sm font-semibold text-gray-800 dark:text-white">Online Booking</p>
        <p class="text-xs text-gray-400 dark:text-gray-500" x-text="salon.online_booking_enabled ? 'Clients can book right now' : 'Booking page is hidden'"></p>
      </div>
      <button
        @click="toggleBooking()"
        :class="salon.online_booking_enabled ? 'bg-green-500' : 'bg-gray-300'"
        class="toggle-track flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-2"
        :aria-checked="salon.online_booking_enabled"
        role="switch"
        aria-label="Toggle online booking"
        :disabled="saving"
      >
        <span
          :class="salon.online_booking_enabled ? 'translate-x-6' : 'translate-x-1'"
          class="toggle-thumb"
        ></span>
      </button>
    </div>
  </div>

  {{-- ── READINESS CHECKLIST ─────────────────────────────────────────────── --}}
  <div x-show="checklist.score < 100" x-cloak
       class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl overflow-hidden shadow-sm">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-50 dark:border-gray-700">
      <div class="flex items-center gap-3">
        <span class="text-lg">🚀</span>
        <div>
          <h2 class="font-semibold text-gray-800 dark:text-white text-sm">Go-Live Readiness</h2>
          <p class="text-xs text-gray-400 dark:text-gray-500" x-text="`${checklist.done} of ${checklist.total} steps complete`"></p>
        </div>
      </div>
      {{-- Progress pill --}}
      <div class="flex items-center gap-3">
        <div class="hidden sm:block w-36 bg-gray-100 dark:bg-gray-700 rounded-full h-2">
          <div class="bar-fill bg-amber-400 h-2 rounded-full"
               :style="`width: ${checklist.score}%`"></div>
        </div>
        <span class="text-sm font-bold text-amber-600" x-text="`${checklist.score}%`"></span>
      </div>
    </div>
    <ul class="divide-y divide-gray-50 dark:divide-gray-700 px-0">
      <template x-for="item in checklist.items" :key="item.key">
        <li class="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
          {{-- Status icon --}}
          <div :class="item.done
              ? 'bg-green-100 dark:bg-green-900/40 text-green-600 dark:text-green-400'
              : 'bg-gray-100 dark:bg-gray-700 text-gray-400 dark:text-gray-500'"
            class="w-7 h-7 rounded-full flex items-center justify-center flex-shrink-0 text-sm">
            <span x-text="item.done ? '✓' : '·'"></span>
          </div>
          {{-- Label + tip --}}
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="item.label"
               :class="item.done ? 'line-through text-gray-400 dark:text-gray-600' : ''"></p>
            <p class="text-xs text-gray-400 dark:text-gray-500 truncate" x-text="item.tip" x-show="!item.done"></p>
          </div>
          {{-- Priority badge --}}
          <span x-show="!item.done"
            :class="{
              'badge-high': item.priority === 'high',
              'badge-med':  item.priority === 'medium',
              'badge-low':  item.priority === 'low',
            }"
            x-text="item.priority"></span>
          {{-- Fix link --}}
          <a x-show="!item.done" :href="item.link"
            class="text-xs text-amber-600 font-semibold hover:underline flex-shrink-0">
            Fix →
          </a>
        </li>
      </template>
    </ul>
  </div>

  {{-- ── TOP KPI CARDS ───────────────────────────────────────────────────── --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

    {{-- Visits --}}
    <div class="stat-card">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Visits</p>
        <span class="text-xl">👁</span>
      </div>
      <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.link_visits ?? '—'"></p>
      <p class="text-xs mt-1 font-medium"
         :class="(stats.visit_trend ?? 0) >= 0 ? 'text-green-600' : 'text-red-500'"
         x-text="(stats.visit_trend ?? 0) !== 0
           ? ((stats.visit_trend > 0 ? '▲ ' : '▼ ') + Math.abs(stats.visit_trend) + '% vs last month')
           : 'vs last month'">
      </p>
    </div>

    {{-- Conversion rate --}}
    <div class="stat-card">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Conversion</p>
        <span class="text-xl">🎯</span>
      </div>
      <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="(stats.conversion_rate ?? 0) + '%'"></p>
      <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="(stats.conversions ?? 0) + ' bookers this month'"></p>
    </div>

    {{-- Online bookings --}}
    <div class="stat-card">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Online Bookings</p>
        <span class="text-xl">📅</span>
      </div>
      <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="stats.online_bookings ?? '—'"></p>
      <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">from your booking page</p>
    </div>

    {{-- Online revenue --}}
    <div class="stat-card">
      <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Online Revenue</p>
        <span class="text-xl">💷</span>
      </div>
      <p class="text-2xl font-bold text-gray-900 dark:text-white"
         x-text="'£' + Number(stats.online_revenue ?? 0).toLocaleString('en-GB', {minimumFractionDigits:2})">
      </p>
      <p class="text-xs text-gray-400 dark:text-gray-500 mt-1" x-text="stats.period ?? ''"></p>
    </div>

  </div>

  {{-- ── SALON PHOTOS ────────────────────────────────────────────────────── --}}
  <div x-data="salonPhotos()" x-init="init()" class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <span class="text-lg">🖼</span>
        <div>
          <h2 class="font-semibold text-gray-800 dark:text-white">Salon Photos</h2>
          <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">Showcase your salon — up to 15 photos</p>
        </div>
      </div>
      <span class="text-xs font-medium px-2.5 py-1 rounded-full"
            :class="photos.length >= 15 ? 'bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400'"
            x-text="photos.length + ' / 15'"></span>
    </div>

    <div class="p-6">
      {{-- Upload zone --}}
      <div x-show="photos.length < 15"
           class="relative border-2 border-dashed border-gray-200 dark:border-gray-600 rounded-2xl p-6 text-center hover:border-amber-400 dark:hover:border-amber-500 transition-colors cursor-pointer group"
           @click="$refs.fileInput.click()"
           @dragover.prevent="dragging = true"
           @dragleave.prevent="dragging = false"
           @drop.prevent="handleDrop($event)"
           :class="dragging ? 'border-amber-400 dark:border-amber-500 bg-amber-50 dark:bg-amber-900/10' : ''">
        <input type="file" x-ref="fileInput" class="hidden" accept="image/jpeg,image/png,image/webp" multiple
               @change="handleFiles($event.target.files)">
        <div class="flex flex-col items-center gap-2 pointer-events-none">
          <span class="text-3xl">📷</span>
          <p class="text-sm font-medium text-gray-600 dark:text-gray-300 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
            Click or drag photos here
          </p>
          <p class="text-xs text-gray-400 dark:text-gray-500">JPG, PNG, WebP · max 5 MB each · <span x-text="15 - photos.length"></span> slots remaining</p>
        </div>
      </div>

      {{-- Upload progress --}}
      <div x-show="uploading" x-cloak class="mt-3">
        <div class="flex items-center gap-2 text-xs text-amber-600 dark:text-amber-400">
          <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
          </svg>
          <span x-text="'Uploading ' + uploadQueue + ' photo(s)…'"></span>
        </div>
      </div>

      {{-- Error message --}}
      <p x-show="uploadError" x-cloak x-text="uploadError"
         class="mt-2 text-xs text-red-500 dark:text-red-400 font-medium"></p>

      {{-- Photo grid --}}
      <div x-show="photos.length > 0" class="mt-5 grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-5 gap-3">
        <template x-for="photo in photos" :key="photo.id">
          <div class="relative group aspect-square rounded-xl overflow-hidden bg-gray-100 dark:bg-gray-700 shadow-sm">
            <img :src="photo.url" :alt="'Salon photo'" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
              <button @click.stop="deletePhoto(photo.id)"
                      class="w-8 h-8 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-sm transition-colors shadow-lg"
                      title="Delete photo">
                ✕
              </button>
            </div>
          </div>
        </template>
      </div>

      {{-- Empty state --}}
      <div x-show="photos.length === 0" class="mt-4 text-center text-gray-300 dark:text-gray-600 py-4">
        <p class="text-sm">No photos yet — add some to make your booking page stand out</p>
      </div>
    </div>
  </div>

  {{-- ── MAIN CONTENT GRID ───────────────────────────────────────────────── --}}
  <div class="grid lg:grid-cols-5 gap-6">

    {{-- LEFT COL (3/5) — Booking link + social sharing + embed --}}
    <div class="lg:col-span-3 space-y-6">

      {{-- ── YOUR BOOKING LINK ─────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center gap-2">
          <span class="text-lg">🔗</span>
          <h2 class="font-semibold text-gray-800 dark:text-white">Your Booking Link</h2>
        </div>
        <div class="p-6 space-y-4">
          {{-- URL bar --}}
          <div class="flex gap-2">
            <div class="flex-1 flex items-center bg-gray-50 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl px-4 py-3 gap-2 min-w-0">
              <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
              </svg>
              <a :href="bookingUrl" target="_blank" rel="noopener"
                 class="text-sm text-amber-700 dark:text-amber-400 font-medium truncate hover:underline"
                 x-text="bookingUrl"></a>
            </div>
            <button @click="copyUrl(bookingUrl, 'main')"
              class="copy-btn border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-300 hover:text-amber-600 flex-shrink-0">
              <span x-text="copied.main ? '✅ Copied' : '📋 Copy'"></span>
            </button>
            <a :href="bookingUrl" target="_blank" rel="noopener"
              class="copy-btn border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-300 hover:text-amber-600 flex-shrink-0">
              ↗ Preview
            </a>
          </div>

          {{-- UTM link builder --}}
          <details class="group">
            <summary class="text-xs text-gray-400 dark:text-gray-500 cursor-pointer hover:text-amber-600 transition select-none list-none flex items-center gap-1">
              <svg class="w-3 h-3 group-open:rotate-90 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              Advanced: UTM link builder
            </summary>
            <div class="mt-3 grid sm:grid-cols-3 gap-2" x-data="{ source:'', medium:'', campaign:'' }">
              <input x-model="source"   placeholder="utm_source (e.g. instagram)"  class="border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-amber-300 outline-none">
              <input x-model="medium"   placeholder="utm_medium (e.g. bio)"        class="border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-amber-300 outline-none">
              <input x-model="campaign" placeholder="utm_campaign (e.g. summer25)"  class="border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-200 rounded-lg px-3 py-2 text-xs focus:ring-2 focus:ring-amber-300 outline-none">
              <div class="sm:col-span-3 flex gap-2 mt-1">
                <div class="flex-1 bg-gray-50 dark:bg-gray-700 border border-gray-100 dark:border-gray-600 rounded-lg px-3 py-2 text-xs text-gray-500 dark:text-gray-400 truncate font-mono"
                     x-text="bookingUrl + (source||medium||campaign ? '?utm_source='+encodeURIComponent(source)+'&utm_medium='+encodeURIComponent(medium)+'&utm_campaign='+encodeURIComponent(campaign) : '')">
                </div>
                <button @click="copyUrl(bookingUrl + '?utm_source='+encodeURIComponent(source)+'&utm_medium='+encodeURIComponent(medium)+'&utm_campaign='+encodeURIComponent(campaign), 'utm')"
                  class="copy-btn border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-300 hover:text-amber-600 flex-shrink-0">
                  <span x-text="copied.utm ? '✅' : '📋 Copy'"></span>
                </button>
              </div>
            </div>
          </details>
        </div>
      </div>

      {{-- ── SOCIAL SHARING ────────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-lg">📣</span>
            <h2 class="font-semibold text-gray-800 dark:text-white">Share on Social</h2>
          </div>
          <span class="text-xs text-gray-400 dark:text-gray-500">Click counts this month</span>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">

            <template x-for="channel in shareChannels" :key="channel.id">
              <a
                :href="channel.href"
                target="_blank" rel="noopener noreferrer"
                @click="trackClick(channel.id)"
                class="flex flex-col items-center gap-2 p-4 rounded-2xl border border-gray-100 hover:border-opacity-60 transition-all hover:shadow-md hover:-translate-y-0.5 cursor-pointer group"
                :style="`background:${channel.bg}; border-color:${channel.border};`"
              >
                <span class="text-2xl" x-text="channel.icon"></span>
                <span class="text-xs font-semibold" :style="`color:${channel.color};`" x-text="channel.label"></span>
                <span class="text-[10px] font-medium px-2 py-0.5 rounded-full"
                  :style="`background:${channel.border}; color:${channel.color};`"
                  x-text="(shareClicks[channel.id] || 0) + ' clicks'"></span>
              </a>
            </template>

          </div>

          {{-- Copy link row --}}
          <div class="mt-4 flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-xl px-4 py-3">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
              <span>🔗</span>
              <span class="font-medium">Copy booking link</span>
            </div>
            <button @click="copyUrl(bookingUrl, 'social'); trackClick('copy_link')"
              class="copy-btn border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:border-amber-300 hover:text-amber-600">
              <span x-text="copied.social ? '✅ Copied!' : '📋 Copy'"></span>
            </button>
          </div>
        </div>
      </div>

      {{-- ── EMBED WIDGET ──────────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center gap-2">
          <span class="text-lg">🖥</span>
          <h2 class="font-semibold text-gray-800 dark:text-white">Embed on Your Website</h2>
        </div>
        <div class="p-6 space-y-4">
          {{-- Tab switcher --}}
          <div class="flex gap-1.5 bg-gray-100 dark:bg-gray-700 p-1 rounded-xl w-fit">
            <button @click="embedTab = 'iframe'" :class="embedTab === 'iframe' ? 'tab-btn on' : 'tab-btn off'" class="tab-btn">iFrame</button>
            <button @click="embedTab = 'js'"     :class="embedTab === 'js'    ? 'tab-btn on' : 'tab-btn off'" class="tab-btn">JavaScript</button>
            <button @click="embedTab = 'react'"  :class="embedTab === 'react' ? 'tab-btn on' : 'tab-btn off'" class="tab-btn">React</button>
          </div>

          {{-- Code display --}}
          <div class="relative group">
            <pre class="embed-code" x-show="embedTab === 'iframe'" x-text="embedCodes.iframe"></pre>
            <pre class="embed-code" x-show="embedTab === 'js'"     x-text="embedCodes.js"    x-cloak></pre>
            <pre class="embed-code" x-show="embedTab === 'react'"  x-text="embedCodes.react"  x-cloak></pre>
            <button
              @click="copyUrl(embedCodes[embedTab], 'embed'); trackClick('embed')"
              class="absolute top-3 right-3 bg-gray-800 hover:bg-gray-700 text-gray-300 hover:text-white text-xs px-3 py-1.5 rounded-lg transition opacity-0 group-hover:opacity-100">
              <span x-text="copied.embed ? '✅ Copied' : '📋 Copy'"></span>
            </button>
          </div>

          <p class="text-xs text-gray-400 dark:text-gray-500">
            Drop this snippet anywhere on your website. The widget automatically matches your salon's branding.
          </p>
        </div>
      </div>

    </div>

    {{-- RIGHT COL (2/5) — QR + settings + analytics summary --}}
    <div class="lg:col-span-2 space-y-6">

      {{-- ── QR CODE ───────────────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center gap-2">
          <span class="text-lg">📱</span>
          <h2 class="font-semibold text-gray-800 dark:text-white">QR Code</h2>
        </div>
        <div class="p-5 flex flex-col items-center gap-4">
          <div class="bg-white border-2 border-gray-100 dark:border-gray-600 rounded-2xl p-3 shadow-inner">
            <img :src="qrUrl" alt="Booking QR code" class="w-40 h-40 rounded-xl" loading="lazy">
          </div>
          <div class="w-full space-y-2">
            <a :href="qrUrl + '&format=png'" download="velour-booking-qr.png"
              @click="trackClick('qr_download')"
              class="flex items-center justify-center gap-2 w-full bg-gray-900 dark:bg-gray-700 text-white text-sm font-semibold py-2.5 rounded-xl hover:bg-gray-700 dark:hover:bg-gray-600 transition">
              ⬇ Download PNG
            </a>
            <p class="text-xs text-gray-400 dark:text-gray-500 text-center">Print on receipts, menus, windows &amp; marketing materials</p>
          </div>
        </div>
      </div>

      {{-- ── BOOKING SETTINGS ──────────────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-lg">⚙️</span>
            <h2 class="font-semibold text-gray-800 dark:text-white">Booking Settings</h2>
          </div>
          <span x-show="saving" class="text-xs text-amber-600 font-medium animate-pulse">Saving…</span>
          <span x-show="saveOk" x-cloak class="text-xs text-green-600 font-medium">✅ Saved</span>
        </div>
        <div class="divide-y divide-gray-50 dark:divide-gray-700">

          <template x-for="setting in bookingSettings" :key="setting.key">
            <div class="flex items-center justify-between px-5 py-3.5">
              <div class="flex-1 pr-4">
                <p class="text-sm font-medium text-gray-800 dark:text-gray-200" x-text="setting.label"></p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5" x-text="setting.description"></p>
              </div>
              <template x-if="setting.type === 'toggle'">
                <button
                  @click="saveSetting(setting.key, !salon[setting.key])"
                  :class="salon[setting.key] ? 'bg-green-500' : 'bg-gray-300 dark:bg-gray-600'"
                  class="toggle-track flex-shrink-0 focus:outline-none focus:ring-2 focus:ring-amber-400 focus:ring-offset-1"
                  :aria-checked="salon[setting.key]"
                  role="switch"
                  :disabled="saving"
                >
                  <span :class="salon[setting.key] ? 'translate-x-6' : 'translate-x-1'" class="toggle-thumb"></span>
                </button>
              </template>
              <template x-if="setting.type === 'number'">
                <input
                  type="number"
                  :value="salon[setting.key]"
                  @change="saveSetting(setting.key, $event.target.valueAsNumber)"
                  :min="setting.min" :max="setting.max"
                  class="w-20 text-right text-sm border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-amber-300 outline-none"
                >
              </template>
            </div>
          </template>

        </div>
      </div>

      {{-- ── TRAFFIC SOURCES (top 5) ───────────────────────────────────── --}}
      <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span class="text-lg">📊</span>
            <h2 class="font-semibold text-gray-800 dark:text-white">Traffic Sources</h2>
          </div>
          <span class="text-xs text-gray-400 dark:text-gray-500">Last 30 days</span>
        </div>
        <div class="p-5 space-y-3" x-show="sources.length > 0">
          <template x-for="src in sources.slice(0,5)" :key="src.source">
            <div class="space-y-1">
              <div class="flex items-center justify-between text-xs">
                <span class="flex items-center gap-1.5 font-medium text-gray-700 dark:text-gray-300">
                  <span x-text="src.icon"></span>
                  <span x-text="src.label"></span>
                </span>
                <div class="flex items-center gap-2">
                  <span class="text-gray-400 dark:text-gray-500" x-text="src.visits + ' visits'"></span>
                  <span class="font-semibold text-amber-600" x-text="src.conversion_rate + '% conv.'"></span>
                </div>
              </div>
              <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-1.5">
                <div class="bar-fill bg-amber-400 h-1.5 rounded-full"
                     :style="`width: ${src.percentage}%`"></div>
              </div>
            </div>
          </template>
        </div>
        <div class="px-5 py-8 text-center text-gray-300 dark:text-gray-600" x-show="sources.length === 0 && !loading">
          <p class="text-3xl mb-2">📭</p>
          <p class="text-sm">No traffic yet — start sharing your link!</p>
        </div>
        <div class="px-5 py-6 text-center" x-show="loading">
          <div class="inline-flex items-center gap-2 text-xs text-gray-400">
            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
            </svg>
            Loading…
          </div>
        </div>
      </div>

    </div>
  </div>

  {{-- ── 30-DAY TREND CHART ──────────────────────────────────────────────── --}}
  <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div class="flex items-center gap-2">
        <span class="text-lg">📈</span>
        <h2 class="font-semibold text-gray-800 dark:text-white">30-Day Trend</h2>
      </div>
      <div class="flex gap-3 text-xs text-gray-600 dark:text-gray-400">
        <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 bg-amber-400 rounded-full inline-block"></span>Page Visits</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-1.5 bg-violet-500 rounded-full inline-block"></span>Online Bookings</span>
      </div>
    </div>
    <div class="p-6">

      {{-- SVG line chart --}}
      <div class="relative h-48 w-full" x-show="trend.length > 0">
        <svg class="w-full h-full" viewBox="0 0 800 180" preserveAspectRatio="none">
          <line x1="0" y1="45"  x2="800" y2="45"  stroke="currentColor" class="text-gray-100 dark:text-gray-700" stroke-width="1"/>
          <line x1="0" y1="90"  x2="800" y2="90"  stroke="currentColor" class="text-gray-100 dark:text-gray-700" stroke-width="1"/>
          <line x1="0" y1="135" x2="800" y2="135" stroke="currentColor" class="text-gray-100 dark:text-gray-700" stroke-width="1"/>
          <line x1="0" y1="180" x2="800" y2="180" stroke="currentColor" class="text-gray-100 dark:text-gray-700" stroke-width="1"/>
          <polyline :points="buildPolyline(trend, 'visits', 800, 180)" class="sparkline" stroke="#f59e0b" stroke-width="2.5" fill="none"/>
          <polyline :points="buildPolyline(trend, 'bookings', 800, 180)" class="sparkline" stroke="#8b5cf6" stroke-width="2.5" fill="none"/>
        </svg>
        <div class="flex justify-between mt-1 px-1">
          <template x-for="(day, i) in trend" :key="i">
            <span x-show="i % 5 === 0 || i === trend.length - 1"
                  class="text-[10px] text-gray-300 dark:text-gray-600" x-text="day.label"></span>
          </template>
        </div>
      </div>

      {{-- Empty state --}}
      <div class="h-48 flex items-center justify-center text-gray-300 dark:text-gray-600" x-show="trend.length === 0 && !loading">
        <div class="text-center">
          <p class="text-4xl mb-2">📉</p>
          <p class="text-sm">No visit data yet</p>
        </div>
      </div>

      {{-- Summary row --}}
      <div class="grid grid-cols-3 gap-4 mt-6 pt-4 border-t border-gray-50 dark:border-gray-700" x-show="trend.length > 0">
        <div class="text-center">
          <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="trend.reduce((a,d) => a + d.visits, 0)"></p>
          <p class="text-xs text-gray-400 dark:text-gray-500">Total visits (30d)</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="trend.reduce((a,d) => a + d.bookings, 0)"></p>
          <p class="text-xs text-gray-400 dark:text-gray-500">Online bookings</p>
        </div>
        <div class="text-center">
          <p class="text-lg font-bold text-gray-900 dark:text-white" x-text="
            (() => {
              const v = trend.reduce((a,d) => a + d.visits, 0);
              const b = trend.reduce((a,d) => a + d.bookings, 0);
              return v > 0 ? (b/v*100).toFixed(1) + '%' : '0%';
            })()
          "></p>
          <p class="text-xs text-gray-400 dark:text-gray-500">Avg conversion</p>
        </div>
      </div>

    </div>
  </div>

  {{-- ── DEVICE SPLIT ────────────────────────────────────────────────────── --}}
  <div class="grid sm:grid-cols-2 gap-6">

    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-50 dark:border-gray-700 flex items-center gap-2">
        <span class="text-lg">📱</span>
        <h2 class="font-semibold text-gray-800 dark:text-white">Visitors by Device</h2>
      </div>
      <div class="p-6 space-y-3" x-show="devices.length > 0">
        <template x-for="dev in devices" :key="dev.device">
          <div class="flex items-center gap-3">
            <span class="text-lg w-7 text-center" x-text="dev.device === 'mobile' ? '📱' : dev.device === 'desktop' ? '💻' : '❓'"></span>
            <div class="flex-1">
              <div class="flex justify-between text-xs mb-1">
                <span class="font-medium text-gray-700 dark:text-gray-300 capitalize" x-text="dev.device"></span>
                <span class="text-gray-500 dark:text-gray-400" x-text="dev.count + ' (' + dev.percentage + '%)'"></span>
              </div>
              <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-2">
                <div class="bar-fill h-2 rounded-full"
                     :class="dev.device === 'mobile' ? 'bg-violet-400' : 'bg-amber-400'"
                     :style="`width:${dev.percentage}%`"></div>
              </div>
            </div>
          </div>
        </template>
      </div>
      <div class="px-6 py-8 text-center text-gray-300 dark:text-gray-600" x-show="devices.length === 0 && !loading">
        <p class="text-sm">No device data yet</p>
      </div>
    </div>

    {{-- ── PRO TIPS ──────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl border border-amber-100 dark:border-amber-800/50 shadow-sm overflow-hidden">
      <div class="px-6 py-4 border-b border-amber-100 dark:border-amber-800/50 flex items-center gap-2">
        <span class="text-lg">💡</span>
        <h2 class="font-semibold text-amber-800 dark:text-amber-300">Growth Tips</h2>
      </div>
      <ul class="p-6 space-y-3">
        <li class="flex items-start gap-3 text-sm text-amber-900 dark:text-amber-200">
          <span class="flex-shrink-0 w-5 h-5 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center text-[10px] font-bold mt-0.5">1</span>
          <span><strong>Instagram bio link</strong> — Add your booking URL to your Instagram bio. It's the #1 driver of online bookings for salons.</span>
        </li>
        <li class="flex items-start gap-3 text-sm text-amber-900 dark:text-amber-200">
          <span class="flex-shrink-0 w-5 h-5 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center text-[10px] font-bold mt-0.5">2</span>
          <span><strong>WhatsApp auto-reply</strong> — Set your booking link as an auto-reply so clients can book without waiting for a response.</span>
        </li>
        <li class="flex items-start gap-3 text-sm text-amber-900 dark:text-amber-200">
          <span class="flex-shrink-0 w-5 h-5 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center text-[10px] font-bold mt-0.5">3</span>
          <span><strong>Window QR sticker</strong> — Print your QR code and put it in your window. Walk-bys become bookings.</span>
        </li>
        <li class="flex items-start gap-3 text-sm text-amber-900 dark:text-amber-200">
          <span class="flex-shrink-0 w-5 h-5 bg-amber-200 dark:bg-amber-800 rounded-full flex items-center justify-center text-[10px] font-bold mt-0.5">4</span>
          <span><strong>Google Business Profile</strong> — Add your booking URL to your Google profile so clients can book directly from search results.</span>
        </li>
      </ul>
    </div>

  </div>

</div>
@endsection

@push('scripts')
<script>
// ── Pre-loaded server data ─────────────────────────────────────────────────
const _serverData = {
  bookingUrl:  "{{ $bookingUrl }}",
  qrUrl:       "{{ $qrUrl }}",
  embedCodes:  @json($embedCodes),
  shareClicks: @json($shareclicks),
  socialLinks: @json($salon->social_links ?? []),
  salon: {
    slug:                    "{{ $salon->slug }}",
    online_booking_enabled:  {{ $salon->online_booking_enabled  ? 'true' : 'false' }},
    new_client_booking_enabled: {{ $salon->new_client_booking_enabled ? 'true' : 'false' }},
    deposit_required:        {{ $salon->deposit_required        ? 'true' : 'false' }},
    instant_confirmation:    {{ $salon->instant_confirmation    ? 'true' : 'false' }},
    deposit_percentage:      {{ $salon->deposit_percentage      ?? 20  }},
    booking_advance_days:    {{ $salon->booking_advance_days    ?? 60  }},
    cancellation_hours:      {{ $salon->cancellation_hours      ?? 24  }},
  },
  // Pre-loaded server-side stats so there's no flash
  stats: {
    link_visits:     {{ $thisMonthVisits }},
    conversions:     {{ $thisMonthConversions }},
    conversion_rate: {{ $thisMonthVisits > 0 ? round(($thisMonthConversions/$thisMonthVisits)*100,1) : 0 }},
    online_bookings: {{ $onlineBookings }},
    online_revenue:  0,
    visit_trend:     0,
    period:          "{{ now()->format('F Y') }}",
  },
  checklist: @json($checklist),
};

// ── Alpine component ───────────────────────────────────────────────────────
function goLivePage() {
  return {
    // State
    loading:     true,
    saving:      false,
    saveOk:      false,
    salon:       { ..._serverData.salon },
    stats:       { ..._serverData.stats },
    checklist:   { ..._serverData.checklist },
    sources:     [],
    trend:       [],
    devices:     [],
    shareClicks: { ..._serverData.shareClicks },
    embedCodes:  { ..._serverData.embedCodes },
    bookingUrl:  _serverData.bookingUrl,
    qrUrl:       _serverData.qrUrl,
    embedTab:    'iframe',
    copied:      { main: false, utm: false, social: false, embed: false },

    // Share channels config
    shareChannels: [
      {
        id: 'instagram',
        label: 'Instagram',
        icon: '📸',
        bg: '#fff0f7',
        border: '#fcd',
        color: '#be185d',
        get href() {
          const profile = (_serverData.socialLinks || {}).instagram;
          return profile || `https://www.instagram.com/`;
        }
      },
      {
        id: 'whatsapp',
        label: 'WhatsApp',
        icon: '💬',
        bg: '#f0fdf4',
        border: '#bbf7d0',
        color: '#15803d',
        get href() {
          const profile = (_serverData.socialLinks || {}).whatsapp;
          if (profile) return profile;
          const url = _serverData.bookingUrl;
          const text = encodeURIComponent(`Book your next appointment with us! 💅\n${url}`);
          return `https://wa.me/?text=${text}`;
        }
      },
      {
        id: 'facebook',
        label: 'Facebook',
        icon: '👍',
        bg: '#eff6ff',
        border: '#bfdbfe',
        color: '#1d4ed8',
        get href() {
          const profile = (_serverData.socialLinks || {}).facebook;
          return profile || `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(_serverData.bookingUrl)}`;
        }
      },
      {
        id: 'google',
        label: 'Google',
        icon: '🔍',
        bg: '#fff7ed',
        border: '#fed7aa',
        color: '#c2410c',
        get href() {
          const profile = (_serverData.socialLinks || {}).google;
          return profile || `https://business.google.com/`;
        }
      },
      {
        id: 'tiktok',
        label: 'TikTok',
        icon: '🎵',
        bg: '#fdf4ff',
        border: '#e9d5ff',
        color: '#7e22ce',
        get href() {
          const profile = (_serverData.socialLinks || {}).tiktok;
          return profile || `https://www.tiktok.com/`;
        }
      },
      {
        id: 'email',
        label: 'Email',
        icon: '✉️',
        bg: '#f8fafc',
        border: '#e2e8f0',
        color: '#334155',
        get href() {
          const url = _serverData.bookingUrl;
          const sub = encodeURIComponent('Book Your Appointment Online');
          const body = encodeURIComponent(`Hi!\n\nYou can now book your appointment online, 24/7:\n${url}\n\nSee you soon!`);
          return `mailto:?subject=${sub}&body=${body}`;
        }
      },
    ],

    // Booking settings definition (drives the settings panel)
    bookingSettings: [
      { key: 'online_booking_enabled',     type: 'toggle', label: 'Online booking',          description: 'Allow clients to book via your link & widget' },
      { key: 'new_client_booking_enabled', type: 'toggle', label: 'New client bookings',      description: 'Accept bookings from first-time clients' },
      { key: 'deposit_required',           type: 'toggle', label: 'Require deposit',          description: 'Charge deposit to reduce no-shows' },
      { key: 'deposit_percentage',         type: 'number', label: 'Deposit %',               description: 'Percentage of service cost charged upfront', min: 1, max: 100 },
      { key: 'instant_confirmation',       type: 'toggle', label: 'Instant confirmation',     description: 'Confirm bookings automatically (no approval needed)' },
      { key: 'booking_advance_days',       type: 'number', label: 'Book up to (days)',        description: 'How far ahead clients can schedule', min: 1, max: 365 },
      { key: 'cancellation_hours',         type: 'number', label: 'Cancel notice (hours)',    description: 'Minimum notice for free cancellation', min: 0, max: 168 },
    ],

    // ── Lifecycle ────────────────────────────────────────────────────────
    async init() {
      await Promise.allSettled([
        this.loadStats(),
        this.loadSources(),
        this.loadTrend(),
        this.loadDevices(),
        this.loadChecklist(),
      ]);
      this.loading = false;
    },

    // ── API fetchers ─────────────────────────────────────────────────────
    async api(path) {
      const res = await fetch(`/api/v1/salon/${path}`, {
        headers: {
          'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        credentials: 'same-origin',
      });
      if (!res.ok) throw new Error(`API ${path} failed: ${res.status}`);
      return res.json();
    },

    async loadStats() {
      try {
        const d = await this.api('share/stats');
        if (d.data) Object.assign(this.stats, d.data);
      } catch(e) { console.warn('stats:', e); }
    },

    async loadSources() {
      try {
        const d = await this.api('share/sources');
        this.sources = d.data?.sources ?? [];
      } catch(e) { console.warn('sources:', e); }
    },

    async loadTrend() {
      try {
        const d = await this.api('share/trend');
        this.trend = d.data?.trend ?? [];
      } catch(e) { console.warn('trend:', e); }
    },

    async loadDevices() {
      try {
        const d = await this.api('share/devices');
        this.devices = d.data?.devices ?? [];
      } catch(e) { console.warn('devices:', e); }
    },

    async loadChecklist() {
      try {
        const d = await this.api('share/checklist');
        if (d.data) this.checklist = d.data;
      } catch(e) { console.warn('checklist:', e); }
    },

    // ── Actions ──────────────────────────────────────────────────────────
    async toggleBooking() {
      await this.saveSetting('online_booking_enabled', !this.salon.online_booking_enabled);
    },

    async saveSetting(key, value) {
      this.saving = true;
      this.saveOk = false;
      this.salon[key] = value;          // optimistic update
      try {
        await fetch('/api/v1/salon/share/customise', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
          },
          credentials: 'same-origin',
          body: JSON.stringify({ [key]: value }),
        });
        this.saveOk = true;
        setTimeout(() => this.saveOk = false, 3000);
      } catch(e) {
        this.salon[key] = !value;       // rollback
        console.error('save failed:', e);
      } finally {
        this.saving = false;
      }
    },

    async trackClick(platform) {
      this.shareClicks[platform] = (this.shareClicks[platform] ?? 0) + 1; // optimistic
      try {
        await fetch('/api/v1/salon/share/track-click', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept':        'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]').content,
          },
          credentials: 'same-origin',
          body: JSON.stringify({ platform }),
        });
      } catch(e) { /* non-critical */ }
    },

    async copyUrl(text, key) {
      try {
        await navigator.clipboard.writeText(text);
        this.copied[key] = true;
        setTimeout(() => this.copied[key] = false, 2500);
      } catch(e) {
        // Fallback for older browsers
        const el = document.createElement('textarea');
        el.value = text;
        el.style.position = 'fixed';
        el.style.opacity  = '0';
        document.body.appendChild(el);
        el.focus(); el.select();
        document.execCommand('copy');
        document.body.removeChild(el);
        this.copied[key] = true;
        setTimeout(() => this.copied[key] = false, 2500);
      }
    },

    // ── Chart helpers ────────────────────────────────────────────────────
    buildPolyline(data, field, width, height) {
      if (!data || data.length === 0) return '';
      const vals  = data.map(d => d[field]);
      const max   = Math.max(...vals, 1);
      const pad   = 10;
      const step  = (width - pad * 2) / (data.length - 1);
      return vals.map((v, i) => {
        const x = pad + i * step;
        const y = height - pad - ((v / max) * (height - pad * 2));
        return `${x},${y}`;
      }).join(' ');
    },
  };
}
</script>

<script>
function salonPhotos() {
  return {
    photos:      @json($photos),
    uploading:   false,
    uploadQueue: 0,
    uploadError: '',
    dragging:    false,

    init() {},

    handleDrop(e) {
      this.dragging = false;
      this.handleFiles(e.dataTransfer.files);
    },

    async handleFiles(files) {
      this.uploadError = '';
      const allowed = 15 - this.photos.length;
      const toUpload = Array.from(files).slice(0, allowed);

      if (files.length > allowed) {
        this.uploadError = `Only ${allowed} slot(s) remaining. Extra files were skipped.`;
      }

      if (toUpload.length === 0) return;

      this.uploading   = true;
      this.uploadQueue = toUpload.length;

      for (const file of toUpload) {
        if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
          this.uploadError = 'Only JPG, PNG and WebP images are allowed.';
          this.uploadQueue--;
          continue;
        }
        if (file.size > 5 * 1024 * 1024) {
          this.uploadError = `"${file.name}" exceeds 5 MB limit.`;
          this.uploadQueue--;
          continue;
        }

        const fd = new FormData();
        fd.append('photo', file);
        fd.append('_token', document.querySelector('meta[name="csrf-token"]').content);

        try {
          const res  = await fetch('{{ route("go-live.photos.upload") }}', { method: 'POST', body: fd, credentials: 'same-origin' });
          const data = await res.json();
          if (!res.ok) {
            this.uploadError = data.error ?? 'Upload failed.';
          } else {
            this.photos.push({ id: data.id, url: data.url });
          }
        } catch(e) {
          this.uploadError = 'Upload failed. Please try again.';
        }

        this.uploadQueue--;
      }

      this.uploading = false;
    },

    async deletePhoto(id) {
      if (!confirm('Remove this photo?')) return;
      try {
        const res = await fetch(`{{ url('go-live/photos') }}/${id}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
          },
          credentials: 'same-origin',
        });
        if (res.ok) {
          this.photos = this.photos.filter(p => p.id !== id);
        }
      } catch(e) {
        console.error('delete failed:', e);
      }
    },
  };
}
</script>
@endpush
