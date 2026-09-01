<?php

namespace App\Support;

final class ExpenseCategoryUi
{
    /** @return array{icon: string, badge: string, chart: string, slug: string} */
    public static function meta(?string $slug, ?string $name = null): array
    {
        $slug = $slug ?: self::guessSlug($name);

        return match ($slug) {
            'salary' => [
                'slug' => 'salary',
                'icon' => '💰',
                'badge' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
                'chart' => '#F59E0B',
            ],
            'inventory' => [
                'slug' => 'inventory',
                'icon' => '📦',
                'badge' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                'chart' => '#3B82F6',
            ],
            'rent' => [
                'slug' => 'rent',
                'icon' => '🏠',
                'badge' => 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-200',
                'chart' => '#8B5CF6',
            ],
            'utilities' => [
                'slug' => 'utilities',
                'icon' => '⚡',
                'badge' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
                'chart' => '#EAB308',
            ],
            'marketing' => [
                'slug' => 'marketing',
                'icon' => '📢',
                'badge' => 'bg-pink-100 text-pink-800 dark:bg-pink-900/40 dark:text-pink-200',
                'chart' => '#EC4899',
            ],
            'equipment' => [
                'slug' => 'equipment',
                'icon' => '🪑',
                'badge' => 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-200',
                'chart' => '#14B8A6',
            ],
            'maintenance' => [
                'slug' => 'maintenance',
                'icon' => '🛠',
                'badge' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200',
                'chart' => '#F97316',
            ],
            default => [
                'slug' => 'other',
                'icon' => '📋',
                'badge' => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300',
                'chart' => '#6B7280',
            ],
        };
    }

    public static function amountTone(float $amount, float $avgExpense): string
    {
        if ($avgExpense <= 0) {
            return 'text-heading';
        }

        return $amount > $avgExpense * 1.5
            ? 'text-rose-600 dark:text-rose-400'
            : 'text-heading';
    }

    public static function vendorInitials(string $vendor): string
    {
        $parts = preg_split('/\s+/', trim($vendor)) ?: [];
        if (count($parts) >= 2) {
            return strtoupper(mb_substr($parts[0], 0, 1).mb_substr($parts[1], 0, 1));
        }

        return strtoupper(mb_substr($vendor, 0, 2));
    }

    /** Smart defaults when a category is selected. */
    public static function smartDefaults(?string $slug): array
    {
        return match ($slug) {
            'salary' => [
                'payment_method' => 'bank_transfer',
                'staff_required' => true,
                'highlight_vendor' => false,
            ],
            'inventory' => [
                'payment_method' => null,
                'staff_required' => false,
                'highlight_vendor' => true,
            ],
            'rent' => [
                'payment_method' => 'bank_transfer',
                'staff_required' => false,
                'highlight_vendor' => true,
            ],
            'utilities' => [
                'payment_method' => 'upi',
                'staff_required' => false,
                'highlight_vendor' => true,
            ],
            default => [
                'payment_method' => null,
                'staff_required' => false,
                'highlight_vendor' => false,
            ],
        };
    }

    private static function guessSlug(?string $name): string
    {
        if (! $name) {
            return 'other';
        }

        $lower = strtolower($name);
        foreach (['salary', 'inventory', 'rent', 'utilities', 'marketing', 'equipment', 'maintenance'] as $key) {
            if (str_contains($lower, $key)) {
                return $key;
            }
        }

        return 'other';
    }
}
