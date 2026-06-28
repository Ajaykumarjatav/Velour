@extends('layouts.app')
@section('title', 'Invoice '.$transaction->reference)
@section('page-title', 'Invoice')

@php
    use App\Support\PosInvoiceFormatting;
    use Illuminate\Support\Facades\URL;

    $transaction->loadMissing(['salon', 'client', 'items', 'staff']);
    $salon = $transaction->salon;
    $sym = $salon ? \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP') : '£';
    $addressLines = PosInvoiceFormatting::salonAddressLines($salon);
    $taxLabel = PosInvoiceFormatting::taxSummaryLabel($transaction);
    $tz = $salon?->timezone ?? config('app.timezone');
    $invoiceDate = ($transaction->completed_at ?? $transaction->created_at)?->timezone($tz);
    $client = $transaction->client;
    $clientName = $client ? trim(($client->first_name ?? '').' '.($client->last_name ?? '')) : '';
    $invoicePdfSignedUrl = URL::temporarySignedRoute(
        'pos.invoice.pdf.signed',
        now()->addDays(14),
        ['transaction' => $transaction->id]
    );
    $waText = PosInvoiceFormatting::whatsappBody($transaction, $invoicePdfSignedUrl);
    $clientPhone = preg_replace('/\D+/', '', (string) ($client?->phone ?? ''));
    $waHref = $clientPhone !== ''
        ? 'https://wa.me/'.$clientPhone.'?text='.rawurlencode($waText)
        : 'https://wa.me/?text='.rawurlencode($waText);
    $defaultInvoiceEmail = old('email', filter_var($transaction->client?->email ?? '', FILTER_VALIDATE_EMAIL) ? $transaction->client->email : '');
@endphp

@push('styles')
<style>
    @media print {
        .no-print { display: none !important; }
        .invoice-sheet { box-shadow: none !important; border: 1px solid #ccc !important; }
    }
</style>
@endpush

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Printable invoice --}}
    <article class="invoice-sheet rounded-2xl border border-gray-200/90 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm overflow-hidden text-gray-900 dark:text-gray-100">
        <header class="border-b border-gray-200 dark:border-gray-700 px-6 py-6 sm:px-8 sm:py-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:justify-between sm:items-start">
                <div class="min-w-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-velour-600 dark:text-velour-400 mb-1">Tax invoice</p>
                    <h1 class="text-xl sm:text-2xl font-bold text-heading tracking-tight">{{ $salon->name ?? config('app.name') }}</h1>
                    @if($addressLines !== [])
                        <address class="not-italic text-sm text-muted mt-2 space-y-0.5">
                            @foreach($addressLines as $line)
                                <p>{{ $line }}</p>
                            @endforeach
                        </address>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted">
                        @if($salon?->phone)<span>Tel: <span class="text-body font-medium">{{ $salon->phone }}</span></span>@endif
                        @if($salon?->email)<span>Email: <span class="text-body font-medium">{{ $salon->email }}</span></span>@endif
                        @if($salon?->website)<span>Web: <span class="text-body font-medium">{{ $salon->website }}</span></span>@endif
                    </div>
                </div>
                <div class="shrink-0 text-left sm:text-right w-full sm:w-auto">
                    <p class="text-3xl sm:text-4xl font-bold tabular-nums text-velour-600 dark:text-velour-400">{{ $sym }}{{ number_format((float) $transaction->total, 2) }}</p>
                    <p class="text-xs text-muted mt-1">Amount due</p>
                    <dl class="mt-4 space-y-1 text-sm">
                        <div class="flex sm:justify-end gap-4">
                            <dt class="text-muted">Invoice no.</dt>
                            <dd class="font-mono font-semibold text-heading">{{ $transaction->reference }}</dd>
                        </div>
                        <div class="flex sm:justify-end gap-4">
                            <dt class="text-muted">Date</dt>
                            <dd class="font-medium text-heading">{{ $invoiceDate?->format('D, j M Y') }}</dd>
                        </div>
                        <div class="flex sm:justify-end gap-4">
                            <dt class="text-muted">Time</dt>
                            <dd class="font-medium text-heading">{{ $invoiceDate?->format('g:i A T') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </header>

        <div class="px-6 py-5 sm:px-8 sm:py-6 grid grid-cols-1 sm:grid-cols-2 gap-6 border-b border-gray-100 dark:border-gray-800">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-muted mb-2">Bill to</p>
                @if($client)
                    <p class="font-semibold text-heading">{{ $clientName !== '' ? $clientName : 'Customer' }}</p>
                    @if($client->phone)<p class="text-sm text-muted mt-1">{{ $client->phone }}</p>@endif
                    @if($client->email)<p class="text-sm text-muted">{{ $client->email }}</p>@endif
                @else
                    <p class="font-semibold text-heading">Walk-in customer</p>
                    <p class="text-sm text-muted mt-1">No client record linked to this sale.</p>
                @endif
            </div>
            <div class="sm:text-right sm:justify-self-end w-full max-w-xs">
                <p class="text-[10px] font-bold uppercase tracking-wider text-muted mb-2">Payment &amp; status</p>
                <p class="font-medium text-heading capitalize">{{ str_replace('_', ' ', $transaction->payment_method) }}</p>
                @php $colors = ['completed'=>'badge-green','refunded'=>'badge-yellow','voided'=>'badge-red']; @endphp
                <p class="mt-2"><span class="{{ $colors[$transaction->status] ?? 'badge-gray' }}">{{ ucfirst($transaction->status) }}</span></p>
                @if($transaction->staff)
                    <p class="text-sm text-muted mt-3">Staff: <span class="text-body font-medium">{{ $transaction->staff->name }}</span></p>
                @endif
            </div>
        </div>

        <div class="px-6 sm:px-8 py-5 overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-left text-[10px] font-bold uppercase tracking-wider text-muted">
                        <th class="pb-3 pr-4 font-semibold">Description</th>
                        <th class="pb-3 px-2 font-semibold text-center w-16">Qty</th>
                        <th class="pb-3 px-2 font-semibold text-right whitespace-nowrap">Unit</th>
                        <th class="pb-3 pl-4 font-semibold text-right whitespace-nowrap">Amount</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($transaction->items as $item)
                    <tr>
                        <td class="py-3 pr-4 align-top">
                            <span class="font-medium text-heading">{{ $item->name }}</span>
                            <span class="block text-[11px] text-muted capitalize mt-0.5">{{ $item->type }}</span>
                        </td>
                        <td class="py-3 px-2 text-center tabular-nums align-top">{{ $item->quantity }}</td>
                        <td class="py-3 px-2 text-right tabular-nums text-muted align-top">{{ $sym }}{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td class="py-3 pl-4 text-right font-semibold tabular-nums text-heading align-top">{{ $sym }}{{ number_format((float) $item->total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 sm:px-8 py-5 bg-gray-50/80 dark:bg-gray-950/50 border-t border-gray-100 dark:border-gray-800">
            <div class="flex justify-end">
                <dl class="w-full max-w-xs space-y-2 text-sm">
                    <div class="flex justify-between gap-8 text-muted">
                        <dt>Subtotal</dt>
                        <dd class="tabular-nums text-heading font-medium">{{ $sym }}{{ number_format((float) $transaction->subtotal, 2) }}</dd>
                    </div>
                    @if((float) $transaction->discount_amount > 0)
                    <div class="flex justify-between gap-8 text-green-600 dark:text-green-400">
                        <dt>Discount</dt>
                        <dd class="tabular-nums font-medium">−{{ $sym }}{{ number_format((float) $transaction->discount_amount, 2) }}</dd>
                    </div>
                    @endif
                    @if((float) $transaction->tax_amount > 0)
                    <div class="flex justify-between gap-8 text-muted">
                        <dt>{{ $taxLabel }}</dt>
                        <dd class="tabular-nums text-heading font-medium">{{ $sym }}{{ number_format((float) $transaction->tax_amount, 2) }}</dd>
                    </div>
                    @endif
                    <div class="flex justify-between gap-8 text-base font-bold text-heading pt-2 border-t border-gray-200 dark:border-gray-700">
                        <dt>Total</dt>
                        <dd class="tabular-nums text-velour-600 dark:text-velour-400">{{ $sym }}{{ number_format((float) $transaction->total, 2) }}</dd>
                    </div>
                </dl>
            </div>
            @if($transaction->notes)
                <p class="mt-4 text-xs text-muted italic border-t border-gray-200 dark:border-gray-700 pt-4">{{ $transaction->notes }}</p>
            @endif
            <p class="mt-4 text-xs text-muted">Thank you for your business.</p>
        </div>
    </article>

    <div class="no-print card p-5 sm:p-6 space-y-4">
        <h2 class="text-sm font-semibold text-heading">Share invoice</h2>
        <p class="text-xs text-muted">Email a formatted invoice, or send a detailed summary via WhatsApp (includes a 14-day link to download the PDF invoice).</p>

        <form action="{{ route('pos.invoice.email', $transaction) }}" method="POST" class="space-y-2">
            @csrf
            <label class="form-label text-xs">Email</label>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="email" name="email" value="{{ $defaultInvoiceEmail }}" required placeholder="client@example.com"
                       class="form-input flex-1 text-sm @error('email') form-input-error @enderror">
                <button type="submit" class="btn-primary text-sm whitespace-nowrap shrink-0">Send by email</button>
            </div>
            @error('email')<p class="form-error text-xs">{{ $message }}</p>@enderror
        </form>

        <div class="pt-2 border-t border-gray-100 dark:border-gray-800">
            <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex w-full sm:w-auto items-center justify-center gap-2 rounded-xl border border-emerald-600/40 bg-emerald-600/10 dark:bg-emerald-900/30 px-4 py-2.5 text-sm font-semibold text-emerald-800 dark:text-emerald-200 hover:bg-emerald-600/20 transition-colors">
                <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Share on WhatsApp
            </a>
            @if($clientPhone === '' && $transaction->client)
                <p class="text-[11px] text-muted mt-1.5">No phone on file — WhatsApp opens so you can choose the recipient.</p>
            @endif
        </div>
    </div>

    <div class="no-print flex flex-wrap gap-3 pb-8">
        <a href="{{ route('pos.invoice.pdf', $transaction) }}" class="btn-outline inline-flex items-center justify-center">Download PDF</a>
        <button type="button" onclick="window.print()" class="btn-outline">Print</button>
        <a href="{{ route('pos.index') }}" class="btn text-muted hover:text-body">Back to sales</a>
    </div>
</div>

@endsection
