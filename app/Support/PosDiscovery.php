<?php

namespace App\Support;

use App\Models\PosTransaction;
use App\Models\Salon;
use App\Models\SalonSetting;
use Illuminate\Support\Facades\Auth;

/**
 * First-run POS education: dashboard, sidebar, and the till.
 * Hidden after this user dismisses it, or after the salon completes a sale.
 */
final class PosDiscovery
{
    public const SETTING_KEY = 'pos_intro_dismissed_user_ids';

    /** @var array<int, bool> */
    private static array $hasSaleCache = [];

    public static function shouldShowForCurrentUser(?Salon $salon): bool
    {
        if ($salon === null || AuthPanel::isAdminStoreBrowse()) {
            return false;
        }

        $user = Auth::user();
        if ($user === null || ! SidebarNav::show($user, 'pos')) {
            return false;
        }

        if (self::currentUserHasDismissed($salon)) {
            return false;
        }

        return ! self::salonHasTakenPayment($salon);
    }

    public static function salonHasTakenPayment(Salon $salon): bool
    {
        $id = (int) $salon->id;
        if (array_key_exists($id, self::$hasSaleCache)) {
            return self::$hasSaleCache[$id];
        }

        return self::$hasSaleCache[$id] = PosTransaction::withoutGlobalScopes()
            ->where('salon_id', $id)
            ->whereIn('status', ['completed', 'refunded', 'partial_refund'])
            ->exists();
    }

    public static function dismissForCurrentUser(Salon $salon): void
    {
        if (AuthPanel::isAdminStoreBrowse()) {
            return;
        }

        $userId = (int) (Auth::id() ?? 0);
        if ($userId < 1) {
            return;
        }

        $ids = self::dismissedUserIds($salon);
        if (in_array($userId, $ids, true)) {
            return;
        }

        $ids[] = $userId;

        SalonSetting::withoutGlobalScopes()->updateOrCreate(
            ['salon_id' => $salon->id, 'key' => self::SETTING_KEY],
            ['value' => json_encode(array_values($ids)), 'type' => 'json']
        );
    }

    public static function currentUserHasDismissed(Salon $salon): bool
    {
        $userId = (int) (Auth::id() ?? 0);
        if ($userId < 1) {
            return false;
        }

        return in_array($userId, self::dismissedUserIds($salon), true);
    }

    /** @return list<int> */
    private static function dismissedUserIds(Salon $salon): array
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
