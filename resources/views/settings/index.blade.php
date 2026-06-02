@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')

@push('styles')
<style>
    @media (min-width: 1280px) {
        .settings-sidebar-panel {
            position: sticky;
            top: 0.75rem;
            max-height: calc(100vh - 6.5rem);
            overflow-y: auto;
        }
    }
    .settings-main-panel .card {
        border-radius: 1rem;
        border-color: rgb(226 232 240 / 0.95);
        background: #fff;
        box-shadow: 0 1px 2px rgb(15 23 42 / 0.04), 0 8px 24px -12px rgb(15 23 42 / 0.12);
        padding: 1rem;
    }
    @media (min-width: 640px) {
        .settings-main-panel .card {
            padding: 1.5rem;
        }
    }
    .dark .settings-main-panel .card {
        border-color: rgb(51 65 85 / 0.7);
        background: rgb(15 23 42 / 0.45);
        box-shadow: 0 1px 2px rgb(0 0 0 / 0.2);
    }
    .settings-main-panel .card h2 {
        letter-spacing: -0.01em;
    }
    .settings-mobile-tabs {
        -webkit-overflow-scrolling: touch;
        scrollbar-width: thin;
    }
    .settings-mobile-tabs::-webkit-scrollbar {
        height: 4px;
    }
    .settings-staff-member-row .form-input,
    .settings-staff-member-row .form-select,
    .settings-staff-member-row .form-textarea {
        min-width: 0;
    }
</style>
@endpush

@section('content')

@php
    $returnTo = old('return_to', request()->query('return_to'));
    $settingsPersonalOnly = $settingsPersonalOnly ?? false;
    $settingsTabLabels = $settingsTabLabels ?? [
        'salon' => 'Business', 'booking' => 'Booking', 'services' => 'Service', 'hours' => 'Hours', 'social' => 'Social Links',
        'notifications' => 'Notifications', 'profile' => 'Profile', 'team' => 'Team', 'security' => 'Security',
    ];
    $settingsInitialTab = $settingsInitialTab ?? session('tab', request()->get('tab', $settingsPersonalOnly ? 'profile' : 'salon'));

    $settingsTabMeta = [
        'salon' => ['phase' => 'Salon setup', 'title' => 'Salon Profile', 'description' => 'Your business identity, contact details, timezone, and how clients experience your brand online.'],
        'booking' => ['phase' => 'Salon setup', 'title' => 'Booking Settings', 'description' => 'Control online booking, deposits, confirmation rules, buffer times, and scheduling limits.'],
        'services' => ['phase' => 'Salon setup', 'title' => 'Services & Catalog', 'description' => 'Business types, categories, and which services appear on your public booking experience.'],
        'hours' => ['phase' => 'Salon setup', 'title' => 'Opening Hours', 'description' => 'Set when your salon is open so availability and booking slots stay accurate.'],
        'social' => ['phase' => 'Presence', 'title' => 'Social Links', 'description' => 'Connect Instagram, Facebook, and other profiles shown on your public site.'],
        'notifications' => ['phase' => 'Account', 'title' => 'Notifications', 'description' => 'Choose how you and your clients receive booking and marketing messages.'],
        'profile' => ['phase' => 'Account', 'title' => 'Your Profile', 'description' => 'Personal details, display preferences, and services you perform.'],
        'team' => ['phase' => 'Account', 'title' => 'Team Members', 'description' => 'Add stylists, assign services, and manage who appears on your booking page.'],
        'security' => ['phase' => 'Account', 'title' => 'Security', 'description' => 'Two-factor authentication, login activity, and sensitive account actions.'],
    ];

    $settingsNavGroupsAll = $settingsPersonalOnly
        ? [['label' => 'Your account', 'tabs' => ['profile', 'team', 'security']]]
        : [
            ['label' => 'Salon setup', 'tabs' => ['salon', 'booking', 'services', 'hours']],
            ['label' => 'Presence', 'tabs' => ['social']],
            ['label' => 'Account', 'tabs' => ['notifications', 'profile', 'team', 'security']],
        ];
    $settingsNavGroups = [];
    foreach ($settingsNavGroupsAll as $group) {
        $tabs = array_values(array_filter($group['tabs'], fn ($k) => isset($settingsTabLabels[$k])));
        if ($tabs !== []) {
            $settingsNavGroups[] = ['label' => $group['label'], 'tabs' => $tabs];
        }
    }

    $settingsProfilePct = null;
    if (isset($headerProfileCompletion) && is_array($headerProfileCompletion) && empty($hideSalonProfileBar)) {
        $settingsProfilePct = max(0, min(100, (int) ($headerProfileCompletion['percentage'] ?? 0)));
    }

    // One JSON object for Alpine: must use single-quoted HTML attribute (see x-data below) — double-quoted x-data breaks when @json emits "quotes".
    $settingsTabCanEdit = $settingsTabCanEdit ?? [];
    $settingsAlpineData = [
        'tab' => (string) $settingsInitialTab,
        'tabMeta' => $settingsTabMeta,
        'canEdit' => $settingsTabCanEdit,
        'showPasswordModal' => $errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'),
        'open' => [],
        'profileCardOpen' => true,
    ];
@endphp

<div class="settings-shell max-w-7xl w-full min-w-0 mx-auto" x-data="settingsPage(@js($settingsAlpineData))">

    <div class="flex flex-col xl:flex-row gap-4 sm:gap-5 xl:gap-8 items-stretch xl:items-start">

        {{-- Sidebar navigation (setup-progress style) --}}
        <aside class="settings-sidebar-panel w-full xl:w-[17.5rem] shrink-0 rounded-2xl border border-slate-200/90 bg-gradient-to-b from-slate-100 via-slate-50 to-white dark:from-slate-900 dark:via-slate-900/95 dark:to-slate-950 dark:border-slate-700/80 shadow-sm shadow-slate-200/30 dark:shadow-none p-3 sm:p-4 xl:p-5">
            <div class="mb-3 sm:mb-4 xl:mb-5">
                <h2 class="text-sm sm:text-base font-semibold text-teal-950 dark:text-teal-50 tracking-tight">Settings</h2>
                @if($settingsProfilePct !== null)
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">Profile {{ $settingsProfilePct }}% complete</p>
                <div class="mt-2 h-1.5 w-full rounded-full bg-teal-100 dark:bg-teal-950/60 overflow-hidden">
                    <div class="h-full rounded-full bg-teal-600 dark:bg-teal-400 transition-all duration-300" style="width: {{ $settingsProfilePct }}%"></div>
                </div>
                @else
                <p class="mt-1 text-xs text-slate-600 dark:text-slate-400">{{ count($settingsTabLabels) }} sections</p>
                @endif
            </div>

            {{-- Mobile / tablet: horizontal section picker --}}
            <div class="xl:hidden settings-mobile-tabs flex gap-2 overflow-x-auto overscroll-x-contain pb-2 mb-1 snap-x snap-mandatory" aria-label="Settings sections (mobile)">
                @foreach($settingsTabLabels as $tabKey => $tabLabel)
                <button type="button"
                        @click="tab='{{ $tabKey }}'"
                        role="tab"
                        :aria-selected="tab==='{{ $tabKey }}'"
                        :class="tab==='{{ $tabKey }}'
                            ? 'bg-teal-700 text-white border-teal-700 dark:bg-teal-600 dark:border-teal-500'
                            : 'bg-white/90 text-slate-700 border-slate-200 dark:bg-slate-800/90 dark:text-slate-200 dark:border-slate-600'"
                        class="snap-start shrink-0 rounded-full border px-3.5 py-2 text-xs font-medium whitespace-nowrap transition-colors">
                    {{ $tabLabel }}
                </button>
                @endforeach
            </div>

            <nav class="hidden xl:block space-y-5" aria-label="Settings sections">
                @foreach($settingsNavGroups as $navGroup)
                <div>
                    <p class="text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-500 dark:text-slate-400 mb-2 px-1">
                        {{ $navGroup['label'] }}
                    </p>
                    <ul class="space-y-1">
                        @foreach($navGroup['tabs'] as $tabKey)
                        @php $tabLabel = $settingsTabLabels[$tabKey]; @endphp
                        <li>
                            <button type="button"
                                    @click="tab='{{ $tabKey }}'"
                                    role="tab"
                                    :aria-selected="tab==='{{ $tabKey }}'"
                                    title="{{ $tabLabel }}"
                                    :class="tab==='{{ $tabKey }}'
                                        ? 'bg-teal-50 border-teal-200/90 text-teal-950 shadow-sm dark:bg-teal-950/50 dark:border-teal-800/60 dark:text-teal-50'
                                        : 'border-transparent text-slate-600 hover:bg-white/80 hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-800/50 dark:hover:text-slate-100'"
                                    class="w-full flex items-center gap-2.5 rounded-xl border px-3 py-2.5 text-left text-sm font-medium transition-all duration-150">
                                <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border text-[10px]"
                                      :class="tab==='{{ $tabKey }}'
                                          ? 'border-teal-600 bg-teal-600 text-white dark:border-teal-400 dark:bg-teal-500'
                                          : 'border-slate-300 bg-white text-slate-400 dark:border-slate-600 dark:bg-slate-800'">
                                    <svg x-show="tab==='{{ $tabKey }}'" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-show="tab!=='{{ $tabKey }}'" class="h-1.5 w-1.5 rounded-full bg-current opacity-40"></span>
                                </span>
                                <span class="truncate">{{ $tabLabel }}</span>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                </div>
                @endforeach
            </nav>

            @if(!$settingsPersonalOnly && $settingsProfilePct !== null && $settingsProfilePct < 100)
            <div class="mt-5 pt-4 border-t border-slate-200/80 dark:border-slate-700/80">
                <a href="{{ route('setup-progress') }}" class="text-xs font-medium text-teal-700 hover:text-teal-900 dark:text-teal-300 dark:hover:text-teal-100 hover:underline">
                    View setup progress →
                </a>
            </div>
            @endif
        </aside>

        {{-- Main panel --}}
        <div class="settings-main-panel flex-1 min-w-0 w-full">

            @if($settingsPersonalOnly)
            <div class="mb-6 rounded-2xl border border-teal-200/80 bg-teal-50/70 dark:border-teal-900/50 dark:bg-teal-950/30 px-4 py-3.5 text-sm text-teal-950 dark:text-teal-100">
                <p class="font-medium">Your account settings</p>
                <p class="mt-1 text-teal-900/80 dark:text-teal-200/90">You can update your profile and security. Salon business, services, and team setup are managed by your admin.</p>
            </div>
            @endif

            {{-- Section header (changes with active tab) --}}
            <header class="mb-4 sm:mb-6 xl:mb-8" x-show="tabMeta[tab]" x-cloak>
                <span class="inline-flex items-center rounded-full bg-teal-700 px-2.5 sm:px-3 py-0.5 sm:py-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-white dark:bg-teal-600"
                      x-text="tabMeta[tab]?.phase"></span>
                <h1 class="mt-2 sm:mt-3 text-xl sm:text-2xl xl:text-[1.75rem] font-semibold text-teal-950 dark:text-teal-50 tracking-tight leading-tight"
                    x-text="tabMeta[tab]?.title"></h1>
                <p class="mt-1.5 sm:mt-2 max-w-2xl text-xs sm:text-sm text-slate-600 dark:text-slate-400 leading-relaxed"
                   x-text="tabMeta[tab]?.description"></p>
            </header>

    {{-- ── Salon Settings ── --}}
    <div x-show="tab==='salon'" x-cloak>
        <p x-show="!canEditTab('salon')" x-cloak class="mb-4 text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to save Business settings.</p>
        <div class="card">
            <h2 class="font-semibold text-heading mb-4 sm:mb-5">Salon Profile</h2>
            <form id="settings-salon-form" action="{{ route('settings.salon') }}" method="POST" class="space-y-4 scroll-mt-24">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <fieldset :disabled="!canEditTab('salon')" class="min-w-0 border-0 p-0 m-0 space-y-4 disabled:opacity-70">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div class="col-span-2">
                        <label class="form-label">Salon name <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $salon->name) }}" required class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Email</label>
                        <input type="email" name="email" value="{{ old('email', $salon->email) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Phone</label>
                        <input type="tel" name="phone" value="{{ old('phone', $salon->phone) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Website</label>
                        <input type="url" name="website" value="{{ old('website', $salon->website) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label" for="settings-salon-currency-trigger">Currency</label>
                        <x-searchable-select
                            id="settings-salon-currency"
                            name="currency"
                            wrapper-class="w-full min-w-0"
                            :search-url="null"
                            search-placeholder="Search currency…"
                            trigger-class="form-select w-full">
                            @foreach(\App\Helpers\CurrencyHelper::selectList() as $code => $lbl)
                            <option value="{{ $code }}" {{ old('currency', $salon->currency ?? 'GBP') === $code ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </x-searchable-select>
                    </div>
                    <div>
                        <label class="form-label" for="settings-salon-timezone-trigger">Timezone</label>
                        <x-searchable-select
                            id="settings-salon-timezone"
                            name="timezone"
                            wrapper-class="w-full min-w-0"
                            :search-url="null"
                            search-placeholder="Search timezone…"
                            trigger-class="form-select w-full">
                            @foreach(\App\Helpers\TimezoneHelper::grouped() as $region => $zones)
                            <optgroup label="{{ $region }}">
                                @foreach($zones as $tz => $label)
                                <option value="{{ $tz }}" {{ old('timezone', $salon->timezone ?? 'UTC') === $tz ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </optgroup>
                            @endforeach
                        </x-searchable-select>
                        <p class="form-hint">Dashboard, revenue, and calendar “days” follow this clock.</p>
                        <p id="settings-location-autosave-hint" class="form-hint hidden"></p>
                    </div>
                    <div class="col-span-2">
                        <button type="button" id="settings-detect-location-btn" class="btn-outline">
                            Auto detect from current location
                        </button>
                        <p class="form-hint">Click to detect and fill Currency and Timezone from your current location.</p>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Booking confirmations show times in</label>
                        <div class="flex flex-col sm:flex-row gap-3 mt-1">
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="radio" name="booking_time_display" value="business" class="rounded-full border-gray-300 text-velour-600"
                                       {{ old('booking_time_display', $bookingTimeDisplay ?? 'business') === 'business' ? 'checked' : '' }}>
                                Business timezone (above)
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="radio" name="booking_time_display" value="customer" class="rounded-full border-gray-300 text-velour-600"
                                       {{ old('booking_time_display', $bookingTimeDisplay ?? 'business') === 'customer' ? 'checked' : '' }}>
                                Customer’s local timezone (when we can detect it)
                            </label>
                        </div>
                        <p class="form-hint">Used for emails and online booking messages. Internal calendar always uses business time.</p>
                    </div>
                    <div class="col-span-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/40 p-4 space-y-3">
                        <p class="text-sm font-semibold text-heading">Service delivery</p>
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" name="home_services_enabled" value="1" class="mt-1 rounded border-gray-300 text-velour-600"
                                   {{ old('home_services_enabled', $salon->home_services_enabled ?? false) ? 'checked' : '' }}>
                            <span class="text-sm text-body leading-relaxed">
                                <span class="font-medium text-heading">Enable home visits (client location)</span>
                                — when on, services you mark as <strong>home visit</strong> appear on your public booking page and API. When off (default), you can still create and manage home services in your catalog; they stay hidden from online booking until you enable this.
                            </span>
                        </label>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Description</label>
                        <textarea name="description" rows="3" class="form-textarea">{{ old('description', $salon->description) }}</textarea>
                    </div>
                    <div class="col-span-2">
                        <label class="form-label">Awards &amp; accolades</label>
                        <textarea name="awards_accolades" rows="4" class="form-textarea" placeholder="Certifications, press, industry awards, memberships…">{{ old('awards_accolades', $salon->awards_accolades) }}</textarea>
                        <p class="form-hint">Shown on your profile and public site where supported. One item per line works well.</p>
                    </div>
                    <div>
                        <label class="form-label">Address line 1</label>
                        <input type="text" name="address_line1" value="{{ old('address_line1', $salon->address_line1) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Address line 2</label>
                        <input type="text" name="address_line2" value="{{ old('address_line2', $salon->address_line2) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">City</label>
                        <input type="text" name="city" value="{{ old('city', $salon->city) }}" class="form-input">
                    </div>
                    <div>
                        <label class="form-label">Postcode</label>
                        <input type="text" name="postcode" value="{{ old('postcode', $salon->postcode) }}" class="form-input">
                    </div>
                </div>
                <button type="submit" class="btn-primary" :disabled="!canEditTab('salon')">Save Changes</button>
                </fieldset>
            </form>
        </div>
    </div>

    {{-- ── Online booking & widget ── --}}
    <div x-show="tab==='booking'" x-cloak>
        <p x-show="!canEditTab('booking')" x-cloak class="mb-4 text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to save Booking settings.</p>
        <div class="card min-w-0">
            <div class="flex items-start gap-2 mb-5">
                <span class="text-lg" aria-hidden="true">⚙️</span>
                <div>
                    <h2 class="font-semibold text-heading">Booking settings</h2>
                    <p class="text-sm text-muted mt-1">Public booking link, embed widget, and client self-service rules.</p>
                </div>
            </div>
            <form id="settings-booking-form" action="{{ route('settings.booking') }}" method="POST" class="space-y-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/40 divide-y divide-gray-200/80 dark:divide-gray-700/80">
                    @foreach([
                        ['online_booking_enabled', 'Online booking', 'Allow clients to book via your link & widget', (bool) old('online_booking_enabled', $salon->online_booking_enabled)],
                        ['new_client_booking_enabled', 'New client bookings', 'Accept bookings from first-time clients', (bool) old('new_client_booking_enabled', $salon->new_client_booking_enabled)],
                        ['deposit_required', 'Require deposit', 'Charge deposit to reduce no-shows', (bool) old('deposit_required', $salon->deposit_required)],
                        ['instant_confirmation', 'Instant confirmation', 'Confirm bookings automatically (no approval needed)', (bool) old('instant_confirmation', $salon->instant_confirmation)],
                    ] as $bookingToggle)
                        @php [$bName, $bLabel, $bHint, $bOn] = $bookingToggle; $bId = 'settings-booking-tab-' . $bName; @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3 items-start sm:items-center px-4 py-3.5 sm:px-5">
                            <div class="space-y-0.5 min-w-0 max-w-full">
                                <label for="{{ $bId }}" class="text-sm font-medium text-heading cursor-pointer block">{{ $bLabel }}</label>
                                <p class="text-xs text-muted leading-snug">{{ $bHint }}</p>
                            </div>
                            <div class="flex items-center justify-start sm:justify-end pt-0.5 sm:pt-0">
                                <input type="hidden" name="{{ $bName }}" value="0">
                                <input type="checkbox" id="{{ $bId }}" name="{{ $bName }}" value="1"
                                       class="rounded border-gray-300 text-velour-600 focus:ring-velour-500 h-5 w-5 shrink-0"
                                       {{ $bOn ? 'checked' : '' }}>
                            </div>
                        </div>
                    @endforeach

                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3 items-start sm:items-center px-4 py-3.5 sm:px-5">
                        <div class="space-y-0.5 min-w-0 max-w-full">
                            <label for="settings-booking-deposit_percentage" class="text-sm font-medium text-heading block">Deposit %</label>
                            <p class="text-xs text-muted leading-snug">Percentage of service cost charged upfront</p>
                        </div>
                        <input type="number" id="settings-booking-deposit_percentage" name="deposit_percentage"
                               value="{{ old('deposit_percentage', $salon->deposit_percentage ?? 20) }}"
                               min="1" max="100" required
                               class="form-input w-full sm:w-24 max-w-[8rem] text-right text-sm tabular-nums shrink-0 @error('deposit_percentage') form-input-error @enderror">
                    </div>
                    @error('deposit_percentage')<p class="px-4 sm:px-5 -mt-2 pb-2 text-xs text-red-600">{{ $message }}</p>@enderror

                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3 items-start sm:items-center px-4 py-3.5 sm:px-5">
                        <div class="space-y-0.5 min-w-0 max-w-full">
                            <label for="settings-booking-booking_advance_days" class="text-sm font-medium text-heading block">Book up to (days)</label>
                            <p class="text-xs text-muted leading-snug">How far ahead clients can schedule</p>
                        </div>
                        <input type="number" id="settings-booking-booking_advance_days" name="booking_advance_days"
                               value="{{ old('booking_advance_days', $salon->booking_advance_days ?? 60) }}"
                               min="1" max="365" required
                               class="form-input w-full sm:w-24 max-w-[8rem] text-right text-sm tabular-nums shrink-0 @error('booking_advance_days') form-input-error @enderror">
                    </div>
                    @error('booking_advance_days')<p class="px-4 sm:px-5 -mt-2 pb-2 text-xs text-red-600">{{ $message }}</p>@enderror

                    <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto] gap-3 items-start sm:items-center px-4 py-3.5 sm:px-5">
                        <div class="space-y-0.5 min-w-0 max-w-full">
                            <label for="settings-booking-cancellation_hours" class="text-sm font-medium text-heading block">Cancel notice (hours)</label>
                            <p class="text-xs text-muted leading-snug">Minimum notice for free cancellation</p>
                        </div>
                        <input type="number" id="settings-booking-cancellation_hours" name="cancellation_hours"
                               value="{{ old('cancellation_hours', $salon->cancellation_hours ?? 24) }}"
                               min="0" max="168" required
                               class="form-input w-full sm:w-24 max-w-[8rem] text-right text-sm tabular-nums shrink-0 @error('cancellation_hours') form-input-error @enderror">
                    </div>
                    @error('cancellation_hours')<p class="px-4 sm:px-5 -mt-2 pb-2 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-gray-200/80 dark:border-gray-700/80 pt-5 pb-0.5">
                    <button type="submit" class="btn-primary shrink-0" :disabled="!canEditTab('booking')">Save booking settings</button>
                </div>
            </form>
        </div>

        @if(!($settingsPersonalOnly ?? false))
        <div id="settings-buffer-rules" class="card min-w-0 mt-6 scroll-mt-24">
            <div class="flex items-start gap-2 mb-5">
                <span class="text-lg" aria-hidden="true">⏱️</span>
                <div>
                    <h2 class="font-semibold text-heading">Buffer time &amp; booking rules</h2>
                    <p class="text-sm text-muted mt-1">Values are saved per salon. Adjust numbers below, then save.</p>
                </div>
            </div>
            <form action="{{ route('settings.buffer-rules') }}" method="POST" class="space-y-0">
                @csrf
                @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @php
                    $bufferRule = $bufferRule ?? null;
                    $bufferRows = [
                        ['buffer_before_minutes', 'Buffer before service', 'Prep time before each appointment.', 'min', 0, 240],
                        ['buffer_after_minutes', 'Buffer after service', 'Clean-up / turnaround time.', 'min', 0, 240],
                        ['max_daily_bookings_per_staff', 'Max daily bookings per staff', 'Cap appointments per staff member per day.', 'appts', 1, 100],
                        ['advance_booking_days', 'Advance booking window', 'How far ahead clients can book.', 'days', 1, 730],
                        ['last_minute_cutoff_hours', 'Last-minute cut-off', 'Minimum notice before start time.', 'hours', 0, 168],
                        ['overbooking_percent', 'Overbooking allowance', 'Extra capacity on busy days.', '%', 0, 100],
                    ];
                @endphp
                <ul class="rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/40 divide-y divide-gray-200/80 dark:divide-gray-700/80">
                    @foreach($bufferRows as [$field, $label, $help, $unit, $min, $max])
                        <li class="grid grid-cols-1 sm:grid-cols-[minmax(0,1fr)_5.5rem_3rem] gap-2 sm:gap-x-4 sm:items-center px-4 py-3.5 sm:px-5">
                            <div class="min-w-0">
                                <label for="settings-buf-{{ $field }}" class="text-sm font-medium text-heading">{{ $label }}</label>
                                <p id="settings-buf-help-{{ $field }}" class="text-xs text-muted mt-0.5 leading-snug">{{ $help }}</p>
                                @error($field)<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div class="flex items-center gap-2 sm:contents">
                                <input id="settings-buf-{{ $field }}" type="number" name="{{ $field }}"
                                       value="{{ old($field, $bufferRule?->$field) }}"
                                       aria-describedby="settings-buf-help-{{ $field }}"
                                       min="{{ $min }}" max="{{ $max }}" required
                                       class="form-input w-24 max-w-[40%] sm:max-w-none sm:w-[5.5rem] text-sm text-right tabular-nums py-2 px-2 sm:justify-self-end @error($field) form-input-error @enderror">
                                <span class="text-xs text-muted tabular-nums w-10 shrink-0 sm:w-auto sm:justify-self-end">{{ $unit }}</span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-6 flex flex-wrap items-center justify-end gap-3 border-t border-gray-200/80 dark:border-gray-700/80 pt-5 pb-0.5">
                    <button type="submit" class="btn-primary shrink-0" :disabled="!canEditTab('booking')">Save rules</button>
                </div>
            </form>
            <p class="text-xs text-muted mt-4 leading-relaxed">
                Booking today still uses each service’s own buffers and staff working days. Hooking these salon-wide rules into live availability can be added in a later release.
            </p>
        </div>
        @endif
    </div>

    {{-- ── Service Setup ── --}}
    <div x-show="tab==='services'" x-cloak>
        <p x-show="!canEditTab('services')" x-cloak class="mb-4 text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to save Service settings.</p>
        <div class="card">
            <h2 class="font-semibold text-heading mb-5">Service Setup</h2>
            <form action="{{ route('settings.services') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @php
                    $selectedTypeIds = array_map('intval', old('business_type_ids', $selectedBusinessTypeIds ?? []));
                    $starterCategoryOld = old('starter_categories', $selectedStarterCategories ?? []);
                    $starterServiceOld = old('starter_services', $selectedStarterServices ?? []);
                    $typeSlugById = collect($businessTypes)->merge($customBusinessTypes)->pluck('slug', 'id')->all();
                    $selectedSlugMap = [];
                    foreach ($selectedTypeIds as $tid) {
                        $slug = $typeSlugById[$tid] ?? null;
                        if (is_string($slug) && $slug !== '') {
                            $selectedSlugMap[$slug] = true;
                        }
                    }
                @endphp
                <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <label class="form-label mb-0">Business types <span class="text-red-500">*</span></label>
                    <p class="form-hint mb-1">Services can only be tagged with types you enable here.</p>
                    <div id="settings-business-types-list" class="flex flex-wrap gap-x-4 gap-y-2">
                        @foreach($businessTypes as $type)
                            @php $checked = in_array((int) $type->id, $selectedTypeIds, true); @endphp
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                <input type="checkbox" name="business_type_ids[]" value="{{ $type->id }}" data-bt-slug="{{ $type->slug }}" class="rounded border-gray-300 text-velour-600" {{ $checked ? 'checked' : '' }}>
                                {{ $type->name }}
                            </label>
                        @endforeach
                        @foreach($customBusinessTypes as $type)
                            @php $checked = in_array((int) $type->id, $selectedTypeIds, true); @endphp
                            <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer" data-custom-existing="1">
                                <input type="checkbox" name="business_type_ids[]" value="{{ $type->id }}" data-bt-slug="{{ $type->slug }}" class="rounded border-gray-300 text-velour-600" {{ $checked ? 'checked' : '' }}>
                                {{ $type->name }}
                                <span class="text-[10px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-500">custom</span>
                            </label>
                        @endforeach
                    </div>
                    @error('business_type_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <label class="form-label mb-0">Service categories <span class="text-gray-400 font-normal">(optional)</span></label>
                    <p class="form-hint">Optional: tick categories to choose which starter services appear below. If no category is selected, services stay hidden.</p>
                    <div id="settings-service-categories-list" class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                        @foreach($starterCatalog as $slug => $items)
                            @php
                                $cats = [];
                                foreach ($items as $item) {
                                    $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                                    if ($catSlug === '') {
                                        $catSlug = 'general';
                                    }
                                    $catName = (string) ($item['category'] ?? 'General');
                                    if (! isset($cats[$catSlug])) {
                                        $cats[$catSlug] = $catName === '' ? 'General' : $catName;
                                    }
                                }
                            @endphp
                            @foreach($cats as $catSlug => $catName)
                                @php $catVal = $slug . ':' . $catSlug; @endphp
                                <label class="settings-service-category-option flex items-start gap-2 text-sm text-body cursor-pointer hidden" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catVal }}">
                                    <input type="checkbox" name="starter_categories[]" value="{{ $catVal }}"
                                           class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500"
                                           {{ in_array($catVal, $starterCategoryOld, true) ? 'checked' : '' }}>
                                    <span>{{ $catName }}</span>
                                </label>
                            @endforeach
                        @endforeach
                    </div>
                    @error('starter_categories')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="space-y-3 rounded-xl border border-gray-200 dark:border-gray-700 p-3 sm:p-4">
                    <label class="form-label mb-0">Services <span class="text-gray-400 font-normal">(optional)</span></label>
                    <p class="form-hint">Select services, then enter time and price manually for each. Services appear only after selecting at least one category.</p>
                    <div id="settings-service-offers-list" class="flex flex-col gap-6">
                        @foreach($starterCatalog as $slug => $items)
                            @php
                                $typeLabel = collect($businessTypes ?? [])->merge(collect($customBusinessTypes ?? []))->firstWhere('slug', $slug)?->name;
                                if (! $typeLabel) {
                                    $typeLabel = \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', (string) $slug));
                                }
                                $groupIndex = [];
                                $grouped = [];
                                foreach ($items as $item) {
                                    $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                                    if ($catSlug === '') {
                                        $catSlug = 'general';
                                    }
                                    $catName = trim((string) ($item['category'] ?? 'General'));
                                    if ($catName === '') {
                                        $catName = 'General';
                                    }
                                    if (! isset($groupIndex[$catSlug])) {
                                        $groupIndex[$catSlug] = count($grouped);
                                        $grouped[] = ['catSlug' => $catSlug, 'catName' => $catName, 'rows' => []];
                                    }
                                    $grouped[$groupIndex[$catSlug]]['rows'][] = $item;
                                }
                            @endphp
                            <div class="settings-service-offers-type-bundle hidden space-y-3" data-bt-slug="{{ $slug }}">
                                <div class="pb-2 border-b border-gray-200 dark:border-gray-700">
                                    <p class="text-sm font-semibold text-heading">{{ $typeLabel }}</p>
                                </div>
                                <div class="space-y-3">
                                    @foreach($grouped as $grp)
                                        <div class="settings-service-offers-cat-bundle rounded-xl border border-gray-200/90 dark:border-gray-700/80 bg-gray-50/70 dark:bg-gray-900/35 p-3 space-y-2">
                                            <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ $grp['catName'] }}</p>
                                            <div class="space-y-2">
                                                @foreach($grp['rows'] as $item)
                                                    @php
                                                        $val = $slug . ':' . $item['key'];
                                                        $token = str_replace(':', '__', $val);
                                                        $catSlug = (string) ($item['category_slug'] ?? \Illuminate\Support\Str::slug((string) ($item['category'] ?? 'General')));
                                                        if ($catSlug === '') {
                                                            $catSlug = 'general';
                                                        }
                                                        $catId = $slug . ':' . $catSlug;
                                                        $checked = in_array($val, $starterServiceOld, true);
                                                        $isUnisex = $slug === 'unisex';
                                                        $savedMeta = (array) (($selectedStarterServiceMeta[$val] ?? []));
                                                        $oldDuration = old("starter_service_meta.$token.duration_minutes", $savedMeta['duration_minutes'] ?? null);
                                                        $oldPrice = old("starter_service_meta.$token.price", $savedMeta['price'] ?? null);
                                                        $oldMenDuration = old("starter_service_meta.$token.men.duration_minutes", $savedMeta['men']['duration_minutes'] ?? null);
                                                        $oldMenPrice = old("starter_service_meta.$token.men.price", $savedMeta['men']['price'] ?? null);
                                                        $oldWomenDuration = old("starter_service_meta.$token.women.duration_minutes", $savedMeta['women']['duration_minutes'] ?? null);
                                                        $oldWomenPrice = old("starter_service_meta.$token.women.price", $savedMeta['women']['price'] ?? null);
                                                    @endphp
                                                    <label class="settings-service-offer-option flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 sm:gap-3 text-sm text-body cursor-pointer hidden rounded-lg border border-transparent px-1 py-1" data-bt-slug="{{ $slug }}" data-cat-id="{{ $catId }}">
                                                        <span class="flex items-start gap-2 min-w-0 sm:min-w-[200px]">
                                                            <input type="checkbox" name="starter_services[]" value="{{ $val }}"
                                                                   class="mt-0.5 rounded border-gray-300 text-velour-600 focus:ring-velour-500 settings-service-checkbox shrink-0"
                                                                   {{ $checked ? 'checked' : '' }}>
                                                            <span class="leading-snug">{{ $item['name'] }}</span>
                                                        </span>
                                                        @if($isUnisex)
                                                            <span class="settings-service-meta-grid grid grid-cols-1 sm:grid-cols-2 gap-2 w-full sm:max-w-xl {{ $checked ? '' : 'hidden' }}">
                                                                <input type="number"
                                                                       min="1"
                                                                       step="1"
                                                                       name="starter_service_meta[{{ $token }}][men][duration_minutes]"
                                                                       value="{{ $oldMenDuration }}"
                                                                       placeholder="Men time (min)"
                                                                       class="form-input text-xs w-full settings-service-meta-input"
                                                                       {{ $checked ? 'required' : 'disabled' }}>
                                                                <input type="number"
                                                                       min="0.01"
                                                                       step="0.01"
                                                                       name="starter_service_meta[{{ $token }}][men][price]"
                                                                       value="{{ $oldMenPrice }}"
                                                                       placeholder="Men price"
                                                                       class="form-input text-xs w-full settings-service-meta-input"
                                                                       {{ $checked ? 'required' : 'disabled' }}>
                                                                <input type="number"
                                                                       min="1"
                                                                       step="1"
                                                                       name="starter_service_meta[{{ $token }}][women][duration_minutes]"
                                                                       value="{{ $oldWomenDuration }}"
                                                                       placeholder="Women time (min)"
                                                                       class="form-input text-xs w-full settings-service-meta-input"
                                                                       {{ $checked ? 'required' : 'disabled' }}>
                                                                <input type="number"
                                                                       min="0.01"
                                                                       step="0.01"
                                                                       name="starter_service_meta[{{ $token }}][women][price]"
                                                                       value="{{ $oldWomenPrice }}"
                                                                       placeholder="Women price"
                                                                       class="form-input text-xs w-full settings-service-meta-input"
                                                                       {{ $checked ? 'required' : 'disabled' }}>
                                                            </span>
                                                        @else
                                                            <span class="settings-service-meta-grid grid grid-cols-1 sm:grid-cols-2 gap-2 w-full sm:max-w-md {{ $checked ? '' : 'hidden' }}">
                                                                <input type="number"
                                                                       min="1"
                                                                       step="1"
                                                                       name="starter_service_meta[{{ $token }}][duration_minutes]"
                                                                       value="{{ $oldDuration }}"
                                                                       placeholder="Time (min)"
                                                                       class="form-input text-xs w-full settings-service-meta-input"
                                                                       {{ $checked ? 'required' : 'disabled' }}>
                                                                <input type="number"
                                                                       min="0.01"
                                                                       step="0.01"
                                                                       name="starter_service_meta[{{ $token }}][price]"
                                                                       value="{{ $oldPrice }}"
                                                                       placeholder="Price"
                                                                       class="form-input text-xs w-full settings-service-meta-input"
                                                                       {{ $checked ? 'required' : 'disabled' }}>
                                                            </span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('starter_services')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    @error('starter_service_meta')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                @if(empty($selectedBusinessTypeSlugs))
                    <p class="text-xs text-amber-600">Select at least one business type in the Business tab first.</p>
                @endif
                <button type="submit" class="btn-primary" :disabled="!canEditTab('services')">Save Service Setup</button>
            </form>
        </div>
    </div>

    {{-- ── Opening Hours ── --}}
    <div x-show="tab==='hours'" x-cloak>
        <p x-show="!canEditTab('hours')" x-cloak class="mb-4 text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to save Hours.</p>
        <div class="card">
            <h2 class="font-semibold text-heading mb-5">Opening Hours</h2>
            <form action="{{ route('settings.hours') }}" method="POST" class="space-y-3">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @php
                    $days    = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
                    $current = $salon->opening_hours ?? [];
                @endphp
                @foreach($days as $day)
                @php $h = $current[$day] ?? ['open'=>true,'from'=>'09:00','to'=>'18:00']; @endphp
                <div class="flex items-center gap-4 py-2 border-b border-gray-100 dark:border-gray-800 last:border-0">
                    <div class="w-28">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="hours[{{ $day }}][open]" value="1"
                                   {{ ($h['open'] ?? false) ? 'checked' : '' }}
                                   class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                            <span class="text-sm font-medium text-body capitalize">{{ $day }}</span>
                        </label>
                    </div>
                    <input type="time" name="hours[{{ $day }}][from]" value="{{ $h['from'] ?? '09:00' }}" class="form-input w-auto">
                    <span class="text-muted text-sm">to</span>
                    <input type="time" name="hours[{{ $day }}][to]" value="{{ $h['to'] ?? '18:00' }}" class="form-input w-auto">
                </div>
                @endforeach
                <div class="pt-2">
                    <button type="submit" class="btn-primary" :disabled="!canEditTab('hours')">Save Hours</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Social Links ── --}}
    <div x-show="tab==='social'" x-cloak>
        <p x-show="!canEditTab('social')" x-cloak class="mb-4 text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to save Social Links.</p>
        <div class="card">
            <h2 class="font-semibold text-heading mb-1">Social Links</h2>
            <p class="text-xs text-muted mb-5">Add your profile URLs. Each link redirects clients to your profile and tracks inbound clicks.</p>

            <form action="{{ route('settings.social-links') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                @php
                $platforms = [
                    'instagram' => ['label' => 'Instagram',       'icon' => '📸', 'placeholder' => 'https://instagram.com/yoursalon'],
                    'facebook'  => ['label' => 'Facebook',        'icon' => '👍', 'placeholder' => 'https://facebook.com/yoursalon'],
                    'tiktok'    => ['label' => 'TikTok',          'icon' => '🎵', 'placeholder' => 'https://tiktok.com/@yoursalon'],
                    'whatsapp'  => ['label' => 'WhatsApp',        'icon' => '💬', 'placeholder' => 'https://wa.me/447700000000'],
                    'google'    => ['label' => 'Google Business', 'icon' => '🔍', 'placeholder' => 'https://g.page/yoursalon'],
                    'twitter'   => ['label' => 'X / Twitter',     'icon' => '🐦', 'placeholder' => 'https://x.com/yoursalon'],
                    'youtube'   => ['label' => 'YouTube',         'icon' => '▶️', 'placeholder' => 'https://youtube.com/@yoursalon'],
                    'linkedin'  => ['label' => 'LinkedIn',        'icon' => '💼', 'placeholder' => 'https://linkedin.com/company/yoursalon'],
                    'pinterest' => ['label' => 'Pinterest',       'icon' => '📌', 'placeholder' => 'https://pinterest.com/yoursalon'],
                ];
                $saved = $salon->social_links ?? [];
                @endphp

                @foreach($platforms as $key => $meta)
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-lg flex-shrink-0">
                        {{ $meta['icon'] }}
                    </div>
                    <div class="flex-1">
                        <label class="form-label mb-1">{{ $meta['label'] }}</label>
                        <div class="flex items-center gap-2">
                            <input type="url"
                                   name="social_links[{{ $key }}]"
                                   value="{{ old("social_links.{$key}", $saved[$key] ?? '') }}"
                                   placeholder="{{ $meta['placeholder'] }}"
                                   class="form-input flex-1">
                            @if(!empty($saved[$key]))
                            <a href="{{ $saved[$key] }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="flex-shrink-0 px-3 py-2 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-muted hover:text-body transition-colors">
                                Visit ↗
                            </a>
                            @endif
                        </div>
                        @error("social_links.{$key}")
                        <p class="form-error mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @endforeach

                <div class="pt-2 flex items-center gap-3">
                    <button type="submit" class="btn-primary" :disabled="!canEditTab('social')">Save Social Links</button>
                    <p class="text-xs text-muted">Links appear on your booking page and Go Live &amp; Share panel.</p>
                </div>
            </form>
        </div>

        {{-- Click stats this month --}}
        @php
        $clickStats = \Illuminate\Support\Facades\DB::table('social_share_clicks')
            ->where('salon_id', $salon->id)
            ->where('clicked_at', '>=', now()->startOfMonth())
            ->selectRaw('platform, COUNT(*) as clicks')
            ->groupBy('platform')
            ->orderByDesc('clicks')
            ->get();
        @endphp

        @if($clickStats->isNotEmpty())
        <div class="card mt-4">
            <h3 class="font-semibold text-heading mb-4">Click Stats — {{ now()->format('F Y') }}</h3>
            <div class="space-y-3">
                @foreach($clickStats as $stat)
                <div class="flex items-center gap-3">
                    <span class="text-sm font-medium text-body w-28 capitalize">{{ $stat->platform }}</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-800 rounded-full h-2">
                        <div class="bg-velour-500 h-2 rounded-full"
                             style="width: {{ min(100, round(($stat->clicks / max($clickStats->max('clicks'), 1)) * 100)) }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-heading w-8 text-right">{{ $stat->clicks }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- ── Notifications (rules, timing, templates, quiet hours) ── --}}
    <div x-show="tab==='notifications'" x-cloak>
        <p x-show="!canEditTab('notifications')" x-cloak class="mb-4 text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to save Notification settings.</p>
        <div class="card">
            <h2 class="font-semibold text-heading mb-1">Notification settings</h2>
            <p class="text-sm text-muted mb-6">Turn channels on or off, set when scheduled reminders go out, and customise message text. Use placeholders in curly braces in your templates.</p>

            <form action="{{ route('settings.notifications') }}" method="POST" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">

                @foreach($notificationDefinitions as $id => $def)
                    @php
                        $rule = $notificationConfig['rules'][$id] ?? ['enabled' => false, 'offset_hours' => null];
                        $tpl = $notificationConfig['templates'][$id] ?? [];
                        $timing = $def['timing'] ?? 'instant';
                    @endphp
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900/40 overflow-hidden">
                        <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-heading text-sm sm:text-base">{{ $def['label'] }}</h3>
                                    @if($timing === 'instant')
                                        <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-800 dark:text-emerald-300">Instant</span>
                                    @else
                                        <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-800 dark:text-amber-300">Scheduled</span>
                                    @endif
                                </div>
                                <p class="text-xs text-muted leading-relaxed">{{ $def['description'] }}</p>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <input type="hidden" name="notification_rules[{{ $id }}][enabled]" value="0">
                                <label class="flex items-center gap-2 cursor-pointer text-sm text-body whitespace-nowrap">
                                    <input type="checkbox" name="notification_rules[{{ $id }}][enabled]" value="1"
                                           class="rounded border-gray-300 dark:border-gray-600 text-velour-600"
                                           @checked((bool) old("notification_rules.$id.enabled", $rule['enabled'] ?? false))>
                                    <span>On</span>
                                </label>
                            </div>
                        </div>

                        @if($timing === 'scheduled')
                            <div class="px-4 sm:px-5 pb-4 flex flex-wrap items-center gap-2 border-t border-gray-100 dark:border-gray-800 pt-4">
                                <label class="text-xs font-medium text-body">Send</label>
                                <select name="notification_rules[{{ $id }}][offset_hours]" class="form-select text-sm w-auto min-w-[8rem]">
                                    @foreach([1,2,4,6,12,24,48,72,96,168] as $h)
                                        <option value="{{ $h }}" @selected((int) old("notification_rules.$id.offset_hours", $rule['offset_hours'] ?? 24) === $h)>
                                            {{ $h }} hour{{ $h !== 1 ? 's' : '' }} before start
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="border-t border-gray-100 dark:border-gray-800 px-4 sm:px-5 py-3 bg-gray-50/80 dark:bg-gray-800/30">
                            <button type="button"
                                    @click="open['{{ $id }}'] = !open['{{ $id }}']"
                                    class="text-sm font-medium text-velour-600 dark:text-velour-400 hover:underline">
                                <span x-text="open['{{ $id }}'] ? 'Hide message templates' : 'Edit message templates'"></span>
                            </button>

                            <div x-show="open['{{ $id }}']" x-cloak class="mt-4 space-y-4">
                                @if(in_array('email', $def['channels'] ?? [], true))
                                    <div>
                                        <label class="form-label text-xs">Email subject</label>
                                        <input type="text" name="notification_templates[{{ $id }}][email_subject]"
                                               value="{{ old("notification_templates.$id.email_subject", $tpl['email_subject'] ?? '') }}"
                                               class="form-input text-sm" placeholder="Subject line">
                                    </div>
                                    <div>
                                        <label class="form-label text-xs">Email body</label>
                                        <textarea name="notification_templates[{{ $id }}][email_body]" rows="5"
                                                  class="form-textarea text-sm font-mono"
                                                  placeholder="Email text">{{ old("notification_templates.$id.email_body", $tpl['email_body'] ?? '') }}</textarea>
                                    </div>
                                @endif
                                @if(in_array('sms', $def['channels'] ?? [], true))
                                    <div>
                                        <label class="form-label text-xs">SMS body <span class="text-muted font-normal">(keep short; ~160 chars recommended)</span></label>
                                        <textarea name="notification_templates[{{ $id }}][sms_body]" rows="3"
                                                  maxlength="640"
                                                  class="form-textarea text-sm font-mono"
                                                  placeholder="SMS text">{{ old("notification_templates.$id.sms_body", $tpl['sms_body'] ?? '') }}</textarea>
                                    </div>
                                @endif
                                @if(!empty($def['variables']))
                                    <p class="text-xs text-muted">
                                        <span class="font-medium text-body">Placeholders:</span>
                                        @foreach($def['variables'] as $v)
                                            <code class="ml-1 px-1.5 py-0.5 rounded bg-gray-200 dark:bg-gray-700 text-[11px]">{!! '{{'.$v.'}}' !!}</code>
                                        @endforeach
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach

                @php $qh = $notificationConfig['quiet_hours'] ?? []; @endphp
                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 sm:p-5">
                    <h3 class="font-semibold text-heading text-sm mb-1">Quiet hours</h3>
                    <p class="text-xs text-muted mb-4">When enabled, scheduled client reminders are skipped during this window (salon timezone).</p>
                    <div class="flex flex-wrap items-center gap-4 mb-4">
                        <input type="hidden" name="qh_enabled" value="0">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-body">
                            <input type="checkbox" name="qh_enabled" value="1" class="rounded border-gray-300 dark:border-gray-600 text-velour-600"
                                   @checked(old('qh_enabled', $qh['enabled'] ?? false) ? true : false)>
                            Enable quiet hours
                        </label>
                    </div>
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label class="form-label text-xs">From</label>
                            <input type="time" name="qh_from" value="{{ old('qh_from', $qh['from'] ?? '22:00') }}" class="form-input text-sm w-auto">
                        </div>
                        <div>
                            <label class="form-label text-xs">To</label>
                            <input type="time" name="qh_to" value="{{ old('qh_to', $qh['to'] ?? '07:00') }}" class="form-input text-sm w-auto">
                        </div>
                        <div>
                            <label class="form-label text-xs">Behaviour</label>
                            <select name="qh_mode" class="form-select text-sm w-auto min-w-[10rem]">
                                <option value="skip" @selected(old('qh_mode', $qh['mode'] ?? 'skip') === 'skip')>Skip send (recommended)</option>
                                <option value="delay" @selected(old('qh_mode', $qh['mode'] ?? 'skip') === 'delay')>Delay (reserved)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary" :disabled="!canEditTab('notifications')">Save notification settings</button>
            </form>
        </div>
    </div>

    {{-- ── My Profile ── --}}
    <div x-show="tab==='profile'" x-cloak class="space-y-5">
        <p x-show="!canEditTab('profile')" x-cloak class="text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to update your profile.</p>
        <div class="card">
            <div class="mb-5 flex items-center justify-between border-b border-gray-200/60 dark:border-gray-800 pb-3">
                <h2 class="font-semibold text-heading">My Profile</h2>
                <div class="flex items-center gap-2">
                    <button type="button"
                            @click="showPasswordModal = true"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100/70 text-gray-600 transition hover:bg-gray-200/80 dark:bg-gray-800/70 dark:text-gray-200 dark:hover:bg-gray-700/80"
                            title="Change password">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"></circle>
                            <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01A1.65 1.65 0 0 0 10 3.09V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.01a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                        </svg>
                    </button>
                    <button type="button"
                            @click="profileCardOpen = !profileCardOpen"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100/70 text-gray-600 transition hover:bg-gray-200/80 dark:bg-gray-800/70 dark:text-gray-200 dark:hover:bg-gray-700/80"
                            :title="profileCardOpen ? 'Minimize section' : 'Expand section'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform" :class="profileCardOpen ? '' : 'rotate-180'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                        </svg>
                    </button>
                </div>
            </div>
            <form x-show="profileCardOpen" x-cloak action="{{ route('settings.profile') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                <input type="hidden" name="return_to" value="{{ $returnTo }}">
                @if($profileStaff)
                <div>
                    <label class="form-label">Photo</label>
                    <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                        <x-staff-avatar :staff="$profileStaff" size="lg" />
                        <div class="flex-1 min-w-0 space-y-2">
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp"
                                   class="form-input text-sm file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-velour-50 file:text-velour-700 dark:file:bg-velour-900/40 dark:file:text-velour-200">
                            <p class="form-hint">JPG, PNG or WebP · max 2 MB</p>
                            @if($profileStaff->avatar)
                                <label class="inline-flex items-center gap-2 text-sm text-body cursor-pointer">
                                    <input type="checkbox" name="remove_avatar" value="1" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                    Remove current photo
                                </label>
                            @endif
                            @error('avatar')<p class="form-error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
                @endif
                <div>
                    <label class="form-label">Full name</label>
                    <input type="text" name="name" value="{{ old('name', $profileStaff ? $profileStaff->name : $user->name) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Email address</label>
                    <input type="email" name="email" value="{{ old('email', $profileStaff ? $profileStaff->email : $user->email) }}" required class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    @if($profileStaff)
                        <input type="tel" name="staff_phone" value="{{ old('staff_phone', $profileStaff->phone) }}" class="form-input" autocomplete="tel">
                    @else
                        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input" autocomplete="tel">
                    @endif
                </div>
                @if($profileStaff)
                <div>
                    <label class="form-label">Role</label>
                    <select name="staff_role" class="form-select">
                        @foreach(\App\Support\StaffJobRoles::options() as $slug => $label)
                            <option value="{{ $slug }}" {{ old('staff_role', $profileStaff->role ?? 'hair_stylist') === $slug ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Commission %</label>
                    <input type="number" name="staff_commission_rate" min="0" max="100" step="0.1"
                           value="{{ old('staff_commission_rate', $profileStaff->commission_rate ?? 0) }}" class="form-input">
                </div>
                @endif
                <div>
                    <label class="form-label">Experience</label>
                    @if($profileStaff)
                        <input type="text" name="staff_experience" value="{{ old('staff_experience', $profileStaff->experience) }}" class="form-input" placeholder="e.g. 5 years">
                    @else
                        <input type="text" name="experience" value="{{ old('experience', $user->experience) }}" class="form-input" placeholder="e.g. 5 years">
                    @endif
                </div>
                @php
                    $profileLangSelected = old('language_proficiency');
                    if (! is_array($profileLangSelected)) {
                        $profileLangSelected = \App\Support\LanguageProficiency::codesFromStored($profileStaff->language_proficiency ?? $user->language_proficiency);
                    }
                @endphp
                @include('settings.partials.language-proficiency-field', [
                    'name' => 'language_proficiency[]',
                    'selected' => $profileLangSelected,
                ])
                @if($profileStaff)
                <div>
                    <label class="form-label">Calendar colour</label>
                    <input type="color" name="staff_color" value="{{ old('staff_color', $profileStaff->color ?? '#7C3AED') }}"
                           class="w-full h-11 px-1 py-1 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 cursor-pointer">
                </div>
                <div>
                    <label class="form-label">Bio</label>
                    <textarea name="staff_bio" rows="3" class="form-textarea">{{ old('staff_bio', $profileStaff->bio) }}</textarea>
                </div>
                <div>
                    <label class="form-label">Awards &amp; accolades</label>
                    <textarea name="staff_awards_accolades" rows="3" class="form-textarea" placeholder="Certifications, press, industry awards…">{{ old('staff_awards_accolades', $profileStaff->awards_accolades) }}</textarea>
                </div>
                @if(($profileStaffServices ?? collect())->count())
                <div x-data="{
                        allServices: false,
                        syncAllServices() {
                            const boxes = this.$refs.serviceList
                                ? [...this.$refs.serviceList.querySelectorAll('input[name=\'staff_services[]\']')]
                                : [];
                            this.allServices = boxes.length > 0 && boxes.every(function (el) { return el.checked; });
                        },
                        setAllServices(checked) {
                            this.allServices = checked;
                            if (!this.$refs.serviceList) return;
                            this.$refs.serviceList.querySelectorAll('input[name=\'staff_services[]\']').forEach(function (el) {
                                el.checked = checked;
                            });
                        }
                    }"
                    x-init="syncAllServices()">
                    <label class="form-label">Services offered</label>
                    <label class="flex items-center gap-2 cursor-pointer mb-2 px-1">
                        <input type="checkbox"
                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600"
                               x-model="allServices"
                               @change="setAllServices(allServices)">
                        <span class="text-sm font-medium text-body">Select all services</span>
                    </label>
                    <div x-ref="serviceList" class="grid grid-cols-1 sm:grid-cols-2 gap-2 border border-gray-200 dark:border-gray-700 rounded-xl p-3 max-h-48 sm:max-h-40 overflow-y-auto bg-white dark:bg-gray-800">
                        @php
                            $selectedStaffServices = old('staff_services');
                            if (! is_array($selectedStaffServices)) {
                                $selectedStaffServices = $profileStaffAssignedServiceIds ?? [];
                            }
                            $selectedStaffServices = array_map('intval', $selectedStaffServices);
                        @endphp
                        @foreach($profileStaffServices as $svc)
                        <label class="flex items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20">
                            <input type="checkbox" name="staff_services[]" value="{{ $svc->id }}"
                                   {{ in_array((int) $svc->id, $selectedStaffServices, true) ? 'checked' : '' }}
                                   @change="syncAllServices()"
                                   class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                            <span class="text-sm text-body truncate">{{ $svc->name }}</span>
                        </label>
                        @endforeach
                    </div>
                    @error('staff_services')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                @endif
                <div>
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="hidden" name="staff_is_active" value="0">
                        <input type="checkbox" name="staff_is_active" value="1"
                               {{ old('staff_is_active', $profileStaff->is_active ?? true) ? 'checked' : '' }}
                               class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                        <span class="text-sm text-body">Active (shows in calendar and booking)</span>
                    </label>
                </div>
                @endif
                {{--
                <div>
                    <label class="form-label">Your timezone</label>
                    <select name="timezone" class="form-select">
                        <option value="">Same as salon / browser</option>
                        @foreach(\App\Helpers\TimezoneHelper::grouped() as $region => $zones)
                        <optgroup label="{{ $region }}">
                            @foreach($zones as $tz => $label)
                            <option value="{{ $tz }}" {{ old('timezone', $user->timezone) === $tz ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </optgroup>
                        @endforeach
                    </select>
                    <p class="form-hint">Used for account activity and notifications. Salon schedule and calendar use the <a href="{{ route('settings.index') }}?tab=salon" class="text-link">business timezone</a>.</p>
                </div>
                --}}
                <div>
                    <label class="form-label">Display language</label>
                    <select name="locale" class="form-select">
                        <option value="">Default (English)</option>
                        @foreach($localeOptions as $code => $label)
                        <option value="{{ $code }}" {{ old('locale', $user->locale) === $code ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="form-hint">Month and day names in dates follow this setting where supported.</p>
                </div>
                <button type="submit" class="btn-primary" :disabled="!canEditTab('profile')">Update Profile</button>
            </form>
        </div>
    </div>

    {{-- ── Team Members ── --}}
    <div x-show="tab==='team'" x-cloak class="space-y-5">
        <p x-show="!canEditTab('team')" x-cloak class="text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to manage team members here.</p>
        @if($settingsPersonalOnly)
        <div class="card">
            <h2 class="font-semibold text-heading mb-2">Team</h2>
            <p class="text-sm text-muted">Team members are managed by your salon admin.</p>
        </div>
        @else
        <div class="card">
            @php
                $mapMemberToRow = function ($member) {
                    $hasServices = \Illuminate\Support\Facades\DB::table('service_staff')
                        ->where('staff_id', $member->id)
                        ->exists();

                    return [
                        'id' => $member->id,
                        'name' => trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')),
                        'email' => $member->email,
                        'phone' => $member->phone,
                        'role' => $member->role,
                        'experience' => $member->experience,
                        'language_proficiency' => $member->language_proficiency,
                        'commission_rate' => $member->commission_rate,
                        'color' => $member->color ?: '#7C3AED',
                        'avatar' => $member->avatar,
                        'bio' => $member->bio,
                        'awards_accolades' => $member->awards_accolades,
                        'assign_services' => $hasServices ? '1' : '0',
                        'services' => $member->services()->withoutTenantScope()->pluck('services.id')->map(fn ($id) => (int) $id)->values()->all(),
                    ];
                };
                $staffRows = old('staff_members');
                if (! is_array($staffRows)) {
                    $staffRows = ($existingTeamMembers ?? collect())->map($mapMemberToRow)->all();
                } elseif (old('save_single_team_member') && count($staffRows) === 1) {
                    $fromDb = ($existingTeamMembers ?? collect())->map($mapMemberToRow)->all();
                    $incoming = $staffRows[0];
                    $incId = (int) ($incoming['id'] ?? 0);
                    if ($incId > 0) {
                        $staffRows = array_map(function ($r) use ($incoming, $incId) {
                            return ((int) ($r['id'] ?? 0) === $incId) ? array_merge($r, $incoming) : $r;
                        }, $fromDb);
                    } else {
                        $staffRows = array_merge($fromDb, [$incoming]);
                    }
                }
                if (count($staffRows) === 0) {
                    $staffRows = [[]];
                }
                $staffRoles = \App\Support\StaffJobRoles::options();
            @endphp
            <div class="mb-4 border-b border-gray-200/60 dark:border-gray-800 pb-3">
                <h2 class="font-semibold text-heading mb-1 flex flex-wrap items-center gap-x-2 gap-y-1">
                    <span>Team members <span class="text-gray-400 font-normal">(optional)</span></span>
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                        Total: {{ count($staffRows) }}
                    </span>
                </h2>
                <p class="form-hint text-xs sm:text-sm leading-relaxed">Add or update team members from your profile settings. Each row has its own arrow to show or hide that person’s fields, and its own Save button so you only submit one person at a time.</p>
            </div>
            <div id="settings-staff-rows" class="space-y-3 sm:space-y-4">
                @foreach($staffRows as $idx => $st)
                    @php $st = is_array($st) ? $st : []; @endphp
                    <div class="settings-staff-member-row rounded-xl border border-gray-200 bg-gray-50/80 dark:bg-gray-900/20 p-3 sm:p-4 min-w-0 overflow-hidden">
                        <div class="flex flex-wrap justify-between items-center gap-2 mb-3">
                            <span class="settings-staff-row-title text-sm font-medium text-body min-w-0">Team member {{ $loop->iteration }}</span>
                            <div class="flex items-center gap-1 shrink-0 ml-auto">
                                <button type="button"
                                        class="settings-staff-row-toggle inline-flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100/70 text-gray-600 transition hover:bg-gray-200/80 dark:bg-gray-800/70 dark:text-gray-200 dark:hover:bg-gray-700/80"
                                        aria-expanded="true"
                                        title="Show or hide this team member’s form">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="settings-staff-row-chevron h-5 w-5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </button>
                                <button type="button" class="settings-staff-remove-btn text-xs font-medium text-red-600 hover:text-red-700 {{ count($staffRows) <= 1 ? 'hidden' : '' }}">Remove</button>
                            </div>
                        </div>
                        <div class="settings-staff-row-body">
                        <form action="{{ route('settings.team-members') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf @method('PUT')
                            <input type="hidden" name="return_to" value="{{ $returnTo }}">
                            <input type="hidden" name="save_single_team_member" value="1">
                            <input type="hidden" name="staff_members[0][id]" value="{{ $st['id'] ?? '' }}">
                            <input type="hidden" name="staff_members[0][services_present]" value="1">
                            @php
                                $avatarUrl = \App\Models\Staff::resolvePublicAvatarUrl($st['avatar'] ?? null);
                                $nameLabel = trim((string) ($st['name'] ?? ''));
                                $nameParts = $nameLabel !== '' ? preg_split('/\s+/u', $nameLabel, -1, PREG_SPLIT_NO_EMPTY) : [];
                                $nameInitials = $nameParts === []
                                    ? '?'
                                    : (count($nameParts) === 1
                                        ? strtoupper(mb_substr($nameParts[0], 0, 2))
                                        : strtoupper(mb_substr($nameParts[0], 0, 1).mb_substr($nameParts[1], 0, 1)));
                                $isExistingMember = !empty($st['id']);
                            @endphp
                            <div>
                                <label class="block text-xs font-medium text-body mb-1">Photo @if(!$avatarUrl)<span class="text-red-500">*</span>@endif</label>
                                <div class="flex flex-col sm:flex-row sm:items-start gap-3">
                                    <x-staff-avatar size="sm" :url="$avatarUrl" :initials="$nameInitials" :color="$st['color'] ?? '#7C3AED'" />
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <input type="file" name="staff_member_avatar" accept="image/jpeg,image/png,image/webp"
                                               @if(!$avatarUrl) required @endif
                                               class="form-input text-xs w-full max-w-full file:mr-2 file:py-1 file:px-2 file:rounded-lg file:border-0 file:bg-velour-50 file:text-velour-700 dark:file:bg-velour-900/40 dark:file:text-velour-200 file:max-w-[calc(100%-0.5rem)]">
                                        <p class="text-[11px] text-muted">JPG, PNG or WebP · max 2 MB</p>
                                        @if($avatarUrl)
                                            <label class="inline-flex items-center gap-2 text-xs text-body cursor-pointer">
                                                <input type="checkbox" name="staff_member_remove_avatar" value="1" class="rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                                Remove current photo
                                            </label>
                                        @endif
                                        @error('staff_member_avatar')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-body mb-1">Full name <span class="text-red-500">*</span></label>
                                    <input type="text" name="staff_members[0][name]" value="{{ $st['name'] ?? '' }}"
                                           required
                                           class="form-input"
                                           placeholder="e.g. Alex Smith">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Email</label>
                                    <input type="email" name="staff_members[0][email]" value="{{ $st['email'] ?? '' }}" autocomplete="off"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Phone</label>
                                    <input type="tel" name="staff_members[0][phone]" value="{{ $st['phone'] ?? '' }}"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Role <span class="text-red-500">*</span></label>
                                    <select name="staff_members[0][role]" required class="form-select">
                                        <option value="">-</option>
                                        @foreach($staffRoles as $slug => $label)
                                            <option value="{{ $slug }}" {{ ($st['role'] ?? '') === $slug ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Commission %</label>
                                    <input type="number" name="staff_members[0][commission_rate]" min="0" max="100" step="0.1"
                                           value="{{ $st['commission_rate'] ?? '0' }}"
                                           class="form-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Experience</label>
                                    <input type="text" name="staff_members[0][experience]" value="{{ $st['experience'] ?? '' }}"
                                           class="form-input" placeholder="e.g. 5 years">
                                </div>
                                <div class="sm:col-span-2">
                                    @php
                                        $staffLangSelected = old('staff_members.0.language_proficiency');
                                        if (! is_array($staffLangSelected)) {
                                            $staffLangSelected = \App\Support\LanguageProficiency::codesFromStored($st['language_proficiency'] ?? '');
                                        }
                                    @endphp
                                    @include('settings.partials.language-proficiency-field', [
                                        'name' => 'staff_members[0][language_proficiency][]',
                                        'selected' => $staffLangSelected,
                                        'hint' => 'Languages this team member uses with clients (standard codes).',
                                    ])
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-body mb-1">Calendar colour</label>
                                    <input type="color" name="staff_members[0][color]" value="{{ $st['color'] ?? '#7C3AED' }}"
                                           class="w-full h-11 px-1 py-1 rounded-xl border border-gray-300 cursor-pointer bg-white">
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-body mb-1">Bio</label>
                                    <textarea name="staff_members[0][bio]" rows="2" placeholder="Optional"
                                              class="form-textarea">{{ $st['bio'] ?? '' }}</textarea>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-body mb-1">Awards &amp; accolades</label>
                                    <textarea name="staff_members[0][awards_accolades]" rows="2" placeholder="Optional — certifications, press, awards…"
                                              class="form-textarea">{{ $st['awards_accolades'] ?? '' }}</textarea>
                                </div>
                            </div>
                            <input type="hidden" name="staff_members[0][assign_services]" value="0">
                            @php
                                $as = $st['assign_services'] ?? null;
                                $assignChecked = (string) $as === '1' || $as === true || $as === 1;
                            @endphp
                            <label class="flex items-start gap-2.5 text-sm text-body cursor-pointer">
                                <input type="checkbox" name="staff_members[0][assign_services]" value="1"
                                       class="settings-staff-assign-services mt-0.5 shrink-0 rounded border-gray-300 dark:border-gray-600 text-velour-600"
                                       {{ $assignChecked ? 'checked' : '' }}>
                                <span class="min-w-0 break-words">Offer all services on this menu to this team member</span>
                            </label>
                            @php
                                $rowServiceOptions = (array) ($teamServices ?? []);
                                $selectedRowServices = old('staff_members.0.services');
                                if (! is_array($selectedRowServices)) {
                                    $selectedRowServices = array_map('intval', (array) ($st['services'] ?? []));
                                } else {
                                    $selectedRowServices = array_map('intval', $selectedRowServices);
                                }
                            @endphp
                            <div>
                                <label class="block text-xs font-medium text-body mb-1">Services offered</label>
                                <p class="text-xs text-muted mb-2">Only services with both time and price configured are shown.</p>
                                @if(count($rowServiceOptions))
                                    <div data-staff-service-list class="grid grid-cols-1 sm:grid-cols-2 gap-2 border border-gray-200 dark:border-gray-700 rounded-xl p-3 max-h-52 sm:max-h-44 overflow-y-auto bg-white dark:bg-gray-800">
                                        @foreach($rowServiceOptions as $svc)
                                            @php $svcId = (int) ($svc['id'] ?? 0); @endphp
                                            <label class="flex items-start sm:items-center gap-2 cursor-pointer p-1.5 rounded-lg hover:bg-velour-50 dark:hover:bg-velour-900/20 min-w-0">
                                                <input type="checkbox" name="staff_members[0][services][]" value="{{ $svcId }}"
                                                       {{ in_array($svcId, $selectedRowServices, true) ? 'checked' : '' }}
                                                       class="settings-staff-service-cb rounded border-gray-300 dark:border-gray-600 text-velour-600">
                                                <span class="text-sm text-body break-words sm:truncate min-w-0">{{ (string) ($svc['name'] ?? '') }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 px-3 py-2 text-xs text-muted">
                                        No services available yet.
                                    </div>
                                @endif
                            </div>
                            @error('staff_members.0.name')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('staff_members.0.email')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('staff_members.0.role')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('staff_members.0.id')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('staff_members.0.language_proficiency')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            @error('staff_members.0.services')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                            <button type="submit" class="btn-primary w-full sm:w-auto" :disabled="!canEditTab('team')">Save this team member</button>
                        </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="settings-add-staff-member" class="mt-2 text-sm font-medium text-velour-600 hover:text-velour-700">+ Add another team member</button>
            @error('staff_members')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        @endif
    </div>

    {{-- ── Security / 2FA ── --}}
    <div x-show="tab==='security'" x-cloak class="space-y-5">
        <p x-show="!canEditTab('security')" x-cloak class="text-sm text-amber-800 dark:text-amber-200 rounded-xl border border-amber-200/80 dark:border-amber-800/60 bg-amber-50 dark:bg-amber-950/40 px-4 py-2.5">View only — you do not have permission to change security settings.</p>
        <div class="card">
            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                <div class="min-w-0">
                    <h2 class="font-semibold text-heading">Two-Factor Authentication</h2>
                    <p class="text-xs text-muted mt-1">Add an extra layer of security to your account with 2FA.</p>
                </div>
                @if($user->hasTwoFactorEnabled())
                <span class="badge-green px-3 py-1 text-xs font-semibold rounded-xl">Enabled</span>
                @else
                <span class="badge-gray px-3 py-1 text-xs font-semibold rounded-xl">Disabled</span>
                @endif
            </div>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('two-factor.setup') }}" class="btn-primary">
                    {{ $user->hasTwoFactorEnabled() ? 'Manage 2FA' : 'Enable 2FA' }}
                </a>
                @if($user->hasTwoFactorEnabled())
                <a href="{{ route('two-factor.recovery') }}" class="btn-outline">Recovery codes</a>
                @endif
            </div>
        </div>

        <div class="card">
            <h2 class="font-semibold text-heading mb-1">Login history</h2>
            <p class="text-xs text-muted mb-4">Your last recorded sign-in.</p>
            <div class="flex items-center gap-3 bg-gray-50 dark:bg-gray-800/60 rounded-xl px-4 py-3 text-sm">
                <svg class="w-4 h-4 text-muted flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-body">
                    Last login:
                    <strong class="text-heading">{{ $user->last_login_at ? \App\Support\DisplayFormatter::userDateTime($user, $currentSalon ?? null, $user->last_login_at) : 'Unknown' }}</strong>
                </span>
            </div>
        </div>

        @if(config('billing.subscriptions_enabled'))
        <div class="card border-red-200 dark:border-red-900/50 p-6">
            <h2 class="font-semibold text-heading mb-1">Danger Zone</h2>
            <p class="text-xs text-muted mb-4">Actions here are irreversible. Proceed with caution.</p>
            <a href="{{ route('billing.cancel') }}"
               class="btn border border-red-300 dark:border-red-700 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                Cancel subscription
            </a>
        </div>
        @endif
    </div>

        </div>{{-- /.settings-main-panel --}}
    </div>{{-- /flex layout --}}

{{-- Password Modal (opened from Profile gear icon) --}}
<x-modal-overlay show="showPasswordModal"
     x-transition.opacity
     @keydown.escape.window="showPasswordModal = false">
    <div class="w-full max-w-lg rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 p-6 shadow-2xl" @click.stop>
        <div class="mb-5 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-heading">Change Password</h3>
            <button type="button"
                    @click="showPasswordModal = false"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-muted hover:bg-gray-100 dark:hover:bg-gray-800"
                    aria-label="Close password modal">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('settings.password') }}" method="POST" class="space-y-4">
            @csrf @method('PUT')
            <input type="hidden" name="return_to" value="{{ $returnTo }}">
            <div>
                <label class="form-label">Current password</label>
                <input type="password" name="current_password" required class="form-input">
                @error('current_password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">New password</label>
                <input type="password" name="password" required autocomplete="new-password" class="form-input">
                @error('password')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="form-label">Confirm new password</label>
                <input type="password" name="password_confirmation" required class="form-input">
            </div>
            <div class="pt-1 flex items-center gap-2">
                <button type="submit" class="btn-primary" :disabled="!canEditTab('security')">Change Password</button>
                <button type="button" @click="showPasswordModal = false" class="btn-outline">Cancel</button>
            </div>
        </form>
    </div>
</x-modal-overlay>

</div>

<script>
(function () {
    function detectRegionCode() {
        try {
            if (typeof Intl !== 'undefined' && typeof Intl.Locale === 'function' && navigator.language) {
                var locale = new Intl.Locale(navigator.language);
                if (locale && locale.region) return String(locale.region).toUpperCase();
            }
        } catch (e) {}

        var lang = String(navigator.language || '').toUpperCase();
        var parts = lang.split(/[-_]/);
        return parts.length > 1 ? parts[1] : '';
    }

    function detectCurrencyByRegion(region) {
        var map = {
            IN: 'INR', US: 'USD', GB: 'GBP', EU: 'EUR', DE: 'EUR', FR: 'EUR', ES: 'EUR', IT: 'EUR', NL: 'EUR', PT: 'EUR',
            IE: 'EUR', BE: 'EUR', AT: 'EUR', FI: 'EUR', GR: 'EUR', LU: 'EUR', SI: 'EUR', SK: 'EUR', LV: 'EUR', LT: 'EUR',
            EE: 'EUR', CY: 'EUR', MT: 'EUR', CA: 'CAD', AU: 'AUD', NZ: 'NZD', SG: 'SGD', AE: 'AED', SA: 'SAR', QA: 'QAR',
            KW: 'KWD', BH: 'BHD', OM: 'OMR', JP: 'JPY', KR: 'KRW', CN: 'CNY', HK: 'HKD', TW: 'TWD', TH: 'THB', MY: 'MYR',
            ID: 'IDR', PH: 'PHP', VN: 'VND', PK: 'PKR', BD: 'BDT', NP: 'NPR', LK: 'LKR', ZA: 'ZAR', NG: 'NGN', KE: 'KES',
            GH: 'GHS', CH: 'CHF', SE: 'SEK', NO: 'NOK', DK: 'DKK', PL: 'PLN', CZ: 'CZK', HU: 'HUF', RO: 'RON', TR: 'TRY',
            BR: 'BRL', MX: 'MXN', AR: 'ARS', CL: 'CLP', CO: 'COP', PE: 'PEN', UY: 'UYU'
        };
        return map[region] || '';
    }

    function detectCurrencyByTimezone(timezone) {
        var tz = String(timezone || '');
        if (tz.indexOf('Asia/Kolkata') === 0) return 'INR';
        if (tz.indexOf('Europe/London') === 0) return 'GBP';
        if (tz.indexOf('Europe/') === 0) return 'EUR';
        if (tz.indexOf('America/') === 0) return 'USD';
        if (tz.indexOf('Asia/Dubai') === 0) return 'AED';
        return '';
    }

    function setAutosaveHint(message, tone) {
        var hint = document.getElementById('settings-location-autosave-hint');
        if (!hint) return;
        hint.classList.remove('hidden', 'text-green-600', 'text-red-500', 'text-gray-500');
        hint.classList.add(tone === 'error' ? 'text-red-500' : (tone === 'success' ? 'text-green-600' : 'text-gray-500'));
        hint.textContent = message;
    }

    function detectTimezoneByRegion(region) {
        var map = {
            IN: 'Asia/Kolkata',
            GB: 'Europe/London',
            US: 'America/New_York',
            AE: 'Asia/Dubai',
            SG: 'Asia/Singapore',
            AU: 'Australia/Sydney',
            CA: 'America/Toronto',
            NZ: 'Pacific/Auckland'
        };
        return map[region] || '';
    }

    function pickTimezoneOption(timezoneSelect, region, detectedTimezone) {
        var candidates = [];
        if (detectedTimezone) candidates.push(detectedTimezone);
        if (region === 'IN') {
            // Ensure India always resolves to IST if available.
            candidates.unshift('Asia/Kolkata', 'Asia/Calcutta');
        } else {
            var byRegion = detectTimezoneByRegion(region);
            if (byRegion) candidates.push(byRegion);
        }

        for (var i = 0; i < candidates.length; i++) {
            var tz = candidates[i];
            if (!tz) continue;
            var option = timezoneSelect.querySelector('option[value="' + tz + '"]');
            if (option) return tz;
        }
        return '';
    }

    function autoFillSalonLocationSettings() {
        var form = document.getElementById('settings-salon-form');
        var detectBtn = document.getElementById('settings-detect-location-btn');
        var timezoneSelect = document.getElementById('settings-salon-timezone');
        var currencySelect = document.getElementById('settings-salon-currency');
        if (!form || !detectBtn || !timezoneSelect || !currencySelect) return;

        function applyDetectedLocation() {
            setAutosaveHint('Detecting current location...', 'neutral');

            var timezone = '';
            try {
                timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            } catch (e) {}

            var region = detectRegionCode();
            var pickedTimezone = pickTimezoneOption(timezoneSelect, region, timezone);
            if (pickedTimezone) {
                timezoneSelect.value = pickedTimezone;
                timezone = pickedTimezone;
            }

            var currency = detectCurrencyByTimezone(timezone) || detectCurrencyByRegion(region);
            if (currency) {
                var currencyOption = currencySelect.querySelector('option[value="' + currency + '"]');
                if (currencyOption) {
                    currencySelect.value = currency;
                }
            }

            if (timezone && currency) {
                setAutosaveHint('Detected and selected. Click Save Changes to apply.', 'success');
            } else if (timezone || currency) {
                setAutosaveHint('Partially detected. Please review and click Save Changes.', 'neutral');
            } else {
                setAutosaveHint('Could not detect location automatically. Please select manually.', 'error');
            }
        }

        detectBtn.addEventListener('click', applyDetectedLocation);
    }

    document.addEventListener('DOMContentLoaded', autoFillSalonLocationSettings);
})();

(function () {
    var input = document.getElementById('settings-custom-business-type-input');
    var addBtn = document.getElementById('settings-add-custom-business-type-btn');
    var list = document.getElementById('settings-custom-business-type-list');
    var typeList = document.getElementById('settings-business-types-list');
    if (!input || !addBtn || !list || !typeList) return;

    function hasValue(value) {
        var wanted = value.trim().toLowerCase();
        var exists = false;
        list.querySelectorAll('input[name="custom_business_types[]"]').forEach(function (el) {
            if (String(el.value || '').trim().toLowerCase() === wanted) {
                exists = true;
            }
        });
        return exists;
    }

    function hasRenderedCustomCheckbox(value) {
        var wanted = value.trim().toLowerCase();
        var exists = false;
        typeList.querySelectorAll('label[data-custom-draft="1"], label[data-custom-existing="1"]').forEach(function (label) {
            var nameNode = label.querySelector('.settings-business-type-name');
            var txt = nameNode ? nameNode.textContent : label.textContent;
            if (String(txt || '').trim().toLowerCase() === wanted) {
                exists = true;
            }
        });
        return exists;
    }

    function slugify(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .replace(/&/g, 'and')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/(^-|-$)/g, '');
    }

    function addImmediateCheckedCustomCheckbox(value) {
        if (hasRenderedCustomCheckbox(value)) return;

        var baseSlug = slugify(value) || 'custom-type';
        var slug = baseSlug;
        var n = 1;
        while (typeList.querySelector('input[name="business_type_ids[]"][data-bt-slug="' + slug + '"]')) {
            slug = baseSlug + '-' + n;
            n++;
        }

        var label = document.createElement('label');
        label.className = 'inline-flex items-center gap-2 text-sm text-body cursor-pointer';
        label.setAttribute('data-custom-draft', '1');
        label.innerHTML =
            '<input type="checkbox" checked disabled class="rounded border-gray-300 text-velour-600 opacity-70 cursor-not-allowed">' +
            '<span class="settings-business-type-name"></span>' +
            '<span class="text-[10px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-velour-100 dark:bg-velour-900/30 text-velour-600 dark:text-velour-300">new</span>';
        label.querySelector('.settings-business-type-name').textContent = value;
        typeList.appendChild(label);
    }

    function addCustomType(raw) {
        var value = String(raw || '').trim();
        if (!value || hasValue(value)) return;

        var pill = document.createElement('span');
        pill.className = 'settings-custom-business-pill inline-flex items-center gap-2 rounded-full bg-velour-100/80 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 px-3 py-1 text-xs';
        pill.innerHTML = '<span></span><button type="button" class="settings-custom-business-remove leading-none">x</button><input type="hidden" name="custom_business_types[]" />';
        pill.querySelector('span').textContent = value;
        pill.querySelector('input[name="custom_business_types[]"]').value = value;
        list.appendChild(pill);
        addImmediateCheckedCustomCheckbox(value);
    }

    addBtn.addEventListener('click', function () {
        addCustomType(input.value);
        input.value = '';
        input.focus();
    });

    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addBtn.click();
        }
    });

    list.addEventListener('click', function (e) {
        var btn = e.target.closest('.settings-custom-business-remove');
        if (!btn) return;
        var pill = btn.closest('.settings-custom-business-pill');
        if (!pill) return;
        var hidden = pill.querySelector('input[name="custom_business_types[]"]');
        var value = hidden ? String(hidden.value || '').trim().toLowerCase() : '';
        pill.remove();

        if (value) {
            typeList.querySelectorAll('label[data-custom-draft="1"]').forEach(function (label) {
                var nameNode = label.querySelector('.settings-business-type-name');
                var txt = nameNode ? nameNode.textContent : '';
                if (String(txt || '').trim().toLowerCase() === value) {
                    label.remove();
                }
            });
        }
    });
})();

(function () {
    function syncServiceSetupVisibility() {
        var selectedSlugs = {};
        var selectedCategoryIds = {};
        var selectedCountBySlug = {};

        document.querySelectorAll('#settings-business-types-list input[name="business_type_ids[]"]:checked').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            if (slug) selectedSlugs[slug] = true;
        });

        var anyCategoryVisible = false;
        document.querySelectorAll('.settings-service-category-option').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var show = !!(slug && selectedSlugs[slug]);
            el.classList.toggle('hidden', !show);
            if (show) anyCategoryVisible = true;

            var chk = el.querySelector('input[type="checkbox"]');
            if (!chk) return;
            if (!show) chk.checked = false;
            if (show && chk.checked) {
                var catId = el.getAttribute('data-cat-id');
                selectedCategoryIds[catId] = true;
                selectedCountBySlug[slug] = (selectedCountBySlug[slug] || 0) + 1;
            }
        });

        var anyServiceVisible = false;
        document.querySelectorAll('.settings-service-offer-option').forEach(function (el) {
            var slug = el.getAttribute('data-bt-slug');
            var catId = el.getAttribute('data-cat-id');
            var show = !!(slug && selectedSlugs[slug]);

            // Keep services hidden until at least one category is selected for this slug.
            if (show) {
                var hasSelectedCategoryForSlug = (selectedCountBySlug[slug] || 0) > 0;
                show = hasSelectedCategoryForSlug && !!selectedCategoryIds[catId];
            }

            el.classList.toggle('hidden', !show);
            if (show) anyServiceVisible = true;

            var chk = el.querySelector('input[type="checkbox"]');
            var meta = el.querySelector('.settings-service-meta-grid');
            var metaInputs = el.querySelectorAll('.settings-service-meta-input');
            if (!show && chk) chk.checked = false;

            var checkedAndVisible = !!(show && chk && chk.checked);
            if (meta) meta.classList.toggle('hidden', !checkedAndVisible);
            metaInputs.forEach(function (inp) {
                inp.disabled = !checkedAndVisible;
                inp.required = checkedAndVisible;
            });
        });

        document.querySelectorAll('.settings-service-offers-type-bundle').forEach(function (bundle) {
            var slug = bundle.getAttribute('data-bt-slug');
            var show = !!(slug && selectedSlugs[slug]);
            bundle.classList.toggle('hidden', !show);
        });
        document.querySelectorAll('.settings-service-offers-cat-bundle').forEach(function (bundle) {
            var any = false;
            bundle.querySelectorAll('.settings-service-offer-option').forEach(function (row) {
                if (!row.classList.contains('hidden')) {
                    any = true;
                }
            });
            bundle.classList.toggle('hidden', !any);
        });

        var categoriesWrap = document.getElementById('settings-service-categories-list');
        if (categoriesWrap) categoriesWrap.classList.toggle('opacity-50', !anyCategoryVisible);

        var servicesWrap = document.getElementById('settings-service-offers-list');
        if (servicesWrap) servicesWrap.classList.toggle('opacity-50', !anyServiceVisible);
    }

    document.addEventListener('change', function (e) {
        if (!e.target) return;
        if (
            e.target.matches('#settings-business-types-list input[name="business_type_ids[]"]') ||
            e.target.closest('.settings-service-category-option') ||
            e.target.classList.contains('settings-service-checkbox')
        ) {
            syncServiceSetupVisibility();
        }
    });
    document.addEventListener('DOMContentLoaded', syncServiceSetupVisibility);
})();

(function () {
    var maxRows = 10;
    var container = document.getElementById('settings-staff-rows');
    var addBtn = document.getElementById('settings-add-staff-member');
    if (!container || !addBtn) return;

    function renumberStaffRows() {
        var rows = container.querySelectorAll('.settings-staff-member-row');
        rows.forEach(function (row, i) {
            var title = row.querySelector('.settings-staff-row-title');
            if (title) title.textContent = 'Team member ' + (i + 1);
            var rm = row.querySelector('.settings-staff-remove-btn');
            if (rm) rm.classList.toggle('hidden', rows.length <= 1);
        });
    }

    addBtn.addEventListener('click', function () {
        var rows = container.querySelectorAll('.settings-staff-member-row');
        if (rows.length >= maxRows) return;
        var clone = rows[0].cloneNode(true);
        clone.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"], input[type="number"], textarea').forEach(function (el) {
            el.value = '';
        });
        clone.querySelectorAll('input[type="color"]').forEach(function (el) {
            el.value = '#7C3AED';
        });
        clone.querySelectorAll('select').forEach(function (el) {
            el.selectedIndex = 0;
        });
        clone.querySelectorAll('input[type="checkbox"]').forEach(function (el) {
            el.checked = false;
        });
        clone.querySelectorAll('input[type="hidden"]').forEach(function (el) {
            if (el.name && el.name.indexOf('[id]') !== -1) el.value = '';
            if (el.name && el.name.indexOf('assign_services') !== -1) el.value = '0';
        });
        var toggle = clone.querySelector('.settings-staff-row-toggle');
        var body = clone.querySelector('.settings-staff-row-body');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'true');
            var chev = toggle.querySelector('.settings-staff-row-chevron');
            if (chev) chev.classList.remove('rotate-180');
        }
        if (body) body.classList.remove('hidden');
        container.appendChild(clone);
        renumberStaffRows();
    });

    container.addEventListener('click', function (e) {
        var tbtn = e.target.closest('.settings-staff-row-toggle');
        if (tbtn && container.contains(tbtn)) {
            var row = tbtn.closest('.settings-staff-member-row');
            var body = row && row.querySelector('.settings-staff-row-body');
            if (body) {
                var open = tbtn.getAttribute('aria-expanded') !== 'false';
                var next = !open;
                tbtn.setAttribute('aria-expanded', next ? 'true' : 'false');
                body.classList.toggle('hidden', !next);
                var chev = tbtn.querySelector('.settings-staff-row-chevron');
                if (chev) chev.classList.toggle('rotate-180', !next);
            }
            return;
        }
        var btn = e.target.closest('.settings-staff-remove-btn');
        if (!btn) return;
        var row = btn.closest('.settings-staff-member-row');
        if (!row || container.querySelectorAll('.settings-staff-member-row').length <= 1) return;
        row.remove();
        renumberStaffRows();
    });

    function setRowServiceChecks(row, checked) {
        var list = row.querySelector('[data-staff-service-list]');
        if (!list) return;
        list.querySelectorAll('.settings-staff-service-cb').forEach(function (cb) {
            cb.checked = checked;
        });
    }

    function syncRowAssignFromServices(row) {
        var assignCb = row.querySelector('.settings-staff-assign-services');
        var list = row.querySelector('[data-staff-service-list]');
        if (!assignCb || !list) return;
        var boxes = list.querySelectorAll('.settings-staff-service-cb');
        assignCb.checked = boxes.length > 0 && Array.prototype.every.call(boxes, function (cb) { return cb.checked; });
    }

    container.addEventListener('change', function (e) {
        var row = e.target.closest('.settings-staff-member-row');
        if (!row) return;
        if (e.target.classList.contains('settings-staff-assign-services')) {
            setRowServiceChecks(row, e.target.checked);
            return;
        }
        if (e.target.classList.contains('settings-staff-service-cb')) {
            syncRowAssignFromServices(row);
        }
    });

    container.querySelectorAll('.settings-staff-member-row').forEach(function (row) {
        var assignCb = row.querySelector('.settings-staff-assign-services');
        if (assignCb && assignCb.checked) {
            setRowServiceChecks(row, true);
        } else {
            syncRowAssignFromServices(row);
        }
    });

    renumberStaffRows();
})();
</script>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
  Alpine.data('settingsPage', (cfg) => ({
    ...cfg,
    canEditTab(tab) {
      return this.canEdit[tab] !== false;
    },
  }));
});
</script>
@endpush

@endsection
