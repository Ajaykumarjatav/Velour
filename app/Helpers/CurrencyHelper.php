<?php

namespace App\Helpers;

class CurrencyHelper
{
    /**
     * All supported currencies: code => [name, symbol, position]
     * position: 'before' | 'after'
     */
    public static function all(): array
    {
        return [
            'AED' => ['name' => 'UAE Dirham',              'symbol' => 'د.إ', 'position' => 'before'],
            'AFN' => ['name' => 'Afghan Afghani',          'symbol' => '؋',   'position' => 'before'],
            'ALL' => ['name' => 'Albanian Lek',            'symbol' => 'L',   'position' => 'after'],
            'AMD' => ['name' => 'Armenian Dram',           'symbol' => '֏',   'position' => 'after'],
            'ANG' => ['name' => 'Netherlands Antillean Guilder', 'symbol' => 'ƒ', 'position' => 'before'],
            'AOA' => ['name' => 'Angolan Kwanza',          'symbol' => 'Kz',  'position' => 'after'],
            'ARS' => ['name' => 'Argentine Peso',          'symbol' => '$',   'position' => 'before'],
            'AUD' => ['name' => 'Australian Dollar',       'symbol' => 'A$',  'position' => 'before'],
            'AWG' => ['name' => 'Aruban Florin',           'symbol' => 'ƒ',   'position' => 'before'],
            'AZN' => ['name' => 'Azerbaijani Manat',       'symbol' => '₼',   'position' => 'before'],
            'BAM' => ['name' => 'Bosnia-Herzegovina Convertible Mark', 'symbol' => 'KM', 'position' => 'before'],
            'BBD' => ['name' => 'Barbadian Dollar',        'symbol' => 'Bds$','position' => 'before'],
            'BDT' => ['name' => 'Bangladeshi Taka',        'symbol' => '৳',   'position' => 'before'],
            'BGN' => ['name' => 'Bulgarian Lev',           'symbol' => 'лв',  'position' => 'after'],
            'BHD' => ['name' => 'Bahraini Dinar',          'symbol' => 'BD',  'position' => 'before'],
            'BIF' => ['name' => 'Burundian Franc',         'symbol' => 'Fr',  'position' => 'before'],
            'BMD' => ['name' => 'Bermudian Dollar',        'symbol' => '$',   'position' => 'before'],
            'BND' => ['name' => 'Brunei Dollar',           'symbol' => 'B$',  'position' => 'before'],
            'BOB' => ['name' => 'Bolivian Boliviano',      'symbol' => 'Bs.', 'position' => 'before'],
            'BRL' => ['name' => 'Brazilian Real',          'symbol' => 'R$',  'position' => 'before'],
            'BSD' => ['name' => 'Bahamian Dollar',         'symbol' => 'B$',  'position' => 'before'],
            'BTN' => ['name' => 'Bhutanese Ngultrum',      'symbol' => 'Nu',  'position' => 'before'],
            'BWP' => ['name' => 'Botswanan Pula',          'symbol' => 'P',   'position' => 'before'],
            'BYN' => ['name' => 'Belarusian Ruble',        'symbol' => 'Br',  'position' => 'before'],
            'BZD' => ['name' => 'Belize Dollar',           'symbol' => 'BZ$', 'position' => 'before'],
            'CAD' => ['name' => 'Canadian Dollar',         'symbol' => 'C$',  'position' => 'before'],
            'CDF' => ['name' => 'Congolese Franc',         'symbol' => 'Fr',  'position' => 'before'],
            'CHF' => ['name' => 'Swiss Franc',             'symbol' => 'Fr',  'position' => 'before'],
            'CLP' => ['name' => 'Chilean Peso',            'symbol' => '$',   'position' => 'before'],
            'CNY' => ['name' => 'Chinese Yuan',            'symbol' => '¥',   'position' => 'before'],
            'COP' => ['name' => 'Colombian Peso',          'symbol' => '$',   'position' => 'before'],
            'CRC' => ['name' => 'Costa Rican Colón',       'symbol' => '₡',   'position' => 'before'],
            'CUP' => ['name' => 'Cuban Peso',              'symbol' => '$',   'position' => 'before'],
            'CVE' => ['name' => 'Cape Verdean Escudo',     'symbol' => '$',   'position' => 'before'],
            'CZK' => ['name' => 'Czech Koruna',            'symbol' => 'Kč',  'position' => 'after'],
            'DJF' => ['name' => 'Djiboutian Franc',        'symbol' => 'Fr',  'position' => 'before'],
            'DKK' => ['name' => 'Danish Krone',            'symbol' => 'kr',  'position' => 'before'],
            'DOP' => ['name' => 'Dominican Peso',          'symbol' => 'RD$', 'position' => 'before'],
            'DZD' => ['name' => 'Algerian Dinar',          'symbol' => 'دج',  'position' => 'before'],
            'EGP' => ['name' => 'Egyptian Pound',          'symbol' => '£',   'position' => 'before'],
            'ERN' => ['name' => 'Eritrean Nakfa',          'symbol' => 'Nfk', 'position' => 'before'],
            'ETB' => ['name' => 'Ethiopian Birr',          'symbol' => 'Br',  'position' => 'before'],
            'EUR' => ['name' => 'Euro',                    'symbol' => '€',   'position' => 'before'],
            'FJD' => ['name' => 'Fijian Dollar',           'symbol' => 'FJ$', 'position' => 'before'],
            'FKP' => ['name' => 'Falkland Islands Pound',  'symbol' => '£',   'position' => 'before'],
            'GBP' => ['name' => 'British Pound',           'symbol' => '£',   'position' => 'before'],
            'GEL' => ['name' => 'Georgian Lari',           'symbol' => '₾',   'position' => 'before'],
            'GHS' => ['name' => 'Ghanaian Cedi',           'symbol' => '₵',   'position' => 'before'],
            'GIP' => ['name' => 'Gibraltar Pound',         'symbol' => '£',   'position' => 'before'],
            'GMD' => ['name' => 'Gambian Dalasi',          'symbol' => 'D',   'position' => 'before'],
            'GNF' => ['name' => 'Guinean Franc',           'symbol' => 'Fr',  'position' => 'before'],
            'GTQ' => ['name' => 'Guatemalan Quetzal',      'symbol' => 'Q',   'position' => 'before'],
            'GYD' => ['name' => 'Guyanese Dollar',         'symbol' => 'GY$', 'position' => 'before'],
            'HKD' => ['name' => 'Hong Kong Dollar',        'symbol' => 'HK$', 'position' => 'before'],
            'HNL' => ['name' => 'Honduran Lempira',        'symbol' => 'L',   'position' => 'before'],
            'HRK' => ['name' => 'Croatian Kuna',           'symbol' => 'kn',  'position' => 'after'],
            'HTG' => ['name' => 'Haitian Gourde',          'symbol' => 'G',   'position' => 'before'],
            'HUF' => ['name' => 'Hungarian Forint',        'symbol' => 'Ft',  'position' => 'after'],
            'IDR' => ['name' => 'Indonesian Rupiah',       'symbol' => 'Rp',  'position' => 'before'],
            'ILS' => ['name' => 'Israeli New Shekel',      'symbol' => '₪',   'position' => 'before'],
            'INR' => ['name' => 'Indian Rupee',            'symbol' => '₹',   'position' => 'before'],
            'IQD' => ['name' => 'Iraqi Dinar',             'symbol' => 'ع.د', 'position' => 'before'],
            'IRR' => ['name' => 'Iranian Rial',            'symbol' => '﷼',   'position' => 'before'],
            'ISK' => ['name' => 'Icelandic Króna',         'symbol' => 'kr',  'position' => 'after'],
            'JMD' => ['name' => 'Jamaican Dollar',         'symbol' => 'J$',  'position' => 'before'],
            'JOD' => ['name' => 'Jordanian Dinar',         'symbol' => 'JD',  'position' => 'before'],
            'JPY' => ['name' => 'Japanese Yen',            'symbol' => '¥',   'position' => 'before'],
            'KES' => ['name' => 'Kenyan Shilling',         'symbol' => 'KSh', 'position' => 'before'],
            'KGS' => ['name' => 'Kyrgyzstani Som',         'symbol' => 'с',   'position' => 'before'],
            'KHR' => ['name' => 'Cambodian Riel',          'symbol' => '៛',   'position' => 'before'],
            'KMF' => ['name' => 'Comorian Franc',          'symbol' => 'Fr',  'position' => 'before'],
            'KPW' => ['name' => 'North Korean Won',        'symbol' => '₩',   'position' => 'before'],
            'KRW' => ['name' => 'South Korean Won',        'symbol' => '₩',   'position' => 'before'],
            'KWD' => ['name' => 'Kuwaiti Dinar',           'symbol' => 'KD',  'position' => 'before'],
            'KYD' => ['name' => 'Cayman Islands Dollar',   'symbol' => 'CI$', 'position' => 'before'],
            'KZT' => ['name' => 'Kazakhstani Tenge',       'symbol' => '₸',   'position' => 'before'],
            'LAK' => ['name' => 'Laotian Kip',             'symbol' => '₭',   'position' => 'before'],
            'LBP' => ['name' => 'Lebanese Pound',          'symbol' => 'ل.ل', 'position' => 'before'],
            'LKR' => ['name' => 'Sri Lankan Rupee',        'symbol' => 'Rs',  'position' => 'before'],
            'LRD' => ['name' => 'Liberian Dollar',         'symbol' => 'L$',  'position' => 'before'],
            'LSL' => ['name' => 'Lesotho Loti',            'symbol' => 'L',   'position' => 'before'],
            'LYD' => ['name' => 'Libyan Dinar',            'symbol' => 'LD',  'position' => 'before'],
            'MAD' => ['name' => 'Moroccan Dirham',         'symbol' => 'MAD', 'position' => 'before'],
            'MDL' => ['name' => 'Moldovan Leu',            'symbol' => 'L',   'position' => 'after'],
            'MGA' => ['name' => 'Malagasy Ariary',         'symbol' => 'Ar',  'position' => 'before'],
            'MKD' => ['name' => 'Macedonian Denar',        'symbol' => 'ден', 'position' => 'after'],
            'MMK' => ['name' => 'Myanmar Kyat',            'symbol' => 'K',   'position' => 'before'],
            'MNT' => ['name' => 'Mongolian Tögrög',        'symbol' => '₮',   'position' => 'before'],
            'MOP' => ['name' => 'Macanese Pataca',         'symbol' => 'P',   'position' => 'before'],
            'MRU' => ['name' => 'Mauritanian Ouguiya',     'symbol' => 'UM',  'position' => 'before'],
            'MUR' => ['name' => 'Mauritian Rupee',         'symbol' => 'Rs',  'position' => 'before'],
            'MVR' => ['name' => 'Maldivian Rufiyaa',       'symbol' => 'Rf',  'position' => 'before'],
            'MWK' => ['name' => 'Malawian Kwacha',         'symbol' => 'MK',  'position' => 'before'],
            'MXN' => ['name' => 'Mexican Peso',            'symbol' => '$',   'position' => 'before'],
            'MYR' => ['name' => 'Malaysian Ringgit',       'symbol' => 'RM',  'position' => 'before'],
            'MZN' => ['name' => 'Mozambican Metical',      'symbol' => 'MT',  'position' => 'before'],
            'NAD' => ['name' => 'Namibian Dollar',         'symbol' => 'N$',  'position' => 'before'],
            'NGN' => ['name' => 'Nigerian Naira',          'symbol' => '₦',   'position' => 'before'],
            'NIO' => ['name' => 'Nicaraguan Córdoba',      'symbol' => 'C$',  'position' => 'before'],
            'NOK' => ['name' => 'Norwegian Krone',         'symbol' => 'kr',  'position' => 'before'],
            'NPR' => ['name' => 'Nepalese Rupee',          'symbol' => 'Rs',  'position' => 'before'],
            'NZD' => ['name' => 'New Zealand Dollar',      'symbol' => 'NZ$', 'position' => 'before'],
            'OMR' => ['name' => 'Omani Rial',              'symbol' => 'ر.ع.','position' => 'before'],
            'PAB' => ['name' => 'Panamanian Balboa',       'symbol' => 'B/.',  'position' => 'before'],
            'PEN' => ['name' => 'Peruvian Sol',            'symbol' => 'S/',  'position' => 'before'],
            'PGK' => ['name' => 'Papua New Guinean Kina',  'symbol' => 'K',   'position' => 'before'],
            'PHP' => ['name' => 'Philippine Peso',         'symbol' => '₱',   'position' => 'before'],
            'PKR' => ['name' => 'Pakistani Rupee',         'symbol' => 'Rs',  'position' => 'before'],
            'PLN' => ['name' => 'Polish Złoty',            'symbol' => 'zł',  'position' => 'after'],
            'PYG' => ['name' => 'Paraguayan Guaraní',      'symbol' => '₲',   'position' => 'before'],
            'QAR' => ['name' => 'Qatari Riyal',            'symbol' => 'QR',  'position' => 'before'],
            'RON' => ['name' => 'Romanian Leu',            'symbol' => 'lei', 'position' => 'after'],
            'RSD' => ['name' => 'Serbian Dinar',           'symbol' => 'din', 'position' => 'after'],
            'RUB' => ['name' => 'Russian Ruble',           'symbol' => '₽',   'position' => 'after'],
            'RWF' => ['name' => 'Rwandan Franc',           'symbol' => 'Fr',  'position' => 'before'],
            'SAR' => ['name' => 'Saudi Riyal',             'symbol' => 'SR',  'position' => 'before'],
            'SBD' => ['name' => 'Solomon Islands Dollar',  'symbol' => 'SI$', 'position' => 'before'],
            'SCR' => ['name' => 'Seychellois Rupee',       'symbol' => 'Rs',  'position' => 'before'],
            'SDG' => ['name' => 'Sudanese Pound',          'symbol' => '£',   'position' => 'before'],
            'SEK' => ['name' => 'Swedish Krona',           'symbol' => 'kr',  'position' => 'after'],
            'SGD' => ['name' => 'Singapore Dollar',        'symbol' => 'S$',  'position' => 'before'],
            'SHP' => ['name' => 'Saint Helena Pound',      'symbol' => '£',   'position' => 'before'],
            'SLL' => ['name' => 'Sierra Leonean Leone',    'symbol' => 'Le',  'position' => 'before'],
            'SOS' => ['name' => 'Somali Shilling',         'symbol' => 'Sh',  'position' => 'before'],
            'SRD' => ['name' => 'Surinamese Dollar',       'symbol' => '$',   'position' => 'before'],
            'STN' => ['name' => 'São Tomé & Príncipe Dobra','symbol' => 'Db', 'position' => 'before'],
            'SVC' => ['name' => 'Salvadoran Colón',        'symbol' => '₡',   'position' => 'before'],
            'SYP' => ['name' => 'Syrian Pound',            'symbol' => '£',   'position' => 'before'],
            'SZL' => ['name' => 'Swazi Lilangeni',         'symbol' => 'L',   'position' => 'before'],
            'THB' => ['name' => 'Thai Baht',               'symbol' => '฿',   'position' => 'before'],
            'TJS' => ['name' => 'Tajikistani Somoni',      'symbol' => 'SM',  'position' => 'before'],
            'TMT' => ['name' => 'Turkmenistani Manat',     'symbol' => 'T',   'position' => 'before'],
            'TND' => ['name' => 'Tunisian Dinar',          'symbol' => 'DT',  'position' => 'before'],
            'TOP' => ['name' => 'Tongan Paʻanga',          'symbol' => 'T$',  'position' => 'before'],
            'TRY' => ['name' => 'Turkish Lira',            'symbol' => '₺',   'position' => 'before'],
            'TTD' => ['name' => 'Trinidad & Tobago Dollar','symbol' => 'TT$', 'position' => 'before'],
            'TWD' => ['name' => 'New Taiwan Dollar',       'symbol' => 'NT$', 'position' => 'before'],
            'TZS' => ['name' => 'Tanzanian Shilling',      'symbol' => 'Sh',  'position' => 'before'],
            'UAH' => ['name' => 'Ukrainian Hryvnia',       'symbol' => '₴',   'position' => 'before'],
            'UGX' => ['name' => 'Ugandan Shilling',        'symbol' => 'Sh',  'position' => 'before'],
            'USD' => ['name' => 'US Dollar',               'symbol' => '$',   'position' => 'before'],
            'UYU' => ['name' => 'Uruguayan Peso',          'symbol' => '$U',  'position' => 'before'],
            'UZS' => ['name' => 'Uzbekistani Som',         'symbol' => 'лв',  'position' => 'before'],
            'VES' => ['name' => 'Venezuelan Bolívar',      'symbol' => 'Bs.S','position' => 'before'],
            'VND' => ['name' => 'Vietnamese Đồng',         'symbol' => '₫',   'position' => 'after'],
            'VUV' => ['name' => 'Vanuatu Vatu',            'symbol' => 'Vt',  'position' => 'before'],
            'WST' => ['name' => 'Samoan Tālā',             'symbol' => 'T',   'position' => 'before'],
            'XAF' => ['name' => 'Central African CFA Franc','symbol' => 'Fr', 'position' => 'before'],
            'XCD' => ['name' => 'East Caribbean Dollar',   'symbol' => 'EC$', 'position' => 'before'],
            'XOF' => ['name' => 'West African CFA Franc',  'symbol' => 'Fr',  'position' => 'before'],
            'XPF' => ['name' => 'CFP Franc',               'symbol' => 'Fr',  'position' => 'before'],
            'YER' => ['name' => 'Yemeni Rial',             'symbol' => '﷼',   'position' => 'before'],
            'ZAR' => ['name' => 'South African Rand',      'symbol' => 'R',   'position' => 'before'],
            'ZMW' => ['name' => 'Zambian Kwacha',          'symbol' => 'ZK',  'position' => 'before'],
            'ZWL' => ['name' => 'Zimbabwean Dollar',       'symbol' => 'Z$',  'position' => 'before'],
        ];
    }

    /** Get symbol for a currency code, fallback to code itself */
    public static function symbol(string $code): string
    {
        return static::all()[$code]['symbol'] ?? $code;
    }

    /** Format an amount with the correct currency symbol and position */
    public static function format(float $amount, string $code, int $decimals = 2): string
    {
        $currencies = static::all();
        $symbol   = $currencies[$code]['symbol']   ?? $code;
        $position = $currencies[$code]['position'] ?? 'before';
        $formatted = number_format($amount, $decimals);

        return $position === 'after'
            ? $formatted . $symbol
            : $symbol . $formatted;
    }

    /** Dropdown-friendly list: code => "CODE (symbol) — Name" */
    public static function selectList(): array
    {
        $list = [];
        foreach (static::all() as $code => $info) {
            $list[$code] = "{$code} ({$info['symbol']}) — {$info['name']}";
        }
        return $list;
    }
}
