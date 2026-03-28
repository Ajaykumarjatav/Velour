<div class="flex flex-col h-full">

    {{-- Logo --}}
    <div class="px-5 py-5 border-b border-gray-100 dark:border-gray-800">
        <p class="text-lg font-black text-gray-900 dark:text-white tracking-tight">
            velour<span class="text-velour-500">.</span>
        </p>
        @if(Auth::check() && Auth::user()->salons()->exists())
        @php $sidebarSalonName = Auth::user()->salons()->first()->name; @endphp
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5 truncate">{{ $sidebarSalonName }}</p>
        @endif
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

        <a href="{{ route('dashboard') }}"
           class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>

        <a href="{{ route('calendar') }}"
           class="sidebar-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            Calendar
        </a>

        <a href="{{ route('appointments.index') }}"
           class="sidebar-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Appointments
        </a>

        <a href="{{ route('clients.index') }}"
           class="sidebar-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Clients
        </a>

        {{-- MANAGE --}}
        <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Manage</p>

        <a href="{{ route('staff.index') }}"
           class="sidebar-link {{ request()->routeIs('staff.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Staff
        </a>

        <a href="{{ route('services.index') }}"
           class="sidebar-link {{ request()->routeIs('services.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
            </svg>
            Services
        </a>

        <a href="{{ route('inventory.index') }}"
           class="sidebar-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Inventory
        </a>

        <a href="{{ route('pos.index') }}"
           class="sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Point of Sale
        </a>

        {{-- GROW --}}
        <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Grow</p>

        <a href="{{ route('go-live') }}"
           class="sidebar-link {{ request()->routeIs('go-live') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            Go Live &amp; Share
        </a>

        <a href="{{ route('marketing.index') }}"
           class="sidebar-link {{ request()->routeIs('marketing.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
            </svg>
            Marketing
        </a>

        <a href="{{ route('reviews.index') }}"
           class="sidebar-link {{ request()->routeIs('reviews.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            Reviews
        </a>

        {{-- Reports sub-menu --}}
        @php $reportsOpen = request()->routeIs('reports.*'); @endphp
        <div x-data="{ open: {{ $reportsOpen ? 'true' : 'false' }} }">
            <button @click="open = !open"
                    class="sidebar-link w-full {{ $reportsOpen ? 'active' : '' }}">
                <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="flex-1 text-left">Reports</span>
                <svg class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" class="ml-4 mt-0.5 space-y-0.5">
                @foreach([
                    ['revenue',      '💰', 'Revenue'],
                    ['appointments', '📅', 'Appointments'],
                    ['staff',        '👤', 'Staff'],
                    ['clients',      '🧑', 'Clients'],
                    ['services',     '✂️', 'Services'],
                ] as [$key, $icon, $label])
                <a href="{{ route('reports.show', $key) }}"
                   class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm transition-colors
                          {{ request()->routeIs('reports.show') && request()->route('type') === $key
                             ? 'bg-velour-50 dark:bg-velour-900/30 text-velour-700 dark:text-velour-300 font-medium'
                             : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                    <span class="text-base leading-none">{{ $icon }}</span>
                    {{ $label }}
                </a>
                @endforeach
            </div>
        </div>

        {{-- ACCOUNT --}}
        <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Account</p>

        <a href="{{ route('billing.dashboard') }}"
           class="sidebar-link {{ request()->routeIs('billing.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
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

        <a href="{{ route('settings.index') }}"
           class="sidebar-link {{ request()->routeIs('settings.*') && !request()->routeIs('two-factor.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Settings
        </a>

        <a href="{{ route('two-factor.setup') }}"
           class="sidebar-link {{ request()->routeIs('two-factor.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span class="flex-1">Security &amp; 2FA</span>
            @if(auth()->user()->hasTwoFactorEnabled())
            <span class="ml-auto w-2 h-2 bg-green-500 rounded-full flex-shrink-0"></span>
            @endif
        </a>

        <a href="{{ route('notifications.index') }}"
           class="sidebar-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="flex-1">Notifications</span>
            @php
                try {
                    $sidebarSalon  = auth()->user()->salons()->first();
                    $sidebarUnread = $sidebarSalon
                        ? \App\Models\SalonNotification::where('salon_id', $sidebarSalon->id)->where('is_read', false)->count()
                        : 0;
                } catch (\Throwable) { $sidebarUnread = 0; }
            @endphp
            @if($sidebarUnread > 0)
            <span class="ml-auto px-1.5 py-0.5 text-[10px] font-bold bg-red-500 text-white rounded-full">
                {{ $sidebarUnread > 9 ? '9+' : $sidebarUnread }}
            </span>
            @endif
        </a>

        @if(auth()->user()->hasRole('tenant_admin') || auth()->user()->isSuperAdmin())
        <p class="px-3 pt-4 pb-1 text-[10px] font-semibold text-gray-400 dark:text-gray-600 uppercase tracking-widest">Admin</p>
        <a href="{{ route('salon-admin.team') }}"
           class="sidebar-link {{ request()->routeIs('salon-admin.team*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Team
        </a>
        <a href="{{ route('salon-admin.subscription') }}"
           class="sidebar-link {{ request()->routeIs('salon-admin.subscription*') ? 'active' : '' }}">
            <svg class="nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            Subscription
        </a>
        @endif

    </nav>

    {{-- Footer --}}
    <div class="p-3 border-t border-gray-100 dark:border-gray-800">
        <div class="px-3 py-2 text-xs text-gray-500 dark:text-gray-500">
            <p class="font-medium text-gray-700 dark:text-gray-300 truncate">{{ Auth::user()->name }}</p>
            <p class="truncate">{{ Auth::user()->email }}</p>
        </div>
        @if(Auth::user()->isSuperAdmin())
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-2 px-3 py-2 text-xs text-gray-400 dark:text-gray-500 hover:text-velour-600 dark:hover:text-velour-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            ⚡ Admin Panel
        </a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-2 px-3 py-2 text-xs text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors w-full">
                Sign out
            </button>
        </form>
    </div>

</div>
