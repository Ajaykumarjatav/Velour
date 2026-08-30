@extends('layouts.app')
@section('title', 'Payment Gateway')
@section('page-title', 'Payment Gateway')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    @if($gateway->exists && $gateway->isConfigured())
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm
                bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800
                text-green-800 dark:text-green-300">
        <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        Cashfree is connected. You can <a href="{{ route('payments.charge') }}" class="underline font-medium">take payments</a>.
    </div>
    @else
    <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm
                bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800
                text-amber-800 dark:text-amber-300">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 3a9 9 0 100 18A9 9 0 0012 3z"/>
        </svg>
        Add your Cashfree API keys below to start accepting payments.
    </div>
    @endif

    <div class="rounded-2xl shadow-sm border p-6 bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
        <h2 class="text-base font-semibold mb-1 text-gray-900 dark:text-white">Cashfree API keys</h2>
        <p class="text-sm mb-5 text-gray-500 dark:text-gray-400">
            Find these in
            <a href="https://merchant.cashfree.com/merchants/login" target="_blank" rel="noopener"
               class="text-velour-600 dark:text-velour-400 hover:underline">
                Cashfree Merchant Dashboard → Payment Gateway → Developers → API Keys
            </a>.
        </p>

        <form action="{{ route('payments.gateway.update') }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">App ID (Client ID)</label>
                <input type="text" name="publishable_key"
                       value="{{ old('publishable_key', $gateway->publishable_key) }}"
                       placeholder="CF…"
                       class="block w-full rounded-lg border px-3 py-2 text-sm font-mono
                              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-velour-500" />
                @error('publishable_key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Secret key</label>
                <input type="password" name="secret_key"
                       value="{{ $gateway->exists && $gateway->secret_key ? '••••••••' : '' }}"
                       placeholder="{{ $gateway->exists && $gateway->secret_key ? 'Leave blank to keep current' : 'Secret key' }}"
                       autocomplete="new-password"
                       class="block w-full rounded-lg border px-3 py-2 text-sm font-mono
                              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-velour-500" />
            </div>

            <div>
                <label class="block text-sm font-medium mb-1 text-gray-700 dark:text-gray-300">Webhook secret (optional)</label>
                <input type="password" name="webhook_secret"
                       value="{{ $gateway->exists && $gateway->webhook_secret ? '••••••••' : '' }}"
                       placeholder="From Cashfree webhook settings"
                       autocomplete="new-password"
                       class="block w-full rounded-lg border px-3 py-2 text-sm font-mono
                              bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-700
                              text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-velour-500" />
            </div>

            <div class="pt-2">
                <button type="submit" class="px-5 py-2 bg-velour-600 hover:bg-velour-700 text-white text-sm font-medium rounded-lg">
                    Save settings
                </button>
            </div>
        </form>
    </div>

    @if($gateway->exists && $gateway->isConfigured())
    <div class="rounded-2xl shadow-sm border p-6 flex items-center justify-between bg-white dark:bg-gray-900 border-gray-100 dark:border-gray-800">
        <div>
            <p class="text-sm font-medium text-gray-900 dark:text-white">Take a payment</p>
            <p class="text-xs mt-0.5 text-gray-500 dark:text-gray-400">Collect payment via Cashfree hosted checkout.</p>
        </div>
        <a href="{{ route('payments.charge') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg">Charge client</a>
    </div>
    @endif

</div>
@endsection
