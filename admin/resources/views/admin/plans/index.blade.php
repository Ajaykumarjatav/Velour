@extends('layouts.admin')
@section('title', 'Plan Management')
@section('page-title', 'Plan Management')
@section('content')

<div class="space-y-5" x-data="{ migrateModal: false }">

  {{-- Flash --}}
  @if(session('success'))
  <div class="px-4 py-3 bg-green-900/30 border border-green-800/50 rounded-xl text-sm text-green-300">{{ session('success') }}</div>
  @endif

  {{-- Plan cards --}}
  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach($planData as $item)
    @php
      $planKey = $item['plan']->key;
      $accentBg = match($planKey) {
        'enterprise' => 'border-purple-700/60 bg-purple-900/10',
        'pro'        => 'border-blue-700/60 bg-blue-900/10',
        'starter'    => 'border-green-700/60 bg-green-900/10',
        default      => 'border-gray-700 bg-gray-900',
      };
      $accentText = match($planKey) {
        'enterprise' => 'text-purple-400', 'pro' => 'text-blue-400',
        'starter' => 'text-green-400', default => 'text-gray-400',
      };
    @endphp
    <div class="border rounded-2xl p-5 {{ $accentBg }}">
      <div class="flex items-start justify-between mb-3">
        <div>
          <h3 class="text-base font-bold text-white">{{ $item['plan']->name }}</h3>
          <p class="{{ $accentText }} text-sm font-semibold mt-0.5">
            £{{ number_format($item['plan']->priceMonthly) }}<span class="text-gray-500 text-xs">/mo</span>
          </p>
        </div>
        <span class="text-2xl font-black text-gray-200">{{ number_format($item['count']) }}</span>
      </div>
      <div class="space-y-1.5 text-xs text-gray-500">
        <div class="flex justify-between">
          <span>Monthly MRR</span>
          <span class="text-gray-300 font-medium">£{{ number_format($item['mrr']) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Annual ARR</span>
          <span class="text-gray-300 font-medium">£{{ number_format($item['arr']) }}</span>
        </div>
        <div class="flex justify-between">
          <span>Staff limit</span>
          <span class="text-gray-300">{{ $item['plan']->limit('staff') === -1 ? '∞' : $item['plan']->limit('staff') }}</span>
        </div>
        <div class="flex justify-between">
          <span>Client limit</span>
          <span class="text-gray-300">{{ $item['plan']->limit('clients') === -1 ? '∞' : number_format($item['plan']->limit('clients')) }}</span>
        </div>
      </div>
      <div class="mt-3 pt-3 border-t border-gray-700/50">
        <p class="text-xs text-gray-600 mb-1">Features:</p>
        <div class="flex flex-wrap gap-1">
          @foreach(array_slice($item['plan']->features ?? [], 0, 4) as $feature)
          <span class="px-1.5 py-0.5 rounded text-[10px] bg-gray-800 text-gray-400">{{ ucwords(str_replace('_',' ',$feature)) }}</span>
          @endforeach
        </div>
      </div>
    </div>
    @endforeach
  </div>

  {{-- Bulk Plan Migration --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-sm font-semibold text-gray-300">Migrate Tenant Plan</h2>
      <p class="text-xs text-gray-500">Move a single user to a different plan (bypasses Stripe)</p>
    </div>
    <form method="POST" action="{{ route('admin.plans.migrate') }}" class="flex flex-wrap gap-3 items-end">
      @csrf
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-gray-400 mb-1.5">User email</label>
        <input type="text" name="user_id" placeholder="Search user ID or use tenant page…"
               class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500 placeholder-gray-600">
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">New plan</label>
        <select name="plan" class="px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
          <option value="free">Free</option>
          <option value="starter">Starter</option>
          <option value="pro">Pro</option>
          <option value="enterprise">Enterprise</option>
        </select>
      </div>
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-gray-400 mb-1.5">Reason *</label>
        <input type="text" name="reason" required placeholder="e.g. Error correction, partner deal…"
               class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500 placeholder-gray-600">
      </div>
      <button type="submit" class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Migrate plan
      </button>
    </form>
  </div>

  {{-- Bulk migration --}}
  <div class="bg-gray-900 border border-amber-800/30 rounded-2xl p-5">
    <div class="flex items-start gap-3 mb-4">
      <span class="text-amber-400 text-xl flex-shrink-0">⚠️</span>
      <div>
        <h2 class="text-sm font-semibold text-gray-300">Bulk Plan Migration</h2>
        <p class="text-xs text-gray-500 mt-0.5">Moves ALL users from one plan to another. Cannot be undone. Use with caution.</p>
      </div>
    </div>
    <form method="POST" action="{{ route('admin.plans.bulk-migrate') }}" class="flex flex-wrap gap-3 items-end"
          onsubmit="return confirm('This will migrate ALL users on the selected plan. Are you absolutely sure?')">
      @csrf
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">From plan</label>
        <select name="from_plan" class="px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500">
          <option value="free">Free</option><option value="starter">Starter</option>
          <option value="pro">Pro</option><option value="enterprise">Enterprise</option>
        </select>
      </div>
      <div>
        <label class="block text-xs text-gray-400 mb-1.5">To plan</label>
        <select name="to_plan" class="px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500">
          <option value="starter">Starter</option><option value="pro">Pro</option>
          <option value="enterprise">Enterprise</option><option value="free">Free</option>
        </select>
      </div>
      <div class="flex-1 min-w-[200px]">
        <label class="block text-xs text-gray-400 mb-1.5">Reason *</label>
        <input type="text" name="reason" required placeholder="e.g. Plan restructure Q1 2025"
               class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-amber-500 placeholder-gray-600">
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer self-end pb-2.5">
        <input type="checkbox" name="confirm" value="1" required class="rounded"> I confirm this bulk action
      </label>
      <button type="submit" class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-amber-700 hover:bg-amber-600 text-white transition-colors">
        Bulk migrate
      </button>
    </form>
  </div>

  {{-- Active Overrides --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-800 flex items-center justify-between">
      <h2 class="text-sm font-semibold text-gray-300">Active Plan Overrides</h2>
      <span class="text-xs text-gray-500">{{ $overrides->total() }} total</span>
    </div>
    @if($overrides->isEmpty())
    <p class="px-5 py-8 text-sm text-gray-600 text-center">No active overrides.</p>
    @else
    <div class="overflow-x-auto">
      <table class="w-full text-sm">
        <thead>
        <tr class="border-b border-gray-800 bg-gray-800/50">
          <th class="text-left px-5 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Salon</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Type</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Detail</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Applied by</th>
          <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Expires</th>
          <th class="px-4 py-3"></th>
        </tr>
        </thead>
        <tbody class="divide-y divide-gray-800/50">
        @foreach($overrides as $o)
        <tr class="hover:bg-gray-800/30">
          <td class="px-5 py-3">
            <a href="{{ route('admin.tenants.show', $o->salon_id) }}" class="text-gray-200 hover:text-white font-medium">
              {{ $o->salon?->name ?? '#'.$o->salon_id }}
            </a>
          </td>
          <td class="px-4 py-3 text-gray-400 capitalize text-xs">{{ str_replace('_',' ',$o->override_type) }}</td>
          <td class="px-4 py-3 text-gray-300 text-xs">
            @if($o->override_plan) → {{ ucfirst($o->override_plan) }} @endif
            @if($o->trial_extension_days) +{{ $o->trial_extension_days }} days @endif
            @if($o->discount_percentage) {{ $o->discount_percentage }}% off @endif
            {{ $o->reason ? '· '.$o->reason : '' }}
          </td>
          <td class="px-4 py-3 text-gray-500 text-xs">{{ $o->appliedBy?->name }}</td>
          <td class="px-4 py-3 text-xs">
            @if($o->expires_at)
              <span class="{{ $o->daysRemaining() <= 7 ? 'text-red-400' : 'text-amber-400' }}">
                in {{ $o->daysRemaining() }}d
              </span>
            @else
              <span class="text-gray-600">Permanent</span>
            @endif
          </td>
          <td class="px-4 py-3">
            <form method="POST" action="{{ route('admin.plans.override.expire', $o->id) }}">
              @csrf @method('PATCH')
              <button type="submit" class="text-xs text-red-400 hover:text-red-300">Expire</button>
            </form>
          </td>
        </tr>
        @endforeach
        </tbody>
      </table>
    </div>
    <div class="px-5 py-3 border-t border-gray-800">{{ $overrides->links() }}</div>
    @endif
  </div>

  {{-- Recent plan migrations (from audit log) --}}
  @if($recentMigrations->count())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h2 class="px-5 py-4 text-sm font-semibold text-gray-300 border-b border-gray-800">Recent Plan Changes</h2>
    <div class="divide-y divide-gray-800/50">
      @foreach($recentMigrations as $log)
      <div class="flex items-center justify-between px-5 py-3 text-sm">
        <div>
          <p class="text-gray-200">{{ $log->description }}</p>
          <p class="text-xs text-gray-500 mt-0.5">{{ $log->occurred_at->diffForHumans() }}</p>
        </div>
        <a href="{{ route('admin.audit.show', $log->id) }}" class="text-xs text-velour-400 hover:text-velour-300">Detail →</a>
      </div>
      @endforeach
    </div>
  </div>
  @endif

</div>
@endsection
