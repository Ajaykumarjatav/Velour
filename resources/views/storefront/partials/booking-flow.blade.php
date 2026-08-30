@php
    $slug = $salon->slug;
    $currency = $data['salon']['currency_symbol'] ?? \App\Helpers\CurrencyHelper::symbol(\App\Helpers\CurrencyHelper::defaultCode());
    $salonName = $data['salon']['name'] ?? $salon->name;
    $onlineBookingEnabled = (bool) ($data['salon']['online_booking_enabled'] ?? $salon->online_booking_enabled);
@endphp
<div x-data="storefrontBooking(@js([
    'slug' => $slug,
    'apiBase' => $apiBase,
    'currency' => $currency,
    'salonName' => $salonName,
    'onlineBookingEnabled' => $onlineBookingEnabled,
]))"
     x-init="init()"
     @storefront-booking-toggle.window="onBookingToggle($event.detail.open)"
     @storefront-book-preselect.window="onBookPreselect($event.detail)"
     x-show="open"
     x-cloak
     class="storefront-booking-overlay">
    <template x-if="!onlineBookingEnabled">
        <div class="min-h-screen flex flex-col">
            <header class="border-b border-white/10 px-4 py-4 max-w-2xl mx-auto w-full flex items-center justify-between">
                <span class="font-manrope font-bold text-lg" x-text="salonName"></span>
                <button type="button" @click="close()" class="text-sm text-white/60 hover:text-white">← Back to site</button>
            </header>
            <main class="flex-1 max-w-lg mx-auto px-4 py-16 text-center">
                <div class="w-16 h-16 rounded-full bg-white/10 flex items-center justify-center mx-auto mb-6 text-2xl">⏸</div>
                <h1 class="text-2xl font-bold mb-3">Online booking is offline</h1>
                <p class="text-white/70 text-sm mb-8">This salon has turned offline booking off for now. Please contact them directly to book an appointment.</p>
                <button type="button" @click="close()" class="inline-flex px-6 py-3 rounded-full bg-primary text-white font-semibold">Back to website</button>
            </main>
        </div>
    </template>

    <template x-if="onlineBookingEnabled && step === 5 && bookingRef">
        <div class="min-h-screen">
            <header class="border-b border-white/10 px-4 py-4 max-w-2xl mx-auto flex items-center justify-between">
                <span class="font-manrope font-bold text-lg" x-text="salonName"></span>
                <button type="button" @click="close()" class="text-sm text-white/60 hover:text-white">← Back to site</button>
            </header>
            <main class="max-w-lg mx-auto px-4 py-12 text-center">
                <div class="w-20 h-20 rounded-full bg-primary flex items-center justify-center mx-auto mb-6 text-3xl shadow-lg shadow-primary/30">✓</div>
                <h1 class="text-2xl font-bold mb-2" x-text="bookingStatus === 'pending' ? 'Request received!' : 'You\'re all booked!'"></h1>
                <p class="text-white/70 text-sm mb-4" x-html="bookingStatus === 'pending' ? 'We\'ve received your booking request. You\'ll get a confirmation at <strong class=\'text-white\'>' + client.email + '</strong> once the salon approves it.' : 'Confirmation sent to <strong class=\'text-white\'>' + client.email + '</strong>'"></p>
                <p x-show="bookingRef" class="inline-block bg-white/10 rounded-full px-4 py-1 text-xs font-mono mb-8" x-text="'Ref: ' + bookingRef"></p>
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-left text-sm space-y-3 mb-8">
                    <div class="flex justify-between gap-4"><span class="text-white/50">Services</span><span class="font-semibold text-right" x-text="selected.services.map(s => s.name).join(', ')"></span></div>
                    <div class="flex justify-between"><span class="text-white/50">Date</span><span class="font-semibold" x-text="confirmDisplay?.date_long || formatDate(selected.date)"></span></div>
                    <div class="flex justify-between"><span class="text-white/50">Time</span><span class="font-semibold" x-text="confirmDisplay?.time || selected.slot?.time"></span></div>
                    <div class="flex justify-between"><span class="text-white/50">With</span><span class="font-semibold" x-text="staffDisplayName()"></span></div>
                </div>
                <button type="button" @click="resetBooking()" class="bg-primary hover:bg-primary-dark text-white font-semibold rounded-full px-8 py-3">Book another appointment</button>
            </main>
        </div>
    </template>

    <template x-if="onlineBookingEnabled && !(step === 5 && bookingRef)">
        <div class="sf-booking-shell">
            <header class="sticky top-0 z-50 bg-black/95 backdrop-blur border-b border-white/10 shrink-0">
                <div class="sf-booking-container sf-booking-header">
                    <div class="flex items-start justify-between gap-3">
                        <button type="button" @click="goBackInFlow()" class="sf-booking-back-btn">
                            <span x-text="headerBackLabel()"></span>
                        </button>
                        <div class="text-right min-w-0 flex-1">
                            <p class="sf-booking-brand-name truncate" x-text="salonName"></p>
                            <p class="sf-booking-brand-tag">Book online</p>
                        </div>
                        <button type="button" x-show="step > 0" @click="close()" x-cloak class="sf-booking-close-btn">Close</button>
                        <span x-show="step === 0" class="w-12 shrink-0" aria-hidden="true"></span>
                    </div>
                </div>
                <div class="sf-booking-container">
                    <div class="sf-stepper" role="navigation" aria-label="Booking progress">
                        <template x-for="(label, i) in steps" :key="'step-' + i">
                            <div style="display: contents">
                                <div class="sf-stepper-item"
                                     :class="{ 'is-active': i === step, 'is-done': i < step }">
                                    <span class="sf-stepper-dot">
                                        <span x-show="i < step" aria-hidden="true">✓</span>
                                        <span x-show="i >= step" x-text="i + 1"></span>
                                    </span>
                                    <span class="sf-stepper-label" x-text="label"></span>
                                </div>
                                <span x-show="i < steps.length - 1"
                                      class="sf-stepper-line"
                                      :class="{ 'is-done': i < step }"
                                      aria-hidden="true"></span>
                            </div>
                        </template>
                    </div>
                </div>
            </header>

            <main class="sf-booking-main">
                <div class="sf-booking-container">
                    <p x-show="globalError" class="bg-red-500/20 border border-red-500/40 text-red-200 rounded-xl p-4 mb-4 text-sm shrink-0" x-text="globalError"></p>

                    <div class="sf-booking-layout">
                        <div class="sf-booking-primary">
                <div x-show="loading && step === 0" class="text-white/60 text-center py-12">Loading services…</div>

                {{-- Step 0: Services --}}
                <div x-show="step === 0 && !loading" class="sf-booking-card">
                    <div class="sf-booking-card-body">
                        <h2 class="sf-booking-section-title">Select Services</h2>

                        <div x-show="bookCategories.length > 0 || bookPackages.length > 0">
                            <div class="sf-search-wrap">
                                <svg class="w-4 h-4 shrink-0 text-white/45" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 100-15 7.5 7.5 0 000 15z"/>
                                </svg>
                                <input type="search" x-model="serviceSearch" placeholder="Search services, packages…"
                                       class="sf-search-input" style="-webkit-appearance:none;">
                                <button type="button" x-show="serviceSearch" @click="serviceSearch = ''" x-cloak
                                        class="shrink-0 text-white/45 hover:text-white p-0.5" aria-label="Clear search">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div x-show="!serviceSearch.trim() && popularServices().length > 0" class="sf-popular-row">
                                <span class="sf-popular-label">Popular:</span>
                                <template x-for="name in popularServices()" :key="'pop-' + name">
                                    <button type="button" @click="quickSearch(name)" class="sf-popular-chip" x-text="name"></button>
                                </template>
                            </div>
                        </div>

                        <template x-if="bookCategories.length === 0 && bookPackages.length === 0">
                            <p class="text-white/50 text-sm py-8 text-center">No services available for booking.</p>
                        </template>

                        <template x-if="bookCategories.length > 0 || bookPackages.length > 0">
                            <div class="sf-services-scroll scrollbar-none space-y-2">

                                {{-- Search results --}}
                                <template x-if="serviceSearch.trim()">
                                    <div class="space-y-0">
                                        <p class="text-[11px] font-medium uppercase tracking-wider text-white/40 mb-2"
                                           x-text="searchResultCount() + ' result' + (searchResultCount() === 1 ? '' : 's')"></p>
                                        <template x-for="pkg in filteredPackages()" :key="'search-pkg-' + pkg.id">
                                            <div @click="togglePackage(pkg)"
                                                 :class="isPackageSelected(pkg) ? 'is-selected' : ''"
                                                 class="sf-svc-row">
                                                <span class="sf-svc-check" aria-hidden="true">✓</span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="sf-svc-name truncate" x-text="pkg.name"></p>
                                                    <p class="sf-svc-meta">Package · <span x-text="(pkg.duration_minutes || 0) + ' min'"></span></p>
                                                </div>
                                                <span class="sf-svc-price tabular-nums" x-text="formatPrice(pkg.price)"></span>
                                            </div>
                                        </template>
                                        <template x-for="svc in filteredServices()" :key="'search-svc-' + svc.id">
                                            <div @click="toggleService(svc)"
                                                 :class="isSelected(svc) ? 'is-selected' : ''"
                                                 class="sf-svc-row">
                                                <span class="sf-svc-check" aria-hidden="true">✓</span>
                                                <div class="min-w-0 flex-1">
                                                    <p class="sf-svc-name truncate" x-text="svc.name"></p>
                                                    <p class="sf-svc-meta">
                                                        <span x-show="svc._categoryName" x-text="svc._categoryName"></span>
                                                        <span x-show="svc._categoryName"> · </span>
                                                        <span x-text="(svc.duration_minutes || 0) + ' min'"></span>
                                                    </p>
                                                </div>
                                                <span class="sf-svc-price tabular-nums" x-text="formatPrice(svc.price)"></span>
                                            </div>
                                        </template>
                                        <p x-show="searchResultCount() === 0" class="text-white/50 text-sm text-center py-8">No matching services or packages.</p>
                                    </div>
                                </template>

                                {{-- Accordion browse --}}
                                <template x-if="!serviceSearch.trim()">
                                    <div class="space-y-2">
                                        <template x-if="bookPackages.length > 0">
                                            <div class="sf-cat-block">
                                                <button type="button" @click="toggleSection('packages')" class="sf-cat-toggle">
                                                    <span class="sf-cat-icon">📦</span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="sf-cat-title">Packages</p>
                                                        <p class="sf-cat-sub">Bundle deals</p>
                                                    </div>
                                                    <span class="sf-cat-count" x-text="bookPackages.length"></span>
                                                    <svg class="sf-cat-chevron" :class="openSection === 'packages' ? 'is-open' : ''"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <div x-show="openSection === 'packages'" x-cloak>
                                                    <template x-for="pkg in bookPackages" :key="'pkg-' + pkg.id">
                                                        <div @click="togglePackage(pkg)"
                                                             :class="isPackageSelected(pkg) ? 'is-selected' : ''"
                                                             class="sf-svc-row">
                                                            <span class="sf-svc-check" aria-hidden="true">✓</span>
                                                            <div class="min-w-0 flex-1">
                                                                <p class="sf-svc-name" x-text="pkg.name"></p>
                                                                <p class="sf-svc-meta" x-text="(pkg.duration_minutes || 0) + ' min'"></p>
                                                            </div>
                                                            <span class="sf-svc-price tabular-nums" x-text="formatPrice(pkg.price)"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                        <template x-for="cat in bookCategories" :key="cat.id">
                                            <div class="sf-cat-block">
                                                <button type="button" @click="toggleSection('cat-' + cat.id)" class="sf-cat-toggle">
                                                    <span class="sf-cat-icon">✂</span>
                                                    <div class="min-w-0 flex-1">
                                                        <p class="sf-cat-title truncate" x-text="cat.name"></p>
                                                        <p x-show="cat.business_type" class="sf-cat-sub truncate" x-text="cat.business_type"></p>
                                                    </div>
                                                    <span class="sf-cat-count" x-text="cat.services?.length ?? 0"></span>
                                                    <svg class="sf-cat-chevron" :class="openSection === ('cat-' + cat.id) ? 'is-open' : ''"
                                                         fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                                </button>
                                                <div x-show="openSection === ('cat-' + cat.id)" x-cloak>
                                                    <template x-for="svc in cat.services" :key="svc.id">
                                                        <div @click="toggleService(svc)"
                                                             :class="isSelected(svc) ? 'is-selected' : ''"
                                                             class="sf-svc-row">
                                                            <span class="sf-svc-check" aria-hidden="true">✓</span>
                                                            <div class="min-w-0 flex-1">
                                                                <p class="sf-svc-name truncate" x-text="svc.name"></p>
                                                                <p class="sf-svc-meta" x-text="(svc.duration_minutes || 0) + ' min'"></p>
                                                            </div>
                                                            <span class="sf-svc-price tabular-nums" x-text="formatPrice(svc.price)"></span>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Step 1: Stylist --}}
                <div x-show="step === 1" class="sf-booking-card">
                    <div class="sf-booking-card-body">
                    <h2 class="sf-booking-section-title">Choose your stylist</h2>
                    <p class="text-white/70 text-sm mb-4">Pick a stylist first, then we’ll show dates and times that work for them.</p>
                    <div x-show="staffLoading" class="flex items-center justify-center gap-2 py-6 text-white/50 text-sm">
                        <span class="inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-primary rounded-full animate-spin"></span>
                        Loading stylists…
                    </div>
                    <div class="space-y-2" x-show="!staffLoading">
                        <button type="button" @click="chooseStaff(null)"
                                :class="selected.staff === null ? 'border-primary bg-primary/15' : 'border-white/10 bg-white/5 hover:border-primary'"
                                class="w-full rounded-xl border-2 p-4 text-left">
                            <span class="font-semibold">Any available stylist</span>
                        </button>
                        <template x-for="member in availableStaff()" :key="member.id">
                            <button type="button" @click="chooseStaff(member)"
                                    :class="selected.staff?.id === member.id ? 'border-primary bg-primary/15' : 'border-white/10 bg-white/5 hover:border-primary'"
                                    class="w-full rounded-xl border-2 p-4 text-left flex items-center gap-3">
                                <span class="w-10 h-10 rounded-full bg-primary/30 flex items-center justify-center text-sm font-bold"
                                      x-text="((member.first_name || '')[0] || '') + ((member.last_name || '')[0] || '')"></span>
                                <span class="font-semibold" x-text="member.first_name + ' ' + member.last_name"></span>
                            </button>
                        </template>
                    </div>
                    <p x-show="!staffLoading && availableStaff().length === 0" class="text-white/50 text-sm py-4">No stylists are available for online booking.</p>
                    </div>
                </div>

                {{-- Step 2: Date & time --}}
                <div x-show="step === 2" class="space-y-4">
                    <div class="text-center sm:text-left">
                        <h2 class="font-manrope font-bold text-base sm:text-lg">When would you like to visit?</h2>
                        <p class="text-xs sm:text-sm text-white/50 mt-0.5">Pick a date, then choose a time.</p>
                        <p x-show="selected.staff" class="text-xs text-primary/90 mt-1.5">
                            Showing times for <span class="font-semibold" x-text="staffDisplayName()"></span>
                        </p>
                    </div>

                    <div class="relative" @click.outside="calendarOpen = false" @keydown.escape.window="calendarOpen = false">
                        <button type="button" @click="calendarOpen = !calendarOpen"
                                class="w-full flex items-center justify-between gap-3 rounded-xl border border-white/15 bg-white/10 hover:bg-white/[0.12] px-4 py-3.5 text-left transition-colors">
                            <div class="min-w-0">
                                <p class="text-[10px] uppercase tracking-widest text-white/40 mb-0.5">Date</p>
                                <p class="text-sm font-semibold text-white truncate"
                                   x-text="selected.date ? formatDate(selected.date) : 'Select a date'"></p>
                            </div>
                            <span class="shrink-0 w-9 h-9 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center text-white/70">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </span>
                        </button>

                        <div x-show="calendarOpen" x-cloak x-transition.origin.top.left
                             class="mt-2 rounded-2xl border border-white/10 bg-[#12151f] p-4 sm:p-5 shadow-2xl shadow-black/50 z-20 relative">
                            <div class="flex items-center justify-between gap-3 mb-4">
                                <button type="button" @click="shiftCalendarMonth(-1)" :disabled="!canShiftCalendar(-1)"
                                        class="w-9 h-9 rounded-xl border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition-colors"
                                        aria-label="Previous month">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <div class="text-center min-w-0 flex-1">
                                    <p class="font-manrope font-semibold text-white text-sm sm:text-base tracking-tight truncate" x-text="calendarMonthLabel"></p>
                                    <p class="text-[10px] uppercase tracking-widest text-white/35 mt-0.5" x-show="selected.date" x-text="formatDateShort(selected.date)"></p>
                                </div>
                                <button type="button" @click="shiftCalendarMonth(1)" :disabled="!canShiftCalendar(1)"
                                        class="w-9 h-9 rounded-xl border border-white/10 bg-white/5 text-white/80 hover:bg-white/10 hover:text-white disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition-colors"
                                        aria-label="Next month">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <button type="button" @click="calendarOpen = false"
                                        class="w-9 h-9 rounded-xl border border-white/10 bg-white/5 text-white/70 hover:bg-white/10 hover:text-white flex items-center justify-center transition-colors"
                                        aria-label="Close calendar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="grid grid-cols-7 gap-1 mb-2">
                                <template x-for="dow in ['Su','Mo','Tu','We','Th','Fr','Sa']" :key="dow">
                                    <div class="text-center text-[10px] sm:text-[11px] font-semibold uppercase tracking-wider text-white/35 py-1" x-text="dow"></div>
                                </template>
                            </div>

                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="cell in calendarCells" :key="cell.key">
                                    <button type="button"
                                            @click="cell.selectable && pickCalendarDate(cell.ymd)"
                                            :disabled="!cell.selectable"
                                            :class="calendarDayClass(cell)"
                                            class="aspect-square rounded-xl text-xs sm:text-sm font-semibold transition-all duration-150 flex items-center justify-center relative"
                                            x-text="cell.day">
                                    </button>
                                </template>
                            </div>

                            <div class="mt-4 flex items-center justify-between gap-2">
                                <button type="button" @click="pickCalendarDate(today)" :disabled="today > maxDate"
                                        class="text-[11px] font-semibold text-primary hover:text-primary-dark disabled:opacity-40">Today</button>
                                <div class="flex items-center gap-3">
                                    <button type="button" x-show="selected.date" @click="clearCalendarDate()"
                                            class="text-[11px] font-semibold text-white/45 hover:text-white">Clear</button>
                                    <button type="button" @click="calendarOpen = false"
                                            class="text-[11px] font-semibold px-3 py-1.5 rounded-lg bg-white/10 text-white hover:bg-white/15">Close</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <template x-if="selected.date">
                        <div class="rounded-xl border border-white/10 bg-[#1a1f2e]/80 p-3 sm:p-4">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <h3 class="font-manrope font-semibold text-sm text-white">Available times</h3>
                                <button type="button" @click="loadSlots()" :disabled="slotsLoading" class="text-[11px] font-semibold text-primary hover:text-primary-dark disabled:opacity-40" x-text="slotsLoading ? 'Loading…' : 'Refresh'"></button>
                            </div>
                            <p x-show="combinedInfo" class="text-[11px] text-white/50 mb-2.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/5" x-text="combinedInfo?.message || combinedInfo?.label"></p>
                            <p x-show="slotsError" class="text-red-300 text-xs sm:text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-2.5 mb-3" x-text="slotsError"></p>
                            <div x-show="slotsLoading" class="flex items-center justify-center gap-2 py-6 text-white/50 text-xs sm:text-sm">
                                <span class="inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-primary rounded-full animate-spin"></span>
                                Finding open slots…
                            </div>
                            <div x-show="!slotsLoading && !slotsError && slots.length === 0" class="text-center py-5 space-y-3">
                                <p class="text-white/50 text-xs sm:text-sm px-2" x-text="emptySlotsMessage()"></p>
                                <button type="button" x-show="selected.staff" @click="goBackInFlow()"
                                        class="inline-flex items-center justify-center rounded-full border border-primary/40 bg-primary/10 px-4 py-2 text-xs sm:text-sm font-semibold text-primary hover:bg-primary/20 transition-colors">
                                    Choose another stylist
                                </button>
                            </div>
                            <template x-for="[period, periodSlots] in groupedSlots()" :key="period">
                                <div class="space-y-3 sm:space-y-4 mb-3">
                                    <p class="text-[9px] sm:text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-1.5" x-text="period"></p>
                                    <div class="sf-slot-grid grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-1.5 sm:gap-2">
                                        <template x-for="slot in periodSlots" :key="slot.time">
                                            <button type="button" :disabled="!slot.available" @click="selectSlot(slot)"
                                                    :class="[
                                                        selected.slot?.time === slot.time
                                                            ? 'border-primary bg-primary text-white shadow-lg shadow-primary/25'
                                                            : (slot.available ? 'border-white/15 bg-white/5 text-white hover:border-primary hover:bg-primary/15 active:scale-[0.98]' : 'border-white/5 text-white/25 cursor-not-allowed')
                                                    ]"
                                                    class="rounded-lg py-2 sm:py-2.5 text-xs sm:text-sm font-semibold border transition-colors duration-150" x-text="slot.time"></button>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                {{-- Step 3: Details --}}
                <div x-show="step === 3" class="space-y-4">
                    <h2 class="font-bold text-lg">Your details</h2>
                    <p x-show="detailsError" class="text-red-300 text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-3" x-text="detailsError"></p>

                    <div class="space-y-1">
                        <input placeholder="Full name" x-model="client.name" autocomplete="name"
                               @input="clearDetailsError('name')"
                               :class="detailsFieldClass('name')">
                        <p x-show="detailsErrors.name" x-cloak class="text-xs text-red-400" x-text="detailsErrors.name"></p>
                    </div>
                    <div class="space-y-1">
                        <input type="email" placeholder="Email" x-model="client.email" autocomplete="email"
                               @input="clearDetailsError('email')"
                               :class="detailsFieldClass('email')">
                        <p x-show="detailsErrors.email" x-cloak class="text-xs text-red-400" x-text="detailsErrors.email"></p>
                    </div>
                    <div class="space-y-1">
                        <input type="tel" placeholder="Phone" x-model="client.phone" autocomplete="tel"
                               @input="clearDetailsError('phone')"
                               :class="detailsFieldClass('phone')">
                        <p x-show="detailsErrors.phone" x-cloak class="text-xs text-red-400" x-text="detailsErrors.phone"></p>
                    </div>
                    <textarea placeholder="Notes (optional)" x-model="client.notes" rows="3" class="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"></textarea>
                    <label class="flex items-center gap-2 text-sm text-white/70">
                        <input type="checkbox" x-model="client.marketing_consent" class="rounded accent-primary">
                        Keep me updated with offers and news
                    </label>
                    <button type="button" @click="goToConfirm()" class="w-full bg-primary hover:bg-primary-dark text-white font-semibold rounded-full py-4 lg:hidden">Review booking</button>
                </div>

                {{-- Step 4: Confirm --}}
                <div x-show="step === 4">
                    <h2 class="font-bold text-lg mb-4">Confirm your booking</h2>
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm space-y-3 mb-6">
                        <div class="flex justify-between gap-4"><span class="text-white/50">Services</span><span class="font-semibold text-right" x-text="selected.services.map(s => s.name).join(', ')"></span></div>
                        <div class="flex justify-between"><span class="text-white/50">Total</span><span class="font-bold text-primary" x-text="currency + totalPrice().toFixed(2)"></span></div>
                        <div class="flex justify-between"><span class="text-white/50">When</span><span class="font-semibold" x-text="formatDate(selected.date) + ' at ' + (selected.slot?.time || '')"></span></div>
                        <div class="flex justify-between"><span class="text-white/50">With</span><span class="font-semibold" x-text="staffDisplayName()"></span></div>
                    </div>
                    <p x-show="bookingError" class="text-red-300 text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-3 mb-4" x-text="bookingError"></p>
                    <button type="button" :disabled="confirming" @click="handleConfirm()" class="w-full bg-primary hover:bg-primary-dark disabled:opacity-60 text-white font-semibold rounded-full py-4 lg:hidden" x-text="confirming ? 'Confirming…' : 'Confirm booking'"></button>
                </div>
                        </div>

                        @include('storefront.partials.booking-summary')
                    </div>
                </div>
            </main>
        </div>
    </template>
</div>

@once
@push('scripts')
<script>
function storefrontBooking(config) {
    return {
        ...config,
        onlineBookingEnabled: Boolean(config.onlineBookingEnabled),
        open: Boolean(config.onlineBookingEnabled) && window.location.hash === '#book',
        pendingIntent: null,
        steps: ['Services', 'Stylist', 'Date & time', 'Your details', 'Confirm'],
        step: 0,
        loading: true,
        globalError: '',
        allServices: [],
        bookPackages: [],
        serviceSearch: '',
        openSection: null,
        slotsLoading: false,
        slots: [],
        combinedInfo: null,
        slotsError: '',
        bookStaff: [],
        staffLoading: false,
        selected: { services: [], staff: null, date: '', slot: null },
        client: { name: '', email: '', phone: '', notes: '', marketing_consent: false },
        detailsError: '',
        detailsErrors: { name: '', email: '', phone: '' },
        bookingError: '',
        confirming: false,
        bookingRef: '',
        bookingStatus: '',
        confirmedStaff: null,
        confirmDisplay: null,
        get today() { return new Date().toISOString().slice(0, 10); },
        get maxDate() { const d = new Date(); d.setDate(d.getDate() + 60); return d.toISOString().slice(0, 10); },
        get bookCategories() { return this.allServices.filter(c => (c.services?.length ?? 0) > 0); },
        get flatServices() { return this.bookCategories.flatMap(c => c.services ?? []); },
        toggleSection(id) {
            this.openSection = this.openSection === id ? null : id;
        },
        searchQuery() {
            return (this.serviceSearch || '').trim().toLowerCase();
        },
        filteredPackages() {
            const q = this.searchQuery();
            if (!q) return [];
            return this.bookPackages.filter(pkg => {
                const name = (pkg.name || '').toLowerCase();
                const names = this.packageServiceNames(pkg).toLowerCase();
                return name.includes(q) || names.includes(q);
            });
        },
        filteredServices() {
            const q = this.searchQuery();
            if (!q) return [];
            const out = [];
            this.bookCategories.forEach(cat => {
                (cat.services || []).forEach(svc => {
                    const name = (svc.name || '').toLowerCase();
                    if (name.includes(q) || (cat.name || '').toLowerCase().includes(q)) {
                        out.push({ ...svc, _categoryName: cat.name });
                    }
                });
            });
            return out;
        },
        searchResultCount() {
            return this.filteredPackages().length + this.filteredServices().length;
        },
        popularServices() {
            const preferred = ['Cutting', 'Blow Dry', 'Beard', 'Manicure', 'Hair Styling', 'Hair Wash'];
            const all = this.flatServices;
            const names = [];
            for (const p of preferred) {
                const match = all.find(s => {
                    const n = (s.name || '').toLowerCase();
                    const q = p.toLowerCase();
                    return n === q || n.includes(q);
                });
                if (match && !names.includes(match.name)) names.push(match.name);
                if (names.length >= 4) break;
            }
            for (const s of all) {
                if (names.length >= 4) break;
                if (!names.includes(s.name)) names.push(s.name);
            }
            return names.slice(0, 4);
        },
        quickSearch(name) {
            this.serviceSearch = name;
        },
        summaryHeading() {
            if (this.step >= 2 && this.selected.date) return 'Your appointment';
            return 'Your booking';
        },
        summaryLines() {
            const lines = [];
            const covered = new Set();
            for (const pkg of this.bookPackages) {
                if (!this.isPackageSelected(pkg)) continue;
                lines.push({
                    key: 'pkg-' + pkg.id,
                    name: pkg.name,
                    duration: pkg.duration_minutes || 0,
                    price: pkg.price || 0,
                });
                this.packageServiceIds(pkg).forEach(id => covered.add(Number(id)));
            }
            for (const s of this.selected.services) {
                if (covered.has(Number(s.id))) continue;
                lines.push({
                    key: 'svc-' + s.id,
                    name: s.name,
                    duration: s.duration_minutes || 0,
                    price: s.price || 0,
                });
            }
            return lines;
        },
        summaryCanContinue() {
            if (this.step === 0) return this.selected.services.length > 0;
            if (this.step === 1) return false;
            if (this.step === 2) return !!this.selected.slot;
            if (this.step === 3 || this.step === 4) return true;
            return false;
        },
        summaryContinue() {
            if (this.step === 0) this.goToStylist();
            else if (this.step === 2 && this.selected.slot) this.step = 3;
            else if (this.step === 3) this.goToConfirm();
            else if (this.step === 4) this.handleConfirm();
        },
        summaryContinueLabel() {
            if (this.step === 0 || this.step === 2) return 'Continue';
            if (this.step === 3) return 'Review booking';
            if (this.step === 4) return this.confirming ? 'Confirming…' : 'Confirm booking';
            return 'Continue';
        },
        selectedCountLabel() {
            const count = this.selected.services.length;
            if (count === 0) return 'No services';
            return count + ' ' + (count === 1 ? 'service' : 'services');
        },
        selectedSummaryNames() {
            const covered = new Set();
            const labels = [];
            for (const pkg of this.bookPackages) {
                if (!this.isPackageSelected(pkg)) continue;
                labels.push(pkg.name);
                this.packageServiceIds(pkg).forEach(id => covered.add(Number(id)));
            }
            for (const s of this.selected.services) {
                if (covered.has(Number(s.id))) continue;
                labels.push(s.name);
            }
            return labels.join(' · ');
        },
        calendarCursor: null,
        calendarOpen: false,
        get calendarMonthLabel() {
            const d = this.calendarCursorDate();
            return d.toLocaleDateString('en-GB', { month: 'long', year: 'numeric' });
        },
        get calendarCells() {
            const cursor = this.calendarCursorDate();
            const year = cursor.getFullYear();
            const month = cursor.getMonth();
            const first = new Date(year, month, 1);
            const startPad = first.getDay();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const cells = [];
            for (let i = 0; i < startPad; i++) {
                cells.push({ key: 'pad-' + i, day: '', ymd: '', selectable: false, inMonth: false, isToday: false, isSelected: false });
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const ymd = this.toYmd(new Date(year, month, day));
                cells.push({
                    key: ymd,
                    day,
                    ymd,
                    inMonth: true,
                    selectable: ymd >= this.today && ymd <= this.maxDate,
                    isToday: ymd === this.today,
                    isSelected: ymd === this.selected.date,
                });
            }
            while (cells.length % 7 !== 0) {
                cells.push({ key: 'end-' + cells.length, day: '', ymd: '', selectable: false, inMonth: false, isToday: false, isSelected: false });
            }
            return cells;
        },
        init() {
            this.calendarCursor = this.today.slice(0, 7) + '-01';
            this.syncBodyBookingState();
            this.$watch('open', () => this.syncBodyBookingState());
            this.setupDraftWatchers();
            if (this.onlineBookingEnabled) {
                this.fetchServices();
            } else {
                this.loading = false;
            }
        },
        draftStorageKey() {
            return 'sf-booking-draft:' + this.slug;
        },
        setupDraftWatchers() {
            if (this._draftWatchersSetup) return;
            this._draftWatchersSetup = true;
            this.$watch('step', () => this.persistDraft());
            this.$watch('client', () => this.persistDraft(), { deep: true });
            this.$watch('selected', () => this.persistDraft(), { deep: true });
            this.$watch('calendarCursor', () => this.persistDraft());
        },
        serializeDraft() {
            if (this.step >= 5) return null;
            return {
                v: 1,
                step: this.step,
                serviceIds: this.selected.services.map(s => s.id),
                hasStaffChoice: this.step >= 2,
                staffId: this.selected.staff?.id ?? null,
                date: this.selected.date || '',
                slotTime: this.selected.slot?.time || '',
                client: { ...this.client },
                calendarCursor: this.calendarCursor,
                savedAt: Date.now(),
            };
        },
        persistDraft() {
            if (!this.onlineBookingEnabled) return;
            try {
                const data = this.serializeDraft();
                if (!data) {
                    sessionStorage.removeItem(this.draftStorageKey());
                    return;
                }
                const hasProgress = data.step > 0
                    || data.serviceIds.length > 0
                    || (data.client.name || '').trim()
                    || (data.client.email || '').trim()
                    || (data.client.phone || '').trim();
                if (!hasProgress) {
                    sessionStorage.removeItem(this.draftStorageKey());
                    return;
                }
                sessionStorage.setItem(this.draftStorageKey(), JSON.stringify(data));
            } catch (e) { /* storage unavailable */ }
        },
        clearDraft() {
            try { sessionStorage.removeItem(this.draftStorageKey()); } catch (e) { /* ignore */ }
        },
        async restoreDraft() {
            const onBook = window.location.hash === '#book' || window.location.hash.indexOf('#book') === 0;
            if (!onBook) return;
            let draft;
            try {
                const raw = sessionStorage.getItem(this.draftStorageKey());
                if (!raw) return;
                draft = JSON.parse(raw);
            } catch { return; }
            if (!draft || draft.v !== 1 || typeof draft.step !== 'number') return;
            if (Date.now() - (draft.savedAt || 0) > 24 * 60 * 60 * 1000) {
                this.clearDraft();
                return;
            }
            if (draft.client) {
                this.client = {
                    name: '', email: '', phone: '', notes: '', marketing_consent: false,
                    ...draft.client,
                };
            }
            if (Array.isArray(draft.serviceIds) && draft.serviceIds.length) {
                const idSet = new Set(draft.serviceIds.map(Number));
                this.selected.services = this.flatServices.filter(s => idSet.has(Number(s.id)));
            }
            if (!this.selected.services.length) {
                this.step = 0;
                return;
            }
            let targetStep = Math.min(Math.max(0, draft.step), 4);
            if (draft.calendarCursor) this.calendarCursor = draft.calendarCursor;
            if (targetStep >= 1) {
                await this.fetchStaff();
                if (draft.hasStaffChoice) {
                    this.selected.staff = draft.staffId != null
                        ? (this.bookStaff.find(s => Number(s.id) === Number(draft.staffId)) ?? null)
                        : null;
                }
            }
            if (targetStep >= 2 && draft.date) {
                this.selected.date = draft.date >= this.today ? draft.date : this.today;
                this.calendarCursor = this.selected.date.slice(0, 7) + '-01';
                await this.loadSlots();
                if (draft.slotTime) {
                    const slot = this.slots.find(s => s.time === draft.slotTime && s.available !== false);
                    if (slot) {
                        this.selected.slot = slot;
                    } else if (targetStep >= 3) {
                        targetStep = 2;
                        this.selected.slot = null;
                    }
                } else if (targetStep >= 3) {
                    targetStep = 2;
                }
            } else if (targetStep >= 2) {
                targetStep = Math.min(targetStep, 1);
            }
            this.step = targetStep;
            this.open = true;
        },
        onBookingToggle(wantOpen) {
            if (!this.onlineBookingEnabled) {
                // Still show the offline notice if someone hits #book
                this.open = Boolean(wantOpen);
                return;
            }
            this.open = Boolean(wantOpen);
        },
        syncBodyBookingState() {
            document.body.classList.toggle('storefront-booking-active', this.open);
        },
        close() {
            if (history.length > 1) history.back();
            else { window.location.hash = ''; this.open = false; }
            this.syncBodyBookingState();
        },
        headerBackLabel() {
            if (this.step === 0) return '← Back to site';
            const prev = this.steps[this.step - 1];
            return prev ? `← ${prev}` : '← Back';
        },
        goBackInFlow() {
            if (this.step === 0) {
                this.close();
                return;
            }
            if (this.step === 2) {
                this.calendarOpen = false;
            }
            this.step = Math.max(0, this.step - 1);
        },
        api(path, opts = {}) {
            const url = this.apiBase.replace(/\/$/, '') + '/api/v1/book/' + this.slug + path;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const headers = {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(csrf ? { 'X-CSRF-TOKEN': csrf } : {}),
                ...(opts.headers ?? {}),
            };
            return fetch(url, { credentials: 'same-origin', headers, ...opts })
                .then(async r => { const d = await r.json().catch(() => ({})); if (!r.ok) throw new Error(d.message || 'Request failed'); return d; });
        },
        parseServicesResponse(data) {
            const raw = data?.services ?? data?.data?.services ?? {};
            if (Array.isArray(raw)) {
                return raw.filter(c => (c.services?.length ?? 0) > 0);
            }
            const cats = [];
            for (const [categoryId, svcs] of Object.entries(raw)) {
                if (!Array.isArray(svcs) || svcs.length === 0) continue;
                const parsedId = categoryId === '' || categoryId === 'null' ? 0 : Number(categoryId);
                const first = svcs[0];
                cats.push({
                    id: Number.isFinite(parsedId) ? parsedId : 0,
                    name: first?.category?.name ?? 'Services',
                    business_type: first?.category?.business_type?.name ?? null,
                    sort_order: first?.category?.sort_order ?? 999,
                    services: svcs,
                });
            }
            return cats.sort((a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name));
        },
        fetchServices() {
            this.loading = true;
            this.api('/services').then(d => {
                this.allServices = this.parseServicesResponse(d);
                this.bookPackages = Array.isArray(d.packages) ? d.packages : [];
            }).catch(() => { this.globalError = 'Failed to load services. Please refresh the page.'; })
              .finally(async () => {
                  this.loading = false;
                  const appliedIntent = this.applyPendingIntent();
                  if (!appliedIntent) await this.restoreDraft();
              });
        },
        onBookPreselect(detail) {
            this.pendingIntent = detail || {};
            this.step = 0;
            this.applyPendingIntent();
        },
        applyPendingIntent() {
            const intent = this.pendingIntent;
            if (!intent || this.loading) return false;
            const packageIds = [...(intent.packageIds || [])];
            if (intent.packageId != null && intent.packageId !== '') packageIds.push(intent.packageId);
            const serviceIds = [...(intent.serviceIds || [])];
            if (intent.serviceId != null && intent.serviceId !== '') serviceIds.push(intent.serviceId);
            this.pendingIntent = null;
            if (!packageIds.length && !serviceIds.length) return false;
            this.clearSlotSelection();
            const idSet = new Set();
            packageIds.forEach(pid => {
                const pkg = this.bookPackages.find(p => Number(p.id) === Number(pid));
                if (pkg) this.packageServiceIds(pkg).forEach(id => idSet.add(Number(id)));
            });
            serviceIds.forEach(id => idSet.add(Number(id)));
            this.selected.services = this.flatServices.filter(s => idSet.has(Number(s.id)));
            this.clearDraft();
            return true;
        },
        packageServiceIds(pkg) { return pkg.service_ids ?? (pkg.services ?? []).map(s => s.id); },
        packageServiceNames(pkg) { return (pkg.services ?? []).map(s => s.name).join(' · '); },
        isPackageSelected(pkg) {
            const ids = this.packageServiceIds(pkg);
            return ids.length > 0 && ids.every(id => this.selected.services.some(s => s.id === id));
        },
        togglePackage(pkg) {
            const ids = new Set(this.packageServiceIds(pkg));
            const svcs = this.flatServices.filter(s => ids.has(s.id));
            if (svcs.length === 0) return;
            if (this.isPackageSelected(pkg)) {
                this.selected.services = this.selected.services.filter(s => !ids.has(s.id));
            } else {
                const existing = new Set(this.selected.services.map(s => s.id));
                const merged = [...this.selected.services];
                svcs.forEach(s => { if (!existing.has(s.id)) merged.push(s); });
                this.selected.services = merged;
            }
            this.clearSlotSelection();
        },
        clearSlotSelection() {
            this.selected.staff = null; this.selected.date = ''; this.selected.slot = null;
            this.slots = []; this.combinedInfo = null;
        },
        isSelected(svc) { return this.selected.services.some(s => s.id === svc.id); },
        toggleService(svc) {
            const idx = this.selected.services.findIndex(s => s.id === svc.id);
            if (idx >= 0) {
                this.selected.services = this.selected.services.filter(s => s.id !== svc.id);
            } else {
                this.selected.services = [...this.selected.services, svc];
            }
            this.clearSlotSelection();
        },
        totalPrice() {
            let total = 0;
            const packageServiceIds = new Set();
            for (const pkg of this.bookPackages) {
                if (!this.isPackageSelected(pkg)) continue;
                total += parseFloat(pkg.price || 0);
                this.packageServiceIds(pkg).forEach(id => packageServiceIds.add(id));
            }
            for (const s of this.selected.services) {
                if (!packageServiceIds.has(s.id)) total += parseFloat(s.price || 0);
            }
            return total;
        },
        formatPrice(v) {
            const n = parseFloat(v || 0);
            if (this.currency === '₹') return '₹' + n.toLocaleString('en-IN', { maximumFractionDigits: 2 });
            return this.currency + n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
        formatDate(dateStr) {
            if (!dateStr) return '';
            try { return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }); }
            catch { return dateStr; }
        },
        formatDateShort(dateStr) {
            if (!dateStr) return '';
            try { return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-GB', { weekday: 'short', day: 'numeric', month: 'short' }); }
            catch { return dateStr; }
        },
        toYmd(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        },
        calendarCursorDate() {
            const raw = this.calendarCursor || (this.today.slice(0, 7) + '-01');
            const parts = raw.split('-').map(Number);
            return new Date(parts[0], (parts[1] || 1) - 1, 1);
        },
        canShiftCalendar(delta) {
            const cursor = this.calendarCursorDate();
            cursor.setMonth(cursor.getMonth() + delta);
            const monthStart = this.toYmd(new Date(cursor.getFullYear(), cursor.getMonth(), 1));
            const monthEnd = this.toYmd(new Date(cursor.getFullYear(), cursor.getMonth() + 1, 0));
            return monthEnd >= this.today && monthStart <= this.maxDate;
        },
        shiftCalendarMonth(delta) {
            if (!this.canShiftCalendar(delta)) return;
            const cursor = this.calendarCursorDate();
            cursor.setMonth(cursor.getMonth() + delta);
            this.calendarCursor = this.toYmd(new Date(cursor.getFullYear(), cursor.getMonth(), 1));
        },
        calendarDayClass(cell) {
            if (!cell.inMonth) return 'invisible pointer-events-none';
            if (cell.isSelected) return 'bg-primary text-white shadow-lg shadow-primary/30 scale-[1.02]';
            if (!cell.selectable) return 'text-white/20 cursor-not-allowed';
            if (cell.isToday) return 'bg-white/10 text-white ring-1 ring-primary/50 hover:bg-primary/20';
            return 'text-white/85 hover:bg-white/10 hover:text-white';
        },
        pickCalendarDate(ymd) {
            if (!ymd || ymd < this.today || ymd > this.maxDate) return;
            this.selected.date = ymd;
            this.selected.slot = null;
            this.calendarCursor = ymd.slice(0, 7) + '-01';
            this.calendarOpen = false;
            this.loadSlots();
        },
        clearCalendarDate() {
            this.selected.date = '';
            this.selected.slot = null;
            this.slots = [];
            this.slotsError = '';
            this.combinedInfo = null;
            this.calendarOpen = false;
        },
        loadSlots() {
            if (!this.selected.date || !this.selected.services.length) return Promise.resolve();
            let date = this.selected.date;
            if (date < this.today) date = this.today;
            this.slotsLoading = true; this.slots = []; this.combinedInfo = null; this.slotsError = '';
            const params = new URLSearchParams({ date });
            this.selected.services.forEach(s => params.append('service_ids[]', s.id));
            if (this.selected.staff?.id) params.append('staff_id', this.selected.staff.id);
            return this.api('/availability?' + params).then(d => {
                this.slots = d.slots ?? d.data?.slots ?? [];
                this.combinedInfo = d.combined ?? d.data?.combined ?? null;
                if (date !== this.selected.date) this.selected.date = date;
            }).catch(e => { this.slots = []; this.slotsError = e.message || 'Could not load available times.'; })
              .finally(() => { this.slotsLoading = false; });
        },
        slotPeriod(time) {
            const hour = parseInt((time || '0').split(':')[0], 10);
            if (hour < 12) return 'Morning';
            if (hour < 17) return 'Afternoon';
            return 'Evening';
        },
        groupedSlots() {
            const groups = { Morning: [], Afternoon: [], Evening: [] };
            this.slots.forEach(s => groups[this.slotPeriod(s.time)].push(s));
            return Object.entries(groups).filter(([, list]) => list.length > 0);
        },
        emptySlotsMessage() {
            if (this.selected.staff) {
                const name = this.staffDisplayName();
                if (name && name !== 'Any available') {
                    return `${name} has no open times on this date. Please choose another stylist or try a different day.`;
                }
                return 'This stylist has no open times on this date. Please choose another stylist or try a different day.';
            }
            return 'No open times on this date. Try another day or go back and pick a different stylist.';
        },
        selectSlot(slot) {
            this.selected.slot = slot;
            this.step = 3;
        },
        goToStylist() {
            this.fetchStaff().then(() => { this.step = 1; });
        },
        goToDateTime() {
            this.step = 2;
            if (this.selected.date) this.loadSlots();
        },
        chooseStaff(member) {
            this.selected.staff = member;
            this.selected.date = '';
            this.selected.slot = null;
            this.slots = [];
            this.combinedInfo = null;
            this.goToDateTime();
        },
        fetchStaff() {
            this.staffLoading = true;
            const params = new URLSearchParams();
            this.selected.services.forEach(s => params.append('service_ids[]', s.id));
            return this.api('/staff?' + params).then(d => {
                this.bookStaff = d.staff ?? d.data?.staff ?? [];
            }).catch(() => { this.bookStaff = []; }).finally(() => { this.staffLoading = false; });
        },
        availableStaff() {
            const list = this.bookStaff.length ? this.bookStaff : (this.selected.slot?.available_staff ?? []);
            return [...list].sort((a, b) => (`${a.first_name} ${a.last_name}`).localeCompare(`${b.first_name} ${b.last_name}`));
        },
        staffDisplayName() {
            if (this.confirmedStaff) return `${this.confirmedStaff.first_name || ''} ${this.confirmedStaff.last_name || ''}`.trim();
            if (this.selected.staff) return `${this.selected.staff.first_name || ''} ${this.selected.staff.last_name || ''}`.trim();
            const fb = this.selected.slot?.available_staff?.[0];
            if (fb) return `${fb.first_name || ''} ${fb.last_name || ''}`.trim();
            return 'Any available';
        },
        resolveHoldStaffId() {
            if (this.selected.staff?.id != null) return this.selected.staff.id;
            const first = this.selected.slot?.available_staff?.[0];
            return first?.id ?? null;
        },
        goToConfirm() {
            this.detailsError = '';
            this.detailsErrors = { name: '', email: '', phone: '' };
            let valid = true;
            if (!this.client.name?.trim()) {
                this.detailsErrors.name = 'Please enter your full name.';
                valid = false;
            }
            if (!this.client.email?.trim()) {
                this.detailsErrors.email = 'Please enter your email address.';
                valid = false;
            }
            if (!this.client.phone?.trim()) {
                this.detailsErrors.phone = 'Please enter your phone number.';
                valid = false;
            }
            if (!valid) {
                this.detailsError = this.detailsErrors.name || this.detailsErrors.email || this.detailsErrors.phone;
                return;
            }
            this.step = 4;
        },
        detailsFieldClass(field) {
            const base = 'w-full rounded-xl px-4 py-3 text-white placeholder:text-white/40 outline-none transition-colors';
            if (this.detailsErrors[field]) {
                return base + ' bg-red-500/10 border-2 border-red-500 ring-2 ring-red-500/25';
            }
            return base + ' bg-white/10 border border-white/20 focus:border-primary/50 focus:ring-2 focus:ring-primary/25';
        },
        clearDetailsError(field) {
            if (this.detailsErrors[field]) {
                this.detailsErrors[field] = '';
            }
            if (!this.detailsErrors.name && !this.detailsErrors.email && !this.detailsErrors.phone) {
                this.detailsError = '';
            }
        },
        splitClientName() {
            const parts = (this.client.name || '').trim().split(/\s+/).filter(Boolean);
            return {
                first_name: parts[0] || '',
                last_name: parts.slice(1).join(' '),
            };
        },
        handleConfirm() {
            this.confirming = true; this.bookingError = '';
            const nameParts = this.splitClientName();
            this.api('/hold', { method: 'POST', body: JSON.stringify({
                service_ids: this.selected.services.map(s => s.id),
                staff_id: this.resolveHoldStaffId(),
                starts_at: `${this.selected.date} ${this.selected.slot.time}:00`,
            }) }).then(hold => this.api('/confirm', { method: 'POST', body: JSON.stringify({
                hold_token: hold.hold_token ?? hold.data?.hold_token,
                first_name: nameParts.first_name,
                last_name: nameParts.last_name,
                email: this.client.email,
                phone: this.client.phone,
                notes: this.client.notes,
                marketing_consent: this.client.marketing_consent,
            }) })).then(confirm => {
                this.bookingRef = confirm.reference ?? confirm.appointment?.reference ?? '';
                this.bookingStatus = confirm.status ?? confirm.appointment?.status ?? 'pending';
                this.confirmedStaff = confirm.appointment?.staff ?? null;
                this.confirmDisplay = confirm.display ?? null;
                this.step = 5;
                this.clearDraft();
            }).catch(e => { this.bookingError = e.message || 'Something went wrong. Please try again.'; })
              .finally(() => { this.confirming = false; });
        },
        resetBooking() {
            this.clearDraft();
            this.step = 0;
            this.bookStaff = [];
            this.serviceSearch = '';
            this.openSection = null;
            this.selected = { services: [], staff: null, date: '', slot: null };
            this.client = { name: '', email: '', phone: '', notes: '', marketing_consent: false };
            this.detailsError = '';
            this.detailsErrors = { name: '', email: '', phone: '' };
            this.bookingRef = ''; this.bookingStatus = ''; this.confirmDisplay = null;
        },
    };
}
</script>
@endpush
@endonce
