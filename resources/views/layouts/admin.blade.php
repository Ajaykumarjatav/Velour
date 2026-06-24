<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>EasyGrox Admin · @yield('title', 'Dashboard')</title>
  <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: {
        fontFamily: { sans: ['"Inter"', 'ui-sans-serif', 'system-ui', 'sans-serif'] },
        colors: { velour: { 50:'#f5f3ff',100:'#ede9fe',200:'#ddd6fe',300:'#c4b5fd',400:'#a78bfa',500:'#8b5cf6',600:'#7c3aed',700:'#6d28d9',800:'#5b21b6',900:'#4c1d95' } }
      } }
    }
  </script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <style>
    html { font-size: 14px; }
    html, body {
      font-family: 'Inter', ui-sans-serif, system-ui, sans-serif;
      font-weight: 400;
      line-height: 1.45;
      font-feature-settings: 'kern' 1, 'liga' 1, 'cv02' 1, 'cv03' 1, 'cv04' 1, 'cv11' 1;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }
    .font-thin, .font-extralight, .font-light, .font-normal { font-weight: 400; }
    .font-medium { font-weight: 500; }
    .font-semibold, .font-bold, .font-extrabold, .font-black { font-weight: 600; }
    strong, b { font-weight: 600; }
    /* Dark UI — match tenant app scrollbar (no .dark class on html here) */
    * {
      scrollbar-width: thin;
      scrollbar-color: rgb(75 85 99) rgb(3 7 18);
    }
    *::-webkit-scrollbar { width: 8px; height: 8px; }
    *::-webkit-scrollbar-track { background: rgb(3 7 18); border-radius: 4px; }
    *::-webkit-scrollbar-thumb { background: rgb(55 65 81); border-radius: 4px; }
    *::-webkit-scrollbar-thumb:hover { background: rgb(75 85 99); }
    .nav-icon { width: 1.25rem; height: 1.25rem; flex-shrink: 0; opacity: 0.9; }

  .admin-shell-sidebar {
    width: 14rem;
    transition: width 0.2s ease;
    overflow: visible;
  }
  html.admin-sidebar-is-collapsed .admin-shell-sidebar {
    width: 4.5rem;
  }
  .admin-sidebar-logo-icon {
    display: none;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-brand {
    display: none;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-logo-icon {
    display: block;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-header-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-header {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-wrapper nav {
    display: flex;
    flex-direction: column;
    padding-left: 0.75rem;
    padding-right: 0.75rem;
    align-items: center;
    overflow: visible;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-wrapper {
    overflow: visible;
  }
  html.admin-sidebar-is-collapsed .admin-nav-link {
    position: relative;
    justify-content: center;
    padding: 0.625rem;
    border-radius: 0.75rem;
    width: 2.75rem;
    overflow: visible;
    font-size: 0;
    gap: 0;
  }
  html.admin-sidebar-is-collapsed .admin-nav-link .nav-icon {
    width: 1.25rem;
    height: 1.25rem;
  }
  html.admin-sidebar-is-collapsed .admin-nav-link::after {
    content: attr(data-title);
    position: absolute;
    left: calc(100% + 0.5rem);
    top: 50%;
    transform: translateY(-50%);
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    background: rgb(17 24 39 / 0.95);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s;
    z-index: 100;
  }
  html.admin-sidebar-is-collapsed .admin-nav-link:hover::after {
    opacity: 1;
  }
  html.admin-sidebar-is-collapsed .admin-nav-label,
  html.admin-sidebar-is-collapsed .admin-nav-badge,
  html.admin-sidebar-is-collapsed .admin-sidebar-footer-text,
  html.admin-sidebar-is-collapsed .admin-impersonate-banner {
    display: none;
  }
  html.admin-sidebar-is-collapsed .admin-footer-link {
    position: relative;
    justify-content: center;
    padding: 0.625rem;
    width: 2.75rem;
    margin-left: auto;
    margin-right: auto;
    font-size: 0;
    gap: 0;
  }
  html.admin-sidebar-is-collapsed .admin-footer-link span {
    display: none;
  }
  html.admin-sidebar-is-collapsed .admin-footer-link::after {
    content: attr(data-title);
    position: absolute;
    left: calc(100% + 0.5rem);
    top: 50%;
    transform: translateY(-50%);
    padding: 0.375rem 0.75rem;
    border-radius: 0.5rem;
    background: rgb(17 24 39 / 0.95);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 500;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s;
    z-index: 100;
  }
  html.admin-sidebar-is-collapsed .admin-footer-link:hover::after {
    opacity: 1;
  }
  html.admin-sidebar-is-collapsed .admin-sidebar-footer {
    padding-left: 0.75rem;
    padding-right: 0.75rem;
  }
  </style>
  @stack('styles')
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full min-h-screen flex min-h-0"
      x-data="{ sidebarOpen: false, sidebarCollapsed: localStorage.getItem('admin-sidebar-collapsed') === '1' }"
      x-init="
        document.documentElement.classList.toggle('admin-sidebar-is-collapsed', sidebarCollapsed);
        $watch('sidebarCollapsed', v => {
          localStorage.setItem('admin-sidebar-collapsed', v ? '1' : '0');
          document.documentElement.classList.toggle('admin-sidebar-is-collapsed', v);
        });
      ">

  {{-- Sidebar --}}
  <aside class="admin-shell-sidebar flex-shrink-0 bg-gray-900 flex flex-col h-screen sticky top-0 z-30 border-r border-gray-800/80">
    <div class="admin-sidebar-wrapper flex flex-col h-full min-h-0 overflow-visible">
    <div class="admin-sidebar-header border-b border-gray-800 px-5 py-5">
      <div class="admin-sidebar-header-inner">
        <div class="admin-sidebar-brand min-w-0">
          <img src="{{ asset('images/easygrox-logo-dark.png') }}" alt="EasyGrox" class="admin-sidebar-logo-full h-12 w-auto max-w-[11rem]">
          <p class="admin-sidebar-subtitle text-xs text-red-400 font-semibold uppercase tracking-widest mt-1.5">Admin Panel</p>
        </div>
        <img src="{{ asset('images/easygrox-icon.png') }}" alt="EasyGrox" class="admin-sidebar-logo-icon w-9 h-9 object-contain mx-auto" title="EasyGrox Admin">
      </div>
    </div>

    {{-- Impersonation banner --}}
    @if(session('impersonating'))
    <div class="admin-impersonate-banner mx-3 mt-3 px-3 py-2 bg-amber-500/20 border border-amber-500/30 rounded-xl text-xs text-amber-300">
      Impersonating user
      <form method="POST" action="{{ route('admin.impersonate.stop') }}" class="mt-1">
        @csrf
        <button type="submit" class="underline">Stop →</button>
      </form>
    </div>
    @endif

    <nav class="flex-1 min-h-0 px-3 py-4 space-y-0.5 overflow-y-auto">
      @php
        $nav = [
          ['route' => 'admin.dashboard',     'icon' => 'dashboard',    'label' => 'Dashboard'],
          ['route' => 'admin.facilities',    'icon' => 'facilities',   'label' => 'Facilities'],
          ['route' => 'admin.tenants',       'icon' => 'tenants',      'label' => 'Tenants'],
          ['route' => 'admin.explorer',      'icon' => 'search',       'label' => 'Explorer'],
          ['route' => 'admin.users',         'icon' => 'team',         'label' => 'Users'],
          ['route' => 'admin.revenue',       'icon' => 'revenue',      'label' => 'Revenue'],
          ['route' => 'admin.plans',         'icon' => 'packages',     'label' => 'Plans'],
          ['route' => 'admin.support.index', 'icon' => 'support',      'label' => 'Support'],
          ['route' => 'admin.analytics',     'icon' => 'analytics',    'label' => 'Analytics'],
          ['route' => 'admin.billing',       'icon' => 'billing',      'label' => 'Billing'],
          ['route' => 'admin.audit.index',   'icon' => 'audit',        'label' => 'Audit Log'],
        ];
        $openTickets = \App\Models\SupportTicket::whereIn('status',['open','in_progress'])->whereNull('assigned_to')->count();
      @endphp
      @foreach($nav as $item)
      @php
        $navTitle = $item['label'];
        if ($item['route'] === 'admin.support.index' && $openTickets > 0) {
            $navTitle .= ' ('.$openTickets.' open)';
        }
      @endphp
      <a href="{{ route($item['route']) }}"
         data-title="{{ $navTitle }}"
         class="admin-nav-link flex items-center gap-2.5 px-3 py-2 rounded-lg text-[13px] transition-colors
                {{ request()->routeIs($item['route'].'*') ? 'bg-velour-600 text-white font-semibold' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
        @include('partials.sidebar-nav-icon', ['icon' => $item['icon']])
        <span class="admin-nav-label flex-1">{{ $item['label'] }}</span>
        @if($item['route'] === 'admin.support.index' && $openTickets > 0)
          <span class="admin-nav-badge text-[10px] font-bold bg-red-600 text-white rounded-full w-4 h-4 flex items-center justify-center flex-shrink-0">
            {{ $openTickets > 9 ? '9+' : $openTickets }}
          </span>
        @endif
      </a>
      @endforeach
    </nav>

    <div class="admin-sidebar-footer p-3 border-t border-gray-800 mt-auto">
      <div class="admin-sidebar-footer-text px-3 py-2 text-xs text-gray-500">
        <p class="font-medium text-gray-300 truncate">{{ Auth::user()->name }}</p>
        <p class="text-gray-500 truncate">{{ Auth::user()->email }}</p>
      </div>
      <a href="{{ route('dashboard') }}"
         data-title="Back to EasyGrox"
         class="admin-footer-link flex items-center gap-2 px-3 py-2 text-xs text-gray-400 hover:text-white rounded-xl hover:bg-gray-800 transition-colors">
        <svg class="w-4 h-4 shrink-0 nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
        <span>← Back to EasyGrox</span>
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                data-title="Sign out"
                class="admin-footer-link flex items-center gap-2 px-3 py-2 text-xs text-gray-400 hover:text-red-400 rounded-xl hover:bg-gray-800 transition-colors w-full">
          <svg class="w-4 h-4 shrink-0 nav-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          <span>Sign out</span>
        </button>
      </form>
    </div>
    </div>
  </aside>

  {{-- Main --}}
  <main class="flex-1 min-h-0 overflow-y-auto bg-gray-950">
    {{-- Top bar --}}
    <div class="sticky top-0 z-10 bg-gray-950/95 backdrop-blur border-b border-gray-800 px-4 sm:px-6 py-3 flex items-center justify-between gap-3">
      <div class="flex items-center gap-2 min-w-0">
        <button type="button"
                @click="sidebarCollapsed = !sidebarCollapsed"
                class="flex p-1.5 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 shrink-0 transition-colors"
                title="Toggle sidebar"
                aria-label="Toggle sidebar">
          <svg class="w-4 h-4 transition-transform duration-200" :class="sidebarCollapsed ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
          </svg>
        </button>
        <h1 class="text-[15px] font-semibold text-white truncate">@yield('page-title', 'Admin')</h1>
      </div>
      <span class="px-2.5 py-1 text-xs font-bold bg-red-900/50 text-red-300 rounded-lg border border-red-800/50 uppercase tracking-wider">
        Super Admin
      </span>
    </div>

    {{-- Flash messages --}}
    <div class="px-6 pt-4">
      @if(session('success'))
      <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-green-900/30 text-green-300 border border-green-800/50">
        {{ session('success') }}
      </div>
      @endif
      @if(session('warning'))
      <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-amber-900/30 text-amber-300 border border-amber-800/50">
        {{ session('warning') }}
      </div>
      @endif
      @if(session('info'))
      <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-blue-900/30 text-blue-300 border border-blue-800/50">
        {{ session('info') }}
      </div>
      @endif
      @if($errors->any())
      <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-red-900/30 text-red-300 border border-red-800/50">
        {{ $errors->first() }}
      </div>
      @endif
    </div>

    <div class="p-5 text-[13px] leading-snug">
      @yield('content')
    </div>
  </main>

  @include('partials.chatbot', ['isAdminLayout' => true])

  @include('partials.form-client-validation')
  @include('partials.disable-double-submit')
  @stack('scripts')
</body>
</html>
