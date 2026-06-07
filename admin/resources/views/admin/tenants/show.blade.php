@extends('layouts.admin')
@section('title', $salon->name)
@section('page-title', $salon->name)
@section('content')

<div class="space-y-5" x-data="{ suspendModal: false, unsuspendModal: false, overrideModal: false }">

  {{-- Flash --}}
  @if(session('success'))
  <div class="px-4 py-3 bg-green-900/30 border border-green-800/50 rounded-xl text-sm text-green-300">{{ session('success') }}</div>
  @endif
  @if($errors->any())
  <div class="px-4 py-3 bg-red-900/30 border border-red-800/50 rounded-xl text-sm text-red-300">{{ $errors->first() }}</div>
  @endif

  {{-- Header --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 flex flex-col sm:flex-row items-start gap-4 justify-between">
    <div>
      <div class="flex items-center gap-3 flex-wrap">
        <h2 class="text-xl font-bold text-white">{{ $salon->name }}</h2>
        @if($salon->is_active)
          <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-green-900/50 text-green-400 border border-green-800/50">Active</span>
        @else
          <span class="px-2.5 py-1 rounded-xl text-xs font-bold bg-red-900/50 text-red-400 border border-red-800/50">Suspended</span>
        @endif
        <span class="px-2.5 py-1 rounded-xl text-xs font-semibold bg-gray-800 text-gray-400">
          {{ ucfirst($owner?->plan ?? 'free') }} plan
        </span>
      </div>
      <p class="text-sm text-gray-500 mt-1 font-mono">{{ $salon->slug }}.velour.app</p>
      @if($salon->domain)<p class="text-xs text-velour-400 mt-0.5">{{ $salon->domain }}</p>@endif
    </div>
    <div class="flex flex-wrap gap-2">
      @if($salon->is_active)
        <button @click="suspendModal=true"
                class="px-4 py-2 text-sm font-medium rounded-xl border border-red-700 text-red-400 hover:bg-red-900/20 transition-colors">
          Suspend
        </button>
      @else
        <button @click="unsuspendModal=true"
                class="px-4 py-2 text-sm font-medium rounded-xl border border-green-700 text-green-400 hover:bg-green-900/20 transition-colors">
          Reinstate
        </button>
      @endif
      <button @click="overrideModal=true"
              class="px-4 py-2 text-sm font-medium rounded-xl border border-velour-700 text-velour-400 hover:bg-velour-900/20 transition-colors">
        Plan Override
      </button>
      @if($owner)
      <form method="POST" action="{{ route('admin.users.impersonate', $owner->id) }}">
        @csrf
        <button type="submit" class="px-4 py-2 text-sm font-medium rounded-xl border border-gray-700 text-gray-300 hover:bg-gray-800 transition-colors">
          Impersonate owner
        </button>
      </form>
      @endif
    </div>
  </div>

  {{-- Key metrics --}}
  <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
    @foreach([
      ['Staff',      $salon->staff_count,        'text-white'],
      ['Clients',    number_format($salon->clients_count), 'text-white'],
      ['Services',   $salon->services_count,     'text-white'],
      ['Appts',      number_format($salon->appointments_count), 'text-white'],
      ['Appts (mo)', number_format($appointmentsThisMonth), 'text-blue-400'],
      ['Revenue (mo)', '£'.number_format($revenueThisMonth,2), 'text-green-400'],
    ] as [$label, $value, $color])
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4 text-center">
      <p class="text-xl font-bold {{ $color }}">{{ $value }}</p>
      <p class="text-xs text-gray-500 mt-0.5 uppercase tracking-wider">{{ $label }}</p>
    </div>
    @endforeach
  </div>

  <div class="grid lg:grid-cols-3 gap-5">

    {{-- Salon details --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Salon Details</h3>
      <dl class="space-y-2.5">
        @foreach([
          'Email'    => $salon->email ?? '—',
          'Phone'    => $salon->phone ?? '—',
          'City'     => $salon->city  ?? '—',
          'Country'  => $salon->country ?? '—',
          'Timezone' => $salon->timezone,
          'Currency' => strtoupper($salon->currency ?? 'GBP'),
          'Created'  => $salon->created_at->format('d M Y'),
          'All-time revenue' => '£'.number_format($revenueAllTime, 2),
        ] as $label => $value)
        <div class="flex justify-between gap-3 text-sm border-b border-gray-800/50 pb-2 last:border-0">
          <dt class="text-gray-500 flex-shrink-0">{{ $label }}</dt>
          <dd class="text-gray-200 text-right">{{ $value }}</dd>
        </div>
        @endforeach
      </dl>
    </div>

    {{-- Owner --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Owner</h3>
      @if($owner)
      <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-velour-800 flex items-center justify-center text-velour-200 font-bold flex-shrink-0">
          {{ strtoupper(substr($owner->name,0,1)) }}
        </div>
        <div>
          <a href="{{ route('admin.users.show', $owner->id) }}" class="font-semibold text-gray-200 hover:text-white text-sm">
            {{ $owner->name }}
          </a>
          <p class="text-xs text-gray-500">{{ $owner->email }}</p>
        </div>
      </div>
      <dl class="space-y-2">
        @foreach([
          'Plan'    => ucfirst($owner->plan ?? 'free'),
          '2FA'     => $owner->hasTwoFactorEnabled() ? '✓ Enabled' : '✗ Disabled',
          'Verified'=> $owner->hasVerifiedEmail() ? '✓ Yes' : '✗ No',
          'Last login' => $owner->last_login_at?->diffForHumans() ?? 'Never',
          'Joined'  => $owner->created_at->format('d M Y'),
        ] as $label => $value)
        <div class="flex justify-between text-sm">
          <dt class="text-gray-500">{{ $label }}</dt>
          <dd class="text-gray-300">{{ $value }}</dd>
        </div>
        @endforeach
      </dl>
      @if($subscription)
      <div class="mt-4 pt-3 border-t border-gray-800">
        <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Subscription</p>
        <p class="text-xs text-gray-300">Status: <span class="font-medium">{{ ucfirst($subscription->stripe_status) }}</span></p>
        @if($subscription->ends_at)<p class="text-xs text-gray-500">Ends: {{ $subscription->ends_at->format('d M Y') }}</p>@endif
      </div>
      @endif
      @else
      <p class="text-sm text-gray-500">No owner found.</p>
      @endif
    </div>

    {{-- Monthly revenue chart (last 6 months) --}}
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
      <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Revenue (6 months)</h3>
      @if($monthlyRevenue->isEmpty())
        <p class="text-sm text-gray-600 text-center py-6">No revenue data yet.</p>
      @else
      @php $maxRev = $monthlyRevenue->max() ?: 1; @endphp
      <div class="space-y-2">
        @foreach($monthlyRevenue as $month => $rev)
        <div class="flex items-center gap-2 text-xs">
          <span class="text-gray-500 w-14 flex-shrink-0">{{ \Carbon\Carbon::parse($month.'-01')->format('M Y') }}</span>
          <div class="flex-1 h-4 bg-gray-800 rounded-full overflow-hidden">
            <div class="h-full bg-green-500/70 rounded-full" style="width: {{ round(($rev / $maxRev) * 100) }}%"></div>
          </div>
          <span class="text-gray-300 w-16 text-right">£{{ number_format($rev, 0) }}</span>
        </div>
        @endforeach
      </div>
      @endif
    </div>

  </div>

  {{-- Suspension history --}}
  @if($suspensions->count())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <h3 class="px-5 py-3.5 text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-800">
      Suspension History
    </h3>
    <div class="divide-y divide-gray-800/50">
      @foreach($suspensions as $sus)
      <div class="px-5 py-3 flex flex-wrap gap-3 justify-between text-sm">
        <div>
          <span class="font-medium text-gray-200">{{ ucwords(str_replace('_',' ',$sus->reason)) }}</span>
          @if($sus->notes)<p class="text-xs text-gray-500 mt-0.5">{{ $sus->notes }}</p>@endif
        </div>
        <div class="text-right text-xs text-gray-500">
          <p>Suspended {{ \Carbon\Carbon::parse($sus->suspended_at)->format('d M Y H:i') }}</p>
          @if($sus->unsuspended_at)
          <p class="text-green-500 mt-0.5">Reinstated {{ \Carbon\Carbon::parse($sus->unsuspended_at)->format('d M Y H:i') }}</p>
          @else
          <span class="px-1.5 py-0.5 rounded bg-red-900/40 text-red-400 font-medium">Active suspension</span>
          @endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Plan overrides --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-800 flex items-center justify-between">
      <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Plan Overrides</h3>
      <button @click="overrideModal=true" class="text-xs text-velour-400 hover:text-velour-300">+ Add override</button>
    </div>
    @if($overrides->isEmpty())
    <p class="px-5 py-6 text-sm text-gray-600">No overrides applied.</p>
    @else
    <div class="divide-y divide-gray-800/50">
      @foreach($overrides as $override)
      <div class="px-5 py-3 flex items-center justify-between gap-4 text-sm">
        <div>
          <span class="font-medium text-gray-200 capitalize">{{ str_replace('_',' ',$override->override_type) }}</span>
          @if($override->override_plan)<span class="ml-2 text-xs text-velour-400">→ {{ ucfirst($override->override_plan) }}</span>@endif
          <p class="text-xs text-gray-500 mt-0.5">By {{ $override->appliedBy?->name }} · {{ $override->created_at->diffForHumans() }}</p>
          @if($override->reason)<p class="text-xs text-gray-600 mt-0.5">{{ $override->reason }}</p>@endif
        </div>
        <div class="text-right flex-shrink-0">
          @if($override->expires_at)
            @if($override->isExpired())
              <span class="text-xs text-red-400">Expired {{ $override->expires_at->diffForHumans() }}</span>
            @else
              <span class="text-xs text-amber-400">Expires {{ $override->expires_at->diffForHumans() }}</span>
            @endif
          @else
            <span class="text-xs text-gray-500">Permanent</span>
          @endif
          @if($override->is_active && !$override->isExpired())
          <form method="POST" action="{{ route('admin.tenants.override.revoke', [$salon->id, $override->id]) }}" class="mt-1">
            @csrf @method('DELETE')
            <button type="submit" class="text-xs text-red-400 hover:text-red-300">Revoke</button>
          </form>
          @endif
        </div>
      </div>
      @endforeach
    </div>
    @endif
  </div>

  {{-- Recent support tickets --}}
  @if($tickets->count())
  <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-800 flex items-center justify-between">
      <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Recent Support Tickets</h3>
      <a href="{{ route('admin.support.index', ['salon_id' => $salon->id]) }}" class="text-xs text-velour-400 hover:text-velour-300">View all →</a>
    </div>
    <div class="divide-y divide-gray-800/50">
      @foreach($tickets as $ticket)
      <div class="px-5 py-3 flex items-center justify-between gap-4 text-sm">
        <div>
          <a href="{{ route('admin.support.show', $ticket) }}" class="font-medium text-gray-200 hover:text-white">
            {{ $ticket->subject }}
          </a>
          <p class="text-xs text-gray-500 mt-0.5">{{ $ticket->ticket_number }} · {{ $ticket->created_at->diffForHumans() }}</p>
        </div>
        <span class="px-2 py-0.5 rounded-lg text-xs font-semibold {{ $ticket->statusColor() }}">
          {{ ucfirst(str_replace('_',' ',$ticket->status)) }}
        </span>
      </div>
      @endforeach
    </div>
  </div>
  @endif

  {{-- Domain edit --}}
  <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
    <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-4">Domain Settings</h3>
    <form method="POST" action="{{ route('admin.tenants.domain', $salon->id) }}" class="flex flex-wrap gap-3">
      @csrf @method('PATCH')
      <div class="flex items-center gap-2 flex-1 min-w-[200px]">
        <input type="text" name="slug" value="{{ $salon->slug }}" placeholder="subdomain"
               class="flex-1 px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
        <span class="text-sm text-gray-500">.velour.app</span>
      </div>
      <input type="text" name="domain" value="{{ $salon->domain }}" placeholder="custom-domain.com (optional)"
             class="flex-1 min-w-[200px] px-4 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500 placeholder-gray-600">
      <button type="submit" class="px-4 py-2 text-sm font-semibold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
        Save domains
      </button>
    </form>
  </div>

  <a href="{{ route('admin.tenants') }}" class="inline-block text-sm text-gray-500 hover:text-gray-300">← All tenants</a>

  {{-- ── Modals ──────────────────────────────────────────────────────────── --}}

  {{-- Suspend modal --}}
  <div x-show="suspendModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg" @click.outside="suspendModal=false">
      <h2 class="text-lg font-bold text-white mb-4">Suspend {{ $salon->name }}</h2>
      <form method="POST" action="{{ route('admin.tenants.suspend', $salon->id) }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Reason *</label>
          <select name="reason" required class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500">
            <option value="policy_violation">Policy violation</option>
            <option value="payment_failure">Payment failure</option>
            <option value="fraud">Fraud</option>
            <option value="abuse">Abuse</option>
            <option value="requested">Requested by owner</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Internal notes (not sent to owner)</label>
          <textarea name="notes" rows="2" placeholder="Context for internal use only…"
                    class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 placeholder-gray-600 resize-none"></textarea>
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Message to owner (included in email)</label>
          <textarea name="customer_message" rows="3" placeholder="We have suspended your account because…"
                    class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 placeholder-gray-600 resize-none"></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
          <input type="checkbox" name="notify_owner" value="1" checked class="rounded"> Notify owner by email
        </label>
        <div class="flex gap-3 pt-2">
          <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold rounded-xl bg-red-700 hover:bg-red-600 text-white transition-colors">
            Confirm Suspension
          </button>
          <button type="button" @click="suspendModal=false" class="px-4 py-2.5 text-sm rounded-xl border border-gray-700 text-gray-400 hover:bg-gray-800 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Unsuspend modal --}}
  <div x-show="unsuspendModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg" @click.outside="unsuspendModal=false">
      <h2 class="text-lg font-bold text-white mb-4">Reinstate {{ $salon->name }}</h2>
      <form method="POST" action="{{ route('admin.tenants.unsuspend', $salon->id) }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Reinstatement reason (internal)</label>
          <input type="text" name="unsuspend_reason" placeholder="e.g. Issue resolved, payment received…"
                 class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 placeholder-gray-600">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Message to owner</label>
          <textarea name="customer_message" rows="2" placeholder="Great news — your account has been reinstated…"
                    class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 placeholder-gray-600 resize-none"></textarea>
        </div>
        <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
          <input type="checkbox" name="notify_owner" value="1" checked class="rounded"> Notify owner by email
        </label>
        <div class="flex gap-3 pt-2">
          <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold rounded-xl bg-green-700 hover:bg-green-600 text-white transition-colors">
            Reinstate Account
          </button>
          <button type="button" @click="unsuspendModal=false" class="px-4 py-2.5 text-sm rounded-xl border border-gray-700 text-gray-400 hover:bg-gray-800 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- Plan override modal --}}
  <div x-show="overrideModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 w-full max-w-lg overflow-y-auto max-h-[90vh]" @click.outside="overrideModal=false" x-data="{ type: 'plan' }">
      <h2 class="text-lg font-bold text-white mb-4">Apply Plan Override</h2>
      <form method="POST" action="{{ route('admin.tenants.override', $salon->id) }}" class="space-y-4">
        @csrf
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Override type *</label>
          <select name="override_type" x-model="type" class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
            <option value="plan">Plan upgrade/downgrade</option>
            <option value="trial_extension">Trial extension</option>
            <option value="custom_limit">Custom resource limits</option>
            <option value="discount">Discount</option>
            <option value="feature_flag">Feature flag</option>
          </select>
        </div>
        <div x-show="type==='plan'">
          <label class="block text-xs text-gray-400 mb-1.5">Override plan</label>
          <select name="override_plan" class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
            <option value="free">Free</option><option value="starter">Starter</option>
            <option value="pro">Pro</option><option value="enterprise">Enterprise</option>
          </select>
        </div>
        <div x-show="type==='trial_extension'">
          <label class="block text-xs text-gray-400 mb-1.5">Extra trial days</label>
          <input type="number" name="trial_extension_days" min="1" max="365" placeholder="30"
                 class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div x-show="type==='discount'">
          <label class="block text-xs text-gray-400 mb-1.5">Discount %</label>
          <input type="number" name="discount_percentage" min="1" max="100" placeholder="20"
                 class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div x-show="type==='custom_limit'" class="grid grid-cols-3 gap-2">
          @foreach(['override_staff_limit'=>'Staff limit','override_client_limit'=>'Client limit','override_services_limit'=>'Services limit'] as $field=>$label)
          <div>
            <label class="block text-xs text-gray-400 mb-1">{{ $label }}</label>
            <input type="number" name="{{ $field }}" min="-1" placeholder="-1 = unlimited"
                   class="w-full px-3 py-2 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
          </div>
          @endforeach
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Reason * (internal)</label>
          <input type="text" name="reason" required placeholder="e.g. Partner deal, beta access, error compensation…"
                 class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500 placeholder-gray-600">
        </div>
        <div>
          <label class="block text-xs text-gray-400 mb-1.5">Expires at (leave blank = permanent)</label>
          <input type="date" name="expires_at"
                 class="w-full px-4 py-2.5 text-sm bg-gray-800 border border-gray-700 text-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-velour-500">
        </div>
        <div class="flex gap-3 pt-2">
          <button type="submit" class="flex-1 px-4 py-2.5 text-sm font-bold rounded-xl bg-velour-600 hover:bg-velour-700 text-white transition-colors">
            Apply Override
          </button>
          <button type="button" @click="overrideModal=false" class="px-4 py-2.5 text-sm rounded-xl border border-gray-700 text-gray-400 hover:bg-gray-800 transition-colors">
            Cancel
          </button>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection
