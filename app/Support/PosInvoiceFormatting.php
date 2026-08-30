<?php

declare(strict_types=1);

namespace App\Support;

use App\Helpers\CurrencyHelper;
use App\Models\PosTransaction;
use App\Models\Salon;
use Illuminate\Support\Facades\Storage;

/**
 * Customer-facing POS invoices branch on salon GST registration:
 * - GST registered (gst_number set) → Tax Invoice with GSTIN
 * - Not registered → plain Invoice (no GSTIN / not a tax invoice)
 *
 * Platform → tenant subscription invoices live under billing/invoice (separate).
 */
final class PosInvoiceFormatting
{
    public static function isGstRegistered(?Salon $salon): bool
    {
        return $salon !== null && trim((string) ($salon->gst_number ?? '')) !== '';
    }

    /** Default GST % for GST-registered salons. Non-GST salons use 0. */
    public static function defaultTaxRatePercent(?Salon $salon): float
    {
        if (! self::isGstRegistered($salon)) {
            return 0.0;
        }

        $configured = (float) config('velour.pos.tax_rate', 0.18);

        return round($configured * 100, 2);
    }

    /** Customer document title: "Tax Invoice" vs "Invoice". */
    public static function customerDocumentTitle(?Salon $salon): string
    {
        return self::isGstRegistered($salon) ? 'Tax Invoice' : 'Invoice';
    }

    public static function customerDocumentTitleUpper(?Salon $salon): string
    {
        return strtoupper(self::customerDocumentTitle($salon));
    }

    public static function customerFooterNote(?Salon $salon): string
    {
        if (self::isGstRegistered($salon)) {
            return 'This is a GST tax invoice. Thank you for your business.';
        }

        return 'This is a retail invoice (business is not GST-registered). Thank you for your business.';
    }

    public static function logoUrl(?Salon $salon): ?string
    {
        if ($salon === null) {
            return null;
        }

        return PublicStorage::url($salon->logo);
    }

    /** Base64 data URI for DomPDF (remote/logo URLs often fail in PDF). */
    public static function logoDataUri(?Salon $salon): ?string
    {
        if ($salon === null) {
            return null;
        }

        $resolved = PublicStorage::resolveExistingPath($salon->logo);
        if ($resolved === null) {
            return null;
        }

        try {
            $bytes = Storage::disk('public')->get($resolved);
        } catch (\Throwable) {
            return null;
        }

        if ($bytes === '' || $bytes === null) {
            return null;
        }

        $fullPath = Storage::disk('public')->path($resolved);
        $mime = @mime_content_type($fullPath) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }

    public static function salonInitials(?Salon $salon): string
    {
        $name = trim((string) ($salon?->name ?? ''));
        if ($name === '') {
            return 'S';
        }

        $parts = preg_split('/\s+/u', $name) ?: [];
        $initials = '';
        foreach (array_slice($parts, 0, 2) as $part) {
            $initials .= mb_strtoupper(mb_substr($part, 0, 1));
        }

        return $initials !== '' ? $initials : mb_strtoupper(mb_substr($name, 0, 1));
    }

    public static function currencyCode(?Salon $salon): string
    {
        return strtoupper((string) ($salon?->currency ?? CurrencyHelper::defaultCode()));
    }

    /** Screen / email amounts. */
    public static function formatAmount(float $amount, ?Salon $salon): string
    {
        return CurrencyHelper::format($amount, self::currencyCode($salon));
    }

    /** DomPDF-safe amounts (DejaVu lacks ₹ glyph). */
    public static function formatAmountPdf(float $amount, ?Salon $salon): string
    {
        $code = self::currencyCode($salon);
        $formatted = number_format($amount, CurrencyHelper::decimalPlaces($code));

        if ($code === 'INR') {
            return 'Rs. '.$formatted;
        }

        $sym = CurrencyHelper::symbol($code);
        $pos = CurrencyHelper::all()[$code]['position'] ?? 'before';

        return $pos === 'after' ? $formatted.$sym : $sym.$formatted;
    }

    public static function amountLabel(PosTransaction $tx): string
    {
        return $tx->status === 'completed' ? 'Amount paid' : 'Amount due';
    }

    /**
     * Shared view data for screen, PDF, and email invoice templates.
     *
     * @return array<string, mixed>
     */
    public static function viewContext(PosTransaction $transaction): array
    {
        $transaction->loadMissing(['salon', 'client', 'items', 'staff']);
        $salon = $transaction->salon;
        $client = $transaction->client;
        $tz = $salon ? SalonTime::timezone($salon) : SalonTime::defaultTimezone();
        $invoiceDate = ($transaction->completed_at ?? $transaction->created_at)?->copy()->timezone($tz);
        $clientName = $client ? trim(($client->first_name ?? '').' '.($client->last_name ?? '')) : '';

        return [
            'transaction'   => $transaction,
            'salon'         => $salon,
            'client'        => $client,
            'addressLines'  => self::salonAddressLines($salon),
            'taxLabel'      => self::taxSummaryLabel($transaction),
            'docTitle'      => self::customerDocumentTitle($salon),
            'gstRegistered' => self::isGstRegistered($salon),
            'invoiceDate'   => $invoiceDate,
            'clientName'    => $clientName,
            'logoUrl'       => self::logoUrl($salon),
            'logoDataUri'   => self::logoDataUri($salon),
            'salonInitials' => self::salonInitials($salon),
            'footerNote'    => self::customerFooterNote($salon),
            'amountLabel'   => self::amountLabel($transaction),
            'fmt'           => fn (float $n): string => self::formatAmount($n, $salon),
            'fmtPdf'        => fn (float $n): string => self::formatAmountPdf($n, $salon),
        ];
    }

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
        $tx->loadMissing('salon');
        $pct = self::impliedTaxPercent($tx);
        $gst = self::isGstRegistered($tx->salon);

        if ($pct !== null) {
            return $gst ? ('GST ('.$pct.'%)') : ('Tax ('.$pct.'%)');
        }

        return $gst ? 'GST' : 'Tax';
    }

    /**
     * WhatsApp-friendly invoice text with subtotal, tax breakdown, and business details.
     */
    public static function whatsappBody(PosTransaction $tx): string
    {
        $tx->loadMissing(['salon', 'client', 'items', 'staff']);
        $salon = $tx->salon;
        $client = $tx->client;
        $sym = $salon ? CurrencyHelper::symbol($salon->currency ?? CurrencyHelper::defaultCode()) : CurrencyHelper::symbol(CurrencyHelper::defaultCode());

        $tz = $salon ? SalonTime::timezone($salon) : SalonTime::defaultTimezone();
        $when = ($tx->completed_at ?? $tx->created_at)?->timezone($tz)->format('D j M Y, g:i A T') ?? '';

        $lines = [];
        $lines[] = '*'.self::customerDocumentTitleUpper($salon).'*';
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
        if (self::isGstRegistered($salon)) {
            $lines[] = 'GSTIN: '.$salon->gst_number;
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
        $lines[] = '';
        $lines[] = self::customerFooterNote($salon);

        return implode("\n", $lines);
    }
}
