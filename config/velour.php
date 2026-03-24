<?php

return [

    /*
    |──────────────────────────────────────────────────────────────────────────
    | Velour Platform Configuration
    |──────────────────────────────────────────────────────────────────────────
    */

    'name'    => 'Velour Salon SaaS',
    'version' => '1.0.0',

    /*
    | Plans & feature gating
    */
    'plans' => [
        'starter' => [
            'label'              => 'Starter',
            'price_monthly'      => 29,
            'max_staff'          => 2,
            'max_services'       => 20,
            'online_booking'     => true,
            'marketing'          => false,
            'reports'            => false,
            'pos'                => true,
            'inventory'          => false,
            'multi_location'     => false,
        ],
        'growth' => [
            'label'              => 'Growth',
            'price_monthly'      => 59,
            'max_staff'          => 8,
            'max_services'       => 100,
            'online_booking'     => true,
            'marketing'          => true,
            'reports'            => true,
            'pos'                => true,
            'inventory'          => true,
            'multi_location'     => false,
        ],
        'pro' => [
            'label'              => 'Pro',
            'price_monthly'      => 99,
            'max_staff'          => 25,
            'max_services'       => -1,   // unlimited
            'online_booking'     => true,
            'marketing'          => true,
            'reports'            => true,
            'pos'                => true,
            'inventory'          => true,
            'multi_location'     => false,
        ],
        'enterprise' => [
            'label'              => 'Enterprise',
            'price_monthly'      => 199,
            'max_staff'          => -1,
            'max_services'       => -1,
            'online_booking'     => true,
            'marketing'          => true,
            'reports'            => true,
            'pos'                => true,
            'inventory'          => true,
            'multi_location'     => true,
        ],
    ],

    /*
    | Booking defaults
    */
    'booking' => [
        'slot_interval_minutes'   => 15,
        'advance_booking_days'    => 60,
        'cancellation_hours'      => 24,
        'hold_expiry_minutes'     => 10,
        'reminder_hours_before'   => [24, 2],
    ],

    /*
    | POS / payments
    */
    'pos' => [
        'tax_rate'             => 0.20,   // UK VAT
        'default_currency'     => 'GBP',
        'stripe_fee_percent'   => 1.4,
        'stripe_fee_fixed'     => 0.20,
    ],

    /*
    | Booking sources (for analytics)
    */
    'booking_sources' => [
        'online'         => 'Online Booking',
        'phone'          => 'Phone',
        'walk_in'        => 'Walk-in',
        'google'         => 'Google Search & Maps',
        'instagram'      => 'Instagram',
        'facebook'       => 'Facebook',
        'whatsapp'       => 'WhatsApp',
        'website_embed'  => 'Website Embed',
        'qr_code'        => 'QR Code',
        'manual'         => 'Manual (Staff)',
        'other'          => 'Other',
    ],

    /*
    | Client tag presets
    */
    'client_tags' => [
        'vip', 'bride', 'student', 'model', 'staff', 'media',
        'allergy-tested', 'patch-tested', 'colour-client', 'new-client',
    ],

    /*
    | Notification types
    */
    'notification_types' => [
        'appointment',   // new booking
        'cancellation',  // client cancelled
        'reschedule',    // client rescheduled
        'reminder',      // reminder sent
        'checkin',       // client checked in
        'payment',       // payment received
        'review',        // new review
        'low_stock',     // inventory alert
        'marketing',     // campaign sent
        'system',        // platform alerts
    ],

];
