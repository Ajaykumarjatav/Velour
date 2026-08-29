@extends('layouts.app')
@section('title', 'Invoice #'.$tx->id)
@section('page-title', 'Platform invoice')
@section('content')

@php
    $platformLogo = \App\Support\MailAssets::logoUrl();
@endphp

<div class="max-w-2xl space-y-4 print:max-w-none">
  <div class="flex items-center justify-between print:hidden">
    <a href="{{ route('billing.dashboard') }}#invoices" class="text-sm text-link font-medium">← Back to invoices</a>
    <button type="button" onclick="window.print()" class="btn-primary text-sm">Print / save PDF</button>
  </div>

  <article class="overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
    <div class="h-1.5 bg-gradient-to-r from-velour-600 via-velour-500 to-velour-400"></div>

    <div class="p-8 space-y-6">
      <div class="flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex gap-4 min-w-0">
          @if($platformLogo)
            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 dark:border-gray-700">
              <img src="{{ $platformLogo }}" alt="{{ config('app.name') }}" class="max-h-full max-w-full object-contain">
            </div>
          @else
            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-velour-600 to-velour-500 text-sm font-bold text-white">
              {{ strtoupper(substr(config('app.name'), 0, 2)) }}
            </div>
          @endif
          <div>
            <p class="text-xs font-semibold uppercase tracking-widest text-muted">Platform invoice</p>
            <h1 class="text-2xl font-black text-heading mt-1">#{{ $tx->id }}</h1>
            <p class="text-sm text-muted mt-1">Subscription billing from {{ config('app.name') }} to your business account.</p>
          </div>
        </div>
        <div class="shrink-0">
          @if($tx->status === 'success')
          <span class="px-3 py-1 rounded-xl bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-bold text-sm">Paid</span>
          @elseif($tx->status === 'failed')
          <span class="px-3 py-1 rounded-xl bg-red-100 text-red-700 font-bold text-sm">Failed</span>
          @else
          <span class="px-3 py-1 rounded-xl bg-amber-100 text-amber-700 font-bold text-sm">Pending</span>
          @endif
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-4 text-sm rounded-xl bg-gray-50/90 p-4 dark:bg-gray-950/50">
        <div>
          <p class="text-muted text-xs uppercase font-semibold mb-1">From</p>
          <p class="font-semibold text-heading">{{ config('app.name') }}</p>
          <p class="text-body">{{ config('mail.purposes.billing.from.address') }}</p>
        </div>
        <div>
          <p class="text-muted text-xs uppercase font-semibold mb-1">Billed to (tenant)</p>
          <p class="font-semibold text-heading">{{ $user->name }}</p>
          <p class="text-body">{{ $user->email }}</p>
        </div>
      </div>

      <div class="grid sm:grid-cols-2 gap-4 text-sm">
        <div>
          <p class="text-muted text-xs uppercase font-semibold mb-1">Date</p>
          <p class="text-heading">{{ ($tx->paid_at ?? $tx->created_at)->timezone(\App\Support\SalonTime::defaultTimezone())->format('d M Y, H:i T') }}</p>
          @if($tx->cashfree_subscription_id)
          <p class="text-muted text-xs mt-2 font-mono">Ref: {{ $tx->cashfree_subscription_id }}</p>
          @endif
        </div>
        <div class="sm:text-right">
          <p class="text-muted text-xs uppercase font-semibold mb-1">Amount paid</p>
          <p class="text-3xl font-black tabular-nums text-velour-600 dark:text-velour-400">{{ $sym }}{{ number_format($tx->amount) }}</p>
        </div>
      </div>

      <table class="w-full text-sm">
        <thead>
          <tr class="text-left text-muted border-b-2 border-gray-200 dark:border-gray-700">
            <th class="py-2 font-medium">Description</th>
            <th class="py-2 font-medium text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr class="border-b border-gray-50 dark:border-gray-800">
            <td class="py-3 text-heading">
              {{ $plan?->name ?? ucfirst($tx->plan_key) }} plan
              <span class="text-muted">({{ ucfirst($tx->interval) }})</span>
            </td>
            <td class="py-3 text-right tabular-nums font-semibold text-heading">{{ $sym }}{{ number_format($tx->amount) }}</td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-end rounded-xl border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-700 dark:bg-gray-950/50">
        <p class="text-lg font-black text-heading">Total {{ $sym }}{{ number_format($tx->amount) }} {{ strtoupper($tx->currency ?? 'INR') }}</p>
      </div>

      @if($tx->status === 'failed' && $tx->failure_reason)
      <p class="text-sm text-red-600">{{ $tx->failure_reason }}</p>
      @endif

      <p class="text-xs text-center text-muted">This is your EasyGrox subscription invoice. Customer sale invoices are issued separately from POS.</p>
    </div>
  </article>
</div>

@endsection
