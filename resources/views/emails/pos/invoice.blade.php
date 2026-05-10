@php
    $salon = $transaction->salon;
    $client = $transaction->client;
    $sym = $salon ? \App\Helpers\CurrencyHelper::symbol($salon->currency ?? 'GBP') : '£';
    $fmt = fn ($n) => $sym . number_format((float) $n, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt {{ $transaction->reference }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f5;font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;font-size:15px;line-height:1.5;color:#18181b;">
<div style="max-width:560px;margin:0 auto;padding:24px 16px;">
    <div style="background:#fff;border-radius:12px;padding:28px 24px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
        <p style="margin:0 0 4px;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:#71717a;">
            Receipt
        </p>
        <h1 style="margin:0 0 8px;font-size:22px;font-weight:700;color:#4c1d95;">
            {{ $salon?->name ?? 'Velour' }}
        </h1>
        <p style="margin:0 0 20px;font-size:13px;color:#52525b;">
            Hi {{ trim(($client->first_name ?? '').' '.($client->last_name ?? '')) }}, thank you for your visit. Below is your invoice for the amount charged.
        </p>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#71717a;">Reference</td>
                <td style="padding:8px 0;text-align:right;font-family:ui-monospace,monospace;font-weight:600;color:#18181b;">{{ $transaction->reference }}</td>
            </tr>
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#71717a;">Date</td>
                <td style="padding:8px 0;text-align:right;font-size:13px;color:#18181b;">
                    {{ ($transaction->completed_at ?? $transaction->created_at)?->timezone($salon?->timezone ?? config('app.timezone'))->format('M j, Y g:i A') }}
                </td>
            </tr>
            <tr>
                <td style="padding:8px 0;font-size:13px;color:#71717a;">Payment</td>
                <td style="padding:8px 0;text-align:right;font-size:13px;color:#18181b;text-transform:capitalize;">
                    {{ str_replace('_', ' ', $transaction->payment_method) }}
                </td>
            </tr>
        </table>

        <p style="margin:0 0 8px;font-size:12px;font-weight:600;color:#71717a;text-transform:uppercase;letter-spacing:.04em;">Items</p>
        <table width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #e4e4e7;">
            @foreach($transaction->items as $line)
                <tr>
                    <td style="padding:12px 0;border-bottom:1px solid #f4f4f5;font-size:14px;color:#18181b;">
                        {{ $line->name }}
                        <span style="color:#71717a;font-size:13px;"> × {{ $line->quantity }}</span>
                    </td>
                    <td style="padding:12px 0;border-bottom:1px solid #f4f4f5;text-align:right;font-size:14px;font-weight:600;color:#18181b;white-space:nowrap;">
                        {{ $fmt($line->total) }}
                    </td>
                </tr>
            @endforeach
        </table>

        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
            <tr>
                <td style="padding:6px 0;font-size:14px;color:#52525b;">Subtotal</td>
                <td style="padding:6px 0;text-align:right;font-size:14px;color:#18181b;">{{ $fmt($transaction->subtotal) }}</td>
            </tr>
            @if((float) $transaction->discount_amount > 0)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#16a34a;">Discount</td>
                    <td style="padding:6px 0;text-align:right;font-size:14px;color:#16a34a;">−{{ $fmt($transaction->discount_amount) }}</td>
                </tr>
            @endif
            @if((float) $transaction->tax_amount > 0)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#52525b;">Tax</td>
                    <td style="padding:6px 0;text-align:right;font-size:14px;color:#18181b;">{{ $fmt($transaction->tax_amount) }}</td>
                </tr>
            @endif
            <tr>
                <td style="padding:14px 0 0;font-size:16px;font-weight:700;color:#18181b;">Total charged</td>
                <td style="padding:14px 0 0;text-align:right;font-size:16px;font-weight:700;color:#4c1d95;">{{ $fmt($transaction->total) }}</td>
            </tr>
        </table>

        @if($transaction->notes)
            <p style="margin:20px 0 0;font-size:13px;color:#71717a;font-style:italic;">{{ $transaction->notes }}</p>
        @endif

        @if($salon?->email)
            <p style="margin:24px 0 0;font-size:13px;color:#71717a;">
                Questions? Contact us at <a href="mailto:{{ $salon->email }}" style="color:#7c3aed;">{{ $salon->email }}</a>
                @if($salon->phone) · {{ $salon->phone }} @endif
            </p>
        @endif
    </div>
    <p style="margin:16px 0 0;text-align:center;font-size:12px;color:#a1a1aa;">
        Powered by Velour
    </p>
</div>
</body>
</html>
