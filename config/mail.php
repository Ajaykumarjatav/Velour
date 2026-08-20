<?php
return [
    'default' => env('MAIL_MAILER', 'smtp'),
    'mailers' => [
        'smtp' => ['transport' => 'smtp', 'scheme' => env('MAIL_SCHEME'), 'host' => env('MAIL_HOST', 'smtp.mailgun.org'), 'port' => env('MAIL_PORT', 587), 'username' => env('MAIL_USERNAME'), 'password' => env('MAIL_PASSWORD'), 'timeout' => null, 'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST))],
        'log'  => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
    ],
    'from'      => ['address' => env('MAIL_FROM_ADDRESS', 'noreply@easygrox.com'), 'name' => env('MAIL_FROM_NAME', 'EasyGrox')],
    // Internal ops inbox for new user / new store alerts
    'ops_notify' => env('MAIL_OPS_NOTIFY', 'ajayajatav439@gmail.com'),
    // Optional CC (comma-separated) for the same ops alerts
    'ops_notify_cc' => env('MAIL_OPS_NOTIFY_CC', ''),
    /*
    | Absolute URL for the header logo in transactional emails. Gmail and other
    | clients cannot load http://localhost images — set MAIL_LOGO_URL in .env for
    | local SMTP testing, or leave blank to use the public EasyGrox CDN fallback
    | whenever APP_URL is local.
    */
    'logo_url' => env('MAIL_LOGO_URL'),
    'markdown'  => ['theme' => 'default', 'paths' => [resource_path('views/vendor/mail')]],
];
