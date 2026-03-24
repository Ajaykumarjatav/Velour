@extends('layouts.app')
@section('title', 'Plans & Pricing')
@section('page-title', 'Plans & Pricing')
@section('content')

<div x-data="{ interval: '{{ $interval }}' }" class="space-y-8">

  @foreach(['success','warning','info'] as $f)
    @if(session($f))
    <div class="{{ $f==='success' ? 'alert-success' : ($f==='warning' ? 'alert-warning' : 'alert-info') }}">{{ session($f) }}</div>
    @endif
  @endforeach

  @if($user->onTrial())
  <div class="alert-info">
    <span>You're on a <strong>free trial</strong> — ends <strong>{{ $user->subscription('default')?->trial_ends_at?->format('d M Y') }}</strong>. Add a payment method to continue after the trial.</span>
  </div>
  @elseif($user->onGracePeriod())
  <div class="alert-warning">
    <span>Your subscription was cancelled and ends on <strong>{{ $sub?->ends_at?->format('d M Y') }}</strong>.
      <a href="{{ route('billing.resume') }}" class="underline font-semibold" onclick="event.preventDefault(); document.getElementById('resume-form').submit();">Resume it</a>
    </span>
    <form id="resume-form" method="POST" action="{{ route('billing.resume') }}" class="hidden">@csrf</form>
  </div>
  @elseif($user->isPastDue())
  <div class="alert-danger">
    <span>Your last payment failed. <a href="{{ route('billing.portal') }}" class="underline font-semibold">Update your payment method</a></span>
  </div>
  @endif

  <div class="flex justify-center">
    <div class="inline-flex bg-gray-100 dark:bg-gray-800 rounded-2xl p-1 gap-1">
      <button @click="interval='monthly'"
              :class="interval==='monthly' ? 'bg-white dark:bg-gray-700 shadow-sm text-heading' : 'text-muted hover:text-body'"
              class="px-5 py-2 text-sm font-semibold rounded-xl transition-all">Monthly</button>
      <button @click="interval='yearly'"
              :class="interval==='yearly' ? 'bg-white dark:bg-gray-700 shadow-sm text-heading' : 'text-muted hover:text-body'"
              class="px-5 py-2 text-sm font-semibold rounded-xl transition-all flex items-center gap-2">
        Yearly
        <span class="px-2 py-0.5 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 text-xs font-bold rounded-lg">Save 20%</span>
      </button>
    </div>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
    @foreach($plans as $plan)
    @php
      $isCurrent   = $current->key === $plan->key;
      $isUpgrade   = !$isCurrent && $plan->isUpgradeFrom($current->key);
      $isDowngrade = !$isCurrent && !$plan->isFree() && !$isUpgrade;
      $borderCls   = $plan->popular ? 'border-velour-400 dark:border-velour-600 ring-2 ring-velour-200 dark:ring-velour-900' : 'border-gray-200 dark:border-gray-800';
      $headerBg    = match($plan->color) { 'velour'=>'bg-velour-600','blue'=>'bg-blue-600','amber'=>'bg-amber-500',default=>'bg-gray-600' };
    @endphp
    <div class="card {{ $borderCls }} overflow-hidden flex flex-col relative">
      @if($plan->popular)
      <div class="absolute top-4 right-4 px-2.5 py-1 bg-velour-600 text-white text-xs font-bold rounded-xl uppercase tracking-wide">Popular</div>
      @endif

      <div class="{{ $headerBg }} px-6 pt-7 pb-6 text-white">
        <p class="text-base font-bold">{{ $plan->name }}</p>
        <p class="text-xs opacity-75 mt-0.5">{{ $plan->tagline }}</p>
        <div class="mt-5">
          <div x-show="interval==='monthly'">
            @if($plan->priceMonthly === 0)
            <p class="text-4xl font-black">Free</p>
            @else
            <p class="text-4xl font-black">{{ config('billing.currency_symbol','£') }}{{ $plan->priceMonthly }}<span class="text-base font-normal opacity-75">/mo</span></p>
            @endif
          </div>
          <div x-show="interval==='yearly'" x-cloak>
            @if($plan->priceYearly === 0)
            <p class="text-4xl font-black">Free</p>
            @else
            <p class="text-4xl font-black">{{ config('billing.currency_symbol','£') }}{{ $plan->priceYearly }}<span class="text-base font-normal opacity-75">/yr</span></p>
            <p class="text-xs opacity-75 mt-1">{{ config('billing.currency_symbol','£') }}{{ number_format($plan->monthlyEquivalentYearly(), 0) }}/mo</p>
            @endif
          </div>
        </div>
      </div>

      <div class="px-6 py-5 flex-1">
        @php
          $featureLabels = ['online_booking'=>'Online booking','marketing'=>'Email & SMS marketing','reports'=>'Advanced reports','api_access'=>'API access','custom_domain'=>'Custom domain','priority_support'=>'Priority support','white_label'=>'White-label','multi_location'=>'Multi-location','remove_branding'=>'Remove Velour branding'];
          $limits = ['staff'=>$plan->isUnlimited('staff') ? 'Unlimited staff' : $plan->limit('staff').' staff','clients'=>$plan->isUnlimited('clients') ? 'Unlimited clients' : number_format($plan->limit('clients')).' clients','services'=>$plan->isUnlimited('services') ? 'Unlimited services' : $plan->limit('services').' services'];
        @endphp
        <ul class="space-y-2.5 mb-4">
          @foreach($limits as $limitLabel)
          <li class="flex items-center gap-2.5 text-sm text-body">
            <span class="w-4 h-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center flex-shrink-0 text-muted text-xs">•</span>
            {{ $limitLabel }}
          </li>
          @endforeach
          @foreach($featureLabels as $key => $label)
          @php $has = $plan->allows($key); @endphp
          <li class="flex items-center gap-2.5 text-sm {{ $has ? 'text-body' : 'text-gray-300 dark:text-gray-600' }}">
            <span class="w-4 h-4 flex-shrink-0 {{ $has ? 'text-green-500' : 'text-gray-200 dark:text-gray-700' }} text-xs flex items-center justify-center">{{ $has ? '✓' : '—' }}</span>
            {{ $label }}
          </li>
          @endforeach
        </ul>
      </div>

      <div class="px-6 pb-6">
        @if($isCurrent)
          <div class="w-full py-2.5 text-center text-sm font-semibold rounded-2xl bg-gray-100 dark:bg-gray-800 text-muted cursor-default">Current plan</div>
        @elseif($plan->isFree())
          <div class="w-full py-2.5 text-center text-sm font-medium rounded-2xl border border-gray-200 dark:border-gray-700 text-muted cursor-not-allowed">Default on cancellation</div>
        @else
          <div x-show="interval==='monthly'">
            <form method="POST" action="{{ route('billing.checkout') }}">@csrf
              <input type="hidden" name="plan" value="{{ $plan->key }}">
              <input type="hidden" name="interval" value="monthly">
              <button type="submit" class="w-full py-2.5 text-sm font-semibold rounded-2xl transition-colors {{ $plan->popular ? 'bg-velour-600 hover:bg-velour-700 text-white' : 'border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-body' }}">
                {{ $isUpgrade ? 'Upgrade' : ($isDowngrade ? 'Downgrade' : 'Get started') }}
                @if($plan->trialDays && !$user->onPaidPlan()) — {{ $plan->trialDays }}-day trial @endif
              </button>
            </form>
          </div>
          <div x-show="interval==='yearly'" x-cloak>
            <form method="POST" action="{{ route('billing.checkout') }}">@csrf
              <input type="hidden" name="plan" value="{{ $plan->key }}">
              <input type="hidden" name="interval" value="yearly">
              <button type="submit" class="w-full py-2.5 text-sm font-semibold rounded-2xl transition-colors {{ $plan->popular ? 'bg-velour-600 hover:bg-velour-700 text-white' : 'border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800 text-body' }}">
                {{ $isUpgrade ? 'Upgrade' : ($isDowngrade ? 'Downgrade' : 'Get started') }} (yearly)
              </button>
            </form>
          </div>
        @endif
      </div>
    </div>
    @endforeach
  </div>

  @if($user->stripe_id)
  <p class="text-center text-sm text-muted">
    Manage your subscription and invoices in the
    <a href="{{ route('billing.dashboard') }}" class="text-link font-medium">billing dashboard</a>
  </p>
  @endif

</div>

@endsection
