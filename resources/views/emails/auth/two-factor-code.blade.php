@extends('emails.auth._layout', ['subject' => 'Your Velour login code: ' . $code])

@section('body')
<p class="greeting">Your login verification code</p>
<p class="text">Hi {{ $user->name }}, here is your 6-digit login code. Enter it on the verification screen to complete your sign-in.</p>

<div class="code-box">
  <div class="code-text">{{ $code }}</div>
  <p class="note" style="margin-top:10px">Expires in 10 minutes</p>
</div>

<p class="note">If you didn't try to sign in to Velour, please <a href="{{ route('password.request') }}" style="color:#7c3aed">secure your account immediately</a> and change your password.</p>
@endsection
