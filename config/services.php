<?php
return [
    'postmark' => ['token' => env('POSTMARK_TOKEN')],
    'ses'      => ['key' => env('AWS_ACCESS_KEY_ID'), 'secret' => env('AWS_SECRET_ACCESS_KEY'), 'region' => env('AWS_DEFAULT_REGION', 'eu-west-2')],
    'stripe'   => ['key' => env('STRIPE_KEY'), 'secret' => env('STRIPE_SECRET'), 'webhook_secret' => env('STRIPE_WEBHOOK_SECRET')],
    'twilio'   => ['sid' => env('TWILIO_SID'), 'token' => env('TWILIO_TOKEN'), 'from' => env('TWILIO_FROM')],
    'pusher'   => ['app_id' => env('PUSHER_APP_ID'), 'app_key' => env('PUSHER_APP_KEY'), 'app_secret' => env('PUSHER_APP_SECRET'), 'app_cluster' => env('PUSHER_APP_CLUSTER')],
];
