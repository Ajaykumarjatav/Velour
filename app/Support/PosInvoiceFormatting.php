<?php

declare(strict_types=1);

namespace App\Support;

use App\Helpers\CurrencyHelper;
use App\Models\PosTransaction;
use App\Models\Salon;

final class PosInvoiceFormatting
{
    /** @return list<string> */
    public static function salonAddressLines(?Salon $salon): array
    {
        if ($salon === null) {
            return [];
        }

        $cityLine = trim(implode(', ', array_filter([
            (string) ($salon->city ?? ''),
            (string) ($salon->county ?? ''),
            (string) ($salon->postcode ?? ''),
        ])));

        $parts = array_filter([
            trim((string) ($salon->address_line1 ?? '')),
            trim((string) ($salon->address_line2 ?? '')),
            $cityLine,
            trim((string) ($salon->country ?? '')),
        ], fn (string $s) => $s !== '');

        return array_values($parts);
    }

    public static function impliedTaxPercent(PosTransaction $tx): ?float
    {
        $tax = (float) $tx->tax_amount;
        if ($tax <= 0) {
            return null;
        }
        $base = max(0.0, (float) $tx->subtotal - (float) $tx->discount_amount);
        if ($base <= 0) {
            return null;
        }

        return round(100 * $tax / $base, 2);
    }

    public static function taxSummaryLabel(PosTransaction $tx): string
    {
        $pct = self::impliedTaxPercent($tx);

        return $pct !== null
            ? 'Tax (GST '.$pct.'%)'
            : 'Tax (GST)';
    }

    /**
     * WhatsApp-friendly invoice text with subtotal, tax breakdown, and business details.
     * Optional signed PDF URL lets clients open a downloadable invoice without logging in.
     */
    public static function whatsappBody(PosTransaction $tx, ?string $pdfUrl = null): string
    {
        $tx->loadMissing(['salon', 'client', 'items', 'staff']);
        $salon = $tx->salon;
        $client = $tx->client;
        $sym = $salon ? CurrencyHelper::symbol($salon->currency ?? 'GBP') : '£';

        $tz = $salon?->timezone ?? config('app.timezone');
        $when = ($tx->completed_at ?? $tx->created_at)?->timezone($tz)->format('D, j M Y, g:i A T') ?? '';

        $lines = [];
        $lines[] = '*TAX INVOICE*';
        $lines[] = '';
        $lines[] = '*'.($salon->name ?? 'Salon').'*';
        foreach (self::salonAddressLines($salon) as $line) {
            $lines[] = $line;
        }
        if ($salon?->phone) {
            $lines[] = 'Phone: '.$salon->phone;
        }
        if ($salon?->email) {
            $lines[] = 'Email: '.$salon->email;
        }
        $lines[] = '──────────────';
        $lines[] = 'Invoice #: `'.$tx->reference.'`';
        $lines[] = 'Date: '.$when;
        $lines[] = '';
        $lines[] = '*Bill to*';
        if ($client) {
            $nm = trim(($client->first_name ?? '').' '.($client->last_name ?? ''));
            $lines[] = $nm !== '' ? $nm : 'Customer';
            if ($client->phone) {
                $lines[] = 'Phone: '.$client->phone;
            }
            if ($client->email) {
                $lines[] = 'Email: '.$client->email;
            }
        } else {
            $lines[] = 'Walk-in customer';
        }
        $lines[] = '';
        $lines[] = '*Line items*';
        foreach ($tx->items as $item) {
            $type = ucfirst((string) $item->type);
            $unit = (float) $item->unit_price;
            $qty = (int) $item->quantity;
            $lineTotal = (float) $item->total;
            $lines[] = '• '.$item->name;
            $lines[] = '  '.$type.' · Qty '.$qty.' × '.$sym.number_format($unit, 2).' = *'.$sym.number_format($lineTotal, 2).'*';
        }
        $lines[] = '';
        $lines[] = '──────────────';
        $lines[] = 'Subtotal: '.$sym.number_format((float) $tx->subtotal, 2);
        if ((float) $tx->discount_amount > 0) {
            $lines[] = 'Discount: −'.$sym.number_format((float) $tx->discount_amount, 2);
        }
        if ((float) $tx->tax_amount > 0) {
            $lines[] = self::taxSummaryLabel($tx).': '.$sym.number_format((float) $tx->tax_amount, 2);
        }
        $lines[] = '*Total due: '.$sym.number_format((float) $tx->total, 2).'*';
        $lines[] = '';
        $lines[] = 'Payment: *'.ucfirst(str_replace('_', ' ', (string) $tx->payment_method)).'*';
        if ($tx->staff) {
            $lines[] = 'Served by: '.trim(($tx->staff->first_name ?? '').' '.($tx->staff->last_name ?? ''));
        }
        if ($tx->notes) {
            $lines[] = '';
            $lines[] = 'Note: '.$tx->notes;
        }
        $pdfUrl = $pdfUrl !== null ? trim($pdfUrl) : '';
        if ($pdfUrl !== '') {
            $lines[] = '';
            $lines[] = 'PDF invoice (download):';
            $lines[] = $pdfUrl;
        }
        $lines[] = '';
        $lines[] = 'Thank you for your business.';

        return implode("\n", $lines);
    }
}
