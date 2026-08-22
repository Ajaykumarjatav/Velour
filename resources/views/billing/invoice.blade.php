@extends('layouts.app')
@section('title', 'Invoice #'.$tx->id)
@section('page-title', 'Invoice')
@section('content')

<div class="max-w-2xl space-y-4 print:max-w-none">
  <div class="flex items-center justify-between print:hidden">
    <a href="{{ route('billing.dashboard') }}#invoices" class="text-sm text-link font-medium">← Back to invoices</a>
    <button type="button" onclick="window.print()" class="btn-primary text-sm">Print / save PDF</button>
  </div>

  <div class="card p-8 space-y-6">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs font-semibold uppercase tracking-widest text-muted">Invoice</p>
        <h1 class="text-2xl font-black text-heading mt-1">#{{ $tx->id }}</h1>
      </div>
      <div class="text-right text-sm">
        @if($tx->status === 'success')
        <span class="px-3 py-1 rounded-xl bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 font-bold">Paid</span>
        @elseif($tx->status === 'failed')
        <span class="px-3 py-1 rounded-xl bg-red-100 text-red-700 font-bold">Failed</span>
        @else
        <span class="px-3 py-1 rounded-xl bg-amber-100 text-amber-700 font-bold">Pending</span>
        @endif
      </div>
    </div>

    <div class="grid sm:grid-cols-2 gap-4 text-sm">
      <div>
        <p class="text-muted text-xs uppercase font-semibold mb-1">Billed to</p>
        <p class="font-semibold text-heading">{{ $user->name }}</p>
        <p class="text-body">{{ $user->email }}</p>
      </div>
      <div>
        <p class="text-muted text-xs uppercase font-semibold mb-1">Date</p>
        <p class="text-heading">{{ ($tx->paid_at ?? $tx->created_at)->format('d M Y, H:i') }}</p>
        @if($tx->cashfree_subscription_id)
        <p class="text-muted text-xs mt-2">Ref: {{ $tx->cashfree_subscription_id }}</p>
        @endif
      </div>
    </div>

    <table class="w-full text-sm">
      <thead>
        <tr class="text-left text-muted border-b border-gray-100 dark:border-gray-800">
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

    <div class="flex justify-end">
      <p class="text-lg font-black text-heading">Total {{ $sym }}{{ number_format($tx->amount) }} {{ strtoupper($tx->currency ?? 'INR') }}</p>
    </div>

    @if($tx->status === 'failed' && $tx->failure_reason)
    <p class="text-sm text-red-600">{{ $tx->failure_reason }}</p>
    @endif
  </div>
</div>

@endsection
