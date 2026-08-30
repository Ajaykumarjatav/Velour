@php
    $invoice = \App\Support\PosInvoiceFormatting::viewContext($transaction);
    $invoice['fmtDisplay'] = $invoice['fmt'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice['docTitle'] }} {{ $transaction->reference }}</title>
    <style>
        @page { size: A4 portrait; margin: 12mm; }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 16px;
            background: #fff;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            color: #18181b;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        @media print {
            body { padding: 0; }
        }
    </style>
</head>
<body>
    @include('pos.partials.customer-invoice-print', $invoice)

    <script>
        window.addEventListener('load', function () {
            setTimeout(function () { window.print(); }, 300);
        });
        window.addEventListener('afterprint', function () {
            if (window.opener) {
                window.close();
            }
        });
    </script>
</body>
</html>
