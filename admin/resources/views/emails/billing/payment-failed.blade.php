@extends('emails.auth._layout', ['subject' => 'Action required: Velour payment failed'])
@section('body')
<p class="greeting" style="color:#dc2626">⚠ Payment failed</p>
<p class="text">Hi {{ $user->name }}, we were unable to collect your Velour subscription payment of <strong>{{ config('billing.currency_symbol', '£') }}{{ number_format($amount, 2) }}</strong>.</p>

@if($nextAttempt)
<p class="text">We'll automatically retry on <strong>{{ $nextAttempt->format('d M Y') }}</strong>. To avoid any interruption, please update your payment method before then.</p>
@else
<p class="text">Please update your payment method to restore full access to your salon.</p>
@endif

<div style="text-align:center">
  <a href="{{ $portalUrl }}" class="btn" style="background:#dc2626">Update payment method →</a>
</div>

<hr class="divider">
<p class="note">If you continue to experience issues, reply to this email and we'll help you resolve it.</p>
@endsection
