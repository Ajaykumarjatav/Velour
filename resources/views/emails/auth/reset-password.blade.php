@extends('emails.auth._layout', ['subject' => 'Reset your Velour password'])

@section('body')
<p class="greeting">Password reset request</p>
<p class="text">Hi {{ $user->name }}, we received a request to reset your Velour password. Click the button below to choose a new one.</p>

<div style="text-align:center">
  <a href="{{ $url }}" class="btn">🔒 Reset my password</a>
</div>

<p class="note">This link expires in {{ $expiry }}. If you didn't request a password reset, no action is required — your account is safe.</p>

<hr class="divider">

<p class="note">If the button doesn't work, copy and paste this URL into your browser:<br>
<a href="{{ $url }}" style="color:#7c3aed;font-size:12px;word-break:break-all;">{{ $url }}</a></p>
@endsection
