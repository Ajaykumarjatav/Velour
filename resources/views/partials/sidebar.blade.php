<div class="flex flex-col h-full min-h-0 sidebar-wrapper">

    {{-- Business status header --}}
    <div class="px-3 py-3 border-b border-gray-100 dark:border-gray-800 min-h-[3.75rem]">
        {{-- Collapsed: store icon only --}}
        <div class="sidebar-logo-icon flex w-8 h-8 items-center justify-center shrink-0 mx-auto"
             title="{{ ($salonBusinessStatus ?? null) ? $salonBusinessStatus['name'] : 'EasyGrox' }}">
            <img src="{{ asset('images/easygrox-icon.png') }}" alt="EasyGrox" class="w-8 h-8 object-contain">
        </div>

        @if(Auth::check() && ($salonBusinessStatus ?? null))
        <div class="sidebar-text relative pr-8" x-data="{ copied: false }">
            <p class="text-[13px] font-semibold text-gray-900 dark:text-white tracking-tight truncate leading-snug">
                {{ $salonBusinessStatus['name'] }}
            </p>
            <div class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5 text-[11px]">
                @if($salonBusinessStatus['is_live'])
                    <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0" aria-hidden="true"></span>
                        Live
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-gray-500 dark:text-gray-400 font-medium">
                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 shrink-0" aria-hidden="true"></span>
                        Offline
                    </span>
                @endif
                <a href="{{ $salonBusinessStatus['setup_url'] }}"
                   class="text-gray-500 dark:text-gray-400 hover:text-velour-600 dark:hover:text-velour-300 transition-colors tabular-nums">
                    {{ $salonBusinessStatus['setup_percent'] }}% Setup Complete
                </a>
                @if(config('billing.subscriptions_enabled') && ($subscriptionReminder ?? null) && !($planExpired ?? false))
                <span class="text-gray-400 dark:text-gray-600" aria-hidden="true">·</span>
                @php
                  $sbDays = $subscriptionReminder['days_remaining'] ?? null;
                  $sbKind = $subscriptionReminder['kind'] ?? 'trial';
                @endphp
                <a href="{{ $subscriptionReminder['renew_url'] ?? route('billing.plans') }}"
                   class="font-medium tabular-nums {{ ($subscriptionReminder['urgent'] ?? false) ? 'text-red-600 dark:text-red-400' : (($subscriptionReminder['warning'] ?? false) ? 'text-amber-600 dark:text-amber-400' : 'text-velour-600 dark:text-velour-400') }} hover:underline">
                  @if($sbDays === null)
                    {{ $subscriptionReminder['plan_label'] ?? 'Plan' }}
                  @elseif($sbDays === 0)
                    {{ $sbKind === 'trial' ? 'Trial ends today' : 'Plan ends today' }}
                  @else
                    {{ $sbDays }}d {{ $sbKind === 'trial' ? 'trial' : 'plan' }} left
                  @endif
                </a>
                @endif
            </div>
            @if($adminStoreBrowse ?? false)
            <p class="mt-1.5 text-[10px] font-medium text-velour-600 dark:text-velour-400 flex items-center gap-1">
                <svg class="w-3 h-3 shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Admin view · read-only
            </p>
            @endif
            <button type="button"
                    class="absolute top-0 right-0 p-1 rounded-md text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                    title="Copy booking link"
                    aria-label="Copy booking link"
                    @click="navigator.clipboard.writeText(@js($salonBusinessStatus['copy_url'])); copied = true; setTimeout(() => copied = false, 2000)">
                <svg x-show="!copied" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
                <svg x-show="copied" x-cloak class="w-4 h-4 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </button>
        </div>
        @else
        <div class="sidebar-text">
            <img src="{{ asset('images/easygrox-logo-light.png') }}" alt="EasyGrox" class="h-7 w-auto max-w-full dark:hidden">
            <img src="{{ asset('images/easygrox-logo-dark.png') }}" alt="EasyGrox" class="h-7 w-auto max-w-full hidden dark:block">
        </div>
        @endif
    </div>

    @php
        $navUser = auth()->user();
        $navShow = fn (string $key): bool => $navUser
            ? \App\Support\SidebarNav::show($navUser, $key)
            : false;
    @endphp

    {{-- Nav --}}
    <nav class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-2.5 py-3 space-y-1"
         x-data="{ openMenu: null }">

        @if($planExpired ?? false)
        <div class="px-1 pb-3 mb-2 border-b border-amber-200/80 dark:border-amber-800/50">
            <p class="text-[11px] text-amber-700 dark:text-amber-300 font-medium mb-2 px-2">Plan expired — renew to unlock</p>
            <a href="{{ route('billing.plans') }}"
               class="sidebar-link {{ request()->routeIs('billing.plans', 'billing.checkout', 'billing.success', 'billing.change*') ? 'active' : '' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'subscription'])
                Renew subscription
            </a>
            <a href="{{ route('billing.dashboard') }}"
               class="sidebar-link {{ request()->routeIs('billing.dashboard', 'billing.portal', 'billing.cancel', 'billing.invoice*') ? 'active' : '' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'billing'])
                Billing
            </a>
        </div>
        @else

        @if($navShow('dashboard'))
        <a href="{{ \App\Support\SalonUrl::dashboardUrl($navUser) }}"
           class="sidebar-link {{ request()->routeIs('dashboard') && ! request()->boolean('desk') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'dashboard'])
            Dashboard
        </a>
        @endif

        {{-- Todos & requests removed --}}

        @if($navShow('tasks'))
        <a href="{{ route('tasks.index') }}"
           class="sidebar-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'tasks'])
            Tasks
        </a>
        @endif

        @if($navShow('calendar'))
        <a href="{{ route('calendar') }}"
           class="sidebar-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'calendar'])
            Calendar
        </a>
        @endif

        @if($navShow('appointments'))
        <a href="{{ route('appointments.index') }}"
           class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'appointments'])
            Appointments
        </a>
        @endif

        @if($navShow('clients'))
        <a href="{{ route('clients.index') }}"
           class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'clients'])
            Clients
        </a>
        @endif

        {{-- BUSINESS --}}
        @php
            $businessMenuActive = request()->routeIs(
                'staff.*', 'services.*', 'service-packages.*', 'multi-location.*',
                'availability.*', 'inventory.*', 'expenses.*', 'pos.*'
            );
        @endphp
        @if(\App\Support\SidebarNav::showBusinessGroup(auth()->user()))
        <x-sidebar-nav-submenu name="business" label="Business" icon="business" :open="$businessMenuActive" :active="$businessMenuActive">
            @if($navShow('staff'))
            <a href="{{ route('staff.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('staff.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'staff', 'small' => true])
                Staff &amp; HR
            </a>
            @endif
            @if($navShow('services'))
            <a href="{{ route('services.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('services.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'services', 'small' => true])
                Services
            </a>
            @endif
            @if($navShow('service_packages'))
            <a href="{{ route('service-packages.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('service-packages.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'packages', 'small' => true])
                Plans/Packages
            </a>
            @endif
            @if($navShow('multi_location'))
            <a href="{{ route('multi-location.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('multi-location.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'location', 'small' => true])
                Multi-Location
            </a>
            @endif
            @if($navShow('availability'))
            <a href="{{ route('availability.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('availability.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'availability', 'small' => true])
                Availability &amp; Resources
            </a>
            @endif
            @if($navShow('inventory'))
            <a href="{{ route('inventory.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('inventory.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'inventory', 'small' => true])
                Inventory &amp; Retail
            </a>
            @endif
            @if($navShow('expenses'))
            <a href="{{ route('expenses.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('expenses.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'expenses', 'small' => true])
                Expenses
            </a>
            @endif
            @if($navShow('pos'))
            <a href="{{ route('pos.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('pos.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'pos', 'small' => true])
                Point of Sale
            </a>
            @endif
        </x-sidebar-nav-submenu>
        @endif

        {{-- GROWTH --}}
        @php
            $isAnalyticsActive = request()->routeIs('reports.analytics');
            $isReportsMenuActive = request()->routeIs('reports.index', 'reports.show', 'revenue.index');
            $growthMenuActive = request()->routeIs(
                'go-live', 'marketing.*',
                'reviews.*', 'reports.analytics', 'reports.index', 'reports.show',
                'revenue.index', 'reports.growth-tips'
            );
            $reportsOpen = $isReportsMenuActive;
        @endphp
        @if(\App\Support\SidebarNav::showGrowthGroup(auth()->user()))
        <x-sidebar-nav-submenu name="growth" label="Growth" icon="growth" :open="$growthMenuActive" :active="$growthMenuActive">
            @if($navShow('go_live'))
            <a href="{{ route('go-live') }}"
               class="sidebar-sub-link {{ request()->routeIs('go-live') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'go_live', 'small' => true])
                Go Live &amp; Share
            </a>
            @endif
            {{-- Temporarily hidden — unused for now
            @if($navShow('website_seo'))
            <a href="{{ route('website-seo.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('website-seo.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'website', 'small' => true])
                Website &amp; SEO
            </a>
            @endif
            @if($navShow('customization'))
            <a href="{{ route('customization.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('customization.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'customization', 'small' => true])
                Customization
            </a>
            @endif
            --}}
            @if($navShow('marketing'))
            <a href="{{ route('marketing.growth') }}"
               class="sidebar-sub-link {{ request()->routeIs('marketing.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'marketing', 'small' => true])
                Marketing
            </a>
            @endif
            @if($navShow('reviews'))
            <a href="{{ route('reviews.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('reviews.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'reviews', 'small' => true])
                Reviews
            </a>
            @endif
            @if($navShow('analytics'))
            <a href="{{ route('reports.analytics') }}"
               class="sidebar-sub-link {{ $isAnalyticsActive ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'analytics', 'small' => true])
                Analytics
            </a>
            @endif
            @if($navShow('growth_tips'))
            <a href="{{ route('reports.growth-tips') }}"
               class="sidebar-sub-link {{ request()->routeIs('reports.growth-tips') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'growth', 'small' => true])
                Growth Tips
            </a>
            @endif
            @php $reportsForUser = \App\Support\ReportCatalog::forUser(auth()->user()); @endphp
            @if($navShow('reports_menu') && count($reportsForUser) > 0)
            <div x-data="{ reportsOpen: {{ $reportsOpen ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button"
                        @click.stop="reportsOpen = !reportsOpen"
                        class="sidebar-sub-link w-full text-left {{ $isReportsMenuActive ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    @include('partials.sidebar-nav-icon', ['icon' => 'reports', 'small' => true])
                    <span class="flex-1">Reports</span>
                    <svg class="w-3 h-3 flex-shrink-0 transition-transform duration-200" :class="reportsOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="reportsOpen" x-transition class="ml-3 space-y-0.5">
                    @foreach($reportsForUser as $report)
                    @php
                        $key = $report['key'];
                        $label = $report['label'];
                        $reportIcon = in_array($key, ['revenue', 'appointments', 'staff', 'clients', 'services', 'inventory', 'marketing'], true) ? $key : 'reports';
                    @endphp
                    <a href="{{ route('reports.show', $key) }}"
                       class="sidebar-sub-link text-sm {{ request()->routeIs('reports.show') && request()->route('type') === $key ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        @include('partials.sidebar-nav-icon', ['icon' => $reportIcon, 'small' => true])
                        {{ $label }}
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </x-sidebar-nav-submenu>
        @endif

        {{-- ACCOUNT --}}
        @php
            $supportMenuActive = request()->routeIs('guide.*');
            $accountMenuActive = request()->routeIs(
                'billing.*', 'settings.*', 'two-factor.*', 'security-support.*',
                'notifications.*', 'deleted-items.*', 'guide.*', 'activity.*'
            );
        @endphp
        @if(\App\Support\SidebarNav::showAccountGroup(auth()->user()))
        <x-sidebar-nav-submenu name="account" label="Account" icon="settings" :open="$accountMenuActive" :active="$accountMenuActive">
            @if(config('billing.subscriptions_enabled') && $navShow('billing'))
            <a href="{{ route('billing.dashboard') }}"
               class="sidebar-sub-link {{ request()->routeIs('billing.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'billing', 'small' => true])
                <span class="flex-1">Billing</span>
                @php
                  $planKey = Auth::user()->plan ?? config('billing.default_plan', 'trial');
                  $planBadge = [
                    'trial'    => ['Trial',    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                    'standard' => ['Standard', 'bg-velour-100 text-velour-700 dark:bg-velour-900/40 dark:text-velour-300'],
                    'premium'  => ['Premium',  'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
                  ][$planKey] ?? [ucfirst($planKey), 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'];
                @endphp
                <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded {{ $planBadge[1] }}">{{ $planBadge[0] }}</span>
            </a>
            @if(Auth::user()->onTrial())
            <a href="{{ route('billing.plans') }}"
               class="sidebar-sub-link text-amber-700 dark:text-amber-300 hover:bg-amber-50 dark:hover:bg-amber-900/20">
                @include('partials.sidebar-nav-icon', ['icon' => 'warning', 'small' => true])
                Trial ending — upgrade
            </a>
            @endif
            @if(Auth::user()->isPastDue())
            <a href="{{ route('billing.portal') }}"
               class="sidebar-sub-link text-red-700 dark:text-red-300 hover:bg-red-50 dark:hover:bg-red-900/20">
                @include('partials.sidebar-nav-icon', ['icon' => 'warning', 'small' => true])
                Payment failed — fix
            </a>
            @endif
            @endif

            @if($navShow('settings'))
            <a href="{{ route('settings.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('settings.*') && !request()->routeIs('two-factor.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'settings', 'small' => true])
                Settings
            </a>
            @endif

            @if($navShow('security_support'))
            <a href="{{ route('security-support.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('security-support.*', 'two-factor.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'security', 'small' => true])
                <span class="flex-1">Security &amp; 2FA</span>
                @if(auth()->user()->hasTwoFactorEnabled())
                <span class="ml-auto w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
                @endif
            </a>
            @endif

            @if($navShow('notifications'))
            <a href="{{ route('notifications.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('notifications.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'notifications', 'small' => true])
                <span class="flex-1">Notifications</span>
                @php
                    try {
                        $sidebarSalon  = $currentSalon ?? auth()->user()->salons()->first();
                        $sidebarUnread = $sidebarSalon
                            ? \App\Models\SalonNotification::where('salon_id', $sidebarSalon->id)->where('is_read', false)->count()
                            : 0;
                    } catch (\Throwable) { $sidebarUnread = 0; }
                @endphp
                @if($sidebarUnread > 0)
                <span class="sidebar-nav-badge bg-red-500 text-white">
                    {{ $sidebarUnread > 9 ? '9+' : $sidebarUnread }}
                </span>
                @endif
            </a>
            @endif

            @if(\App\Support\SidebarNav::showDeletedItems(auth()->user()))
            @php
                try {
                    $deletedItemsCount = ($currentSalon ?? auth()->user()->salons()->first())
                        ? \App\Support\DeletedItemsRegistry::countForSalon(
                            (int) ($currentSalon ?? auth()->user()->salons()->first())->id,
                            auth()->user()
                        )
                        : 0;
                } catch (\Throwable) {
                    $deletedItemsCount = 0;
                }
            @endphp
            <a href="{{ route('deleted-items.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('deleted-items.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'trash', 'small' => true])
                <span class="flex-1">Deleted Items</span>
                @if($deletedItemsCount > 0)
                <span class="sidebar-nav-badge bg-amber-400/90 text-amber-950 dark:bg-amber-500/90 dark:text-amber-950">
                    {{ $deletedItemsCount > 99 ? '99+' : $deletedItemsCount }}
                </span>
                @endif
            </a>
            @can('view-activity-log')
            <a href="{{ route('activity.index') }}"
               class="sidebar-sub-link {{ request()->routeIs('activity.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'guide', 'small' => true])
                <span class="flex-1">Activity log</span>
            </a>
            @endcan
            @endif

            @if($navShow('support') || $navShow('guide'))
            <div x-data="{ supportOpen: {{ $supportMenuActive ? 'true' : 'false' }}, chatUnread: false }"
                 x-init="window.addEventListener('velour-chat-unread', e => chatUnread = e.detail)"
                 class="space-y-0.5">
                <button type="button"
                        @click.stop="supportOpen = !supportOpen"
                        class="sidebar-sub-link w-full text-left {{ $supportMenuActive ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    @include('partials.sidebar-nav-icon', ['icon' => 'support', 'small' => true])
                    <span class="flex-1">Support</span>
                    <span x-show="chatUnread" class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0" aria-hidden="true"></span>
                    <svg class="w-3 h-3 flex-shrink-0 transition-transform duration-200" :class="supportOpen ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="supportOpen" x-transition class="ml-3 space-y-0.5">
                    @if($navShow('support'))
                    <button type="button"
                            class="sidebar-sub-link w-full text-left text-sm text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                            @click="window.dispatchEvent(new CustomEvent('velour-chat-open'))"
                            aria-label="Open EasyGrox Assistant">
                        @include('partials.sidebar-nav-icon', ['icon' => 'assistant', 'small' => true])
                        <span class="flex-1">EasyGrox Assistant</span>
                        <span x-show="chatUnread" class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0" aria-hidden="true"></span>
                    </button>
                    @endif
                    @if($navShow('guide'))
                    <a href="{{ route('guide.index') }}"
                       class="sidebar-sub-link text-sm {{ request()->routeIs('guide.*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                        @include('partials.sidebar-nav-icon', ['icon' => 'guide', 'small' => true])
                        Guide &amp; Setup
                    </a>
                    @endif
                </div>
            </div>
            @endif
        </x-sidebar-nav-submenu>
        @endif

        {{-- ADMIN --}}
        @php
            $adminMenuActive = request()->routeIs('salon-admin.*');
        @endphp
        @if(\App\Support\SidebarNav::showAdminGroup(auth()->user()))
        <x-sidebar-nav-submenu name="admin" label="Admin" icon="team" :open="$adminMenuActive" :active="$adminMenuActive">
            @if(\App\Support\SidebarNav::showAccountTeam(auth()->user()) || auth()->user()->isSuperAdmin())
            <a href="{{ route('salon-admin.team') }}"
               class="sidebar-sub-link {{ request()->routeIs('salon-admin.team*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'team', 'small' => true])
                Team
            </a>
            @endif
            @if(config('billing.subscriptions_enabled') && ($navShow('billing') || auth()->user()->salons()->exists()))
            <a href="{{ route('salon-admin.subscription') }}"
               class="sidebar-sub-link {{ request()->routeIs('salon-admin.subscription*') ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold' : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'subscription', 'small' => true])
                Subscription
            </a>
            @endif
        </x-sidebar-nav-submenu>
        @endif

        @endif
    </nav>

    {{-- Footer --}}
    <div class="p-3 border-t border-gray-100 dark:border-gray-800">
        @if(Auth::user()->isSuperAdmin())
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-2 px-3 py-2 text-xs text-gray-400 dark:text-gray-500 hover:text-velour-600 dark:hover:text-velour-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            ⚡ Admin Panel
        </a>
        @endif
    </div>

</div>
<script>
(function () {
    document.querySelectorAll('.sidebar-link').forEach(function (el) {
        var text = el.textContent.trim();
        if (text) el.setAttribute('data-title', text);
    });

    function findActiveLink(nav) {
        return nav.querySelector('a.sidebar-link.active, a.sidebar-sub-link.font-semibold')
            || nav.querySelector('button.sidebar-link.active');
    }

    function scrollLinkIntoNav(nav, link) {
        if (!nav || !link) return;

        var padding = 16;
        var navRect = nav.getBoundingClientRect();
        var linkRect = link.getBoundingClientRect();
        var delta = 0;

        if (linkRect.top < navRect.top + padding) {
            delta = linkRect.top - navRect.top - padding;
        } else if (linkRect.bottom > navRect.bottom - padding) {
            delta = linkRect.bottom - navRect.bottom + padding;
        }

        if (delta !== 0) {
            nav.scrollTop += delta;
        }
    }

    function restoreSidebarScroll() {
        document.querySelectorAll('.sidebar-wrapper nav').forEach(function (nav) {
            var active = findActiveLink(nav);
            if (!active) return;

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    scrollLinkIntoNav(nav, active);
                });
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restoreSidebarScroll);
    } else {
        restoreSidebarScroll();
    }
})();
</script>
