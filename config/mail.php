<?php

$sharedSmtp = [
    'transport'    => 'smtp',
    'host'         => env('MAIL_HOST', 'smtp.hostinger.com'),
    'port'         => env('MAIL_PORT', 465),
    'encryption'   => env('MAIL_ENCRYPTION', 'ssl'),
    'timeout'      => null,
    'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
];

$purposeSmtp = static function (string $purpose, string $defaultAddress, string $defaultName) use ($sharedSmtp): array {
    $key = strtoupper($purpose);

    return array_merge($sharedSmtp, [
        'username' => env("MAIL_{$key}_USERNAME", $defaultAddress),
        'password' => env("MAIL_{$key}_PASSWORD"),
    ]);
};

return [
    'default' => env('MAIL_MAILER', 'support'),

    'mailers' => [
        'smtp'       => array_merge($sharedSmtp, [
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
        ]),
        'onboarding' => $purposeSmtp('onboarding', 'onboarding@easygrox.com', 'EasyGrox Onboarding'),
        'auth'       => $purposeSmtp('auth', 'auth@easygrox.com', 'EasyGrox Security'),
        'bookings'   => $purposeSmtp('bookings', 'bookings@easygrox.com', 'EasyGrox Bookings'),
        'support'    => $purposeSmtp('support', 'support@easygrox.com', 'EasyGrox Support'),
        'billing'    => $purposeSmtp('billing', 'billing@easygrox.com', 'EasyGrox Billing'),
        'log'        => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
    ],

    /*
    | Purpose → mailer + From header for transactional email.
    | onboarding — welcome + new tenant ops alerts
    | auth       — verify email, password reset, 2FA OTP
    | bookings   — client/staff booking confirmations, reviews, staff alerts
    | support    — helpdesk ticket notifications
    | billing    — POS invoices, subscription / billing receipts
    */
    'purposes' => [
        'onboarding' => [
            'mailer' => 'onboarding',
            'from'   => [
                'address' => env('MAIL_ONBOARDING_FROM', 'onboarding@easygrox.com'),
                'name'    => env('MAIL_ONBOARDING_FROM_NAME', 'EasyGrox Onboarding'),
            ],
        ],
        'auth' => [
            'mailer' => 'auth',
            'from'   => [
                'address' => env('MAIL_AUTH_FROM', 'auth@easygrox.com'),
                'name'    => env('MAIL_AUTH_FROM_NAME', 'EasyGrox Security'),
            ],
        ],
        'bookings' => [
            'mailer' => 'bookings',
            'from'   => [
                'address' => env('MAIL_BOOKINGS_FROM', 'bookings@easygrox.com'),
                'name'    => env('MAIL_BOOKINGS_FROM_NAME', 'EasyGrox Bookings'),
            ],
        ],
        'support' => [
            'mailer' => 'support',
            'from'   => [
                'address' => env('MAIL_SUPPORT_FROM', 'support@easygrox.com'),
                'name'    => env('MAIL_SUPPORT_FROM_NAME', 'EasyGrox Support'),
            ],
        ],
        'billing' => [
            'mailer' => 'billing',
            'from'   => [
                'address' => env('MAIL_BILLING_FROM', 'billing@easygrox.com'),
                'name'    => env('MAIL_BILLING_FROM_NAME', 'EasyGrox Billing'),
            ],
        ],
    ],

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'support@easygrox.com'),
        'name'    => env('MAIL_FROM_NAME', 'EasyGrox'),
    ],

    // Internal ops inbox for new user / new store / tenant feedback alerts (recipient, not sender)
    'ops_notify'     => env('MAIL_OPS_NOTIFY', 'ajayajatav439@gmail.com'),
    'support_notify' => env('MAIL_SUPPORT_NOTIFY', 'support@easygrox.com'),
    'ops_notify_cc'  => env('MAIL_OPS_NOTIFY_CC', ''),

    /*
    | Absolute URL for the header logo in transactional emails.
    */
    'logo_url' => env('MAIL_LOGO_URL'),

    'markdown' => ['theme' => 'default', 'paths' => [resource_path('views/vendor/mail')]],
];
