<?php

namespace App\Support;

final class ExpensePaymentUi
{
    /** @return array{icon: string, label: string} */
    public static function meta(string $key): array
    {
        return match ($key) {
            'cash' => ['icon' => '💵', 'label' => 'Cash'],
            'bank_transfer' => ['icon' => '🏦', 'label' => 'Bank Transfer'],
            'card' => ['icon' => '💳', 'label' => 'Card'],
            'upi' => ['icon' => '📱', 'label' => 'UPI'],
            'cheque' => ['icon' => '📝', 'label' => 'Cheque'],
            default => ['icon' => '💰', 'label' => ucfirst(str_replace('_', ' ', $key))],
        };
    }

    /** @return array<string, array{icon: string, label: string}> */
    public static function all(): array
    {
        $out = [];
        foreach (\App\Models\Expense::PAYMENT_METHODS as $key => $label) {
            $out[$key] = self::meta($key);
            $out[$key]['label'] = $label;
        }

        return $out;
    }
}
