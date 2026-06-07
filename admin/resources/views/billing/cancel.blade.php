@extends('layouts.app')
@section('title', 'Cancel Subscription')
@section('page-title', 'Cancel Subscription')
@section('content')

<div class="max-w-xl space-y-5">

  <div class="alert-danger">
    <span class="text-2xl mt-0.5">⚠️</span>
    <div>
      <p class="font-semibold">You're about to cancel your subscription</p>
      <p class="text-sm mt-1">
        Your <strong>{{ $user->currentPlan()->name }}</strong> plan will remain active until
        <strong>{{ $endsAt->format('d F Y') }}</strong>. After that your account switches to the Free plan.
      </p>
    </div>
  </div>

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-4">You'll lose access to:</h2>
    @php
      $loses = [];
      foreach($user->currentPlan()->features as $key => $val) {
        if($val) $loses[] = ['online_booking'=>'Online booking widget','marketing'=>'Email & SMS marketing campaigns','reports'=>'Advanced revenue & staff reports','api_access'=>'API & third-party integrations','custom_domain'=>'Custom domain','priority_support'=>'Priority support','white_label'=>'White-label branding','remove_branding'=>'Velour branding removal'][$key] ?? $key;
      }
    @endphp
    <ul class="space-y-2">
      @foreach($loses as $item)
      <li class="flex items-center gap-2.5 text-sm text-body">
        <span class="text-red-400">✕</span> {{ $item }}
      </li>
      @endforeach
      @if($user->currentPlan()->limit('staff') > 1)
      <li class="flex items-center gap-2.5 text-sm text-body">
        <span class="text-red-400">✕</span> Staff access reduced from {{ $user->currentPlan()->limit('staff') }} to 1
      </li>
      @endif
    </ul>
  </div>

  <div class="bg-velour-50 dark:bg-velour-900/20 border border-velour-200 dark:border-velour-800 rounded-2xl p-5 text-sm text-velour-700 dark:text-velour-300">
    <p class="font-semibold mb-2">Before you go…</p>
    <ul class="space-y-1.5">
      <li>• Need a lower price? <a href="{{ route('billing.plans') }}?interval=yearly" class="underline font-medium">Switch to yearly billing</a> and save 20%.</li>
      <li>• Having trouble? <a href="mailto:support@velour.app" class="underline font-medium">Contact support</a> — we'll help.</li>
      <li>• Heading away? You can pause from the <a href="{{ route('billing.portal') }}" class="underline font-medium">billing portal</a>.</li>
    </ul>
  </div>

  <div class="card p-6">
    <h2 class="font-semibold text-heading mb-4">Confirm cancellation</h2>
    <form method="POST" action="{{ route('billing.cancel') }}" class="space-y-4">
      @csrf @method('DELETE')
      <div>
        <label class="form-label">Reason for cancelling <span class="text-muted font-normal">(optional)</span></label>
        <textarea name="reason" rows="3" placeholder="Help us improve..." class="form-textarea"></textarea>
      </div>
      <div>
        <label class="form-label">Confirm your password <span class="text-red-500">*</span></label>
        <input type="password" name="password" required
               class="form-input w-full sm:w-64 @error('password') border-red-400 dark:border-red-600 @enderror">
        @error('password')<p class="form-error">{{ $message }}</p>@enderror
      </div>
      <div class="flex gap-3 pt-2">
        <button type="submit" class="btn-danger">Yes, cancel my subscription</button>
        <a href="{{ route('billing.dashboard') }}" class="btn-outline">Keep my plan</a>
      </div>
    </form>
  </div>

</div>

@endsection
