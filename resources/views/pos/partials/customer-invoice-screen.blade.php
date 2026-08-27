{{-- Professional customer invoice — web / print. Expects PosInvoiceFormatting::viewContext(). --}}
@php
    /** @var \App\Models\PosTransaction $transaction */
    /** @var \App\Models\Salon|null $salon */
    /** @var \App\Models\Client|null $client */
    $fmt = $fmt ?? fn (float $n) => \App\Support\PosInvoiceFormatting::formatAmount($n, $salon);
    $statusColors = ['completed' => 'badge-green', 'refunded' => 'badge-yellow', 'voided' => 'badge-red'];
@endphp

<article class="invoice-sheet overflow-hidden rounded-2xl border border-gray-200/90 bg-white text-gray-900 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100">
    {{-- Brand accent --}}
    <div class="h-1.5 bg-gradient-to-r from-velour-600 via-velour-500 to-velour-400"></div>

    <header class="border-b border-gray-100 px-6 py-6 dark:border-gray-800 sm:px-8 sm:py-7">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            {{-- Store identity --}}
            <div class="flex min-w-0 gap-4">
                @if($logoUrl ?? null)
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-gray-200 bg-white p-1.5 shadow-sm dark:border-gray-700 dark:bg-gray-950">
                        <img src="{{ $logoUrl }}" alt="{{ $salon?->name ?? 'Logo' }}" class="max-h-full max-w-full object-contain">
                    </div>
                @else
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-velour-600 to-velour-500 text-lg font-bold text-white shadow-sm">
                        {{ $salonInitials ?? 'S' }}
                    </div>
                @endif
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-velour-600 dark:text-velour-400">{{ $docTitle ?? 'Invoice' }}</p>
                    <h1 class="mt-1 text-xl font-bold tracking-tight text-heading sm:text-2xl">{{ $salon?->name ?? config('app.name') }}</h1>
                    @if(!empty($addressLines))
                        <address class="mt-2 space-y-0.5 not-italic text-sm leading-relaxed text-muted">
                            @foreach($addressLines as $line)
                                <p>{{ $line }}</p>
                            @endforeach
                        </address>
                    @endif
                    <dl class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted">
                        @if($salon?->phone)
                            <div><span class="font-medium text-body/70">Tel</span> {{ $salon->phone }}</div>
                        @endif
                        @if($salon?->email)
                            <div><span class="font-medium text-body/70">Email</span> {{ $salon->email }}</div>
                        @endif
                        @if($gstRegistered ?? false)
                            <div><span class="font-medium text-body/70">GSTIN</span> <span class="font-mono text-body">{{ $salon->gst_number }}</span></div>
                        @endif
                    </dl>
                </div>
            </div>

            {{-- Total highlight --}}
            <div class="w-full shrink-0 rounded-xl border border-velour-200/80 bg-gradient-to-br from-velour-50 to-white px-5 py-4 dark:border-velour-900/50 dark:from-velour-950/40 dark:to-gray-900 lg:max-w-[240px]">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-muted">{{ $amountLabel ?? 'Amount due' }}</p>
                <p class="mt-1 text-3xl font-bold tabular-nums text-velour-600 dark:text-velour-400">{{ $fmt((float) $transaction->total) }}</p>
                <dl class="mt-4 space-y-1.5 border-t border-velour-100 pt-3 text-sm dark:border-velour-900/40">
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Invoice no.</dt>
                        <dd class="font-mono text-xs font-semibold text-heading">{{ $transaction->reference }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Date</dt>
                        <dd class="font-medium text-heading">{{ $invoiceDate?->format('D, j M Y') }}</dd>
                    </div>
                    <div class="flex justify-between gap-3">
                        <dt class="text-muted">Time</dt>
                        <dd class="font-medium text-heading">{{ $invoiceDate?->format('g:i A T') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </header>

    {{-- Bill to + payment --}}
    <div class="grid grid-cols-1 gap-4 border-b border-gray-100 px-6 py-5 dark:border-gray-800 sm:grid-cols-2 sm:px-8 sm:py-6">
        <div class="rounded-xl bg-gray-50/90 p-4 dark:bg-gray-950/50">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-muted">Bill to</p>
            @if($client)
                <p class="font-semibold text-heading">{{ ($clientName ?? '') !== '' ? $clientName : 'Customer' }}</p>
                @if($client->phone)<p class="mt-1 text-sm text-muted">{{ $client->phone }}</p>@endif
                @if($client->email)<p class="text-sm text-muted">{{ $client->email }}</p>@endif
            @else
                <p class="font-semibold text-heading">Walk-in customer</p>
                <p class="mt-1 text-sm text-muted">No client record linked to this sale.</p>
            @endif
        </div>
        <div class="rounded-xl bg-gray-50/90 p-4 sm:text-right dark:bg-gray-950/50">
            <p class="mb-2 text-[10px] font-bold uppercase tracking-wider text-muted">Payment &amp; status</p>
            <p class="font-medium capitalize text-heading">{{ str_replace('_', ' ', $transaction->payment_method) }}</p>
            <p class="mt-2">
                <span class="{{ $statusColors[$transaction->status] ?? 'badge-gray' }}">{{ ucfirst($transaction->status) }}</span>
            </p>
            @if($transaction->staff)
                <p class="mt-3 text-sm text-muted">Served by <span class="font-medium text-body">{{ $transaction->staff->name }}</span></p>
            @endif
        </div>
    </div>

    {{-- Line items --}}
    <div class="overflow-x-auto px-6 py-5 sm:px-8">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b-2 border-gray-200 text-left text-[10px] font-bold uppercase tracking-wider text-muted dark:border-gray-700">
                    <th class="pb-3 pr-4 font-semibold">Description</th>
                    <th class="w-16 pb-3 px-2 text-center font-semibold">Qty</th>
                    <th class="whitespace-nowrap pb-3 px-2 text-right font-semibold">Unit price</th>
                    <th class="whitespace-nowrap pb-3 pl-4 text-right font-semibold">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($transaction->items as $item)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-950/30">
                    <td class="py-3.5 pr-4 align-top">
                        <span class="font-medium text-heading">{{ $item->name }}</span>
                        <span class="mt-0.5 block text-[11px] capitalize text-muted">{{ $item->type }}</span>
                    </td>
                    <td class="px-2 py-3.5 text-center tabular-nums align-top">{{ $item->quantity }}</td>
                    <td class="px-2 py-3.5 text-right tabular-nums text-muted align-top">{{ $fmt((float) $item->unit_price) }}</td>
                    <td class="py-3.5 pl-4 text-right font-semibold tabular-nums text-heading align-top">{{ $fmt((float) $item->total) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Totals --}}
    <div class="border-t border-gray-100 bg-gray-50/80 px-6 py-5 dark:border-gray-800 dark:bg-gray-950/50 sm:px-8">
        <div class="flex justify-end">
            <dl class="w-full max-w-sm space-y-2 rounded-xl border border-gray-200/80 bg-white p-4 text-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="flex justify-between gap-8 text-muted">
                    <dt>Subtotal</dt>
                    <dd class="tabular-nums font-medium text-heading">{{ $fmt((float) $transaction->subtotal) }}</dd>
                </div>
                @if((float) $transaction->discount_amount > 0)
                <div class="flex justify-between gap-8 text-green-600 dark:text-green-400">
                    <dt>Discount</dt>
                    <dd class="tabular-nums font-medium">−{{ $fmt((float) $transaction->discount_amount) }}</dd>
                </div>
                @endif
                @if((float) $transaction->tax_amount > 0)
                <div class="flex justify-between gap-8 text-muted">
                    <dt>{{ $taxLabel ?? 'Tax' }}</dt>
                    <dd class="tabular-nums font-medium text-heading">{{ $fmt((float) $transaction->tax_amount) }}</dd>
                </div>
                @endif
                <div class="flex justify-between gap-8 border-t border-gray-200 pt-3 text-base font-bold text-heading dark:border-gray-700">
                    <dt>Total</dt>
                    <dd class="tabular-nums text-velour-600 dark:text-velour-400">{{ $fmt((float) $transaction->total) }}</dd>
                </div>
            </dl>
        </div>
        @if($transaction->notes)
            <p class="mt-4 border-t border-gray-200 pt-4 text-xs italic text-muted dark:border-gray-700">{{ $transaction->notes }}</p>
        @endif
        <p class="mt-4 text-center text-xs text-muted">{{ $footerNote ?? '' }}</p>
    </div>
</article>
