<?php

namespace App\Support;

/**
 * Standard 15-minute appointment slot grid for admin create/edit pickers.
 */
final class AppointmentSlotGrid
{
    /** @return list<string> HH:MM from 09:00 through 17:30 */
    public static function allTimes(): array
    {
        $times = [];
        for ($hour = 9; $hour <= 17; $hour++) {
            for ($minute = 0; $minute < 60; $minute += 15) {
                if ($hour === 17 && $minute > 30) {
                    break 2;
                }
                $times[] = sprintf('%02d:%02d', $hour, $minute);
            }
        }

        return $times;
    }

    /**
     * @return array{morning: list<string>, afternoon: list<string>, evening: list<string>}
     */
    public static function byPeriod(): array
    {
        $morning = [];
        $afternoon = [];
        $evening = [];

        foreach (self::allTimes() as $time) {
            $hour = (int) substr($time, 0, 2);
            if ($hour < 12) {
                $morning[] = $time;
            } elseif ($hour < 16) {
                $afternoon[] = $time;
            } else {
                $evening[] = $time;
            }
        }

        return [
            'morning'   => $morning,
            'afternoon' => $afternoon,
            'evening'   => $evening,
        ];
    }
}
