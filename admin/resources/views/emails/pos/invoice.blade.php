@php
    $invoice = \App\Support\PosInvoiceFormatting::viewContext($transaction);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice['docTitle'] }} {{ $transaction->reference }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.5;color:#18181b;">
<div style="max-width:640px;margin:0 auto;padding:24px 16px;">
    @include('pos.partials.customer-invoice-print', $invoice)
    <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">Powered by {{ config('app.name') }}</p>
</div>
</body>
</html>
