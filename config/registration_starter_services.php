<?php

/**
 * Predefined starter services offered at registration, keyed by business_types.slug.
 * Each row: key (unique within slug), name, duration_minutes, price (GBP), optional buffer_minutes.
 */
return [
    'salon' => [
        ['key' => 'cut-blowdry', 'name' => 'Cut & Blowdry', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 10],
        ['key' => 'mens-cut', 'name' => 'Men\'s Cut', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 30, 'price' => 35.00, 'buffer_minutes' => 10],
        ['key' => 'full-colour', 'name' => 'Full Colour', 'category' => 'Colour Services', 'category_slug' => 'colour-services', 'duration_minutes' => 120, 'price' => 90.00, 'buffer_minutes' => 15],
        ['key' => 'balayage', 'name' => 'Balayage', 'category' => 'Colour Services', 'category_slug' => 'colour-services', 'duration_minutes' => 180, 'price' => 120.00, 'buffer_minutes' => 15],
    ],
    'spa' => [
        ['key' => 'massage-60', 'name' => 'Relaxation Massage (60 min)', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 75.00, 'buffer_minutes' => 10],
        ['key' => 'facial-express', 'name' => 'Express Facial', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 55.00, 'buffer_minutes' => 5],
        ['key' => 'body-scrub', 'name' => 'Body Scrub', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 75, 'price' => 85.00, 'buffer_minutes' => 10],
    ],
    'nail-studio' => [
        ['key' => 'gel-manicure', 'name' => 'Gel Manicure', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 40.00, 'buffer_minutes' => 10],
        ['key' => 'gel-pedicure', 'name' => 'Gel Pedicure', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 45.00, 'buffer_minutes' => 10],
        ['key' => 'nail-art', 'name' => 'Nail Art Add-On', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 5],
    ],
    'wellness-center' => [
        ['key' => 'consultation', 'name' => 'Wellness Consultation', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 5],
        ['key' => 'infrared', 'name' => 'Infrared Session', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 10],
    ],
    'barbershop' => [
        ['key' => 'skin-fade', 'name' => 'Skin Fade', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 10],
        ['key' => 'beard-trim', 'name' => 'Beard Trim & Shape', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 30, 'price' => 18.00, 'buffer_minutes' => 5],
        ['key' => 'hot-towel', 'name' => 'Hot Towel Shave', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 10],
    ],
    'massage-therapy-center' => [
        ['key' => 'deep-tissue', 'name' => 'Deep Tissue Massage (60)', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 10],
        ['key' => 'sports-massage', 'name' => 'Sports Massage (45)', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 55.00, 'buffer_minutes' => 10],
    ],
    'ayurvedic-alternative-medicine-clinic' => [
        ['key' => 'initial-consult', 'name' => 'Initial Ayurvedic Consultation', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 10],
    ],
    'fitness-yoga-studio' => [
        ['key' => 'drop-in-yoga', 'name' => 'Drop-In Yoga Class', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 15.00, 'buffer_minutes' => 10],
        ['key' => 'pt-session', 'name' => 'Personal Training Session', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 50.00, 'buffer_minutes' => 10],
    ],
    'cosmetic-aesthetic-clinic' => [
        ['key' => 'skin-consult', 'name' => 'Skin Consultation', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 5],
        ['key' => 'facial-peel', 'name' => 'Chemical Peel', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 60, 'price' => 95.00, 'buffer_minutes' => 10],
    ],
    'tattoo-piercing-studio' => [
        ['key' => 'tattoo-small', 'name' => 'Tattoo — small piece', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 90, 'price' => 80.00, 'buffer_minutes' => 15],
        ['key' => 'piercing-standard', 'name' => 'Standard Piercing', 'category' => 'General', 'category_slug' => 'general', 'duration_minutes' => 30, 'price' => 35.00, 'buffer_minutes' => 10],
    ],
];
