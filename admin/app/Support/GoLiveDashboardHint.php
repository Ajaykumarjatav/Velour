<?php

namespace App\Support;

use App\Models\Salon;
use App\Models\SalonSetting;
use App\Support\AuthPanel;
use Illuminate\Support\Facades\Auth;

/**
 * Dashboard website hint: hide after the user opens Go Live once.
 */
final class GoLiveDashboardHint
{
    public const SETTING_KEY = 'go_live_page_visited_user_ids';

    public static function markCurrentUserVisited(Salon $salon): void
    {
        if (AuthPanel::isAdminStoreBrowse()) {
            return;
        }

        $userId = (int) (Auth::id() ?? 0);
        if ($userId < 1) {
            return;
        }

        $ids = self::visitedUserIds($salon);
        if (in_array($userId, $ids, true)) {
            return;
        }

        $ids[] = $userId;

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => self::SETTING_KEY],
            ['value' => json_encode(array_values($ids)), 'type' => 'json']
        );
    }

    public static function currentUserHasVisited(Salon $salon): bool
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId < 1) {
            return false;
        }

        return in_array($userId, self::visitedUserIds($salon), true);
    }

    /** @return list<int> */
    private static function visitedUserIds(Salon $salon): array
    {
        $raw = SalonSetting::withoutGlobalScopes()
            ->where('salon_id', $salon->id)
            ->where('key', self::SETTING_KEY)
            ->value('value');

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;
        if (! is_array($decoded)) {
            return [];
        }

        return array_values(array_unique(array_map('intval', $decoded)));
    }
}
