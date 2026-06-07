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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $transaction->reference }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.5;color:#18181b;">
<div style="max-width:640px;margin:0 auto;padding:24px 16px;">
    <div style="background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.06);border:1px solid #e4e4e7;">
        {{-- Header --}}
        <div style="padding:24px 24px 20px;border-bottom:1px solid #e4e4e7;background:linear-gradient(180deg,#faf5ff 0%,#fff 100%);">
            <p style="margin:0 0 6px;font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase;color:#7c3aed;">Tax invoice</p>
            <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top" style="padding-right:16px;">
                        <h1 style="margin:0 0 10px;font-size:22px;font-weight:700;color:#4c1d95;line-height:1.2;">{{ $salon?->name ?? 'EasyGrox' }}</h1>
                        @foreach($addressLines as $line)
                            <p style="margin:0 0 2px;font-size:13px;color:#52525b;">{{ $line }}</p>
                        @endforeach
                        <p style="margin:12px 0 0;font-size:12px;color:#71717a;">
                            @if($salon?->phone)<span style="margin-right:12px;">{{ $salon->phone }}</span>@endif
                            @if($salon?->email)<a href="mailto:{{ $salon->email }}" style="color:#7c3aed;text-decoration:none;">{{ $salon->email }}</a>@endif
                        </p>
                    </td>
                    <td valign="top" align="right" style="white-space:nowrap;">
                        <p style="margin:0;font-size:28px;font-weight:700;color:#7c3aed;">{{ $fmt($transaction->total) }}</p>
                        <p style="margin:4px 0 0;font-size:11px;color:#71717a;">Amount due</p>
                        <table cellpadding="0" cellspacing="0" style="margin-top:14px;font-size:13px;" align="right">
                            <tr>
                                <td style="color:#71717a;padding:2px 12px 2px 0;">Invoice no.</td>
                                <td style="font-family:ui-monospace,monospace;font-weight:600;color:#18181b;">{{ $transaction->reference }}</td>
                            </tr>
                            <tr>
                                <td style="color:#71717a;padding:2px 12px 2px 0;">Date</td>
                                <td style="color:#18181b;">{{ $invoiceDate?->format('M j, Y') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div style="padding:20px 24px;border-bottom:1px solid #f4f4f5;">
            <p style="margin:0 0 8px;font-size:10px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#71717a;">Bill to</p>
            @if($client)
                <p style="margin:0;font-size:15px;font-weight:600;color:#18181b;">{{ $clientName !== '' ? $clientName : 'Customer' }}</p>
                @if($client->phone)<p style="margin:6px 0 0;font-size:13px;color:#52525b;">{{ $client->phone }}</p>@endif
            @else
                <p style="margin:0;font-size:15px;font-weight:600;color:#18181b;">Walk-in customer</p>
            @endif
            <p style="margin:16px 0 0;font-size:13px;color:#52525b;">
                @php $greet = $clientName !== '' ? $clientName : 'there'; @endphp
                Hi {{ $greet }}, thank you for your visit. Below is your itemised invoice.
            </p>
        </div>

        {{-- Line items table --}}
        <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
            <thead>
                <tr style="background:#fafafa;">
                    <th align="left" style="padding:12px 16px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;">Description</th>
                    <th align="center" style="padding:12px 8px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;width:48px;">Qty</th>
                    <th align="right" style="padding:12px 8px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;">Unit</th>
                    <th align="right" style="padding:12px 16px;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:1px solid #e4e4e7;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaction->items as $line)
                    <tr>
                        <td style="padding:14px 16px;border-bottom:1px solid #f4f4f5;vertical-align:top;">
                            <span style="font-size:14px;font-weight:600;color:#18181b;">{{ $line->name }}</span>
                            <span style="display:block;font-size:12px;color:#71717a;text-transform:capitalize;margin-top:2px;">{{ $line->type }}</span>
                        </td>
                        <td align="center" style="padding:14px 8px;border-bottom:1px solid #f4f4f5;font-size:14px;color:#18181b;">{{ $line->quantity }}</td>
                        <td align="right" style="padding:14px 8px;border-bottom:1px solid #f4f4f5;font-size:14px;color:#52525b;">{{ $fmt($line->unit_price) }}</td>
                        <td align="right" style="padding:14px 16px;border-bottom:1px solid #f4f4f5;font-size:14px;font-weight:600;color:#18181b;">{{ $fmt($line->total) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="padding:20px 24px 24px;background:#fafafa;">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width:280px;margin-left:auto;">
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#52525b;">Subtotal</td>
                    <td align="right" style="padding:6px 0;font-size:14px;color:#18181b;">{{ $fmt($transaction->subtotal) }}</td>
                </tr>
                @if((float) $transaction->discount_amount > 0)
                    <tr>
                        <td style="padding:6px 0;font-size:14px;color:#16a34a;">Discount</td>
                        <td align="right" style="padding:6px 0;font-size:14px;color:#16a34a;">−{{ $fmt($transaction->discount_amount) }}</td>
                    </tr>
                @endif
                @if((float) $transaction->tax_amount > 0)
                    <tr>
                        <td style="padding:6px 0;font-size:14px;color:#52525b;">{{ $taxLabel }}</td>
                        <td align="right" style="padding:6px 0;font-size:14px;color:#18181b;">{{ $fmt($transaction->tax_amount) }}</td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:14px 0 0;font-size:16px;font-weight:700;color:#18181b;border-top:1px solid #e4e4e7;">Total</td>
                    <td align="right" style="padding:14px 0 0;font-size:16px;font-weight:700;color:#4c1d95;border-top:1px solid #e4e4e7;">{{ $fmt($transaction->total) }}</td>
                </tr>
            </table>
            <p style="margin:16px 0 0;font-size:13px;color:#52525b;">
                Payment: <strong style="color:#18181b;text-transform:capitalize;">{{ str_replace('_', ' ', $transaction->payment_method) }}</strong>
                @if($transaction->staff)
                    · Staff: <strong style="color:#18181b;">{{ $transaction->staff->name }}</strong>
                @endif
            </p>
            @if($transaction->notes)
                <p style="margin:16px 0 0;font-size:13px;color:#71717a;font-style:italic;">{{ $transaction->notes }}</p>
            @endif
            <p style="margin:20px 0 0;font-size:13px;color:#71717a;">Thank you for your business.</p>
        </div>
    </div>
    <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">Powered by EasyGrox</p>
</div>
</body>
</html>
