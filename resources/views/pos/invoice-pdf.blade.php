@php
    $invoice = \App\Support\PosInvoiceFormatting::viewContext($transaction);
    // DomPDF crashes on PNG without GD — drop salon PNG only.
    if (! \App\Support\PosInvoiceFormatting::canEmbedImagesInPdf()) {
        $invoice['logoDataUri'] = null;
        $invoice['logoUrl'] = null;
    }
    if (isset($invoice['platform']) && is_array($invoice['platform'])) {
        // Metallic gradient PNG/JPEG looks broken in DomPDF on white paper — use crisp HTML mark.
        $invoice['platform']['pdf_crisp_mark'] = true;
        $invoice['platform']['logo_data_uri'] = null;
        $invoice['platform']['logo_url'] = null;
    }
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
