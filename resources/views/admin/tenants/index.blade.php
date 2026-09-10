@extends('layouts.admin')
@section('title', 'Tenants')
@section('page-title', 'Tenant accounts')
@section('content')

<div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
  @foreach([
    ['Accounts', $stats['total'], 'text-gray-200'],
    ['Blocked', $stats['blocked'], $stats['blocked'] > 0 ? 'text-red-400' : 'text-gray-500'],
    ['Active stores', $stats['active'], 'text-green-400'],
    ['Suspended stores', $stats['suspended'], $stats['suspended'] > 0 ? 'text-red-400' : 'text-gray-500'],
    ['New accounts (month)', $stats['new_month'], 'text-velour-400'],
  ] as [$label, $val, $color])
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
    <p class="text-2xl font-black {{ $color }}">{{ number_format($val) }}</p>
    <p class="text-xs text-gray-500 mt-1 uppercase tracking-wider">{{ $label }}</p>
  </div>
  @endforeach
</div>

<form method="GET" action="{{ route('admin.tenants') }}"
      class="bg-gray-900 border border-gray-800 rounded-2xl p-4 mb-5 flex flex-wrap gap-3">
  <input type="search" name="search" value="{{ request('search') }}"
         placeholder="Search account, email, store name…"
         class="flex-1 min-w-[200px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-velour-500">
  <select name="status" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
    <option value="">All statuses</option>
    <option value="active" @selected(request('status')==='active')>Active</option>
    <option value="blocked" @selected(request('status')==='blocked')>Blocked accounts</option>
    <option value="suspended" @selected(request('status')==='suspended')>Stores suspended only</option>
  </select>
  <select name="plan" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
    <option value="">All plans</option>
    @foreach($planOptions as $p)
    <option value="{{ $p }}" @selected(request('plan')===$p)>{{ \App\Billing\Plan::labelFor($p) }}</option>
    @endforeach
  </select>
  <select name="sort" class="px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl">
    <option value="">Newest first</option>
    <option value="oldest" @selected(request('sort')==='oldest')>Oldest first</option>
    <option value="name" @selected(request('sort')==='name')>Name A–Z</option>
    <option value="stores" @selected(request('sort')==='stores')>Most stores</option>
  </select>
  <div class="flex gap-2">
    <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white">Filter</button>
    <a href="{{ route('admin.tenants.export') }}" class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800">Export CSV</a>
  </div>
</form>

<div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-x-auto">
  <table class="w-full text-sm min-w-[36rem]">
    <thead>
    <tr class="border-b border-gray-800 bg-gray-800/50">
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Account</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden sm:table-cell">Plan</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Stores</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden lg:table-cell">Clients</th>
      <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden md:table-cell">Appts</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase">Status</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden sm:table-cell">Joined</th>
      <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase hidden md:table-cell">Device</th>
      <th class="px-4 py-3"></th>
    </tr>
    </thead>
    <tbody class="divide-y divide-gray-800/50">
    @forelse($accounts as $account)
    @php
      $agg = $aggregateStats[$account->id] ?? null;
      $planColor = match($account->plan) {
        'premium'  => 'text-amber-400 bg-amber-900/30', 'standard' => 'text-velour-400 bg-velour-900/30',
        'trial' => 'text-blue-400 bg-blue-900/30', default => 'text-gray-500 bg-gray-800',
      };
      $activeStores = (int) ($agg->active_stores ?? $account->salons->where('is_active', true)->count());
      $allSuspended = ! $account->is_active || ($account->stores_count > 0 && $activeStores === 0);
      $isBlocked = ! $account->is_active;
    @endphp
    <tr class="hover:bg-gray-800/30 transition-colors {{ $allSuspended ? 'opacity-60' : '' }}">
      <td class="px-4 py-3">
        <a href="{{ route('admin.tenants.owners.show', $account->id) }}" class="font-semibold text-gray-200 hover:text-white">{{ $account->name }}</a>
        <p class="text-xs text-gray-500">{{ $account->email }}</p>
      </td>
      <td class="px-4 py-3 hidden sm:table-cell">
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $planColor }}">{{ \App\Billing\Plan::labelFor($account->plan) }}</span>
      </td>
      <td class="px-4 py-3 text-right text-gray-300">
        {{ $account->stores_count }}
        @php $freelancerStores = $account->salons->where('is_freelancer', true)->count(); @endphp
        @if($freelancerStores > 0)
          <p class="text-[10px] text-cyan-400/90 font-medium">{{ $freelancerStores }} freelancer</p>
        @endif
      </td>
      <td class="px-4 py-3 hidden lg:table-cell text-right text-gray-300">{{ number_format((int) ($agg->clients_total ?? 0)) }}</td>
      <td class="px-4 py-3 hidden md:table-cell text-right text-gray-300">{{ number_format((int) ($agg->appointments_total ?? 0)) }}</td>
      <td class="px-4 py-3">
        @if($isBlocked)
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-red-900/50 text-red-400">Blocked</span>
        @elseif($allSuspended)
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-amber-900/50 text-amber-400">Stores suspended</span>
        @else
          <span class="px-2 py-0.5 rounded-lg text-xs font-semibold bg-green-900/50 text-green-400">Active</span>
        @endif
      </td>
      <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">{{ $account->created_at->format('d M Y') }}</td>
      <td class="px-4 py-3 hidden md:table-cell">
        @php $device = $account->signup_device ?: 'Unknown'; @endphp
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold
          @if($device === 'Mobile') bg-sky-900/40 text-sky-300
          @elseif($device === 'Tablet') bg-violet-900/40 text-violet-300
          @elseif($device === 'App') bg-amber-900/40 text-amber-300
          @elseif($device === 'Desktop') bg-gray-800 text-gray-300
          @else bg-gray-800 text-gray-500 @endif"
          @if($account->signup_user_agent) title="{{ $account->signup_user_agent }}" @endif>{{ $device }}</span>
      </td>
      <td class="px-4 py-3 text-right overflow-visible">
        <div class="inline-flex items-center justify-end gap-3">
          @if(! $account->welcome_whatsapp_sent_at)
          <button type="button"
                  class="js-welcome-wa-toggle inline-flex items-center justify-center w-8 h-8 rounded-lg text-emerald-400 hover:text-emerald-300 hover:bg-gray-800/80 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50"
                  data-url="{{ route('admin.tenants.owners.welcome-whatsapp', $account->id) }}"
                  data-name="{{ $account->name }}"
                  title="Welcome WhatsApp"
                  aria-expanded="false"
                  aria-haspopup="true">
            <span class="sr-only">Welcome WhatsApp options</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
          </button>
          @endif
          <a href="{{ route('admin.tenants.owners.logs', $account->id) }}"
             class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-gray-400 hover:text-velour-300 hover:bg-gray-800/80"
             title="View all logs">
            <span class="sr-only">View all logs</span>
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
          </a>
          <a href="{{ route('admin.tenants.stores', $account->id) }}" class="text-xs text-velour-400 hover:text-velour-300 font-medium whitespace-nowrap">View stores →</a>
        </div>
      </td>
    </tr>
    @empty
    <tr><td colspan="9" class="px-5 py-12 text-center text-sm text-gray-500">No tenant accounts found.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>

<div class="mt-4">{{ $accounts->links() }}</div>

{{-- Fixed menu escapes table overflow so both options stay fully visible --}}
<div id="welcome-wa-menu"
     class="hidden fixed z-[10060] w-56 rounded-xl border border-gray-700 bg-gray-900 shadow-2xl py-1"
     role="menu">
  <button type="button"
          id="welcome-wa-send"
          class="w-full px-3 py-2.5 text-xs font-medium text-emerald-300 hover:bg-gray-800 text-left"
          role="menuitem">
    Send welcome message
  </button>
  <button type="button"
          id="welcome-wa-mark"
          class="w-full px-3 py-2.5 text-xs font-medium text-gray-300 hover:bg-gray-800 text-left border-t border-gray-800"
          role="menuitem">
    Already sent — mark &amp; hide
  </button>
</div>

<script>
(function () {
  var flash = document.getElementById('admin-inline-flash');
  var menu = document.getElementById('welcome-wa-menu');
  var sendBtn = document.getElementById('welcome-wa-send');
  var markBtn = document.getElementById('welcome-wa-mark');
  var activeToggle = null;

  function showMsg(text, kind) {
    if (!flash) {
      flash = document.createElement('div');
      flash.id = 'admin-inline-flash';
      var host = document.querySelector('main') || document.body;
      host.prepend(flash);
    }
    flash.className = 'mb-4 px-4 py-3 rounded-xl text-sm border ' + (kind === 'error'
      ? 'bg-red-900/30 text-red-300 border-red-800/50'
      : 'bg-green-900/30 text-green-300 border-green-800/50');
    flash.textContent = text;
    flash.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  function closeMenu() {
    if (!menu) return;
    menu.classList.add('hidden');
    if (activeToggle) {
      activeToggle.setAttribute('aria-expanded', 'false');
      activeToggle = null;
    }
  }

  function placeMenu(toggle) {
    var rect = toggle.getBoundingClientRect();
    var menuWidth = 224;
    var menuHeight = 84;
    var gap = 6;
    var left = Math.min(Math.max(8, rect.right - menuWidth), window.innerWidth - menuWidth - 8);
    var top = rect.bottom + gap;
    if (top + menuHeight > window.innerHeight - 8) {
      top = Math.max(8, rect.top - menuHeight - gap);
    }
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
  }

  function openMenu(toggle) {
    activeToggle = toggle;
    toggle.setAttribute('aria-expanded', 'true');
    placeMenu(toggle);
    menu.classList.remove('hidden');
  }

  function requestWelcome(url, payload, toggle) {
    var headers = window.EasyGroxHttp
      ? window.EasyGroxHttp.csrfHeaders({ Accept: 'application/json', 'Content-Type': 'application/json' })
      : {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
          Accept: 'application/json',
          'Content-Type': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        };

    sendBtn.disabled = true;
    markBtn.disabled = true;
    toggle.disabled = true;

    fetch(url, {
      method: 'POST',
      headers: headers,
      credentials: 'same-origin',
      body: JSON.stringify(payload),
    }).then(function (res) {
      return res.json().catch(function () { return {}; }).then(function (data) {
        return { ok: res.ok, data: data };
      });
    }).then(function (result) {
      var data = result.data || {};
      var msg = data.message || (result.ok ? 'Done.' : 'Could not update WhatsApp status.');
      showMsg(msg, result.ok ? 'ok' : 'error');
      closeMenu();
      sendBtn.disabled = false;
      markBtn.disabled = false;
      if (result.ok && data.hide_button) {
        toggle.remove();
      } else {
        toggle.disabled = false;
      }
    }).catch(function () {
      showMsg('Network error. Please try again.', 'error');
      closeMenu();
      sendBtn.disabled = false;
      markBtn.disabled = false;
      toggle.disabled = false;
    });
  }

  document.querySelectorAll('.js-welcome-wa-toggle').forEach(function (toggle) {
    toggle.addEventListener('click', function (event) {
      event.stopPropagation();
      if (activeToggle === toggle && !menu.classList.contains('hidden')) {
        closeMenu();
        return;
      }
      openMenu(toggle);
    });
  });

  sendBtn.addEventListener('click', function (event) {
    event.stopPropagation();
    if (!activeToggle) return;
    var toggle = activeToggle;
    var url = toggle.getAttribute('data-url');
    if (!url) return;
    if (!confirm('Send welcome WhatsApp from +91 99501 05679?')) return;
    requestWelcome(url, {}, toggle);
  });

  markBtn.addEventListener('click', function (event) {
    event.stopPropagation();
    if (!activeToggle) return;
    var toggle = activeToggle;
    var url = toggle.getAttribute('data-url');
    if (!url) return;
    if (!confirm('Mark as already sent? The WhatsApp button will be hidden.')) return;
    requestWelcome(url, { already_sent: true }, toggle);
  });

  menu.addEventListener('click', function (event) { event.stopPropagation(); });
  document.addEventListener('click', closeMenu);
  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeMenu();
  });
  window.addEventListener('resize', closeMenu);
  window.addEventListener('scroll', closeMenu, true);
})();
</script>
@endsection
