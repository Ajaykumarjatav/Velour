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
        /* Zero page margin hides Chrome/Edge print headers & footers (URL, date, page #). */
        @page { size: A4 portrait; margin: 0; }
        html, body {
            margin: 0;
            padding: 0;
            background: #fff;
            color: #18181b;
        }
        * { box-sizing: border-box; }
        body {
            padding: 12mm 14mm;
            font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        .pos-invoice-print-root {
            width: 100%;
            max-width: 100%;
        }
        .pos-invoice-print-root .customer-invoice-sheet {
            width: 100%;
            max-width: 100%;
        }
        .pos-invoice-print-root .customer-invoice-sheet > table,
        .pos-invoice-print-root .customer-invoice-head {
            width: 100%;
            table-layout: fixed;
        }
        @media print {
            html, body {
                width: 210mm;
                background: #fff !important;
                color: #18181b !important;
            }
            body { padding: 10mm 12mm; }
            .pos-invoice-print-root .customer-invoice-sheet {
                border-radius: 0 !important;
            }
        }
    </style>
</head>
<body>
    <div class="pos-invoice-print-root">
        @include('pos.partials.customer-invoice-print', $invoice)
    </div>

    <script>
        window.addEventListener('load', function () {
            document.title = '\u200B';
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
