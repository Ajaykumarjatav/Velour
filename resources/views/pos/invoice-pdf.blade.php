@php
    use App\Support\PosInvoiceFormatting;

    $transaction->loadMissing(['salon', 'client', 'items', 'staff']);
    $salon = $transaction->salon;
    $client = $transaction->client;
    $sym = $salon ? \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP') : '£';
    $fmt = fn ($n) => $sym . number_format((float) $n, 2);
    $addressLines = PosInvoiceFormatting::salonAddressLines($salon);
    $taxLabel = PosInvoiceFormatting::taxSummaryLabel($transaction);
    $tz = $salon?->timezone ?? config('app.timezone');
    $invoiceDate = ($transaction->completed_at ?? $transaction->created_at)?->timezone($tz);
    $clientName = $client ? trim(($client->first_name ?? '').' '.($client->last_name ?? '')) : '';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $transaction->reference }}</title>
</head>
<body style="margin:0;padding:16px;font-family:DejaVu Sans,sans-serif;font-size:12px;line-height:1.45;color:#18181b;">
    <div style="border:1px solid #e4e4e7;border-radius:8px;overflow:hidden;">
        <div style="padding:18px 20px;border-bottom:1px solid #e4e4e7;background:#faf5ff;">
            <p style="margin:0 0 6px;font-size:9px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#7c3aed;">Tax invoice</p>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top" style="padding-right:12px;">
                        <h1 style="margin:0 0 8px;font-size:18px;font-weight:700;color:#4c1d95;">{{ $salon?->name ?? 'Salon' }}</h1>
                        @foreach($addressLines as $line)
                            <p style="margin:0 0 2px;font-size:11px;color:#52525b;">{{ $line }}</p>
                        @endforeach
                        <p style="margin:10px 0 0;font-size:10px;color:#71717a;">
                            @if($salon?->phone)<span style="margin-right:10px;">{{ $salon->phone }}</span>@endif
                            @if($salon?->email)<span>{{ $salon->email }}</span>@endif
                        </p>
                    </td>
                    <td valign="top" align="right" style="white-space:nowrap;">
                        <p style="margin:0;font-size:22px;font-weight:700;color:#7c3aed;">{{ $fmt($transaction->total) }}</p>
                        <p style="margin:2px 0 0;font-size:10px;color:#71717a;">Amount due</p>
                        <table cellpadding="0" cellspacing="0" style="margin-top:10px;font-size:11px;" align="right">
                            <tr>
                                <td style="color:#71717a;padding:2px 10px 2px 0;">Invoice no.</td>
                                <td style="font-family:DejaVu Sans Mono,monospace;font-weight:600;">{{ $transaction->reference }}</td>
                            </tr>
                            <tr>
                                <td style="color:#71717a;padding:2px 10px 2px 0;">Date</td>
                                <td>{{ $invoiceDate?->format('M j, Y') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div style="padding:14px 20px;border-bottom:1px solid #f4f4f5;">
            <p style="margin:0 0 6px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#71717a;">Bill to</p>
            @if($client)
                <p style="margin:0;font-size:13px;font-weight:600;">{{ $clientName !== '' ? $clientName : 'Customer' }}</p>
                @if($client->phone)<p style="margin:4px 0 0;font-size:11px;color:#52525b;">{{ $client->phone }}</p>@endif
            @else
                <p style="margin:0;font-size:13px;font-weight:600;">Walk-in customer</p>
            @endif
        </div>

        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
            <thead>
                <tr style="background:#fafafa;">
                    <th align="left" style="padding:8px 12px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;">Description</th>
                    <th align="center" style="padding:8px 6px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;width:40px;">Qty</th>
                    <th align="right" style="padding:8px 6px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;">Unit</th>
                    <th align="right" style="padding:8px 12px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $line)
                    <tr>
                        <td style="padding:10px 12px;border-bottom:1px solid #f4f4f5;vertical-align:top;">
                            <span style="font-size:11px;font-weight:600;">{{ $line->name }}</span>
                            <span style="display:block;font-size:10px;color:#71717a;text-transform:capitalize;margin-top:1px;">{{ $line->type }}</span>
                        </td>
                        <td align="center" style="padding:10px 6px;border-bottom:1px solid #f4f4f5;">{{ $line->quantity }}</td>
                        <td align="right" style="padding:10px 6px;border-bottom:1px solid #f4f4f5;">{{ $fmt($line->unit_price) }}</td>
                        <td align="right" style="padding:10px 12px;border-bottom:1px solid #f4f4f5;font-weight:600;">{{ $fmt($line->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="padding:14px 20px 18px;background:#fafafa;">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:260px;margin-left:auto;">
                <tr>
                    <td style="padding:4px 0;font-size:11px;color:#52525b;">Subtotal</td>
                    <td align="right" style="padding:4px 0;font-size:11px;">{{ $fmt($transaction->subtotal) }}</td>
                </tr>
                @if((float) $transaction->discount_amount > 0)
                    <tr>
                        <td style="padding:4px 0;font-size:11px;color:#16a34a;">Discount</td>
                        <td align="right" style="padding:4px 0;font-size:11px;color:#16a34a;">−{{ $fmt($transaction->discount_amount) }}</td>
                    </tr>
                @endif
                @if((float) $transaction->tax_amount > 0)
                    <tr>
                        <td style="padding:4px 0;font-size:11px;color:#52525b;">{{ $taxLabel }}</td>
                        <td align="right" style="padding:4px 0;font-size:11px;">{{ $fmt($transaction->tax_amount) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:10px 0 0;font-size:13px;font-weight:700;border-top:1px solid #e4e4e7;">Total</td>
                    <td align="right" style="padding:10px 0 0;font-size:13px;font-weight:700;color:#4c1d95;border-top:1px solid #e4e4e7;">{{ $fmt($transaction->total) }}</td>
                </tr>
            </table>
            <p style="margin:12px 0 0;font-size:11px;color:#52525b;">
                Payment: <strong style="text-transform:capitalize;">{{ str_replace('_', ' ', $transaction->payment_method) }}</strong>
                @if($transaction->staff)
                    · Staff: <strong>{{ $transaction->staff->name }}</strong>
                @endif
            </p>
            @if($transaction->notes)
                <p style="margin:10px 0 0;font-size:11px;color:#71717a;font-style:italic;">{{ $transaction->notes }}</p>
            @endif
            <p style="margin:14px 0 0;font-size:11px;color:#71717a;">Thank you for your business.</p>
        </div>
    </div>
</body>
</html>
