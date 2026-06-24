@php
  $reminder = $subscriptionReminder ?? null;
@endphp
@if($reminder && !($planExpired ?? false))
@php
  $days = $reminder['days_remaining'] ?? null;
  $kind = $reminder['kind'] ?? 'trial';
  $urgent = ! empty($reminder['urgent']);
  $warning = ! empty($reminder['warning']);
  $endsAt = $reminder['ends_at'] ?? null;

  if ($kind === 'expired' || $urgent) {
      $toneWrap = 'border-red-200/70 dark:border-red-800/40 bg-red-50/90 dark:bg-red-950/30';
      $toneText = 'text-red-800 dark:text-red-300';
      $toneBtn = 'text-red-700 dark:text-red-300 hover:text-red-900 dark:hover:text-red-100';
  } elseif ($warning || $kind === 'grace') {
      $toneWrap = 'border-amber-200/70 dark:border-amber-800/40 bg-amber-50/90 dark:bg-amber-950/30';
      $toneText = 'text-amber-900 dark:text-amber-200';
      $toneBtn = 'text-amber-800 dark:text-amber-300 hover:text-amber-950 dark:hover:text-amber-100';
  } elseif ($kind === 'active') {
      $toneWrap = 'border-emerald-200/70 dark:border-emerald-800/40 bg-emerald-50/80 dark:bg-emerald-950/25';
      $toneText = 'text-emerald-800 dark:text-emerald-300';
      $toneBtn = 'text-emerald-700 dark:text-emerald-300 hover:text-emerald-900';
  } else {
      $toneWrap = 'border-blue-200/70 dark:border-blue-800/40 bg-blue-50/90 dark:bg-blue-950/25';
      $toneText = 'text-blue-900 dark:text-blue-200';
      $toneBtn = 'text-blue-800 dark:text-blue-300 hover:text-blue-950 dark:hover:text-blue-100';
  }

  if ($kind === 'expired') {
      $message = 'Your plan has expired. Renew to keep using the salon panel.';
  } elseif ($days === null) {
      $message = ($reminder['plan_label'] ?? 'Plan').' · Active';
  } elseif ($days === 0) {
      $message = $kind === 'trial'
          ? 'Trial ends today'
          : ($kind === 'grace' ? 'Subscription ends today' : 'Plan access ends today');
  } elseif ($days === 1) {
      $message = $kind === 'trial'
          ? '1 day left on your trial'
          : ($kind === 'grace' ? '1 day left on your subscription' : '1 day left on plan access');
  } else {
      $message = $kind === 'trial'
          ? "{$days} days left on your trial"
          : ($kind === 'grace' ? "{$days} days left on your subscription" : "{$days} days left on plan access");
  }

  $ctaLabel = match ($kind) {
      'expired' => 'Renew now',
      'grace'   => 'Manage billing',
      'active'  => 'Billing',
      default   => 'Upgrade plan',
  };
@endphp
<div class="px-4 sm:px-6 py-2 border-b {{ $toneWrap }}">
  <div class="flex flex-wrap items-center justify-between gap-2 min-h-8">
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs sm:text-sm {{ $toneText }}">
      <span class="font-semibold">{{ $message }}</span>
      @if($endsAt && $days !== null)
      <span class="opacity-80">· Ends {{ $endsAt->format('d M Y') }}</span>
      @endif
      @if(($reminder['plan_label'] ?? null) && $kind !== 'active')
      <span class="hidden sm:inline opacity-70">· {{ $reminder['plan_label'] }}</span>
      @endif
    </div>
    <a href="{{ $reminder['renew_url'] ?? route('billing.plans') }}"
       class="text-xs font-semibold whitespace-nowrap shrink-0 {{ $toneBtn }}">
      {{ $ctaLabel }} →
    </a>
  </div>
</div>
@endif
