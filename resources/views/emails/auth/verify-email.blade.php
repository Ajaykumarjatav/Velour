@extends('emails.auth._layout', ['subject' => 'Verify your Velour email address'])

@section('body')
<p class="greeting">Verify your email address</p>
<p class="text">Hi {{ $user->name }}, welcome to Velour! Please verify your email address to activate your account and start managing your salon.</p>

<div style="text-align:center">
  <a href="{{ $url }}" class="btn">✓ Verify email address</a>
</div>

<p class="note">This link expires in {{ $expiry }}. If you didn't create a Velour account, you can safely ignore this email.</p>

<hr class="divider">

<p class="note">If the button doesn't work, copy and paste this URL into your browser:<br>
<a href="{{ $url }}" style="color:#7c3aed;font-size:12px;word-break:break-all;">{{ $url }}</a></p>
@endsection
