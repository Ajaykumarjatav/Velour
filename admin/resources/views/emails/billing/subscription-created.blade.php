@extends('emails.auth._layout', ['subject' => 'Welcome to Velour ' . $plan])
@section('body')
<p class="greeting">{{ $onTrial ? "Your {$plan} trial has started! 🎉" : "Welcome to Velour {$plan}!" }}</p>

@if($onTrial)
<p class="text">Hi {{ $user->name }}, your <strong>{{ $trialDays }}-day free trial</strong> of the {{ $plan }} plan is now active. No payment is required during the trial.</p>
<p class="text">Explore everything {{ $plan }} has to offer — and if you decide Velour isn't right for you, simply cancel before the trial ends and you won't be charged a penny.</p>
@else
<p class="text">Hi {{ $user->name }}, your Velour {{ $plan }} subscription is now active. Thank you for choosing Velour to run your salon.</p>
@endif

<div style="text-align:center">
  <a href="{{ route('dashboard') }}" class="btn">Open Velour dashboard →</a>
</div>

<hr class="divider">
<p class="note">Questions? Reply to this email or visit our <a href="{{ config('app.url') }}/support" style="color:#7c3aed">help centre</a>.</p>
@endsection
