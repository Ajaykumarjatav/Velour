@extends('layouts.app')
@section('title', 'Charge Client')
@section('page-title', 'Charge Client')

@push('styles')
<script src="https://js.stripe.com/v3/"></script>
@endpush

@section('content')
<div class="max-w-lg mx-auto">
    <div class="rounded-2xl shadow-sm border p-6
                bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
        <h2 class="text-base font-semibold mb-5 text-gray-900 dark:text-white">Collect payment</h2>

        <form id="payment-form" action="{{ route('payments.charge.process') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Amount ({{ strtoupper($salon->currency ?? 'GBP') }})
                </label>
                <input type="number" name="amount" id="amount" step="0.01" min="0.50"
                       value="{{ old('amount', $amount) }}" required
                       class="block w-full rounded-lg border px-3 py-2 text-sm
                              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600
                              focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description</label>
                <input type="text" name="description" value="{{ old('description', $description) }}"
                       placeholder="e.g. Haircut & colour"
                       class="block w-full rounded-lg border px-3 py-2 text-sm
                              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600
                              focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Client email <span class="text-gray-400 dark:text-gray-500 font-normal">(for receipt)</span>
                </label>
                <input type="email" name="client_email" value="{{ old('client_email', $client_email) }}"
                       placeholder="client@example.com"
                       class="block w-full rounded-lg border px-3 py-2 text-sm
                              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-600
                              focus:outline-none focus:ring-2 focus:ring-velour-500 focus:border-transparent" />
            </div>

            <input type="hidden" name="currency" value="{{ $salon->currency ?? 'gbp' }}" />

            {{-- Stripe card element --}}
            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Card details</label>
                <div id="card-element"
                     class="block w-full rounded-lg border px-3 py-2.5
                            bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700">
                </div>
                <p id="card-errors" class="mt-1 text-xs text-red-600 dark:text-red-400 hidden"></p>
            </div>

            <input type="hidden" name="payment_method_id" id="payment_method_id" />

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" id="submit-btn"
                        class="flex-1 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg transition-colors disabled:opacity-50">
                    Charge
                </button>
                <a href="{{ route('payments.gateway') }}"
                   class="px-4 py-2.5 text-sm rounded-lg border transition-colors
                          text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700
                          hover:text-gray-900 dark:hover:text-white hover:bg-gray-50 dark:hover:bg-gray-800">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const isDark  = document.documentElement.classList.contains('dark');
    const stripe  = Stripe('{{ $publishable_key }}');
    const elements = stripe.elements();
    const card    = elements.create('card', {
        style: {
            base: {
                fontSize: '14px',
                color: isDark ? '#f3f4f6' : '#111827',
                '::placeholder': { color: isDark ? '#6b7280' : '#9ca3af' },
                iconColor: isDark ? '#9ca3af' : '#6b7280',
            }
        }
    });
    card.mount('#card-element');

    card.on('change', function (e) {
        const el = document.getElementById('card-errors');
        if (e.error) {
            el.textContent = e.error.message;
            el.classList.remove('hidden');
        } else {
            el.classList.add('hidden');
        }
    });

    document.getElementById('payment-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.textContent = 'Processing…';

        const { paymentMethod, error } = await stripe.createPaymentMethod({
            type: 'card',
            card: card,
            billing_details: {
                email: document.querySelector('[name=client_email]').value || undefined,
            },
        });

        if (error) {
            const el = document.getElementById('card-errors');
            el.textContent = error.message;
            el.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = 'Charge';
            return;
        }

        document.getElementById('payment_method_id').value = paymentMethod.id;
        this.submit();
    });
})();
</script>
@endpush
