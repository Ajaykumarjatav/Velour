<?php

/*
| Each theme's `branding` block is the last step of the storefront fallback
| chain: salon's per-theme upload → salon-wide logo/cover image → these values.
| `logo => null` means the theme has no mark of its own and uses the shared
| EasyGrox lockup. Newlines in headings become <br> in the hero.
*/

return [
    'default' => 'glow-rose',

    'themes' => [
        'glow-rose' => [
            'label'   => 'Glow Rose',
            'preview' => '26254 1.png',
            'accent'  => '#D14D41',
            'branding' => [
                'logo'       => null,
                'banner'     => '26254 1.png',
                'heading'    => 'Redefining Style for Every You.',
                'subheading' => 'Premium hair, skin, and grooming services tailored for all genders. Experience a new standard of self-care.',
            ],
            'tokens'  => [
                'primary'     => '#D14D41',
                'primaryDark' => '#B44445',
                'deepMaroon'  => '#63242E',
                'salmon'      => '#D14D41',
                'iconCircle'  => '#FFEFEF',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 46.png', 'Rectangle 46 (1).png', 'Rectangle 27 (1).png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'noun-hair-cut-6384205 1.png',
            ],
        ],
        'beauty' => [
            'label'   => 'Beauty',
            'preview' => 'cta_section-removebg-preview 1.png',
            'accent'  => '#D14D41',
            'branding' => [
                'logo'       => null,
                'banner'     => 'Rectangle 98.png',
                'heading'    => "Where Elegance\nMeets Confidence.",
                'subheading' => 'Premium salon services designed to refresh your look, relax your mind, and enhance your natural beauty — from hair styling to skincare, nails, and bridal makeovers.',
            ],
            'tokens'  => [
                'primary'     => '#c98f8f',
                'primaryDark' => '#b87f7f',
                'deepMaroon'  => '#795152',
                'salmon'      => '#c98f8f',
                'iconCircle'  => '#FFEFEF',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 48.png', 'Group 84.png', 'Rectangle 46.png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'noun-hair-cut-6384205 1.png',
            ],
        ],
        'nail' => [
            'label'   => 'Nail',
            'preview' => 'Rectangle 27 (1).png',
            'accent'  => '#63242E',
            'branding' => [
                'logo'       => null,
                'banner'     => 'Rectangle 65.png',
                'heading'    => "When Nails\nBecomes Art",
                'subheading' => 'Luxury nails create timeless design crafted to elevate your everyday style.',
            ],
            'tokens'  => [
                'primary'     => '#b5556e',
                'primaryDark' => '#9a4860',
                'deepMaroon'  => '#72344A',
                'salmon'      => '#b5556e',
                'iconCircle'  => '#FFEFEF',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 48.png', 'Group 84.png', 'Rectangle 46.png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'noun-hair-cut-6384205 1.png',
            ],
        ],
        'tattoo' => [
            'label'   => 'Tattoo',
            'preview' => 'hero icon 1.png',
            'accent'  => '#9a031e',
            'branding' => [
                'logo'       => null,
                'banner'     => 'Rectangle 98.png',
                'heading'    => "Precision in\nPermanence",
                'subheading' => 'Where surgical standards meet classical artistry. We specialize in bespoke designs crafted to endure a lifetime.',
            ],
            'tokens'  => [
                'primary'     => '#9a031e',
                'primaryDark' => '#7d0218',
                'deepMaroon'  => '#1a1a1a',
                'salmon'      => '#9a031e',
                'iconCircle'  => '#2a2a2a',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 48.png', 'Group 84.png', 'Rectangle 46.png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'equipment 1.png',
            ],
        ],
        'mackup' => [
            'label'   => 'Mackup',
            'preview' => 'hugeicons_scissor-rectangle.png',
            'accent'  => '#B8943A',
            'branding' => [
                'logo'       => null,
                'banner'     => 'Rectangle 98.png',
                'heading'    => "Soft Glam.\nBold Elegance.",
                'subheading' => 'Luxury makeup artistry designed to enhance your natural beauty for weddings, celebrations, photoshoots, and unforgettable moments.',
            ],
            'tokens'  => [
                'primary'     => '#b7846a',
                'primaryDark' => '#a6755d',
                'deepMaroon'  => '#72344A',
                'salmon'      => '#b7846a',
                'iconCircle'  => '#FFEFEF',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 48.png', 'Group 84.png', 'Rectangle 46.png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'noun-hair-cut-6384205 1.png',
            ],
        ],
        'pet-grooming' => [
            'label'   => 'Pet Grooming',
            'preview' => 'Groomed Golden Retriever.png',
            'accent'  => '#7a8b72',
            'branding' => [
                'logo'       => null,
                'banner'     => 'Groomed Golden Retriever.png',
                // The hero partial still carries the Nail theme's copy; this is the
                // intended text and takes effect when that partial is wired up.
                'heading'    => "Grooming They Love,\nResults You Notice",
                'subheading' => 'Gentle, unhurried grooming for every coat and temperament — from a quick tidy-up to a full spa day for your pet.',
            ],
            'tokens'  => [
                'primary'     => '#7a8b72',
                'primaryDark' => '#6a7b62',
                'deepMaroon'  => '#444a38',
                'salmon'      => '#7a8b72',
                'iconCircle'  => '#eef2eb',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 48.png', 'Group 84.png', 'Rectangle 46.png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'noun-hair-cut-6384205 1.png',
            ],
        ],
        'spa' => [
            'label'   => 'Spa',
            'preview' => 'plant 2.png',
            'accent'  => '#7f390B',
            'branding' => [
                'logo'       => null,
                'banner'     => 'Rectangle 65.png',
                'heading'    => "Experience The\nArt Of Relaxation",
                'subheading' => 'Indulge in luxurious spa rituals and therapeutic massage experiences designed to restore balance, beauty, and inner calm.',
            ],
            'tokens'  => [
                'primary'     => '#7f390B',
                'primaryDark' => '#6f3109',
                'deepMaroon'  => '#886a46',
                'salmon'      => '#7f390B',
                'iconCircle'  => '#f5efe8',
            ],
            'assets' => [
                'packageImages'   => ['Rectangle 48.png', 'Rectangle 48.png', 'Rectangle 46.png'],
                'locationGallery' => ['Rectangle 58.png', 'Rectangle 59.png', 'Rectangle 60.png'],
                'serviceIcon'     => 'noun-hair-cut-6384205 1.png',
            ],
        ],
    ],

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
