<?php

/**
 * Predefined starter services offered at registration, keyed by business_types.slug.
 * Each row: key (unique within slug), name, duration_minutes, price (GBP), optional buffer_minutes.
 */
return [
    'womens' => [
        ['key' => 'full-hands-wax', 'name' => 'Full Hands Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 45, 'price' => 20.00, 'buffer_minutes' => 0],
        ['key' => 'full-legs-wax', 'name' => 'Full Legs Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 60, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'half-legs-wax', 'name' => 'Half Legs Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 35, 'price' => 18.00, 'buffer_minutes' => 0],
        ['key' => 'underarms-wax', 'name' => 'Underarms Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 20, 'price' => 10.00, 'buffer_minutes' => 0],
        ['key' => 'bikini-wax', 'name' => 'Bikini Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 30, 'price' => 18.00, 'buffer_minutes' => 0],
        ['key' => 'bikini-line-wax', 'name' => 'Bikini Line Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 20, 'price' => 14.00, 'buffer_minutes' => 0],
        ['key' => 'full-body-wax', 'name' => 'Full Body Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 120, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'face-wax', 'name' => 'Face Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 25, 'price' => 12.00, 'buffer_minutes' => 0],
        ['key' => 'rica-wax', 'name' => 'Rica Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 45, 'price' => 28.00, 'buffer_minutes' => 0],
        ['key' => 'roll-on-wax', 'name' => 'Roll-on Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 30, 'price' => 16.00, 'buffer_minutes' => 0],
        ['key' => 'o3-plus-facial', 'name' => 'O3+ Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'gold-facial', 'name' => 'Gold Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],
        ['key' => 'wine-facial', 'name' => 'Wine Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 60, 'price' => 62.00, 'buffer_minutes' => 0],
        ['key' => 'brightening-facial', 'name' => 'Brightening Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 55, 'price' => 58.00, 'buffer_minutes' => 0],
        ['key' => 'anti-aging-facial', 'name' => 'Anti-aging Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 70, 'price' => 75.00, 'buffer_minutes' => 0],
        ['key' => 'hydra-facial', 'name' => 'Hydra Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 55, 'price' => 56.00, 'buffer_minutes' => 0],
        ['key' => 'ubtan-facial', 'name' => 'Ubtan Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 50, 'price' => 52.00, 'buffer_minutes' => 0],
        ['key' => 'face-cleanup', 'name' => 'Face Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 35, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'fruit-cleanup', 'name' => 'Fruit Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 40, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'tan-removal-cleanup', 'name' => 'Tan Removal Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'o3-plus-cleanup', 'name' => 'O3+ Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 45, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'face-detan', 'name' => 'Face Detan', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 30, 'price' => 32.00, 'buffer_minutes' => 0],
        ['key' => 'neck-detan', 'name' => 'Neck Detan', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 25, 'price' => 28.00, 'buffer_minutes' => 0],
        ['key' => 'basic-manicure', 'name' => 'Basic Manicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 35, 'price' => 20.00, 'buffer_minutes' => 0],
        ['key' => 'basic-pedicure', 'name' => 'Basic Pedicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 40, 'price' => 24.00, 'buffer_minutes' => 0],
        ['key' => 'spa-manicure', 'name' => 'Spa Manicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 50, 'price' => 32.00, 'buffer_minutes' => 0],
        ['key' => 'spa-pedicure', 'name' => 'Spa Pedicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 60, 'price' => 38.00, 'buffer_minutes' => 0],
        ['key' => 'gel-polish-addon', 'name' => 'Gel Polish (if added)', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 20, 'price' => 12.00, 'buffer_minutes' => 0],
        ['key' => 'cut-file', 'name' => 'Cut & File', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 20, 'price' => 10.00, 'buffer_minutes' => 0],
        ['key' => 'haircut-if-applicable', 'name' => 'Haircut (if applicable)', 'category' => 'Hair wash & blow dry', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'hair-trimming', 'name' => 'Hair Trimming', 'category' => 'Hair wash & blow dry', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 0],
        ['key' => 'blow-dry', 'name' => 'Blow Dry', 'category' => 'Hair wash & blow dry', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 35, 'price' => 22.00, 'buffer_minutes' => 0],
        ['key' => 'hair-styling', 'name' => 'Hair Styling', 'category' => 'Hair wash & blow dry', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 40, 'price' => 28.00, 'buffer_minutes' => 0],
        ['key' => 'hair-ironing', 'name' => 'Hair Ironing', 'category' => 'Hair wash & blow dry', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'hair-curling', 'name' => 'Hair Curling', 'category' => 'Hair wash & blow dry', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 45, 'price' => 32.00, 'buffer_minutes' => 0],
        ['key' => 'face-bleach', 'name' => 'Face Bleach', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 25, 'price' => 18.00, 'buffer_minutes' => 0],
        ['key' => 'full-body-bleach', 'name' => 'Full Body Bleach', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 60, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'de-tan-treatment', 'name' => 'De-tan Treatment', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'skin-lightening-treatment', 'name' => 'Skin Lightening Treatment', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'wax-facial-package', 'name' => 'Wax + Facial Package', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 90, 'price' => 80.00, 'buffer_minutes' => 0],
        ['key' => 'cleanup-wax-combo', 'name' => 'Cleanup + Wax Combo', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 80, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'monthly-maintenance-package', 'name' => 'Monthly Maintenance Package', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 120, 'price' => 120.00, 'buffer_minutes' => 0],
        ['key' => 'custom-package', 'name' => 'Custom Package (build your own)', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 120, 'price' => 130.00, 'buffer_minutes' => 0],

        // Nail Studio
        ['key' => 'acrylic-extensions', 'name' => 'Acrylic Extensions', 'category' => 'Nail Studio', 'category_slug' => 'nail-studio', 'duration_minutes' => 90, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'gel-nail-extensions', 'name' => 'Gel Nail Extensions', 'category' => 'Nail Studio', 'category_slug' => 'nail-studio', 'duration_minutes' => 90, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'nail-art-design', 'name' => 'Nail Art & Design', 'category' => 'Nail Studio', 'category_slug' => 'nail-studio', 'duration_minutes' => 45, 'price' => 25.00, 'buffer_minutes' => 0],
        ['key' => 'gel-polish-manicures', 'name' => 'Gel Polish & Manicures', 'category' => 'Nail Studio', 'category_slug' => 'nail-studio', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'pedicures-studio', 'name' => 'Pedicures', 'category' => 'Nail Studio', 'category_slug' => 'nail-studio', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'infill-removals', 'name' => 'Infill & Removals', 'category' => 'Nail Studio', 'category_slug' => 'nail-studio', 'duration_minutes' => 60, 'price' => 35.00, 'buffer_minutes' => 0],

        // Lash Bar
        ['key' => 'classic-extensions', 'name' => 'Classic Extensions', 'category' => 'Lash Bar', 'category_slug' => 'lash-bar', 'duration_minutes' => 90, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'volume-hybrid-lashes', 'name' => 'Volume & Hybrid Lashes', 'category' => 'Lash Bar', 'category_slug' => 'lash-bar', 'duration_minutes' => 120, 'price' => 85.00, 'buffer_minutes' => 0],
        ['key' => 'lash-lift-tint', 'name' => 'Lash Lift & Tint', 'category' => 'Lash Bar', 'category_slug' => 'lash-bar', 'duration_minutes' => 60, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'lash-refills-removal', 'name' => 'Refills & Removal', 'category' => 'Lash Bar', 'category_slug' => 'lash-bar', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 0],

        // Brow Architecture
        ['key' => 'brow-lamination', 'name' => 'Brow Lamination', 'category' => 'Brow Architecture', 'category_slug' => 'brow-architecture', 'duration_minutes' => 60, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'microblading-shading', 'name' => 'Microblading & Shading', 'category' => 'Brow Architecture', 'category_slug' => 'brow-architecture', 'duration_minutes' => 120, 'price' => 180.00, 'buffer_minutes' => 0],
        ['key' => 'brow-tinting-shaping', 'name' => 'Brow Tinting & Shaping', 'category' => 'Brow Architecture', 'category_slug' => 'brow-architecture', 'duration_minutes' => 40, 'price' => 28.00, 'buffer_minutes' => 0],

        // Semi-Permanent Makeup
        ['key' => 'lip-blush', 'name' => 'Lip Blush', 'category' => 'Semi-Permanent Makeup', 'category_slug' => 'semi-permanent-makeup', 'duration_minutes' => 120, 'price' => 180.00, 'buffer_minutes' => 0],
        ['key' => 'permanent-eyeliner', 'name' => 'Permanent Eyeliner', 'category' => 'Semi-Permanent Makeup', 'category_slug' => 'semi-permanent-makeup', 'duration_minutes' => 90, 'price' => 150.00, 'buffer_minutes' => 0],
        ['key' => 'beauty-spot-corrections', 'name' => 'Beauty Spot & Corrections', 'category' => 'Semi-Permanent Makeup', 'category_slug' => 'semi-permanent-makeup', 'duration_minutes' => 60, 'price' => 80.00, 'buffer_minutes' => 0],

        // Hair & Aesthetic Add-ons
        ['key' => 'hair-extensions', 'name' => 'Hair Extensions', 'category' => 'Hair & Aesthetic Add-ons', 'category_slug' => 'hair-aesthetic-add-ons', 'duration_minutes' => 120, 'price' => 150.00, 'buffer_minutes' => 0],
        ['key' => 'hair-styling-care', 'name' => 'Hair Styling & Care', 'category' => 'Hair & Aesthetic Add-ons', 'category_slug' => 'hair-aesthetic-add-ons', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 0],
    ],
    'mans' => [
        ['key' => 'haircut', 'name' => 'Haircut', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 30, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'kids-haircut', 'name' => 'Kids Haircut', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 25, 'price' => 25.00, 'buffer_minutes' => 0],
        ['key' => 'hair-styling', 'name' => 'Hair Styling', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 35, 'price' => 28.00, 'buffer_minutes' => 0],
        ['key' => 'blow-dry', 'name' => 'Blow Dry', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 25, 'price' => 20.00, 'buffer_minutes' => 0],

        ['key' => 'beard-trimming', 'name' => 'Beard Trimming', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 0],
        ['key' => 'beard-styling', 'name' => 'Beard Styling', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 25, 'price' => 18.00, 'buffer_minutes' => 0],
        ['key' => 'clean-shave', 'name' => 'Clean Shave', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 20, 'price' => 14.00, 'buffer_minutes' => 0],
        ['key' => 'beard-design', 'name' => 'Beard Design', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 30, 'price' => 22.00, 'buffer_minutes' => 0],

        ['key' => 'o3-plus-facial-men', 'name' => 'O3+ Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 50, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'detan-facial-men', 'name' => 'Detan Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 45, 'price' => 48.00, 'buffer_minutes' => 0],
        ['key' => 'brightening-facial-men', 'name' => 'Brightening Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'cleanup-men', 'name' => 'Cleanup', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 35, 'price' => 35.00, 'buffer_minutes' => 0],

        ['key' => 'hair-colour-application', 'name' => 'Hair Colour Application', 'category' => 'Hair Color', 'category_slug' => 'hair-color', 'duration_minutes' => 50, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'loreal-hair-colour', 'name' => "L'Oréal Hair Colour", 'category' => 'Hair Color', 'category_slug' => 'hair-color', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],
        ['key' => 'grey-coverage', 'name' => 'Grey Coverage', 'category' => 'Hair Color', 'category_slug' => 'hair-color', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],

        ['key' => 'pedicure-men', 'name' => 'Pedicure', 'category' => 'Hand & Foot Care', 'category_slug' => 'hand-foot-care', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'manicure-basic-men', 'name' => 'Manicure (basic)', 'category' => 'Hand & Foot Care', 'category_slug' => 'hand-foot-care', 'duration_minutes' => 35, 'price' => 25.00, 'buffer_minutes' => 0],
        ['key' => 'nail-cut-file-men', 'name' => 'Nail Cut & File', 'category' => 'Hand & Foot Care', 'category_slug' => 'hand-foot-care', 'duration_minutes' => 20, 'price' => 12.00, 'buffer_minutes' => 0],

        ['key' => 'head-massage', 'name' => 'Head Massage', 'category' => 'Massage & Relaxation', 'category_slug' => 'massage-relaxation', 'duration_minutes' => 25, 'price' => 20.00, 'buffer_minutes' => 0],
        ['key' => 'head-neck-shoulder-massage', 'name' => 'Head-Neck-Shoulder Massage', 'category' => 'Massage & Relaxation', 'category_slug' => 'massage-relaxation', 'duration_minutes' => 35, 'price' => 30.00, 'buffer_minutes' => 0],

        ['key' => 'haircut-beard-combo', 'name' => 'Haircut + Beard Combo', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 50, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'haircut-facial', 'name' => 'Haircut + Facial', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 75, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'complete-grooming-package', 'name' => 'Complete Grooming Package', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 110, 'price' => 95.00, 'buffer_minutes' => 0],
    ],
    'unisex' => [
        ['key' => 'haircut-men-women', 'name' => 'Haircut (Men/Women)', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'hair-trim-unisex', 'name' => 'Hair Trim', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 30, 'price' => 25.00, 'buffer_minutes' => 0],
        ['key' => 'blow-dry-unisex', 'name' => 'Blow Dry', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 35, 'price' => 28.00, 'buffer_minutes' => 0],
        ['key' => 'hair-styling-unisex', 'name' => 'Hair Styling', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 40, 'price' => 32.00, 'buffer_minutes' => 0],

        ['key' => 'hair-spa-unisex', 'name' => 'Hair Spa', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'keratin-treatment-unisex', 'name' => 'Keratin Treatment', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 90, 'price' => 120.00, 'buffer_minutes' => 0],
        ['key' => 'smoothening-unisex', 'name' => 'Smoothening', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 90, 'price' => 110.00, 'buffer_minutes' => 0],
        ['key' => 'rebonding-unisex', 'name' => 'Rebonding', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 120, 'price' => 140.00, 'buffer_minutes' => 0],
        ['key' => 'botox-treatment-unisex', 'name' => 'Botox Treatment', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 90, 'price' => 130.00, 'buffer_minutes' => 0],

        ['key' => 'global-color-unisex', 'name' => 'Global Color', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 120, 'price' => 95.00, 'buffer_minutes' => 0],
        ['key' => 'root-touch-up-unisex', 'name' => 'Root Touch-up', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'highlights-unisex', 'name' => 'Highlights', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 120, 'price' => 110.00, 'buffer_minutes' => 0],
        ['key' => 'balayage-unisex', 'name' => 'Balayage', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 150, 'price' => 140.00, 'buffer_minutes' => 0],
        ['key' => 'fashion-colors-unisex', 'name' => 'Fashion Colors', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 120, 'price' => 125.00, 'buffer_minutes' => 0],

        ['key' => 'facial-unisex', 'name' => 'Facial', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 50, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'cleanup-unisex', 'name' => 'Cleanup', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 40, 'price' => 38.00, 'buffer_minutes' => 0],
        ['key' => 'detan-unisex', 'name' => 'Detan', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 45, 'price' => 42.00, 'buffer_minutes' => 0],
        ['key' => 'skin-treatments-unisex', 'name' => 'Skin Treatments', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],

        ['key' => 'haircut-facial-combo-unisex', 'name' => 'Haircut + Facial Combo', 'category' => 'Grooming & Styling Packages', 'category_slug' => 'grooming-styling-packages', 'duration_minutes' => 90, 'price' => 75.00, 'buffer_minutes' => 0],
        ['key' => 'hair-spa-styling-package-unisex', 'name' => 'Hair Spa + Styling Package', 'category' => 'Grooming & Styling Packages', 'category_slug' => 'grooming-styling-packages', 'duration_minutes' => 110, 'price' => 95.00, 'buffer_minutes' => 0],

        ['key' => 'bridal-makeup-unisex', 'name' => 'Bridal Makeup', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 120, 'price' => 180.00, 'buffer_minutes' => 0],
        ['key' => 'party-makeup-unisex', 'name' => 'Party Makeup', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 90, 'price' => 120.00, 'buffer_minutes' => 0],
        ['key' => 'hairstyling-unisex', 'name' => 'Hairstyling', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'saree-draping-unisex', 'name' => 'Saree Draping', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 40, 'price' => 50.00, 'buffer_minutes' => 0],

        // Relaxation Massage
        ['key' => 'swedish-massage', 'name' => 'Swedish Massage', 'category' => 'Relaxation Massage', 'category_slug' => 'relaxation-massage', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'aromatherapy-massage', 'name' => 'Aromatherapy Massage', 'category' => 'Relaxation Massage', 'category_slug' => 'relaxation-massage', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],
        ['key' => 'relaxation-massage', 'name' => 'Relaxation Massage', 'category' => 'Relaxation Massage', 'category_slug' => 'relaxation-massage', 'duration_minutes' => 60, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'candle-massage', 'name' => 'Candle Massage', 'category' => 'Relaxation Massage', 'category_slug' => 'relaxation-massage', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],

        // Deep & Therapeutic
        ['key' => 'deep-tissue-massage', 'name' => 'Deep Tissue Massage', 'category' => 'Deep & Therapeutic', 'category_slug' => 'deep-therapeutic', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'sports-massage', 'name' => 'Sports Massage', 'category' => 'Deep & Therapeutic', 'category_slug' => 'deep-therapeutic', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'trigger-point-therapy', 'name' => 'Trigger Point Therapy', 'category' => 'Deep & Therapeutic', 'category_slug' => 'deep-therapeutic', 'duration_minutes' => 45, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'myofascial-release', 'name' => 'Myofascial Release', 'category' => 'Deep & Therapeutic', 'category_slug' => 'deep-therapeutic', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],

        // Asian / Eastern
        ['key' => 'thai-massage', 'name' => 'Thai Massage', 'category' => 'Asian / Eastern', 'category_slug' => 'asian-eastern', 'duration_minutes' => 75, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'balinese-massage', 'name' => 'Balinese Massage', 'category' => 'Asian / Eastern', 'category_slug' => 'asian-eastern', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'shiatsu', 'name' => 'Shiatsu', 'category' => 'Asian / Eastern', 'category_slug' => 'asian-eastern', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],
        ['key' => 'tui-na', 'name' => 'Tui Na', 'category' => 'Asian / Eastern', 'category_slug' => 'asian-eastern', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],

        // Indian / Ayurvedic
        ['key' => 'abhyanga', 'name' => 'Abhyanga', 'category' => 'Indian / Ayurvedic', 'category_slug' => 'indian-ayurvedic', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'shiro-abhyanga', 'name' => 'Shiro Abhyanga', 'category' => 'Indian / Ayurvedic', 'category_slug' => 'indian-ayurvedic', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'pada-abhyanga', 'name' => 'Pada Abhyanga', 'category' => 'Indian / Ayurvedic', 'category_slug' => 'indian-ayurvedic', 'duration_minutes' => 30, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'marma-massage', 'name' => 'Marma Massage', 'category' => 'Indian / Ayurvedic', 'category_slug' => 'indian-ayurvedic', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 0],
        ['key' => 'udvartana', 'name' => 'Udvartana', 'category' => 'Indian / Ayurvedic', 'category_slug' => 'indian-ayurvedic', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 0],

        // Hot / Warm Therapies
        ['key' => 'hot-stone-massage', 'name' => 'Hot Stone Massage', 'category' => 'Hot / Warm Therapies', 'category_slug' => 'hot-warm-therapies', 'duration_minutes' => 75, 'price' => 80.00, 'buffer_minutes' => 0],
        ['key' => 'warm-bamboo-massage', 'name' => 'Warm Bamboo Massage', 'category' => 'Hot / Warm Therapies', 'category_slug' => 'hot-warm-therapies', 'duration_minutes' => 60, 'price' => 75.00, 'buffer_minutes' => 0],
        ['key' => 'herbal-compress-massage', 'name' => 'Herbal Compress Massage', 'category' => 'Hot / Warm Therapies', 'category_slug' => 'hot-warm-therapies', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],

        // Lymphatic & Circulatory
        ['key' => 'manual-lymphatic-drainage', 'name' => 'Manual Lymphatic Drainage', 'category' => 'Lymphatic & Circulatory', 'category_slug' => 'lymphatic-circulatory', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'lymphatic-massage', 'name' => 'Lymphatic Massage', 'category' => 'Lymphatic & Circulatory', 'category_slug' => 'lymphatic-circulatory', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],

        // Reflexology & Pressure Point
        ['key' => 'foot-reflexology', 'name' => 'Foot Reflexology', 'category' => 'Reflexology & Pressure Point', 'category_slug' => 'reflexology-pressure-point', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'hand-reflexology', 'name' => 'Hand Reflexology', 'category' => 'Reflexology & Pressure Point', 'category_slug' => 'reflexology-pressure-point', 'duration_minutes' => 30, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'acupressure', 'name' => 'Acupressure', 'category' => 'Reflexology & Pressure Point', 'category_slug' => 'reflexology-pressure-point', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],

        // Head, Face & Upper Body
        ['key' => 'head-massage-unisex', 'name' => 'Head Massage', 'category' => 'Head, Face & Upper Body', 'category_slug' => 'head-face-upper-body', 'duration_minutes' => 30, 'price' => 25.00, 'buffer_minutes' => 0],
        ['key' => 'scalp-massage', 'name' => 'Scalp Massage', 'category' => 'Head, Face & Upper Body', 'category_slug' => 'head-face-upper-body', 'duration_minutes' => 30, 'price' => 25.00, 'buffer_minutes' => 0],
        ['key' => 'neck-shoulder-massage', 'name' => 'Neck & Shoulder Massage', 'category' => 'Head, Face & Upper Body', 'category_slug' => 'head-face-upper-body', 'duration_minutes' => 40, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'back-massage', 'name' => 'Back Massage', 'category' => 'Head, Face & Upper Body', 'category_slug' => 'head-face-upper-body', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],

        // Couples & Multi-Therapist
        ['key' => 'couples-massage', 'name' => 'Couples Massage', 'category' => 'Couples & Multi-Therapist', 'category_slug' => 'couples-multi-therapist', 'duration_minutes' => 60, 'price' => 120.00, 'buffer_minutes' => 0],
        ['key' => 'four-hand-massage', 'name' => 'Four-Hand Massage', 'category' => 'Couples & Multi-Therapist', 'category_slug' => 'couples-multi-therapist', 'duration_minutes' => 60, 'price' => 140.00, 'buffer_minutes' => 0],

        // Prenatal / Postnatal
        ['key' => 'prenatal-massage', 'name' => 'Prenatal Massage', 'category' => 'Prenatal / Postnatal', 'category_slug' => 'prenatal-postnatal', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'postnatal-massage', 'name' => 'Postnatal Massage', 'category' => 'Prenatal / Postnatal', 'category_slug' => 'prenatal-postnatal', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],

        // Sports & Recovery
        ['key' => 'athlete-recovery-massage', 'name' => 'Athlete Recovery Massage', 'category' => 'Sports & Recovery', 'category_slug' => 'sports-recovery', 'duration_minutes' => 60, 'price' => 75.00, 'buffer_minutes' => 0],
        ['key' => 'stretching-therapy', 'name' => 'Stretching Therapy', 'category' => 'Sports & Recovery', 'category_slug' => 'sports-recovery', 'duration_minutes' => 45, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'mobility-massage', 'name' => 'Mobility Massage', 'category' => 'Sports & Recovery', 'category_slug' => 'sports-recovery', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],

        // Body Scrub & Exfoliation
        ['key' => 'body-scrub', 'name' => 'Body Scrub', 'category' => 'Body Scrub & Exfoliation', 'category_slug' => 'body-scrub-exfoliation', 'duration_minutes' => 45, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'body-polish', 'name' => 'Body Polish', 'category' => 'Body Scrub & Exfoliation', 'category_slug' => 'body-scrub-exfoliation', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'coffee-scrub', 'name' => 'Coffee Scrub', 'category' => 'Body Scrub & Exfoliation', 'category_slug' => 'body-scrub-exfoliation', 'duration_minutes' => 45, 'price' => 48.00, 'buffer_minutes' => 0],
        ['key' => 'salt-scrub', 'name' => 'Salt Scrub', 'category' => 'Body Scrub & Exfoliation', 'category_slug' => 'body-scrub-exfoliation', 'duration_minutes' => 45, 'price' => 48.00, 'buffer_minutes' => 0],
        ['key' => 'sugar-scrub', 'name' => 'Sugar Scrub', 'category' => 'Body Scrub & Exfoliation', 'category_slug' => 'body-scrub-exfoliation', 'duration_minutes' => 45, 'price' => 48.00, 'buffer_minutes' => 0],

        // Body Wraps & Masks
        ['key' => 'body-wrap', 'name' => 'Body Wrap', 'category' => 'Body Wraps & Masks', 'category_slug' => 'body-wraps-masks', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'mud-wrap', 'name' => 'Mud Wrap', 'category' => 'Body Wraps & Masks', 'category_slug' => 'body-wraps-masks', 'duration_minutes' => 60, 'price' => 75.00, 'buffer_minutes' => 0],
        ['key' => 'clay-wrap', 'name' => 'Clay Wrap', 'category' => 'Body Wraps & Masks', 'category_slug' => 'body-wraps-masks', 'duration_minutes' => 60, 'price' => 75.00, 'buffer_minutes' => 0],
        ['key' => 'algae-wrap', 'name' => 'Algae Wrap', 'category' => 'Body Wraps & Masks', 'category_slug' => 'body-wraps-masks', 'duration_minutes' => 60, 'price' => 80.00, 'buffer_minutes' => 0],
        ['key' => 'chocolate-wrap', 'name' => 'Chocolate Wrap', 'category' => 'Body Wraps & Masks', 'category_slug' => 'body-wraps-masks', 'duration_minutes' => 60, 'price' => 80.00, 'buffer_minutes' => 0],

        // Hydrotherapy
        ['key' => 'jacuzzi-whirlpool', 'name' => 'Jacuzzi / Whirlpool', 'category' => 'Hydrotherapy', 'category_slug' => 'hydrotherapy', 'duration_minutes' => 30, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'hydro-massage', 'name' => 'Hydro Massage', 'category' => 'Hydrotherapy', 'category_slug' => 'hydrotherapy', 'duration_minutes' => 45, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'vichy-shower', 'name' => 'Vichy Shower', 'category' => 'Hydrotherapy', 'category_slug' => 'hydrotherapy', 'duration_minutes' => 30, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'contrast-therapy', 'name' => 'Contrast Therapy', 'category' => 'Hydrotherapy', 'category_slug' => 'hydrotherapy', 'duration_minutes' => 30, 'price' => 45.00, 'buffer_minutes' => 0],

        // Sauna & Steam
        ['key' => 'sauna', 'name' => 'Sauna', 'category' => 'Sauna & Steam', 'category_slug' => 'sauna-steam', 'duration_minutes' => 30, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'steam-bath', 'name' => 'Steam Bath', 'category' => 'Sauna & Steam', 'category_slug' => 'sauna-steam', 'duration_minutes' => 30, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'turkish-hammam', 'name' => 'Turkish Hammam', 'category' => 'Sauna & Steam', 'category_slug' => 'sauna-steam', 'duration_minutes' => 45, 'price' => 55.00, 'buffer_minutes' => 0],

        // Thermal / Mineral
        ['key' => 'thermal-bath', 'name' => 'Thermal Bath', 'category' => 'Thermal / Mineral', 'category_slug' => 'thermal-mineral', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'mineral-bath', 'name' => 'Mineral Bath', 'category' => 'Thermal / Mineral', 'category_slug' => 'thermal-mineral', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'thalassotherapy', 'name' => 'Thalassotherapy', 'category' => 'Thermal / Mineral', 'category_slug' => 'thermal-mineral', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],

        // Facial Spa
        ['key' => 'classic-facial', 'name' => 'Classic Facial', 'category' => 'Facial Spa', 'category_slug' => 'facial-spa', 'duration_minutes' => 50, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'hydrating-facial', 'name' => 'Hydrating Facial', 'category' => 'Facial Spa', 'category_slug' => 'facial-spa', 'duration_minutes' => 55, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'anti-aging-facial-unisex', 'name' => 'Anti-Aging Facial', 'category' => 'Facial Spa', 'category_slug' => 'facial-spa', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'acne-facial', 'name' => 'Acne Facial', 'category' => 'Facial Spa', 'category_slug' => 'facial-spa', 'duration_minutes' => 55, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'brightening-facial-unisex', 'name' => 'Brightening Facial', 'category' => 'Facial Spa', 'category_slug' => 'facial-spa', 'duration_minutes' => 55, 'price' => 58.00, 'buffer_minutes' => 0],

        // Advanced Facial
        ['key' => 'gua-sha-facial', 'name' => 'Gua Sha Facial', 'category' => 'Advanced Facial', 'category_slug' => 'advanced-facial', 'duration_minutes' => 50, 'price' => 60.00, 'buffer_minutes' => 0],
        ['key' => 'face-cupping', 'name' => 'Face Cupping', 'category' => 'Advanced Facial', 'category_slug' => 'advanced-facial', 'duration_minutes' => 45, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'microdermabrasion', 'name' => 'Microdermabrasion', 'category' => 'Advanced Facial', 'category_slug' => 'advanced-facial', 'duration_minutes' => 45, 'price' => 65.00, 'buffer_minutes' => 0],
        ['key' => 'hydra-facial-unisex', 'name' => 'Hydra Facial', 'category' => 'Advanced Facial', 'category_slug' => 'advanced-facial', 'duration_minutes' => 55, 'price' => 75.00, 'buffer_minutes' => 0],

        // Hair & Scalp Spa (Hair Spa already exists under Hair Treatments — skipped)
        ['key' => 'scalp-treatment', 'name' => 'Scalp Treatment', 'category' => 'Hair & Scalp Spa', 'category_slug' => 'hair-scalp-spa', 'duration_minutes' => 45, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'japanese-head-spa', 'name' => 'Japanese Head Spa', 'category' => 'Hair & Scalp Spa', 'category_slug' => 'hair-scalp-spa', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 0],
        ['key' => 'hair-scalp-detox', 'name' => 'Hair & Scalp Detox', 'category' => 'Hair & Scalp Spa', 'category_slug' => 'hair-scalp-spa', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 0],

        // Recovery (Contrast Therapy already exists under Hydrotherapy — skipped)
        ['key' => 'cold-plunge', 'name' => 'Cold Plunge', 'category' => 'Recovery', 'category_slug' => 'recovery', 'duration_minutes' => 20, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'ice-bath', 'name' => 'Ice Bath', 'category' => 'Recovery', 'category_slug' => 'recovery', 'duration_minutes' => 20, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'cryotherapy', 'name' => 'Cryotherapy', 'category' => 'Recovery', 'category_slug' => 'recovery', 'duration_minutes' => 20, 'price' => 45.00, 'buffer_minutes' => 0],

        // Mind & Holistic
        ['key' => 'meditation', 'name' => 'Meditation', 'category' => 'Mind & Holistic', 'category_slug' => 'mind-holistic', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'breathwork', 'name' => 'Breathwork', 'category' => 'Mind & Holistic', 'category_slug' => 'mind-holistic', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'yoga', 'name' => 'Yoga', 'category' => 'Mind & Holistic', 'category_slug' => 'mind-holistic', 'duration_minutes' => 60, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'sound-bath', 'name' => 'Sound Bath', 'category' => 'Mind & Holistic', 'category_slug' => 'mind-holistic', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 0],
        ['key' => 'reiki', 'name' => 'Reiki', 'category' => 'Mind & Holistic', 'category_slug' => 'mind-holistic', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 0],

        // Luxury Experiences
        ['key' => 'signature-massage', 'name' => 'Signature Massage', 'category' => 'Luxury Experiences', 'category_slug' => 'luxury-experiences', 'duration_minutes' => 90, 'price' => 120.00, 'buffer_minutes' => 0],
        ['key' => 'full-body-ritual', 'name' => 'Full Body Ritual', 'category' => 'Luxury Experiences', 'category_slug' => 'luxury-experiences', 'duration_minutes' => 120, 'price' => 150.00, 'buffer_minutes' => 0],
        ['key' => 'spa-journey', 'name' => 'Spa Journey', 'category' => 'Luxury Experiences', 'category_slug' => 'luxury-experiences', 'duration_minutes' => 150, 'price' => 180.00, 'buffer_minutes' => 0],
        ['key' => 'private-spa-experience', 'name' => 'Private Spa Experience', 'category' => 'Luxury Experiences', 'category_slug' => 'luxury-experiences', 'duration_minutes' => 180, 'price' => 250.00, 'buffer_minutes' => 0],

        // Mobile Spa
        ['key' => 'mobile-massage', 'name' => 'Mobile Massage', 'category' => 'Mobile Spa', 'category_slug' => 'mobile-spa', 'duration_minutes' => 60, 'price' => 80.00, 'buffer_minutes' => 0],
        ['key' => 'corporate-massage', 'name' => 'Corporate Massage', 'category' => 'Mobile Spa', 'category_slug' => 'mobile-spa', 'duration_minutes' => 30, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'event-massage', 'name' => 'Event Massage', 'category' => 'Mobile Spa', 'category_slug' => 'mobile-spa', 'duration_minutes' => 30, 'price' => 55.00, 'buffer_minutes' => 0],

        // Bridal
        ['key' => 'bridal-spa', 'name' => 'Bridal Spa', 'category' => 'Bridal', 'category_slug' => 'bridal', 'duration_minutes' => 120, 'price' => 180.00, 'buffer_minutes' => 0],
    ],
    'pet' => [
        ['key' => 'pet-bathing', 'name' => 'Bathing', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 0],
        ['key' => 'pet-shampoo-wash', 'name' => 'Shampoo Wash', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 30, 'price' => 22.00, 'buffer_minutes' => 0],
        ['key' => 'pet-conditioning', 'name' => 'Conditioning', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 0],
        ['key' => 'pet-towel-dry', 'name' => 'Towel Dry', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 15, 'price' => 10.00, 'buffer_minutes' => 0],

        ['key' => 'pet-full-body-haircut', 'name' => 'Full Body Haircut', 'category' => 'Haircut & Styling', 'category_slug' => 'haircut-styling', 'duration_minutes' => 60, 'price' => 35.00, 'buffer_minutes' => 0],
        ['key' => 'pet-breed-specific-haircut', 'name' => 'Breed-specific Haircut', 'category' => 'Haircut & Styling', 'category_slug' => 'haircut-styling', 'duration_minutes' => 70, 'price' => 45.00, 'buffer_minutes' => 0],
        ['key' => 'pet-trimming', 'name' => 'Trimming', 'category' => 'Haircut & Styling', 'category_slug' => 'haircut-styling', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 0],

        ['key' => 'pet-nail-trimming', 'name' => 'Nail Trimming', 'category' => 'Nail & Paw Care', 'category_slug' => 'nail-paw-care', 'duration_minutes' => 20, 'price' => 12.00, 'buffer_minutes' => 0],
        ['key' => 'pet-paw-cleaning', 'name' => 'Paw Cleaning', 'category' => 'Nail & Paw Care', 'category_slug' => 'nail-paw-care', 'duration_minutes' => 20, 'price' => 14.00, 'buffer_minutes' => 0],
        ['key' => 'pet-paw-massage', 'name' => 'Paw Massage', 'category' => 'Nail & Paw Care', 'category_slug' => 'nail-paw-care', 'duration_minutes' => 20, 'price' => 16.00, 'buffer_minutes' => 0],

        ['key' => 'pet-ear-cleaning', 'name' => 'Ear Cleaning', 'category' => 'Ear & Hygiene Cleaning', 'category_slug' => 'ear-hygiene-cleaning', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 0],
        ['key' => 'pet-eye-cleaning', 'name' => 'Eye Cleaning', 'category' => 'Ear & Hygiene Cleaning', 'category_slug' => 'ear-hygiene-cleaning', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 0],
        ['key' => 'pet-sanitary-cleaning', 'name' => 'Sanitary Cleaning', 'category' => 'Ear & Hygiene Cleaning', 'category_slug' => 'ear-hygiene-cleaning', 'duration_minutes' => 25, 'price' => 18.00, 'buffer_minutes' => 0],

        ['key' => 'pet-de-shedding-treatment', 'name' => 'De-shedding Treatment', 'category' => 'De-shedding & Coat Care', 'category_slug' => 'de-shedding-coat-care', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 0],
        ['key' => 'pet-brushing', 'name' => 'Brushing', 'category' => 'De-shedding & Coat Care', 'category_slug' => 'de-shedding-coat-care', 'duration_minutes' => 25, 'price' => 15.00, 'buffer_minutes' => 0],
        ['key' => 'pet-coat-conditioning', 'name' => 'Coat Conditioning', 'category' => 'De-shedding & Coat Care', 'category_slug' => 'de-shedding-coat-care', 'duration_minutes' => 30, 'price' => 22.00, 'buffer_minutes' => 0],

        ['key' => 'pet-anti-tick-bath', 'name' => 'Anti-tick Bath', 'category' => 'Tick & Flea Treatment', 'category_slug' => 'tick-flea-treatment', 'duration_minutes' => 40, 'price' => 28.00, 'buffer_minutes' => 0],
        ['key' => 'pet-flea-removal-treatment', 'name' => 'Flea Removal Treatment', 'category' => 'Tick & Flea Treatment', 'category_slug' => 'tick-flea-treatment', 'duration_minutes' => 45, 'price' => 32.00, 'buffer_minutes' => 0],

        ['key' => 'pet-full-grooming-package', 'name' => 'Full Grooming Package', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 90, 'price' => 55.00, 'buffer_minutes' => 0],
        ['key' => 'pet-bath-haircut-combo', 'name' => 'Bath + Haircut Combo', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 80, 'price' => 50.00, 'buffer_minutes' => 0],
        ['key' => 'pet-spa-package', 'name' => 'Spa Package', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 100, 'price' => 65.00, 'buffer_minutes' => 0],
    ],
];
