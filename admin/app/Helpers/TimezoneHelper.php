<?php

namespace App\Helpers;

class TimezoneHelper
{
    /**
     * All timezones grouped by region for a clean dropdown.
     * Returns: ['Region' => ['Timezone/Name' => 'Timezone/Name (UTC+X)', ...], ...]
     */
    public static function grouped(): array
    {
        $groups = [];
        $zones  = \DateTimeZone::listIdentifiers();

        foreach ($zones as $tz) {
            $parts  = explode('/', $tz, 2);
            $region = $parts[0];

            // Skip generic/deprecated zones
            if (in_array($region, ['Etc', 'SystemV', 'US', 'Canada', 'Mexico', 'Chile', 'Brazil', 'Cuba', 'Egypt', 'Eire', 'GB', 'GMT', 'Greenwich', 'Hongkong', 'Iceland', 'Iran', 'Israel', 'Jamaica', 'Japan', 'Kwajalein', 'Libya', 'MET', 'MST', 'MST7MDT', 'NZ', 'NZ-CHAT', 'Navajo', 'PRC', 'PST8PDT', 'Poland', 'Portugal', 'ROC', 'ROK', 'Singapore', 'Turkey', 'UCT', 'UTC', 'Universal', 'W-SU', 'WET', 'Zulu'])) {
                continue;
            }

            try {
                $dt     = new \DateTime('now', new \DateTimeZone($tz));
                $offset = $dt->getOffset();
                $hours  = intdiv(abs($offset), 3600);
                $mins   = (abs($offset) % 3600) / 60;
                $sign   = $offset >= 0 ? '+' : '-';
                $label  = str_replace('_', ' ', $tz) . ' (UTC' . $sign . sprintf('%02d:%02d', $hours, $mins) . ')';
                $groups[$region][$tz] = $label;
            } catch (\Exception $e) {
                continue;
            }
        }

        ksort($groups);
        return $groups;
    }

    /** Flat list for validation: just the timezone identifiers */
    public static function all(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    /** Flat select list: tz => label */
    public static function selectList(): array
    {
        $list = [];
        foreach (static::grouped() as $region => $zones) {
            foreach ($zones as $tz => $label) {
                $list[$tz] = $label;
            }
        }
        return $list;
    }
}
