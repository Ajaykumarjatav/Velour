@extends('layouts.app')
@section('title', 'Guide')
@section('page-title', 'How to use EasyGrox')
@section('content')

<div class="max-w-4xl space-y-6">

    <div class="rounded-2xl border border-stone-200/90 dark:border-gray-800 bg-[#FFF9F2] dark:bg-gray-900 shadow-sm p-6 sm:p-7">
        <p class="text-xs font-semibold uppercase tracking-widest text-velour-700 dark:text-velour-300">Guide &amp; Setup</p>
        <h1 class="text-2xl sm:text-3xl font-semibold tracking-tight text-heading mt-1">How {{ $salon->name }} runs on EasyGrox</h1>
        <p class="text-sm text-muted mt-2 max-w-2xl">
            This matches the live product: Settings + Go Live for launch, then Calendar, Appointments, and POS every day.
        </p>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <span class="inline-flex text-xs font-medium px-2.5 py-1 rounded-lg bg-white dark:bg-gray-800 border border-stone-200 dark:border-gray-700 text-heading">
                Setup {{ $progress['percent'] }}% · {{ $progress['completed'] }}/{{ $progress['total'] }}
            </span>
            @if($isStylistScoped)
            <span class="inline-flex text-xs font-medium px-2.5 py-1 rounded-lg bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-200 border border-amber-100 dark:border-amber-800">
                Staff login — use Calendar, Appointments, Clients, and POS
            </span>
            @else
            <span class="inline-flex text-xs font-medium px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-200 border border-emerald-100 dark:border-emerald-800">
                Owner / admin
            </span>
            @endif
            <a href="{{ \App\Support\SalonUrl::route('setup-progress') }}" class="text-xs font-semibold text-link">Open setup progress →</a>
        </div>
    </div>

    @unless($isStylistScoped)
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-start justify-between gap-3 flex-wrap">
            <div>
                <h2 class="font-semibold text-heading">Launch checklist</h2>
                <p class="text-sm text-muted mt-0.5">Same steps as Setup Progress and Go Live readiness. Incomplete rows jump to the exact field.</p>
            </div>
            @if($showGoLive ?? false)
            <a href="{{ route('go-live') }}" class="btn-primary btn-sm">Go Live &amp; Share</a>
            @endif
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($progress['items'] as $i => $item)
            <a href="{{ $item['link'] }}" class="flex items-start gap-4 px-6 py-3.5 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                <span class="mt-0.5 w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold
                    {{ $item['done']
                        ? 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300'
                        : 'bg-velour-100 dark:bg-velour-900/40 text-velour-700 dark:text-velour-300' }}">
                    @if($item['done'])
                    ✓
                    @else
                    {{ $i + 1 }}
                    @endif
                </span>
                <span class="flex-1 min-w-0">
                    <span class="block font-semibold {{ $item['done'] ? 'text-muted line-through' : 'text-heading group-hover:text-velour-700 dark:group-hover:text-velour-300' }}">{{ $item['label'] }}</span>
                    <span class="block text-sm text-muted">{{ $item['tip'] }}</span>
                </span>
                <span class="text-muted text-sm flex-shrink-0">{{ $item['done'] ? 'Done' : 'Open →' }}</span>
            </a>
            @endforeach
        </div>
    </div>
    @endunless

    <div class="card p-6">
        <h2 class="font-semibold text-heading">Daily routine</h2>
        <p class="text-sm text-muted mt-0.5 mb-4">Front desk and owners use the same three screens.</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-velour-600 dark:text-velour-400">Morning</p>
                <p class="font-semibold text-heading mt-1">Start of day</p>
                <p class="text-sm text-muted mt-2">Open <a href="{{ route('calendar') }}" class="text-link">Calendar</a>, then confirm or adjust in <a href="{{ route('appointments.index') }}" class="text-link">Appointments</a>.</p>
            </div>
            <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-velour-600 dark:text-velour-400">Open hours</p>
                <p class="font-semibold text-heading mt-1">During the day</p>
                <p class="text-sm text-muted mt-2">Check in on Calendar. Charge in <a href="{{ route('pos.index') }}" class="text-link">POS</a> (services + retail).</p>
            </div>
            <div class="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/40 p-4">
                <p class="text-xs font-bold uppercase tracking-wide text-velour-600 dark:text-velour-400">Close</p>
                <p class="font-semibold text-heading mt-1">End of day</p>
                <p class="text-sm text-muted mt-2">Review reports under Growth, reply to <a href="{{ route('reviews.index') }}" class="text-link">Reviews</a>, clear <a href="{{ route('notifications.index') }}" class="text-link">Notifications</a>.</p>
            </div>
        </div>
    </div>

    @if(!empty($screenshots))
    <div class="card p-6">
        <h2 class="font-semibold text-heading">Visual walkthrough</h2>
        <p class="text-sm text-muted mt-0.5 mb-4">Click a screenshot to open that page.</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($screenshots as $shot)
            <a href="{{ $shot['link'] }}" class="group rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden bg-white dark:bg-gray-900/40 hover:border-velour-300 dark:hover:border-velour-700 transition-colors">
                <img src="{{ $shot['url'] }}" alt="{{ $shot['title'] }}" class="w-full h-40 object-cover">
                <div class="p-3">
                    <p class="font-semibold text-sm text-heading group-hover:text-velour-700 dark:group-hover:text-velour-300">{{ $shot['title'] }}</p>
                    <p class="text-xs text-muted mt-1">{{ $shot['caption'] }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @foreach($featureGroups as $group)
    <div class="card p-6">
        <h2 class="font-semibold text-heading">{{ $group['label'] }}</h2>
        <p class="text-sm text-muted mt-0.5 mb-4">Menus you can open in the sidebar (hidden items stay off this list).</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($group['items'] as $feature)
            <a href="{{ $feature['href'] }}" class="rounded-xl border border-gray-200 dark:border-gray-800 p-4 hover:border-velour-300 dark:hover:border-velour-700 hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                <p class="font-semibold text-heading">{{ $feature['title'] }}</p>
                <p class="text-sm text-muted mt-1">{{ $feature['hint'] }}</p>
            </a>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="card p-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="font-semibold text-heading">Need help?</h2>
            <p class="text-sm text-muted mt-0.5">Empty slots → hours &amp; availability. Missing login → Admin → Team after Staff &amp; HR.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ \App\Support\SalonUrl::route('setup-progress') }}" class="btn-outline btn-sm">Setup progress</a>
            @if($showGoLive ?? false)
            <a href="{{ route('go-live') }}" class="btn-outline btn-sm">Go Live</a>
            @endif
            @if($showAvailability ?? false)
            <a href="{{ route('availability.index') }}" class="btn-outline btn-sm">Availability</a>
            @endif
            @if($showStaff ?? false)
            <a href="{{ route('staff.index') }}" class="btn-outline btn-sm">Staff &amp; HR</a>
            @endif
            @if($showBilling ?? false)
            <a href="{{ route('billing.dashboard') }}" class="btn-outline btn-sm">Billing</a>
            @endif
        </div>
    </div>

</div>

@endsection
