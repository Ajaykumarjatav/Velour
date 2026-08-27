@php
    $invoice = \App\Support\PosInvoiceFormatting::viewContext($transaction);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice['docTitle'] }} {{ $transaction->reference }}</title>
    <style>
        @page { margin: 14mm 12mm; }
        body { margin: 0; padding: 0; font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #18181b; }
    </style>
</head>
<body>
    @include('pos.partials.customer-invoice-print', $invoice)
</body>
</html>
