<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\Service;
use App\Models\Staff;

class RegistrationStaff
{
    /**
     * Create optional team members after the salon (and starter services) exist.
     *
     * @param  list<array<string, mixed>>  $members
     */
    public static function seed(Salon $salon, array $members): void
    {
        $members = array_values(array_filter(
            $members,
            fn ($row) => is_array($row) && trim((string) ($row['name'] ?? '')) !== ''
        ));

        if ($members === []) {
            return;
        }

        $serviceIds = Service::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->orderBy('sort_order')
            ->pluck('id')
            ->all();

        $maxSort = (int) Staff::withoutGlobalScopes()->where('salon_id', $salon->id)->max('sort_order');

        $defaultColors = ['#7C3AED', '#EC4899', '#0EA5E9', '#14B8A6', '#F59E0B', '#84CC16'];

        foreach ($members as $i => $row) {
            $name = trim((string) $row['name']);
            $parts = preg_split('/\s+/u', $name, 2, PREG_SPLIT_NO_EMPTY) ?: [];
            $first = (string) ($parts[0] ?? '');
            $last = (string) ($parts[1] ?? '');

            $initials = mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));
            if ($initials === '') {
                $initials = '??';
            }
            $initials = mb_substr($initials, 0, 3);

            $comm = isset($row['commission_rate']) ? (float) $row['commission_rate'] : 0.0;
            $comm = max(0.0, min(100.0, $comm));

            $color = isset($row['color']) && is_string($row['color']) && preg_match('/^#[0-9A-Fa-f]{6}$/', $row['color'])
                ? $row['color']
                : $defaultColors[$i % count($defaultColors)];

            $assign = ! empty($row['assign_services']);

            $staff = Staff::withoutGlobalScopes()->create([
                'salon_id'        => $salon->id,
                'user_id'         => null,
                'first_name'      => $first,
                'last_name'       => $last,
                'email'           => self::optionalString($row['email'] ?? null),
                'phone'           => self::optionalString($row['phone'] ?? null),
                'role'            => $row['role'],
                'bio'             => self::optionalString($row['bio'] ?? null),
                'color'           => $color,
                'commission_rate' => $comm,
                'is_active'       => true,
                'bookable_online' => true,
                'initials'        => $initials,
                'sort_order'      => ++$maxSort,
            ]);

            if ($assign && $serviceIds !== []) {
                $staff->services()->sync($serviceIds);
            }
        }
    }

    private static function optionalString(mixed $v): ?string
    {
        if ($v === null) {
            return null;
        }
        $s = trim((string) $v);

        return $s === '' ? null : $s;
    }
}
