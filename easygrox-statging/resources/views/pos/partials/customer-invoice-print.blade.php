{{-- Customer invoice for PDF + email (inline styles). Expects PosInvoiceFormatting::viewContext(). --}}
@php
    /** @var \App\Models\PosTransaction $transaction */
    $fmt = $fmtDisplay ?? $fmtPdf ?? fn (float $n) => \App\Support\PosInvoiceFormatting::formatAmountPdf($n, $salon ?? null);
    $logoSrc = ($logoDataUri ?? null) ?: ($logoUrl ?? null);
@endphp

<div style="border:1px solid #e4e4e7;border-radius:12px;overflow:hidden;font-family:DejaVu Sans,Helvetica,Arial,sans-serif;color:#18181b;">
    <div style="height:4px;background:linear-gradient(90deg,#7c3aed,#a78bfa,#c4b5fd);"></div>

    {{-- Header --}}
    <div style="padding:22px 24px 20px;border-bottom:1px solid #ececf0;background:#fafafa;">
        <table width="100%" cellpadding="0" cellspacing="0">
            <tr>
                <td valign="top" style="padding-right:16px;">
                    <table cellpadding="0" cellspacing="0">
                        <tr>
                            @if($logoSrc)
                            <td valign="top" style="padding-right:14px;">
                                <div style="width:64px;height:64px;border:1px solid #e4e4e7;border-radius:10px;background:#fff;padding:6px;text-align:center;">
                                    <img src="{{ $logoSrc }}" alt="" style="max-width:52px;max-height:52px;display:block;margin:0 auto;">
                                </div>
                            </td>
                            @else
                            <td valign="top" style="padding-right:14px;">
                                <div style="width:64px;height:64px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#9333ea);color:#fff;font-size:20px;font-weight:700;text-align:center;line-height:64px;">
                                    {{ $salonInitials ?? 'S' }}
                                </div>
                            </td>
                            @endif
                            <td valign="top">
                                <p style="margin:0 0 4px;font-size:9px;font-weight:700;letter-spacing:.16em;text-transform:uppercase;color:#7c3aed;">{{ $docTitle ?? 'Invoice' }}</p>
                                <h1 style="margin:0 0 8px;font-size:20px;font-weight:700;color:#312e81;line-height:1.2;">{{ $salon?->name ?? 'Salon' }}</h1>
                                @foreach($addressLines ?? [] as $line)
                                    <p style="margin:0 0 2px;font-size:11px;color:#52525b;line-height:1.45;">{{ $line }}</p>
                                @endforeach
                                <p style="margin:10px 0 0;font-size:10px;color:#71717a;line-height:1.6;">
                                    @if($salon?->phone)<span style="margin-right:12px;">Tel: {{ $salon->phone }}</span>@endif
                                    @if($salon?->email)<span>Email: {{ $salon->email }}</span>@endif
                                </p>
                                @if($gstRegistered ?? false)
                                    <p style="margin:4px 0 0;font-size:10px;color:#3f3f46;">GSTIN: <span style="font-family:DejaVu Sans Mono,monospace;">{{ $salon->gst_number }}</span></p>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td valign="top" align="right" style="white-space:nowrap;width:200px;">
                    <div style="border:1px solid #ddd6fe;border-radius:10px;background:#fff;padding:14px 16px;text-align:right;">
                        <p style="margin:0;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#71717a;">{{ $amountLabel ?? 'Amount due' }}</p>
                        <p style="margin:4px 0 0;font-size:24px;font-weight:700;color:#7c3aed;">{{ $fmt((float) $transaction->total) }}</p>
                        <table cellpadding="0" cellspacing="0" style="margin-top:12px;font-size:11px;width:100%;" align="right">
                            <tr>
                                <td style="color:#71717a;padding:2px 8px 2px 0;text-align:left;">Invoice no.</td>
                                <td style="font-family:DejaVu Sans Mono,monospace;font-weight:600;text-align:right;">{{ $transaction->reference }}</td>
                            </tr>
                            <tr>
                                <td style="color:#71717a;padding:2px 8px 2px 0;text-align:left;">Date</td>
                                <td style="text-align:right;">{{ $invoiceDate?->format('D, j M Y') }}</td>
                            </tr>
                            <tr>
                                <td style="color:#71717a;padding:2px 8px 2px 0;text-align:left;">Time</td>
                                <td style="text-align:right;">{{ $invoiceDate?->format('g:i A T') }}</td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    {{-- Bill to + payment --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom:1px solid #f4f4f5;">
        <tr>
            <td width="50%" valign="top" style="padding:16px 12px 16px 24px;background:#fafafa;">
                <p style="margin:0 0 6px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#71717a;">Bill to</p>
                @if($client ?? null)
                    <p style="margin:0;font-size:13px;font-weight:600;">{{ ($clientName ?? '') !== '' ? $clientName : 'Customer' }}</p>
                    @if($client->phone)<p style="margin:4px 0 0;font-size:11px;color:#52525b;">{{ $client->phone }}</p>@endif
                    @if($client->email)<p style="margin:2px 0 0;font-size:11px;color:#52525b;">{{ $client->email }}</p>@endif
                @else
                    <p style="margin:0;font-size:13px;font-weight:600;">Walk-in customer</p>
                @endif
            </td>
            <td width="50%" valign="top" style="padding:16px 24px 16px 12px;background:#fafafa;text-align:right;">
                <p style="margin:0 0 6px;font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#71717a;">Payment &amp; status</p>
                <p style="margin:0;font-size:13px;font-weight:600;text-transform:capitalize;">{{ str_replace('_', ' ', $transaction->payment_method) }}</p>
                <p style="margin:6px 0 0;font-size:11px;color:#16a34a;font-weight:600;text-transform:capitalize;">{{ $transaction->status }}</p>
                @if($transaction->staff)
                    <p style="margin:8px 0 0;font-size:11px;color:#52525b;">Served by <strong>{{ $transaction->staff->name }}</strong></p>
                @endif
            </td>
        </tr>
    </table>

    {{-- Items --}}
    <table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
        <thead>
            <tr style="background:#f9fafb;">
                <th align="left" style="padding:10px 16px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:2px solid #e4e4e7;">Description</th>
                <th align="center" style="padding:10px 8px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:2px solid #e4e4e7;width:44px;">Qty</th>
                <th align="right" style="padding:10px 8px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:2px solid #e4e4e7;">Unit</th>
                <th align="right" style="padding:10px 16px;font-size:8px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#71717a;border-bottom:2px solid #e4e4e7;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $line)
            <tr>
                <td style="padding:12px 16px;border-bottom:1px solid #f4f4f5;vertical-align:top;">
                    <span style="font-size:12px;font-weight:600;">{{ $line->name }}</span>
                    <span style="display:block;font-size:10px;color:#71717a;text-transform:capitalize;margin-top:2px;">{{ $line->type }}</span>
                </td>
                <td align="center" style="padding:12px 8px;border-bottom:1px solid #f4f4f5;font-size:12px;">{{ $line->quantity }}</td>
                <td align="right" style="padding:12px 8px;border-bottom:1px solid #f4f4f5;font-size:12px;color:#52525b;">{{ $fmt((float) $line->unit_price) }}</td>
                <td align="right" style="padding:12px 16px;border-bottom:1px solid #f4f4f5;font-size:12px;font-weight:600;">{{ $fmt((float) $line->total) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Totals --}}
    <div style="padding:18px 24px 22px;background:#fafafa;border-top:1px solid #ececf0;">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:280px;margin-left:auto;border:1px solid #e4e4e7;border-radius:10px;background:#fff;">
            <tr>
                <td style="padding:12px 14px 4px;font-size:12px;color:#52525b;">Subtotal</td>
                <td align="right" style="padding:12px 14px 4px;font-size:12px;">{{ $fmt((float) $transaction->subtotal) }}</td>
            </tr>
            @if((float) $transaction->discount_amount > 0)
            <tr>
                <td style="padding:4px 14px;font-size:12px;color:#16a34a;">Discount</td>
                <td align="right" style="padding:4px 14px;font-size:12px;color:#16a34a;">−{{ $fmt((float) $transaction->discount_amount) }}</td>
            </tr>
            @endif
            @if((float) $transaction->tax_amount > 0)
            <tr>
                <td style="padding:4px 14px;font-size:12px;color:#52525b;">{{ $taxLabel ?? 'Tax' }}</td>
                <td align="right" style="padding:4px 14px;font-size:12px;">{{ $fmt((float) $transaction->tax_amount) }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding:10px 14px 12px;font-size:14px;font-weight:700;border-top:1px solid #e4e4e7;">Total</td>
                <td align="right" style="padding:10px 14px 12px;font-size:14px;font-weight:700;color:#7c3aed;border-top:1px solid #e4e4e7;">{{ $fmt((float) $transaction->total) }}</td>
            </tr>
        </table>
        @if($transaction->notes)
            <p style="margin:14px 0 0;font-size:11px;color:#71717a;font-style:italic;">{{ $transaction->notes }}</p>
        @endif
        <p style="margin:16px 0 0;font-size:11px;color:#71717a;text-align:center;line-height:1.5;">{{ $footerNote ?? '' }}</p>
    </div>
</div>
