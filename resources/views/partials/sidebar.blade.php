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
            </div>
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
        $navShow = fn (string $key): bool => \App\Support\SidebarNav::show(auth()->user(), $key);
    @endphp

    {{-- Nav --}}
    <nav class="flex-1 min-h-0 overflow-y-auto overscroll-contain px-2.5 py-3 space-y-1">

        @if($navShow('dashboard'))
        <a href="{{ route('dashboard') }}"
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

        {{-- MANAGE --}}
        @if(\App\Support\SidebarNav::showManageHeading(auth()->user()))
        <p class="nav-section-title">Business</p>
        @endif

        @if($navShow('staff'))
        <a href="{{ route('staff.index') }}"
           class="sidebar-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'staff'])
            Staff &amp; HR
        </a>
        @endif

        @if($navShow('services'))
        <a href="{{ route('services.index') }}"
           class="sidebar-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'services'])
            Services
        </a>
        @endif

        @if($navShow('service_packages'))
        <a href="{{ route('service-packages.index') }}"
           class="sidebar-link {{ request()->routeIs('service-packages.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'packages'])
            Plans/Packages
        </a>
        @endif

        @if($navShow('multi_location'))
        <a href="{{ route('multi-location.index') }}"
           class="sidebar-link {{ request()->routeIs('multi-location.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'location'])
            Multi-Location
        </a>
        @endif

        @if($navShow('availability'))
        <a href="{{ route('availability.index') }}"
           class="sidebar-link {{ request()->routeIs('availability.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'availability'])
            Availability &amp; Resources
        </a>
        @endif

        @if($navShow('inventory'))
        <a href="{{ route('inventory.index') }}"
           class="sidebar-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'inventory'])
            Inventory &amp; Retail
        </a>
        @endif

        @if($navShow('expenses'))
        <a href="{{ route('expenses.index') }}"
           class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'expenses'])
            Expenses
        </a>
        @endif

        @if($navShow('pos'))
        <a href="{{ route('pos.index') }}"
           class="sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'pos'])
            Point of Sale
        </a>
        @endif

        {{-- GROW --}}
        @if(\App\Support\SidebarNav::showGrowHeading(auth()->user()))
        <p class="nav-section-title">Insights</p>
        @endif

        @if($navShow('go_live'))
        <a href="{{ route('go-live') }}"
           class="sidebar-link {{ request()->routeIs('go-live') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'go_live'])
            Go Live &amp; Share
        </a>
        @endif

        @if($navShow('website_seo'))
        <a href="{{ route('website-seo.index') }}"
           class="sidebar-link {{ request()->routeIs('website-seo.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'website'])
            Website &amp; SEO
        </a>
        @endif

        @if($navShow('customization'))
        <a href="{{ route('customization.index') }}"
           class="sidebar-link {{ request()->routeIs('customization.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'customization'])
            Customization
        </a>
        @endif

        @if($navShow('marketing'))
        <a href="{{ route('marketing.growth') }}"
           class="sidebar-link {{ request()->routeIs('marketing.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'marketing'])
            Marketing
        </a>
        @endif

        @if($navShow('reviews'))
        <a href="{{ route('reviews.index') }}"
           class="sidebar-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'reviews'])
            Reviews
        </a>
        @endif

        @php
            $isAnalyticsActive = request()->routeIs('reports.analytics');
            $isReportsMenuActive = request()->routeIs('reports.index')
                || request()->routeIs('reports.show')
                || request()->routeIs('revenue.index');
        @endphp
        @if($navShow('analytics'))
        <a href="{{ route('reports.analytics') }}"
           class="sidebar-link {{ $isAnalyticsActive ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'analytics'])
            Analytics
        </a>
        @endif

        {{-- Reports sub-menu --}}
        @if($navShow('reports_menu'))
        @php $reportsOpen = $isReportsMenuActive; @endphp
        <div x-data="{ open: {{ $reportsOpen ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ $reportsOpen ? 'active' : '' }}">
                @include('partials.sidebar-nav-icon', ['icon' => 'reports'])
                <span class="flex-1 text-left">Reports</span>
                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-4 mt-0.5 space-y-0.5">
                @foreach(\App\Support\ReportCatalog::forUser(auth()->user()) as $report)
                @php $key = $report['key']; $label = $report['label']; @endphp
                <a href="{{ route('reports.show', $key) }}"
                   class="sidebar-sub-link
                          {{ request()->routeIs('reports.show') && request()->route('type') === $key
                             ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold'
                             : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ request()->routeIs('reports.show') && request()->route('type') === $key ? 'text-velour-600 dark:text-velour-300' : 'text-gray-400 dark:text-gray-500' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        @include('reports._nav-icon', ['key' => $key])
                    </svg>
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- ACCOUNT --}}
        @if($navShow('billing') || $navShow('settings') || $navShow('security_support') || $navShow('notifications') || $navShow('growth_tips') || $navShow('support') || $navShow('guide') || \App\Support\SidebarNav::showDeletedItems(auth()->user()))
        <p class="nav-section-title">Account</p>
        @endif

        @if(config('billing.subscriptions_enabled') && $navShow('billing'))
        <a href="{{ route('billing.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'billing'])
            <span class="flex-1">Billing</span>
            @php
              $planKey = Auth::user()->plan ?? 'free';
              $planBadge = [
                'free'       => ['Free',       'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'],
                'starter'    => ['Starter',    'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300'],
                'pro'        => ['Pro',        'bg-velour-100 text-velour-700 dark:bg-velour-900/40 dark:text-velour-300'],
                'enterprise' => ['Enterprise', 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300'],
              ][$planKey] ?? ['Free', 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400'];
            @endphp
            <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold rounded {{ $planBadge[1] }}">{{ $planBadge[0] }}</span>
        </a>

        @if(Auth::user()->onTrial())
        <a href="{{ route('billing.plans') }}"
           class="mx-1 flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium
                  bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-300
                  hover:bg-amber-100 dark:hover:bg-amber-900/30 transition-colors">
            ⏳ Trial ending — upgrade
        </a>
        @endif

        @if(Auth::user()->isPastDue())
        <a href="{{ route('billing.portal') }}"
           class="mx-1 flex items-center gap-2 px-3 py-2 rounded-xl text-xs font-medium
                  bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300
                  hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors">
            ⚠️ Payment failed
        </a>
        @endif
        @endif

        @if($navShow('settings'))
        <a href="{{ route('settings.index') }}"
           class="sidebar-link {{ request()->routeIs('settings.*') && !request()->routeIs('two-factor.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'settings'])
            Settings
        </a>
        @endif

        @if($navShow('security_support'))
        <a href="{{ route('security-support.index') }}"
           class="sidebar-link {{ request()->routeIs('security-support.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'security'])
            <span class="flex-1">Security &amp; 2FA</span>
            @if(auth()->user()->hasTwoFactorEnabled())
            <span class="ml-auto w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
            @endif
        </a>
        @endif

        @if($navShow('notifications'))
        <a href="{{ route('notifications.index') }}"
           class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'notifications'])
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
           class="sidebar-link {{ request()->routeIs('deleted-items.*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'trash'])
            <span class="flex-1">Deleted Items</span>
            @if($deletedItemsCount > 0)
            <span class="sidebar-nav-badge bg-amber-400/90 text-amber-950 dark:bg-amber-500/90 dark:text-amber-950">
                {{ $deletedItemsCount > 99 ? '99+' : $deletedItemsCount }}
            </span>
            @endif
        </a>
        @endif

        @if($navShow('growth_tips'))
        <a href="{{ route('reports.growth-tips') }}"
           class="sidebar-link {{ request()->routeIs('reports.growth-tips') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'growth'])
            Growth Tips
        </a>
        @endif

        {{-- Support sub-menu --}}
        @if($navShow('support') || $navShow('guide'))
        @php $supportMenuActive = request()->routeIs('guide.*'); @endphp
        <div x-data="{ open: {{ $supportMenuActive ? 'true' : 'false' }}, chatUnread: false }"
             x-init="window.addEventListener('velour-chat-unread', e => chatUnread = e.detail)">
            <button type="button"
                    @click="open = !open"
                    class="sidebar-link relative w-full {{ $supportMenuActive ? 'active' : '' }}"
                    data-title="Support">
                @include('partials.sidebar-nav-icon', ['icon' => 'support'])
                <span x-show="chatUnread"
                      class="absolute left-6 top-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-[#16161f]"
                      aria-hidden="true"></span>
                <span class="flex-1 text-left">Support</span>
                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="ml-4 mt-0.5 space-y-0.5">
                @if($navShow('support'))
                <button type="button"
                        class="sidebar-sub-link w-full text-left
                               text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800"
                        @click="window.dispatchEvent(new CustomEvent('velour-chat-open'))"
                        aria-label="Open EasyGrox Assistant"
                        data-title="EasyGrox Assistant">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    <span class="flex-1">EasyGrox Assistant</span>
                    <span x-show="chatUnread"
                          class="w-2 h-2 bg-red-500 rounded-full flex-shrink-0"
                          aria-hidden="true"></span>
                </button>
                @endif
                @if($navShow('guide'))
                <a href="{{ route('guide.index') }}"
                   class="sidebar-sub-link
                          {{ request()->routeIs('guide.*')
                             ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-semibold'
                             : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                   data-title="Guide &amp; Setup">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ request()->routeIs('guide.*') ? 'text-velour-600 dark:text-velour-300' : 'text-gray-400 dark:text-gray-500' }}"
                         fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253a3 3 0 11.001 5.999A3 3 0 0112 6.253zm-7.5 6.5h15M9 18h6"/>
                    </svg>
                    Guide &amp; Setup
                </a>
                @endif
            </div>
        </div>
        @endif

        @if(\App\Support\SidebarNav::showAccountTeam(auth()->user()) || auth()->user()->isSuperAdmin())
        <p class="nav-section-title">Admin</p>
        <a href="{{ route('salon-admin.team') }}"
           class="sidebar-link {{ request()->routeIs('salon-admin.team*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'team'])
            Team
        </a>
        @if(config('billing.subscriptions_enabled') && ($navShow('billing') || auth()->user()->salons()->exists()))
        <a href="{{ route('salon-admin.subscription') }}"
           class="sidebar-link {{ request()->routeIs('salon-admin.subscription*') ? 'active' : '' }}">
            @include('partials.sidebar-nav-icon', ['icon' => 'subscription'])
            Subscription
        </a>
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
