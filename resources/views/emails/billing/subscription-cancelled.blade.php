@extends('emails.auth._layout', ['subject' => 'Your Velour subscription has ended'])
@section('body')
<p class="greeting">Your subscription has ended</p>
<p class="text">Hi {{ $user->name }}, your Velour subscription has been cancelled and your account has been moved to the Free plan.</p>
<p class="text">You can still access your salon data, but some features are now restricted. To restore full access, resubscribe at any time.</p>
<div style="text-align:center">
  <a href="{{ route('billing.plans') }}" class="btn">View plans →</a>
</div>
<hr class="divider">
<p class="note">We'd love to hear why you left. Reply to this email and let us know.</p>
@endsection
