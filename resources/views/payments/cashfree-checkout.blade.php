@extends('layouts.app')
@section('title', 'Complete payment')
@section('page-title', 'Collect payment')

@section('content')
<div class="max-w-lg mx-auto text-center space-y-6 py-8">
  <div class="rounded-2xl shadow-sm border p-8 bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Opening Cashfree checkout…</p>
    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $currency }} {{ number_format((float) $amount, 2) }}</p>
    <p id="payment-message" class="text-sm text-red-600 mt-4 hidden"></p>
    <button type="button" id="open-cashfree" class="mt-6 w-full py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">Pay now</button>
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
    cashfree.checkout({
      paymentSessionId: sessionId,
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
