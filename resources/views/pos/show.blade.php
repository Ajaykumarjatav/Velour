@extends('layouts.app')
@section('title', 'Transaction '.$transaction->reference)
@section('page-title', 'Transaction Receipt')
@section('content')

<div class="max-w-lg">
    <div class="card overflow-hidden">
        <div class="bg-velour-600 text-white px-6 py-5 text-center">
            <p class="text-xs font-semibold uppercase tracking-widest opacity-75 mb-1">Receipt</p>
            <p class="text-2xl font-bold">@money($transaction->total)</p>
            <p class="text-sm opacity-75 mt-1">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
        </div>

        <div class="p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="stat-label mb-0.5">Reference</p>
                    <p class="font-mono font-semibold text-heading">{{ $transaction->reference }}</p>
                </div>
                <div>
                    <p class="stat-label mb-0.5">Client</p>
                    <p class="font-semibold text-heading">
                        {{ $transaction->client ? $transaction->client->first_name.' '.$transaction->client->last_name : 'Walk-in' }}
                    </p>
                </div>
                <div>
                    <p class="stat-label mb-0.5">Payment</p>
                    <p class="font-semibold text-heading capitalize">{{ str_replace('_',' ',$transaction->payment_method) }}</p>
                </div>
                <div>
                    <p class="stat-label mb-0.5">Status</p>
                    @php $colors = ['completed'=>'badge-green','refunded'=>'badge-yellow','voided'=>'badge-red']; @endphp
                    <span class="{{ $colors[$transaction->status] ?? 'badge-gray' }}">{{ ucfirst($transaction->status) }}</span>
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800 pt-4">
                <h3 class="stat-label mb-3">Items</h3>
                <div class="space-y-2">
                    @foreach($transaction->items as $item)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-body">
                            {{ ucfirst($item->item_type) }}
                            @if($item->item_type === 'service' && $item->service) — {{ $item->service->name }}
                            @elseif($item->item_type === 'product' && $item->product) — {{ $item->product->name }}
                            @endif
                            @if($item->quantity > 1)<span class="text-muted"> × {{ $item->quantity }}</span>@endif
                        </span>
                        <span class="font-semibold text-heading">@money($item->subtotal)</span>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-100 dark:border-gray-800 pt-4 space-y-1.5 text-sm">
                <div class="flex justify-between text-muted">
                    <span>Subtotal</span>
                    <span>@money($transaction->subtotal)</span>
                </div>
                @if($transaction->discount_amount > 0)
                <div class="flex justify-between text-green-600 dark:text-green-400">
                    <span>Discount</span>
                    <span>−@money($transaction->discount_amount)</span>
                </div>
                @endif
                <div class="flex justify-between font-bold text-heading text-base pt-1 border-t border-gray-100 dark:border-gray-800">
                    <span>Total</span>
                    <span>@money($transaction->total)</span>
                </div>
            </div>

            @if($transaction->notes)
            <p class="text-xs text-muted italic border-t border-gray-100 dark:border-gray-800 pt-4">{{ $transaction->notes }}</p>
            @endif
        </div>
    </div>

    <div class="mt-4 flex gap-3">
        <button onclick="window.print()" class="btn-outline">Print</button>
        <a href="{{ route('pos.index') }}" class="btn text-muted hover:text-body">Back to sales</a>
    </div>
</div>

@endsection
