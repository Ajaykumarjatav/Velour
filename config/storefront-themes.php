<?php

return [
    'default' => 'glow-rose',

    /*
    |--------------------------------------------------------------------------
    | Registered salon website themes
    |--------------------------------------------------------------------------
    | Add a folder under salon-website/themes/{id} and run npm install && build.
    */
    'themes' => [
        'glow-rose' => [
            'label'   => 'Glow Rose',
            'preview' => '26254 1.png',
            'accent'  => '#D14D41',
        ],
        'beauty' => [
            'label'   => 'Beauty',
            'preview' => 'cta_section-removebg-preview 1.png',
            'accent'  => '#D14D41',
        ],
        'nail' => [
            'label'   => 'Nail',
            'preview' => 'Rectangle 27 (1).png',
            'accent'  => '#63242E',
        ],
        'tattoo' => [
            'label'   => 'Tattoo',
            'preview' => 'hero icon 1.png',
            'accent'  => '#9a031e',
        ],
        'mackup' => [
            'label'   => 'Mackup',
            'preview' => 'hugeicons_scissor-rectangle.png',
            'accent'  => '#B8943A',
        ],
        'pet-grooming' => [
            'label'   => 'Pet Grooming',
            'preview' => 'Groomed Golden Retriever.png',
            'accent'  => '#7a8b72',
        ],
        'spa' => [
            'label'   => 'Spa',
            'preview' => 'plant 2.png',
            'accent'  => '#7f390B',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Legacy display names stored before slug-based themes
    |--------------------------------------------------------------------------
    */
    'legacy_labels' => [
        'Glow Rose' => 'glow-rose',
        'Beauty'    => 'beauty',
        'Nail'      => 'nail',
        'Tattoo'    => 'tattoo',
        'Mackup'        => 'mackup',
        'Mockup'        => 'mackup',
        'mockup'        => 'mackup',
        'Pet Grooming'  => 'pet-grooming',
        'Spa'           => 'spa',
    ],
];
