@extends('emails.auth._layout', ['subject' => 'Your Velour trial ends in 3 days'])
@section('body')
<p class="greeting">Your trial ends {{ $trialEndsAt->format('d M Y') }}</p>
<p class="text">Hi {{ $user->name }}, just a heads-up — your Velour free trial ends in 3 days. To keep your full access, add a payment method before the trial expires.</p>
<p class="text">No action is needed to cancel; if you choose not to subscribe, your account will automatically switch to the Free plan when the trial ends.</p>
<div style="text-align:center">
  <a href="{{ $billingUrl }}" class="btn">Add payment method →</a>
</div>
<hr class="divider">
<p class="note">The trial ends on <strong>{{ $trialEndsAt->format('d M Y \a\t H:i') }} UTC</strong>.</p>
@endsection
