<?php

namespace App\Support;

use App\Models\Salon;

final class StorefrontLegal
{
    /**
     * Placeholder values for the tenant-website Terms template.
     *
     * @return array<string, string>
     */
    public static function placeholders(Salon $salon): array
    {
        $website = StorefrontUrl::website($salon);
        $address = trim(implode(', ', array_filter([
            $salon->address_line1,
            $salon->address_line2,
            $salon->city,
            $salon->county,
            $salon->postcode,
            $salon->country,
        ])));

        $categories = $salon->serviceCategories()
            ->orderBy('id')
            ->pluck('name')
            ->filter()
            ->take(4)
            ->values()
            ->all();

        $hours = (int) ($salon->cancellation_hours ?? 0);
        $cancelPeriod = $hours > 0
            ? $hours.' hours before the appointment'
            : 'as shown when you book';

        $currency = strtoupper((string) ($salon->currency ?: 'INR'));
        $type = $salon->businessType?->name ?? 'Beauty and wellness';
        $tagline = $salon->description
            ? (strlen($salon->description) > 160 ? substr($salon->description, 0, 157).'…' : $salon->description)
            : 'Appointments, services, and care from our studio.';

        $dash = 'as listed on this website';

        return [
            'Last Updated Date' => now()->format('j F Y'),
            'Business Name' => (string) $salon->name,
            'Business Type' => $type,
            'Business Address' => $address !== '' ? $address : $dash,
            'Business Phone' => trim((string) $salon->phone) !== '' ? (string) $salon->phone : $dash,
            'Business Email' => trim((string) $salon->email) !== '' ? (string) $salon->email : $dash,
            'Website URL' => $website,
            'Business City' => trim((string) $salon->city) !== '' ? (string) $salon->city : 'the business location',
            'Business State' => trim((string) ($salon->county ?? '')) !== '' ? (string) $salon->county : 'India',
            'Business Tagline' => $tagline,
            'Service Category 1' => $categories[0] ?? 'Services listed on this website',
            'Service Category 2' => $categories[1] ?? '',
            'Service Category 3' => $categories[2] ?? '',
            'Service Category 4' => $categories[3] ?? '',
            'Cancellation Period' => $cancelPeriod,
            'Cancellation Fee' => 'as communicated at booking, where a fee applies',
            'Rescheduling Policy' => 'Reschedule with the same notice period as cancellations, subject to availability.',
            'Currency' => $currency,
            'Payment Method 1' => 'card',
            'Payment Method 2' => 'UPI / wallets',
            'Payment Method 3' => 'other methods enabled at checkout',
            'Refund Policy Summary' => 'Refunds follow the policy shown at booking and applicable law.',
            'Gift Card Policy' => 'as stated when the gift card or voucher is purchased',
            'Privacy Policy URL' => StorefrontUrl::legal($salon, 'privacy'),
            'Terms URL' => StorefrontUrl::legal($salon, 'terms'),
            'Support Hours' => 'during published opening hours',
            'Privacy Contact Name or Designation' => 'Privacy / grievance contact',
            'Privacy Email' => trim((string) $salon->email) !== '' ? (string) $salon->email : $dash,
        ];
    }
}
