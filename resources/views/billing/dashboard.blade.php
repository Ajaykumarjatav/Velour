@extends('layouts.app')
@section('title', 'Billing')
@section('page-title', 'Billing & Subscription')
@section('content')

<div class="max-w-3xl space-y-6">

  @foreach(['success','warning','info'] as $f)
    @if(session($f))
    <div class="{{ $f==='success' ? 'alert-success' : ($f==='warning' ? 'alert-warning' : 'alert-info') }}">
      {{ session($f) }}
    </div>
    @endif
  @endforeach

  <div class="card overflow-hidden">
    <div class="px-6 py-5 bg-gradient-to-r from-velour-600 to-velour-500 text-white">
      <p class="text-xs font-semibold uppercase tracking-widest opacity-75 mb-1">Current Plan</p>
      <div class="flex items-end justify-between flex-wrap gap-3">
        <div>
          <p class="text-3xl font-black">{{ $current->name }}</p>
          <p class="text-sm opacity-80 mt-0.5">{{ $current->tagline }}</p>
        </div>
        @if($sub)
        <div class="text-right">
          @if($sub->onTrial())
          <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-bold rounded-xl border border-white/30">Trial ends {{ $sub->trial_ends_at?->format('d M Y') }}</span>
          @elseif($sub->cancelled())
          <span class="px-3 py-1.5 bg-red-500/80 text-white text-sm font-bold rounded-xl">Cancels {{ $sub->ends_at?->format('d M Y') }}</span>
          @elseif($sub->pastDue())
          <span class="px-3 py-1.5 bg-amber-500/80 text-white text-sm font-bold rounded-xl">Payment overdue</span>
          @else
          <span class="px-3 py-1.5 bg-white/20 text-white text-sm font-bold rounded-xl border border-white/30">Active</span>
          @endif
        </div>
        @endif
      </div>
    </div>
    <div class="px-6 py-5 space-y-4">
      @php $resources = ['staff'=>'Staff', 'clients'=>'Clients', 'services'=>'Services']; @endphp
      <div class="grid grid-cols-3 gap-4">
        @foreach($resources as $key => $label)
        @php $lim = $current->limit($key); @endphp
        <div class="text-center bg-gray-50 dark:bg-gray-800/60 rounded-2xl p-4">
          <p class="text-xl font-black text-heading">{{ $lim === -1 ? 'inf' : number_format($lim) }}</p>
          <p class="stat-label mt-0.5">{{ $label }}</p>
        </div>
        @endforeach
      </div>
      @if($user->pm_type)
      <div class="flex items-center justify-between py-3 border-t border-gray-100 dark:border-gray-800">
        <div class="flex items-center gap-3 text-sm text-body">
          <span class="text-xl">{{ $user->pm_type === 'card' ? 'card' : 'bank' }}</span>
          <span>{{ Str::title(str_replace('_', ' ', $user->pm_type)) }} {{ $user->pm_last_four ? '....' . $user->pm_last_four : '' }}</span>
        </div>
        <a href="{{ route('billing.portal') }}" class="text-sm text-link font-medium">Update</a>
      </div>
      @endif
      <div class="flex flex-wrap gap-3 pt-2 border-t border-gray-100 dark:border-gray-800">
        <a href="{{ route('billing.plans') }}" class="btn-primary">{{ $sub && !$sub->cancelled() ? 'Change plan' : 'View plans' }}</a>
        <a href="{{ route('billing.portal') }}" class="btn-outline">Stripe Customer Portal</a>
        @if($sub && $sub->onGracePeriod())
        <form method="POST" action="{{ route('billing.resume') }}">@csrf
          <button type="submit" class="btn border border-green-300 dark:border-green-700 text-green-700 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20">Resume subscription</button>
        </form>
        @elseif($sub && !$sub->cancelled())
        <a href="{{ route('billing.cancel') }}" class="btn border border-red-200 dark:border-red-800 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20">Cancel subscription</a>
        @endif
      </div>
    </div>
  </div>

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-4">Plan Features</h2>
    @php
      $featureLabels = [
        'online_booking'=>['Online booking','book'],'marketing'=>['Email & SMS marketing','mail'],
        'reports'=>['Advanced reports','chart'],'api_access'=>['API access','api'],
        'custom_domain'=>['Custom domain','globe'],'priority_support'=>['Priority support','star'],
        'white_label'=>['White-label branding','tag'],'remove_branding'=>['Remove Velour branding','clean'],
      ];
    @endphp
    <div class="grid sm:grid-cols-2 gap-2">
      @foreach($featureLabels as $key => [$label, $icon])
      @php $has = $current->allows($key); @endphp
      <div class="flex items-center gap-3 px-4 py-2.5 rounded-xl {{ $has ? 'bg-green-50 dark:bg-green-900/20' : 'bg-gray-50 dark:bg-gray-800/60' }}">
        <span class="text-sm {{ $has ? 'text-heading font-medium' : 'text-muted line-through' }}">{{ $label }}</span>
        @if($has)
        <span class="ml-auto text-xs text-green-600 dark:text-green-400 font-bold">ok</span>
        @else
        <a href="{{ route('billing.plans') }}" class="ml-auto text-xs text-link font-medium">Upgrade</a>
        @endif
      </div>
      @endforeach
    </div>
  </div>

  @if(count($invoices))
  <div class="table-wrap">
    <h2 class="px-6 py-4 font-semibold text-heading border-b border-gray-100 dark:border-gray-800">Invoices</h2>
    <table class="data-table">
      <thead><tr><th>Date</th><th class="text-right">Amount</th><th>Status</th><th class="text-right w-[1%] whitespace-nowrap">Actions</th></tr></thead>
      <tbody>
        @foreach($invoices as $invoice)
        <tr>
          <td class="text-body">{{ $invoice->date()->format('d M Y') }}</td>
          <td class="font-semibold text-heading text-right">{{ $invoice->total() }}</td>
          <td><span class="{{ $invoice->paid ? 'badge-green' : 'badge-yellow' }}">{{ $invoice->paid ? 'Paid' : 'Open' }}</span></td>
          <td class="text-right"><a href="{{ route('billing.invoice.download', $invoice->id) }}" class="text-sm text-link font-medium">PDF</a></td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @else
  <div class="card p-8 text-center"><p class="text-muted text-sm">No invoices yet.</p></div>
  @endif

  <div class="card p-6" x-data="{ open: false }">
    <button @click="open=!open" class="text-sm text-muted hover:text-body font-medium flex items-center gap-2">
      <span x-text="open ? 'v' : '>'"></span> Apply a promo code
    </button>
    <div x-show="open" x-cloak class="mt-4">
      <form method="POST" action="{{ route('billing.promo') }}" class="flex gap-3">
        @csrf
        <input type="text" name="promo_code" placeholder="VOUCHER123" class="form-input flex-1 uppercase tracking-wide @error('promo_code') border-red-400 dark:border-red-600 @enderror">
        <button type="submit" class="btn-primary">Apply</button>
      </form>
      @error('promo_code')<p class="form-error mt-1.5">{{ $message }}</p>@enderror
    </div>
  </div>

</div>
@endsection
