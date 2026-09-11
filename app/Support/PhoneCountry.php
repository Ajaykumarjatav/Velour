<?php

namespace App\Support;

class PhoneCountry
{
    public const DEFAULT_ISO = 'IN';

    /**
     * iso => [name, dial, min, max, pattern without delimiters, hint]
     *
     * @return array<string, array{name: string, dial: string, min: int, max: int, pattern: string, hint: string}>
     */
    public static function all(): array
    {
        return [
            'IN' => ['name' => 'India', 'dial' => '91', 'min' => 10, 'max' => 10, 'pattern' => '^[6-9]\\d{9}$', 'hint' => '10-digit Indian mobile starting with 6–9'],
            'AE' => ['name' => 'United Arab Emirates', 'dial' => '971', 'min' => 9, 'max' => 9, 'pattern' => '^5\\d{8}$', 'hint' => '9-digit UAE mobile starting with 5'],
            'SA' => ['name' => 'Saudi Arabia', 'dial' => '966', 'min' => 9, 'max' => 9, 'pattern' => '^5\\d{8}$', 'hint' => '9-digit Saudi mobile starting with 5'],
            'QA' => ['name' => 'Qatar', 'dial' => '974', 'min' => 8, 'max' => 8, 'pattern' => '^[3567]\\d{7}$', 'hint' => '8-digit Qatar number'],
            'KW' => ['name' => 'Kuwait', 'dial' => '965', 'min' => 8, 'max' => 8, 'pattern' => '^[569]\\d{7}$', 'hint' => '8-digit Kuwait number'],
            'BH' => ['name' => 'Bahrain', 'dial' => '973', 'min' => 8, 'max' => 8, 'pattern' => '^[3679]\\d{7}$', 'hint' => '8-digit Bahrain number'],
            'OM' => ['name' => 'Oman', 'dial' => '968', 'min' => 8, 'max' => 8, 'pattern' => '^[79]\\d{7}$', 'hint' => '8-digit Oman number'],
            'PK' => ['name' => 'Pakistan', 'dial' => '92', 'min' => 10, 'max' => 10, 'pattern' => '^3\\d{9}$', 'hint' => '10-digit Pakistan mobile starting with 3'],
            'BD' => ['name' => 'Bangladesh', 'dial' => '880', 'min' => 10, 'max' => 10, 'pattern' => '^1\\d{9}$', 'hint' => '10-digit Bangladesh mobile starting with 1'],
            'NP' => ['name' => 'Nepal', 'dial' => '977', 'min' => 10, 'max' => 10, 'pattern' => '^9\\d{9}$', 'hint' => '10-digit Nepal mobile starting with 9'],
            'LK' => ['name' => 'Sri Lanka', 'dial' => '94', 'min' => 9, 'max' => 9, 'pattern' => '^7\\d{8}$', 'hint' => '9-digit Sri Lanka mobile starting with 7'],
            'US' => ['name' => 'United States', 'dial' => '1', 'min' => 10, 'max' => 10, 'pattern' => '^[2-9]\\d{2}[2-9]\\d{6}$', 'hint' => '10-digit US number'],
            'CA' => ['name' => 'Canada', 'dial' => '1', 'min' => 10, 'max' => 10, 'pattern' => '^[2-9]\\d{2}[2-9]\\d{6}$', 'hint' => '10-digit Canadian number'],
            'GB' => ['name' => 'United Kingdom', 'dial' => '44', 'min' => 10, 'max' => 10, 'pattern' => '^[1-9]\\d{9}$', 'hint' => '10-digit UK number without the leading 0'],
            'SG' => ['name' => 'Singapore', 'dial' => '65', 'min' => 8, 'max' => 8, 'pattern' => '^[3689]\\d{7}$', 'hint' => '8-digit Singapore number'],
            'AU' => ['name' => 'Australia', 'dial' => '61', 'min' => 9, 'max' => 9, 'pattern' => '^[4]\\d{8}$', 'hint' => '9-digit Australian mobile starting with 4'],
            'NZ' => ['name' => 'New Zealand', 'dial' => '64', 'min' => 8, 'max' => 10, 'pattern' => '^2\\d{7,9}$', 'hint' => 'New Zealand mobile starting with 2'],
            'MY' => ['name' => 'Malaysia', 'dial' => '60', 'min' => 9, 'max' => 10, 'pattern' => '^1\\d{8,9}$', 'hint' => '9–10 digit Malaysia mobile starting with 1'],
            'ID' => ['name' => 'Indonesia', 'dial' => '62', 'min' => 9, 'max' => 12, 'pattern' => '^8\\d{8,11}$', 'hint' => 'Indonesian mobile starting with 8'],
            'PH' => ['name' => 'Philippines', 'dial' => '63', 'min' => 10, 'max' => 10, 'pattern' => '^9\\d{9}$', 'hint' => '10-digit Philippines mobile starting with 9'],
            'VN' => ['name' => 'Vietnam', 'dial' => '84', 'min' => 9, 'max' => 9, 'pattern' => '^[3-9]\\d{8}$', 'hint' => '9-digit Vietnam mobile'],
            'TH' => ['name' => 'Thailand', 'dial' => '66', 'min' => 9, 'max' => 9, 'pattern' => '^[6-9]\\d{8}$', 'hint' => '9-digit Thailand mobile'],
            'JP' => ['name' => 'Japan', 'dial' => '81', 'min' => 10, 'max' => 10, 'pattern' => '^[789]0\\d{8}$', 'hint' => '10-digit Japan mobile'],
            'KR' => ['name' => 'South Korea', 'dial' => '82', 'min' => 9, 'max' => 10, 'pattern' => '^1\\d{8,9}$', 'hint' => 'South Korea mobile starting with 1'],
            'CN' => ['name' => 'China', 'dial' => '86', 'min' => 11, 'max' => 11, 'pattern' => '^1[3-9]\\d{9}$', 'hint' => '11-digit China mobile'],
            'HK' => ['name' => 'Hong Kong', 'dial' => '852', 'min' => 8, 'max' => 8, 'pattern' => '^[4-9]\\d{7}$', 'hint' => '8-digit Hong Kong number'],
            'TW' => ['name' => 'Taiwan', 'dial' => '886', 'min' => 9, 'max' => 9, 'pattern' => '^9\\d{8}$', 'hint' => '9-digit Taiwan mobile starting with 9'],
            'DE' => ['name' => 'Germany', 'dial' => '49', 'min' => 10, 'max' => 11, 'pattern' => '^1[5-7]\\d{8,9}$', 'hint' => 'German mobile starting with 15, 16, or 17'],
            'FR' => ['name' => 'France', 'dial' => '33', 'min' => 9, 'max' => 9, 'pattern' => '^[67]\\d{8}$', 'hint' => '9-digit French mobile starting with 6 or 7'],
            'ES' => ['name' => 'Spain', 'dial' => '34', 'min' => 9, 'max' => 9, 'pattern' => '^[67]\\d{8}$', 'hint' => '9-digit Spanish mobile starting with 6 or 7'],
            'IT' => ['name' => 'Italy', 'dial' => '39', 'min' => 9, 'max' => 10, 'pattern' => '^3\\d{8,9}$', 'hint' => 'Italian mobile starting with 3'],
            'NL' => ['name' => 'Netherlands', 'dial' => '31', 'min' => 9, 'max' => 9, 'pattern' => '^6\\d{8}$', 'hint' => '9-digit Dutch mobile starting with 6'],
            'PT' => ['name' => 'Portugal', 'dial' => '351', 'min' => 9, 'max' => 9, 'pattern' => '^9\\d{8}$', 'hint' => '9-digit Portugal mobile starting with 9'],
            'IE' => ['name' => 'Ireland', 'dial' => '353', 'min' => 9, 'max' => 9, 'pattern' => '^8\\d{8}$', 'hint' => '9-digit Irish mobile starting with 8'],
            'BE' => ['name' => 'Belgium', 'dial' => '32', 'min' => 9, 'max' => 9, 'pattern' => '^4\\d{8}$', 'hint' => '9-digit Belgian mobile starting with 4'],
            'AT' => ['name' => 'Austria', 'dial' => '43', 'min' => 10, 'max' => 13, 'pattern' => '^6\\d{9,12}$', 'hint' => 'Austrian mobile starting with 6'],
            'CH' => ['name' => 'Switzerland', 'dial' => '41', 'min' => 9, 'max' => 9, 'pattern' => '^7\\d{8}$', 'hint' => '9-digit Swiss mobile starting with 7'],
            'SE' => ['name' => 'Sweden', 'dial' => '46', 'min' => 9, 'max' => 9, 'pattern' => '^7\\d{8}$', 'hint' => '9-digit Swedish mobile starting with 7'],
            'NO' => ['name' => 'Norway', 'dial' => '47', 'min' => 8, 'max' => 8, 'pattern' => '^[49]\\d{7}$', 'hint' => '8-digit Norwegian number'],
            'DK' => ['name' => 'Denmark', 'dial' => '45', 'min' => 8, 'max' => 8, 'pattern' => '^[2-9]\\d{7}$', 'hint' => '8-digit Danish number'],
            'PL' => ['name' => 'Poland', 'dial' => '48', 'min' => 9, 'max' => 9, 'pattern' => '^[4-8]\\d{8}$', 'hint' => '9-digit Polish mobile'],
            'ZA' => ['name' => 'South Africa', 'dial' => '27', 'min' => 9, 'max' => 9, 'pattern' => '^[6-8]\\d{8}$', 'hint' => '9-digit South Africa mobile'],
            'NG' => ['name' => 'Nigeria', 'dial' => '234', 'min' => 10, 'max' => 10, 'pattern' => '^[789]\\d{9}$', 'hint' => '10-digit Nigeria mobile'],
            'KE' => ['name' => 'Kenya', 'dial' => '254', 'min' => 9, 'max' => 9, 'pattern' => '^7\\d{8}$', 'hint' => '9-digit Kenya mobile starting with 7'],
            'GH' => ['name' => 'Ghana', 'dial' => '233', 'min' => 9, 'max' => 9, 'pattern' => '^[25]\\d{8}$', 'hint' => '9-digit Ghana mobile'],
            'BR' => ['name' => 'Brazil', 'dial' => '55', 'min' => 10, 'max' => 11, 'pattern' => '^[1-9]\\d{9,10}$', 'hint' => '10–11 digit Brazil number'],
            'MX' => ['name' => 'Mexico', 'dial' => '52', 'min' => 10, 'max' => 10, 'pattern' => '^[1-9]\\d{9}$', 'hint' => '10-digit Mexico number'],
            'AR' => ['name' => 'Argentina', 'dial' => '54', 'min' => 10, 'max' => 10, 'pattern' => '^\\d{10}$', 'hint' => '10-digit Argentina number'],
            'CL' => ['name' => 'Chile', 'dial' => '56', 'min' => 9, 'max' => 9, 'pattern' => '^9\\d{8}$', 'hint' => '9-digit Chile mobile starting with 9'],
            'CO' => ['name' => 'Colombia', 'dial' => '57', 'min' => 10, 'max' => 10, 'pattern' => '^3\\d{9}$', 'hint' => '10-digit Colombia mobile starting with 3'],
            'TR' => ['name' => 'Turkey', 'dial' => '90', 'min' => 10, 'max' => 10, 'pattern' => '^5\\d{9}$', 'hint' => '10-digit Turkey mobile starting with 5'],
            'EG' => ['name' => 'Egypt', 'dial' => '20', 'min' => 10, 'max' => 10, 'pattern' => '^1\\d{9}$', 'hint' => '10-digit Egypt mobile starting with 1'],
            'RU' => ['name' => 'Russia', 'dial' => '7', 'min' => 10, 'max' => 10, 'pattern' => '^9\\d{9}$', 'hint' => '10-digit Russia mobile starting with 9'],
        ];
    }

    /** @return list<string> */
    public static function isoCodes(): array
    {
        return array_keys(self::all());
    }

    /**
     * India first, then A–Z by country name.
     *
     * @return array<string, array{name: string, dial: string, min: int, max: int, pattern: string, hint: string}>
     */
    public static function selectList(): array
    {
        $all = self::all();
        $india = ['IN' => $all['IN']];
        unset($all['IN']);
        uasort($all, fn (array $a, array $b): int => strcmp($a['name'], $b['name']));

        return $india + $all;
    }

    /** @return array{iso: string, dial: string, national: string} */
    public static function split(?string $stored): array
    {
        $default = self::all()[self::DEFAULT_ISO];
        $digits = preg_replace('/\D+/', '', (string) $stored) ?? '';

        if ($digits === '') {
            return ['iso' => self::DEFAULT_ISO, 'dial' => $default['dial'], 'national' => ''];
        }

        $ranked = self::all();
        uasort($ranked, fn (array $a, array $b): int => strlen($b['dial']) <=> strlen($a['dial']));

        foreach ($ranked as $iso => $meta) {
            $dial = $meta['dial'];
            if (! str_starts_with($digits, $dial)) {
                continue;
            }
            $national = substr($digits, strlen($dial));
            $len = strlen($national);
            if ($len >= $meta['min'] && $len <= $meta['max'] && self::matchesPattern($national, $meta['pattern'])) {
                return ['iso' => $iso, 'dial' => $dial, 'national' => $national];
            }
        }

        $len = strlen($digits);
        if ($len >= $default['min'] && $len <= $default['max']) {
            return ['iso' => self::DEFAULT_ISO, 'dial' => $default['dial'], 'national' => $digits];
        }

        return ['iso' => self::DEFAULT_ISO, 'dial' => $default['dial'], 'national' => $digits];
    }

    public static function normalizeNational(string $iso, string $national): string
    {
        $meta = self::all()[$iso] ?? self::all()[self::DEFAULT_ISO];
        $digits = preg_replace('/\D+/', '', $national) ?? '';
        $dial = $meta['dial'];

        if ($digits !== '' && str_starts_with($digits, $dial)) {
            $rest = substr($digits, strlen($dial));
            if (strlen($rest) >= $meta['min'] && strlen($rest) <= $meta['max']) {
                $digits = $rest;
            }
        }

        if ($digits !== '' && $digits[0] === '0') {
            $stripped = ltrim($digits, '0');
            if ($stripped !== '' && strlen($stripped) >= $meta['min'] && strlen($stripped) <= $meta['max']) {
                $digits = $stripped;
            }
        }

        if (strlen($digits) > $meta['max']) {
            $digits = substr($digits, 0, $meta['max']);
        }

        return $digits;
    }

    public static function isValid(string $iso, string $national): bool
    {
        $meta = self::all()[$iso] ?? null;
        if ($meta === null) {
            return false;
        }

        $digits = preg_replace('/\D+/', '', $national) ?? '';
        $len = strlen($digits);
        if ($len < $meta['min'] || $len > $meta['max']) {
            return false;
        }

        return self::matchesPattern($digits, $meta['pattern']);
    }

    public static function toE164(string $iso, string $national): string
    {
        $meta = self::all()[$iso] ?? self::all()[self::DEFAULT_ISO];
        $digits = self::normalizeNational($iso, $national);

        return '+'.$meta['dial'].$digits;
    }

    public static function validationMessage(string $iso): string
    {
        $meta = self::all()[$iso] ?? self::all()[self::DEFAULT_ISO];

        return 'Enter a valid '.$meta['name'].' phone number ('.$meta['hint'].').';
    }

    /**
     * @return array{default_iso: string, countries: array<string, array{dial: string, min: int, max: int, pattern: string, hint: string}>, timezone_iso: array<string, string>}
     */
    public static function clientConfig(): array
    {
        $countries = [];
        foreach (self::all() as $iso => $meta) {
            $countries[$iso] = [
                'name' => $meta['name'],
                'dial' => $meta['dial'],
                'min' => $meta['min'],
                'max' => $meta['max'],
                'pattern' => $meta['pattern'],
                'hint' => $meta['hint'],
                'message' => 'Enter a valid '.$meta['name'].' phone number ('.$meta['hint'].').',
            ];
        }

        return [
            'default_iso' => self::DEFAULT_ISO,
            'countries' => $countries,
            'timezone_iso' => [
                'Asia/Kolkata' => 'IN',
                'Asia/Calcutta' => 'IN',
                'Asia/Dubai' => 'AE',
                'Asia/Riyadh' => 'SA',
                'Asia/Qatar' => 'QA',
                'Asia/Kuwait' => 'KW',
                'Asia/Bahrain' => 'BH',
                'Asia/Muscat' => 'OM',
                'Asia/Karachi' => 'PK',
                'Asia/Dhaka' => 'BD',
                'Asia/Kathmandu' => 'NP',
                'Asia/Colombo' => 'LK',
                'Europe/London' => 'GB',
                'America/New_York' => 'US',
                'America/Chicago' => 'US',
                'America/Denver' => 'US',
                'America/Los_Angeles' => 'US',
                'America/Toronto' => 'CA',
                'America/Vancouver' => 'CA',
                'Asia/Singapore' => 'SG',
                'Australia/Sydney' => 'AU',
                'Pacific/Auckland' => 'NZ',
                'Asia/Kuala_Lumpur' => 'MY',
                'Asia/Jakarta' => 'ID',
                'Asia/Manila' => 'PH',
                'Asia/Ho_Chi_Minh' => 'VN',
                'Asia/Bangkok' => 'TH',
                'Asia/Tokyo' => 'JP',
                'Asia/Seoul' => 'KR',
                'Asia/Shanghai' => 'CN',
                'Asia/Hong_Kong' => 'HK',
                'Asia/Taipei' => 'TW',
                'Europe/Berlin' => 'DE',
                'Europe/Paris' => 'FR',
                'Europe/Madrid' => 'ES',
                'Europe/Rome' => 'IT',
                'Europe/Amsterdam' => 'NL',
                'Europe/Lisbon' => 'PT',
                'Europe/Dublin' => 'IE',
                'Europe/Brussels' => 'BE',
                'Europe/Vienna' => 'AT',
                'Europe/Zurich' => 'CH',
                'Europe/Stockholm' => 'SE',
                'Europe/Oslo' => 'NO',
                'Europe/Copenhagen' => 'DK',
                'Europe/Warsaw' => 'PL',
                'Africa/Johannesburg' => 'ZA',
                'Africa/Lagos' => 'NG',
                'Africa/Nairobi' => 'KE',
                'Africa/Accra' => 'GH',
                'America/Sao_Paulo' => 'BR',
                'America/Mexico_City' => 'MX',
                'America/Argentina/Buenos_Aires' => 'AR',
                'America/Santiago' => 'CL',
                'America/Bogota' => 'CO',
                'Europe/Istanbul' => 'TR',
                'Africa/Cairo' => 'EG',
                'Europe/Moscow' => 'RU',
            ],
        ];
    }

    /** @return array<string, string> timezone => ISO */
    public static function timezoneIsoMap(): array
    {
        return self::clientConfig()['timezone_iso'];
    }

    public static function isoFromTimezone(?string $timezone): string
    {
        $timezone = trim((string) $timezone);
        $map = self::timezoneIsoMap();

        if ($timezone !== '' && isset($map[$timezone])) {
            return $map[$timezone];
        }

        if ($timezone !== '') {
            try {
                $location = (new \DateTimeZone($timezone))->getLocation();
                $code = is_array($location) ? strtoupper((string) ($location['country_code'] ?? '')) : '';
                if ($code !== '' && $code !== '??' && isset(self::all()[$code])) {
                    return $code;
                }
            } catch (\Exception) {
                // Invalid timezone identifier — fall through to default.
            }

            if (str_starts_with($timezone, 'Asia/Kolkata') || str_starts_with($timezone, 'Asia/Calcutta')) {
                return 'IN';
            }
            if (str_starts_with($timezone, 'Asia/Dubai')) {
                return 'AE';
            }
            if (str_starts_with($timezone, 'Europe/London')) {
                return 'GB';
            }
        }

        return self::DEFAULT_ISO;
    }

    /**
     * Store E.164 using the timezone's country code when the national number fits that country.
     * Numbers that already have a valid different international country code are left unchanged.
     */
    public static function applyTimezoneCountry(?string $stored, ?string $timezone): ?string
    {
        $raw = trim((string) $stored);
        if ($raw === '') {
            return $stored === null ? null : '';
        }

        $iso = self::isoFromTimezone($timezone);
        if (! isset(self::all()[$iso])) {
            $iso = self::DEFAULT_ISO;
        }

        $national = self::normalizeNational($iso, $raw);
        if (self::isValid($iso, $national)) {
            return self::toE164($iso, $national);
        }

        $split = self::split($raw);
        $looksE164 = str_starts_with($raw, '+');
        if ($looksE164 && self::isValid($split['iso'], $split['national'])) {
            return self::toE164($split['iso'], $split['national']);
        }

        return $stored;
    }

    public static function countryFieldName(string $phoneField): string
    {
        return preg_replace('/([^.]+)$/', '$1_country', $phoneField);
    }

    /**
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>
     */
    public static function merge(array $rules, string $phoneField = 'phone', bool $required = false): array
    {
        unset($rules[$phoneField]);

        return array_merge($rules, self::rules($phoneField, $required));
    }

    /**
     * Drop country-code companion keys so mass-assignment does not see them.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function exceptCountryFields(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = self::exceptCountryFields($value);
            }
            if (is_string($key) && str_ends_with($key, '_country') && $key !== 'country') {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function rules(string $phoneField = 'phone', bool $required = false): array
    {
        $countryField = self::countryFieldName($phoneField);

        return [
            $countryField => ['nullable', 'string', 'size:2', \Illuminate\Validation\Rule::in(self::isoCodes())],
            $phoneField => [
                $required ? 'required' : 'nullable',
                'string',
                'max:20',
                self::rule(),
            ],
        ];
    }

    public static function rule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $raw = trim((string) $value);
            if ($raw === '') {
                return;
            }

            $countryAttribute = self::countryFieldName($attribute);
            $iso = strtoupper((string) data_get(request()->all(), $countryAttribute, ''));
            $split = self::split($raw);
            if ($iso === '' || ! in_array($iso, self::isoCodes(), true)) {
                $iso = $split['iso'];
            }

            $digits = preg_replace('/\D+/', '', $raw) ?? '';
            $looksE164 = str_starts_with($raw, '+') || strlen($digits) > 11;
            $national = $looksE164
                ? $split['national']
                : self::normalizeNational($iso, $raw);

            if ($national === '') {
                return;
            }

            if (! self::isValid($iso, $national)) {
                $fail(self::validationMessage($iso));
            }
        };
    }

    private static function matchesPattern(string $digits, string $pattern): bool
    {
        return (bool) preg_match('/'.$pattern.'/', $digits);
    }
}
