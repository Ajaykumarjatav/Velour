@extends('layouts.app')
@section('title', 'Complete payment')
@section('page-title', 'Complete payment')
@section('content')

<div class="max-w-lg mx-auto text-center space-y-6 py-8">
  <div class="card p-8">
    <p class="text-sm text-muted mb-2">Redirecting to Cashfree…</p>
    <h2 class="text-xl font-bold text-heading">{{ $plan->name }}</h2>
    <p class="text-sm text-muted mt-1">{{ ucfirst($interval) }} · {{ config('billing.currency_symbol','₹') }}{{ $interval === 'yearly' ? $plan->priceYearly : $plan->priceMonthly }}</p>
    <p id="payment-message" class="text-sm text-red-600 mt-4 hidden"></p>
    <button type="button" id="open-cashfree" class="btn-primary mt-6 w-full">Open secure checkout</button>
  </div>
</div>

@push('scripts')
<script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const cashfree = Cashfree({ mode: @json($mode) });
  const sessionId = @json($sessionId);
  const messageEl = document.getElementById('payment-message');

  function openCheckout() {
    cashfree.subscriptionsCheckout({
      subsSessionId: sessionId,
      redirectTarget: '_self',
    }).then(function (result) {
      if (result && result.error) {
        messageEl.textContent = result.error.message || 'Checkout could not be opened.';
        messageEl.classList.remove('hidden');
      }
    });
  }

  document.getElementById('open-cashfree').addEventListener('click', openCheckout);
  openCheckout();
});
</script>
@endpush
@endsection
