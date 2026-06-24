@extends('layouts.app')
@section('title', 'Charge Client')
@section('page-title', 'Charge Client')

@section('content')
<div class="max-w-lg mx-auto">
    <div class="rounded-2xl shadow-sm border p-6 bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
        <h2 class="text-base font-semibold mb-5 text-gray-900 dark:text-white">Collect payment via Cashfree</h2>

        <form action="{{ route('payments.charge.process') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">
                    Amount ({{ strtoupper($salon->currency ?? 'INR') }})
                </label>
                <input type="number" name="amount" step="0.01" min="1"
                       value="{{ old('amount', $amount) }}" required
                       class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Description</label>
                <input type="text" name="description" value="{{ old('description', $description) }}"
                       placeholder="e.g. Haircut & colour"
                       class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Client name</label>
                <input type="text" name="client_name" value="{{ old('client_name') }}"
                       class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Client email</label>
                <input type="email" name="client_email" value="{{ old('client_email', $client_email) }}"
                       class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Client phone</label>
                <input type="text" name="client_phone" value="{{ old('client_phone') }}" placeholder="10-digit mobile"
                       class="block w-full rounded-lg border px-3 py-2 text-sm bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700 text-gray-900 dark:text-gray-100" />
            </div>

            <input type="hidden" name="currency" value="{{ strtoupper($salon->currency ?? 'INR') }}" />

            <div class="pt-2 flex items-center gap-3">
                <button type="submit" class="flex-1 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-lg">
                    Continue to Cashfree
                </button>
                <a href="{{ route('payments.gateway') }}" class="px-4 py-2.5 text-sm rounded-lg border text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
