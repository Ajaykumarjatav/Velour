<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-950">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Velour Admin · @yield('title', 'Dashboard')</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: { extend: { colors: { velour: { 50:'#f5f3ff',100:'#ede9fe',200:'#ddd6fe',300:'#c4b5fd',400:'#a78bfa',500:'#8b5cf6',600:'#7c3aed',700:'#6d28d9',800:'#5b21b6',900:'#4c1d95' } } } }
    }
  </script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full flex" x-data="{ sidebarOpen: false }">

  {{-- Sidebar --}}
  <aside class="w-56 flex-shrink-0 bg-gray-900 flex flex-col h-screen sticky top-0">
    <div class="px-5 py-5 border-b border-gray-800">
      <p class="text-lg font-black text-white tracking-tight">velour<span class="text-velour-400">.</span></p>
      <p class="text-xs text-red-400 font-semibold uppercase tracking-widest mt-0.5">Admin Panel</p>
    </div>

    {{-- Impersonation banner --}}
    @if(session('impersonating'))
    <div class="mx-3 mt-3 px-3 py-2 bg-amber-500/20 border border-amber-500/30 rounded-xl text-xs text-amber-300">
      Impersonating user
      <form method="POST" action="{{ route('admin.impersonate.stop') }}" class="mt-1">
        @csrf
        <button type="submit" class="underline">Stop →</button>
      </form>
    </div>
    @endif

    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
      @php
        $nav = [
          ['route' => 'admin.dashboard',    'icon' => '◼',  'label' => 'Dashboard'],
          ['route' => 'admin.tenants',      'icon' => '🏠',  'label' => 'Tenants'],
          ['route' => 'admin.users',        'icon' => '👤',  'label' => 'Users'],
          ['route' => 'admin.revenue',      'icon' => '💰',  'label' => 'Revenue'],
          ['route' => 'admin.plans',        'icon' => '📦',  'label' => 'Plans'],
          ['route' => 'admin.support.index','icon' => '🎧',  'label' => 'Support'],
          ['route' => 'admin.analytics',    'icon' => '📊',  'label' => 'Analytics'],
          ['route' => 'admin.billing',      'icon' => '💳',  'label' => 'Billing'],
          ['route' => 'admin.audit.index',  'icon' => '🔐',  'label' => 'Audit Log'],
        ];
        $openTickets = \App\Models\SupportTicket::whereIn('status',['open','in_progress'])->whereNull('assigned_to')->count();
      @endphp
      @foreach($nav as $item)
      <a href="{{ route($item['route']) }}"
         class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm transition-colors
                {{ request()->routeIs($item['route'].'*') ? 'bg-velour-600 text-white' : 'text-gray-400 hover:text-white hover:bg-gray-800' }}">
        <span class="text-base">{{ $item['icon'] }}</span>
        <span class="flex-1">{{ $item['label'] }}</span>
        @if($item['route'] === 'admin.support.index' && $openTickets > 0)
          <span class="text-[10px] font-bold bg-red-600 text-white rounded-full w-4 h-4 flex items-center justify-center flex-shrink-0">
            {{ $openTickets > 9 ? '9+' : $openTickets }}
          </span>
        @endif
      </a>
      @endforeach
    </nav>

    <div class="p-3 border-t border-gray-800">
      <div class="px-3 py-2 text-xs text-gray-500">
        <p class="font-medium text-gray-300 truncate">{{ Auth::user()->name }}</p>
        <p class="text-gray-500 truncate">{{ Auth::user()->email }}</p>
      </div>
      <a href="{{ route('dashboard') }}"
         class="flex items-center gap-2 px-3 py-2 text-xs text-gray-400 hover:text-white rounded-xl hover:bg-gray-800 transition-colors">
        ← Back to Velour
      </a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="flex items-center gap-2 px-3 py-2 text-xs text-gray-400 hover:text-red-400 rounded-xl hover:bg-gray-800 transition-colors w-full">
          Sign out
        </button>
      </form>
    </div>
  </aside>

  {{-- Main --}}
  <main class="flex-1 min-h-screen overflow-y-auto bg-gray-950">
    {{-- Top bar --}}
    <div class="sticky top-0 z-10 bg-gray-950/95 backdrop-blur border-b border-gray-800 px-6 py-3 flex items-center justify-between">
      <h1 class="text-lg font-semibold text-white">@yield('page-title', 'Admin')</h1>
      <span class="px-2.5 py-1 text-xs font-bold bg-red-900/50 text-red-300 rounded-lg border border-red-800/50 uppercase tracking-wider">
        Super Admin
      </span>
    </div>

    {{-- Flash messages --}}
    <div class="px-6 pt-4">
      @foreach(['success' => 'green', 'warning' => 'amber', 'info' => 'blue'] as $type => $color)
        @if(session($type))
        <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-{{ $color }}-900/30 text-{{ $color }}-300 border border-{{ $color }}-800/50">
          {{ session($type) }}
        </div>
        @endif
      @endforeach
      @if($errors->any())
      <div class="mb-4 px-4 py-3 rounded-xl text-sm bg-red-900/30 text-red-300 border border-red-800/50">
        {{ $errors->first() }}
      </div>
      @endif
    </div>

    <div class="p-6">
      @yield('content')
    </div>
  </main>

  @include('partials.chatbot', ['isAdminLayout' => true])

  @stack('scripts')
</body>
</html>
