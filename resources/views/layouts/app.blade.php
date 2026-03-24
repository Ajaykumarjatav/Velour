<!DOCTYPE html>
<html lang="en" class="h-full" x-data x-bind:class="$store.theme.dark ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Velour</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        velour: {
                            50:'#f5f3ff',100:'#ede9fe',200:'#ddd6fe',300:'#c4b5fd',
                            400:'#a78bfa',500:'#8b5cf6',600:'#7c3aed',700:'#6d28d9',
                            800:'#5b21b6',900:'#4c1d95',950:'#2e1065'
                        }
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        body { font-family: 'Inter', sans-serif; }

        /* ── Sidebar links ── */
        .sidebar-link {
            @apply flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors
                   text-gray-500 hover:bg-gray-100 hover:text-gray-900
                   dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-white;
        }
        .sidebar-link.active {
            @apply bg-velour-600 text-white hover:bg-velour-700 dark:bg-velour-600 dark:text-white dark:hover:bg-velour-700;
        }
        .nav-icon { @apply w-5 h-5 flex-shrink-0; }
        [x-cloak] { display: none !important; }

        /* ══════════════════════════════════════════════════════════════════
           GLOBAL DARK MODE TOKENS
           These apply automatically to every view that uses the standard
           card / form / table / badge patterns — no per-view changes needed.
        ══════════════════════════════════════════════════════════════════ */

        /* ── Cards ── */
        .card {
            @apply bg-white dark:bg-gray-900
                   border border-gray-200 dark:border-gray-800
                   rounded-2xl shadow-sm;
        }
        .card-header {
            @apply px-5 py-4 border-b border-gray-100 dark:border-gray-800;
        }
        .card-body  { @apply px-5 py-4; }
        .card-footer {
            @apply px-5 py-3 border-t border-gray-100 dark:border-gray-800
                   bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl;
        }

        /* ── Page headings ── */
        .page-title   { @apply text-xl font-bold text-gray-900 dark:text-white; }
        .page-subtitle{ @apply text-sm text-gray-500 dark:text-gray-400; }
        .section-title{ @apply text-base font-semibold text-gray-800 dark:text-gray-100; }

        /* ── Form elements ── */
        .form-label {
            @apply block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1;
        }
        .form-input {
            @apply w-full rounded-xl border border-gray-300 dark:border-gray-700
                   bg-white dark:bg-gray-800
                   text-gray-900 dark:text-gray-100
                   placeholder-gray-400 dark:placeholder-gray-500
                   px-3 py-2 text-sm
                   focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent
                   transition-colors;
        }
        .form-select {
            @apply form-input appearance-none cursor-pointer;
        }
        .form-textarea {
            @apply form-input resize-none;
        }
        .form-hint {
            @apply mt-1 text-xs text-gray-500 dark:text-gray-400;
        }
        .form-error {
            @apply mt-1 text-xs text-red-600 dark:text-red-400;
        }

        /* ── Buttons ── */
        .btn {
            @apply inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl
                   text-sm font-medium transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2
                   dark:focus:ring-offset-gray-950 disabled:opacity-50 disabled:cursor-not-allowed;
        }
        .btn-primary {
            @apply btn bg-velour-600 hover:bg-velour-700 text-white focus:ring-velour-500;
        }
        .btn-secondary {
            @apply btn bg-gray-100 hover:bg-gray-200 text-gray-700
                   dark:bg-gray-800 dark:hover:bg-gray-700 dark:text-gray-200
                   focus:ring-gray-400;
        }
        .btn-danger {
            @apply btn bg-red-600 hover:bg-red-700 text-white focus:ring-red-500;
        }
        .btn-outline {
            @apply btn border border-gray-300 dark:border-gray-700
                   text-gray-700 dark:text-gray-300
                   hover:bg-gray-50 dark:hover:bg-gray-800
                   focus:ring-gray-400;
        }
        .btn-sm { @apply px-3 py-1.5 text-xs; }
        .btn-lg { @apply px-6 py-3 text-base; }

        /* ── Tables ── */
        .table-wrap {
            @apply w-full overflow-x-auto rounded-2xl border border-gray-200 dark:border-gray-800;
        }
        table.data-table {
            @apply w-full text-sm;
        }
        table.data-table thead {
            @apply bg-gray-50 dark:bg-gray-800/60;
        }
        table.data-table thead th {
            @apply px-4 py-3 text-left text-xs font-semibold
                   text-gray-500 dark:text-gray-400 uppercase tracking-wider;
        }
        table.data-table tbody tr {
            @apply border-t border-gray-100 dark:border-gray-800
                   hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors;
        }
        table.data-table tbody td {
            @apply px-4 py-3 text-gray-700 dark:text-gray-300;
        }

        /* ── Stat / metric cards ── */
        .stat-card {
            @apply card p-5;
        }
        .stat-label {
            @apply text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider mb-1;
        }
        .stat-value {
            @apply text-2xl font-bold text-gray-900 dark:text-white;
        }
        .stat-sub {
            @apply text-xs text-gray-500 dark:text-gray-400 mt-0.5;
        }

        /* ── Badges ── */
        .badge {
            @apply inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium;
        }
        .badge-green  { @apply badge bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400; }
        .badge-red    { @apply badge bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400; }
        .badge-yellow { @apply badge bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400; }
        .badge-blue   { @apply badge bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400; }
        .badge-purple { @apply badge bg-velour-100 text-velour-700 dark:bg-velour-900/30 dark:text-velour-400; }
        .badge-gray   { @apply badge bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400; }

        /* ── Dividers ── */
        .divider { @apply border-t border-gray-100 dark:border-gray-800; }

        /* ── Empty states ── */
        .empty-state {
            @apply flex flex-col items-center justify-center py-16 text-center;
        }
        .empty-state-icon {
            @apply w-12 h-12 text-gray-300 dark:text-gray-600 mb-3;
        }
        .empty-state-title {
            @apply text-sm font-medium text-gray-500 dark:text-gray-400;
        }
        .empty-state-sub {
            @apply text-xs text-gray-400 dark:text-gray-500 mt-1;
        }

        /* ── List items ── */
        .list-item {
            @apply flex items-center gap-3 px-4 py-3
                   border-b border-gray-100 dark:border-gray-800 last:border-0
                   hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors;
        }

        /* ── Alert / notice boxes ── */
        .alert-info    { @apply flex gap-3 px-4 py-3 rounded-xl text-sm bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300; }
        .alert-success { @apply flex gap-3 px-4 py-3 rounded-xl text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300; }
        .alert-warning { @apply flex gap-3 px-4 py-3 rounded-xl text-sm bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300; }
        .alert-danger  { @apply flex gap-3 px-4 py-3 rounded-xl text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300; }

        /* ── Modal / dialog ── */
        .modal-backdrop {
            @apply fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4;
        }
        .modal-box {
            @apply bg-white dark:bg-gray-900 rounded-2xl shadow-2xl
                   border border-gray-200 dark:border-gray-800
                   w-full max-w-lg;
        }
        .modal-header {
            @apply flex items-center justify-between px-6 py-4
                   border-b border-gray-100 dark:border-gray-800;
        }
        .modal-body   { @apply px-6 py-5; }
        .modal-footer {
            @apply flex items-center justify-end gap-3 px-6 py-4
                   border-t border-gray-100 dark:border-gray-800
                   bg-gray-50 dark:bg-gray-800/50 rounded-b-2xl;
        }

        /* ── Dropdown menus ── */
        .dropdown-menu {
            @apply absolute z-50 mt-1 rounded-xl shadow-lg border py-1 min-w-[160px]
                   bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800;
        }
        .dropdown-item {
            @apply block w-full text-left px-4 py-2 text-sm
                   text-gray-700 dark:text-gray-300
                   hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors;
        }

        /* ── Tabs ── */
        .tab-bar {
            @apply flex gap-1 border-b border-gray-200 dark:border-gray-800 mb-5;
        }
        .tab-item {
            @apply px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors
                   text-gray-500 dark:text-gray-400 border-transparent
                   hover:text-gray-700 dark:hover:text-gray-200;
        }
        .tab-item.active {
            @apply text-velour-600 dark:text-velour-400 border-velour-600 dark:border-velour-400;
        }

        /* ── Pagination ── */
        .pagination-link {
            @apply px-3 py-1.5 rounded-lg text-sm font-medium transition-colors
                   text-gray-600 dark:text-gray-400
                   hover:bg-gray-100 dark:hover:bg-gray-800;
        }
        .pagination-link.active {
            @apply bg-velour-600 text-white hover:bg-velour-700;
        }
        .pagination-link.disabled {
            @apply opacity-40 cursor-not-allowed pointer-events-none;
        }

        /* ── Text helpers ── */
        .text-muted  { @apply text-gray-500 dark:text-gray-400; }
        .text-body   { @apply text-gray-700 dark:text-gray-300; }
        .text-heading{ @apply text-gray-900 dark:text-white; }
        .text-link   { @apply text-velour-600 dark:text-velour-400 hover:underline; }
    </style>
    @stack('styles')
</head>

<body class="h-full bg-gray-50 dark:bg-gray-950 transition-colors duration-200"
      x-data="{ sidebarOpen: false }">
<div class="flex h-full">

    {{-- Desktop sidebar --}}
    <aside class="hidden lg:flex lg:flex-col lg:w-56 lg:fixed lg:inset-y-0 z-30
                  bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800 transition-colors duration-200">
        @include('partials.sidebar')
    </aside>

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen=false"
         class="fixed inset-0 bg-black/60 z-40 lg:hidden"></div>

    {{-- Mobile sidebar --}}
    <aside x-show="sidebarOpen" x-cloak
           x-transition:enter="transition ease-out duration-200"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transition ease-in duration-150"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 w-56 z-50 lg:hidden flex flex-col
                  bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-800">
        @include('partials.sidebar')
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-h-screen lg:pl-56">

        {{-- Top bar --}}
        <header class="sticky top-0 z-20 h-14 px-4 sm:px-6 flex items-center justify-between
                       bg-white dark:bg-gray-950 border-b border-gray-200 dark:border-gray-800
                       transition-colors duration-200">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen=true"
                        class="lg:hidden p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-base font-semibold text-gray-900 dark:text-white">
                    @yield('page-title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-1 sm:gap-2">
                {{-- Theme toggle --}}
                <button @click="$store.theme.toggle()"
                        class="p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
                        title="Toggle theme">
                    <svg x-show="$store.theme.dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg x-show="!$store.theme.dark" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                {{-- Notifications --}}
                @php
                    $headerUnreadCount   = 0;
                    $headerNotifications = collect();
                    try {
                        $headerSalon = auth()->user()->salons()->first();
                        if ($headerSalon) {
                            $headerUnreadCount   = \App\Models\SalonNotification::where('salon_id', $headerSalon->id)->where('is_read', false)->count();
                            $headerNotifications = \App\Models\SalonNotification::where('salon_id', $headerSalon->id)->latest()->limit(6)->get();
                        }
                    } catch (\Throwable) {}
                @endphp
                <div class="relative" x-data="{ notifOpen: false }">
                    <button @click="notifOpen=!notifOpen"
                            class="relative p-2 rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($headerUnreadCount > 0)
                        <span class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                            {{ $headerUnreadCount > 9 ? '9+' : $headerUnreadCount }}
                        </span>
                        @endif
                    </button>
                    <div x-show="notifOpen" x-cloak @click.outside="notifOpen=false"
                         class="absolute right-0 mt-2 w-80 rounded-2xl shadow-xl border z-50 overflow-hidden
                                bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800">
                            <span class="text-sm font-semibold text-gray-800 dark:text-gray-100">Notifications</span>
                            @if($headerUnreadCount > 0)
                            <form method="POST" action="{{ route('notifications.mark-all-read') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-velour-600 dark:text-velour-400 hover:underline font-medium">Mark all read</button>
                            </form>
                            @endif
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-gray-50 dark:divide-gray-800">
                            @forelse($headerNotifications as $n)
                            <div class="px-4 py-3 {{ !$n->is_read ? 'bg-velour-50/50 dark:bg-velour-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-800' }}">
                                <div class="flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full mt-1.5 flex-shrink-0 {{ !$n->is_read ? 'bg-velour-500' : 'bg-transparent' }}"></div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-{{ $n->is_read ? 'normal' : 'semibold' }} text-gray-800 dark:text-gray-100 truncate">{{ $n->title }}</p>
                                        @if($n->body)<p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 line-clamp-1">{{ $n->body }}</p>@endif
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $n->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="px-4 py-8 text-center text-xs text-gray-400 dark:text-gray-500">No notifications yet</div>
                            @endforelse
                        </div>
                        <div class="px-4 py-2.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-800/50">
                            <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-velour-600 dark:text-velour-400 hover:underline" @click="notifOpen=false">
                                View all →
                            </a>
                        </div>
                    </div>
                </div>

                {{-- User menu --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open=!open"
                            class="flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-velour-600 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300 max-w-[120px] truncate">
                            {{ auth()->user()->name }}
                        </span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" x-cloak @click.outside="open=false"
                         class="absolute right-0 mt-2 w-48 rounded-xl shadow-lg border py-1 z-50
                                bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
                        <a href="{{ route('settings.index') }}"
                           class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800">Settings</a>
                        <hr class="my-1 border-gray-100 dark:border-gray-800">
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6">

            @if(session('success'))
            <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl text-sm
                        bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300">
                <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 px-4 py-3 rounded-xl text-sm
                        bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300">
                <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="mb-4 px-4 py-3 rounded-xl text-sm
                        bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300">
                <p class="font-medium mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
    (function () {
        const saved = localStorage.getItem('velour-theme');
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        if (saved === 'dark' || (!saved && prefersDark)) {
            document.documentElement.classList.add('dark');
        }
    })();
    document.addEventListener('alpine:init', () => {
        Alpine.store('theme', {
            dark: document.documentElement.classList.contains('dark'),
            toggle() {
                this.dark = !this.dark;
                if (this.dark) {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('velour-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('velour-theme', 'light');
                }
            }
        });
    });
</script>

@include('partials.chatbot')
@stack('scripts')
</body>
</html>
