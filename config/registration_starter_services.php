<?php

/**
 * Predefined starter services offered at registration, keyed by business_types.slug.
 * Each row: key (unique within slug), name, duration_minutes, price (GBP), optional buffer_minutes.
 */
return [
    'womens' => [
        ['key' => 'full-arms-wax', 'name' => 'Full Arms Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 45, 'price' => 20.00, 'buffer_minutes' => 5],
        ['key' => 'full-legs-wax', 'name' => 'Full Legs Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 60, 'price' => 30.00, 'buffer_minutes' => 5],
        ['key' => 'half-legs-wax', 'name' => 'Half Legs Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 35, 'price' => 18.00, 'buffer_minutes' => 5],
        ['key' => 'underarms-wax', 'name' => 'Underarms Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 20, 'price' => 10.00, 'buffer_minutes' => 5],
        ['key' => 'bikini-wax', 'name' => 'Bikini Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 30, 'price' => 18.00, 'buffer_minutes' => 5],
        ['key' => 'bikini-line-wax', 'name' => 'Bikini Line Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 20, 'price' => 14.00, 'buffer_minutes' => 5],
        ['key' => 'full-body-wax', 'name' => 'Full Body Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 120, 'price' => 65.00, 'buffer_minutes' => 10],
        ['key' => 'face-wax', 'name' => 'Face Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 25, 'price' => 12.00, 'buffer_minutes' => 5],
        ['key' => 'rica-wax', 'name' => 'Rica Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 45, 'price' => 28.00, 'buffer_minutes' => 5],
        ['key' => 'roll-on-wax', 'name' => 'Roll-on Wax', 'category' => 'Waxing & Hair Removal', 'category_slug' => 'waxing-hair-removal', 'duration_minutes' => 30, 'price' => 16.00, 'buffer_minutes' => 5],
        ['key' => 'o3-plus-facial', 'name' => 'O3+ Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 10],
        ['key' => 'gold-facial', 'name' => 'Gold Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 10],
        ['key' => 'wine-facial', 'name' => 'Wine Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 60, 'price' => 62.00, 'buffer_minutes' => 10],
        ['key' => 'brightening-facial', 'name' => 'Brightening Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 55, 'price' => 58.00, 'buffer_minutes' => 10],
        ['key' => 'anti-aging-facial', 'name' => 'Anti-aging Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 70, 'price' => 75.00, 'buffer_minutes' => 10],
        ['key' => 'hydrating-facial', 'name' => 'Hydrating Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 55, 'price' => 56.00, 'buffer_minutes' => 10],
        ['key' => 'ubtan-facial', 'name' => 'Ubtan Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 50, 'price' => 52.00, 'buffer_minutes' => 10],
        ['key' => 'face-cleanup', 'name' => 'Face Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 35, 'price' => 30.00, 'buffer_minutes' => 10],
        ['key' => 'fruit-cleanup', 'name' => 'Fruit Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 40, 'price' => 35.00, 'buffer_minutes' => 10],
        ['key' => 'tan-removal-cleanup', 'name' => 'Tan Removal Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 10],
        ['key' => 'o3-plus-cleanup', 'name' => 'O3+ Cleanup', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 45, 'price' => 45.00, 'buffer_minutes' => 10],
        ['key' => 'face-detan', 'name' => 'Face Detan', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 30, 'price' => 32.00, 'buffer_minutes' => 10],
        ['key' => 'neck-detan', 'name' => 'Neck Detan', 'category' => 'Cleanup & Detan', 'category_slug' => 'cleanup-detan', 'duration_minutes' => 25, 'price' => 28.00, 'buffer_minutes' => 10],
        ['key' => 'basic-manicure', 'name' => 'Basic Manicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 35, 'price' => 20.00, 'buffer_minutes' => 5],
        ['key' => 'basic-pedicure', 'name' => 'Basic Pedicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 40, 'price' => 24.00, 'buffer_minutes' => 5],
        ['key' => 'spa-manicure', 'name' => 'Spa Manicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 50, 'price' => 32.00, 'buffer_minutes' => 10],
        ['key' => 'spa-pedicure', 'name' => 'Spa Pedicure', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 60, 'price' => 38.00, 'buffer_minutes' => 10],
        ['key' => 'gel-polish-addon', 'name' => 'Gel Polish (if added)', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 20, 'price' => 12.00, 'buffer_minutes' => 5],
        ['key' => 'cut-file', 'name' => 'Cut & File', 'category' => 'Manicure & Pedicure', 'category_slug' => 'manicure-pedicure', 'duration_minutes' => 20, 'price' => 10.00, 'buffer_minutes' => 5],
        ['key' => 'haircut-if-applicable', 'name' => 'Haircut (if applicable)', 'category' => 'Hair Care & Styling', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 10],
        ['key' => 'hair-trimming', 'name' => 'Hair Trimming', 'category' => 'Hair Care & Styling', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 5],
        ['key' => 'blow-dry', 'name' => 'Blow Dry', 'category' => 'Hair Care & Styling', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 35, 'price' => 22.00, 'buffer_minutes' => 5],
        ['key' => 'hair-styling', 'name' => 'Hair Styling', 'category' => 'Hair Care & Styling', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 40, 'price' => 28.00, 'buffer_minutes' => 10],
        ['key' => 'hair-ironing', 'name' => 'Hair Ironing', 'category' => 'Hair Care & Styling', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 10],
        ['key' => 'hair-curling', 'name' => 'Hair Curling', 'category' => 'Hair Care & Styling', 'category_slug' => 'hair-care-styling', 'duration_minutes' => 45, 'price' => 32.00, 'buffer_minutes' => 10],
        ['key' => 'face-bleach', 'name' => 'Face Bleach', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 25, 'price' => 18.00, 'buffer_minutes' => 5],
        ['key' => 'full-body-bleach', 'name' => 'Full Body Bleach', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 60, 'price' => 45.00, 'buffer_minutes' => 10],
        ['key' => 'de-tan-treatment', 'name' => 'De-tan Treatment', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 45, 'price' => 35.00, 'buffer_minutes' => 10],
        ['key' => 'skin-lightening-treatment', 'name' => 'Skin Lightening Treatment', 'category' => 'Bleach & Skin Treatments', 'category_slug' => 'bleach-skin-treatments', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 10],
        ['key' => 'wax-facial-package', 'name' => 'Wax + Facial Package', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 90, 'price' => 80.00, 'buffer_minutes' => 10],
        ['key' => 'cleanup-wax-combo', 'name' => 'Cleanup + Wax Combo', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 80, 'price' => 70.00, 'buffer_minutes' => 10],
        ['key' => 'monthly-maintenance-package', 'name' => 'Monthly Maintenance Package', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 120, 'price' => 120.00, 'buffer_minutes' => 15],
        ['key' => 'custom-package', 'name' => 'Custom Package (build your own)', 'category' => 'Packages & Combos', 'category_slug' => 'packages-combos', 'duration_minutes' => 120, 'price' => 130.00, 'buffer_minutes' => 15],
    ],
    'mans' => [
        ['key' => 'haircut', 'name' => 'Haircut', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 30, 'price' => 35.00, 'buffer_minutes' => 10],
        ['key' => 'kids-haircut', 'name' => 'Kids Haircut', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 25, 'price' => 25.00, 'buffer_minutes' => 10],
        ['key' => 'hair-styling', 'name' => 'Hair Styling', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 35, 'price' => 28.00, 'buffer_minutes' => 10],
        ['key' => 'blow-dry', 'name' => 'Blow Dry', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 25, 'price' => 20.00, 'buffer_minutes' => 5],

        ['key' => 'beard-trimming', 'name' => 'Beard Trimming', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 5],
        ['key' => 'beard-styling', 'name' => 'Beard Styling', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 25, 'price' => 18.00, 'buffer_minutes' => 5],
        ['key' => 'clean-shave', 'name' => 'Clean Shave', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 20, 'price' => 14.00, 'buffer_minutes' => 5],
        ['key' => 'beard-design', 'name' => 'Beard Design', 'category' => 'Beard & Shaving', 'category_slug' => 'beard-shaving', 'duration_minutes' => 30, 'price' => 22.00, 'buffer_minutes' => 5],

        ['key' => 'o3-plus-facial-men', 'name' => 'O3+ Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 50, 'price' => 55.00, 'buffer_minutes' => 10],
        ['key' => 'detan-facial-men', 'name' => 'Detan Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 45, 'price' => 48.00, 'buffer_minutes' => 10],
        ['key' => 'brightening-facial-men', 'name' => 'Brightening Facial', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 45, 'price' => 50.00, 'buffer_minutes' => 10],
        ['key' => 'cleanup-men', 'name' => 'Cleanup', 'category' => 'Facials & Skin Care', 'category_slug' => 'facials-skin-care', 'duration_minutes' => 35, 'price' => 35.00, 'buffer_minutes' => 10],

        ['key' => 'hair-colour-application', 'name' => 'Hair Colour Application', 'category' => 'Hair Color', 'category_slug' => 'hair-color', 'duration_minutes' => 50, 'price' => 45.00, 'buffer_minutes' => 10],
        ['key' => 'loreal-hair-colour', 'name' => "L'Oréal Hair Colour", 'category' => 'Hair Color', 'category_slug' => 'hair-color', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 10],
        ['key' => 'grey-coverage', 'name' => 'Grey Coverage', 'category' => 'Hair Color', 'category_slug' => 'hair-color', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 10],

        ['key' => 'pedicure-men', 'name' => 'Pedicure', 'category' => 'Hand & Foot Care', 'category_slug' => 'hand-foot-care', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 10],
        ['key' => 'manicure-basic-men', 'name' => 'Manicure (basic)', 'category' => 'Hand & Foot Care', 'category_slug' => 'hand-foot-care', 'duration_minutes' => 35, 'price' => 25.00, 'buffer_minutes' => 10],
        ['key' => 'nail-cut-file-men', 'name' => 'Nail Cut & File', 'category' => 'Hand & Foot Care', 'category_slug' => 'hand-foot-care', 'duration_minutes' => 20, 'price' => 12.00, 'buffer_minutes' => 5],

        ['key' => 'head-massage', 'name' => 'Head Massage', 'category' => 'Massage & Relaxation', 'category_slug' => 'massage-relaxation', 'duration_minutes' => 25, 'price' => 20.00, 'buffer_minutes' => 5],
        ['key' => 'head-neck-shoulder-massage', 'name' => 'Head-Neck-Shoulder Massage', 'category' => 'Massage & Relaxation', 'category_slug' => 'massage-relaxation', 'duration_minutes' => 35, 'price' => 30.00, 'buffer_minutes' => 10],

        ['key' => 'haircut-beard-combo', 'name' => 'Haircut + Beard Combo', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 50, 'price' => 45.00, 'buffer_minutes' => 10],
        ['key' => 'haircut-facial', 'name' => 'Haircut + Facial', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 75, 'price' => 65.00, 'buffer_minutes' => 10],
        ['key' => 'complete-grooming-package', 'name' => 'Complete Grooming Package', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 110, 'price' => 95.00, 'buffer_minutes' => 15],
    ],
    'unisex' => [
        ['key' => 'haircut-men-women', 'name' => 'Haircut (Men/Women)', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 45, 'price' => 40.00, 'buffer_minutes' => 10],
        ['key' => 'hair-trim-unisex', 'name' => 'Hair Trim', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 30, 'price' => 25.00, 'buffer_minutes' => 5],
        ['key' => 'blow-dry-unisex', 'name' => 'Blow Dry', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 35, 'price' => 28.00, 'buffer_minutes' => 5],
        ['key' => 'hair-styling-unisex', 'name' => 'Hair Styling', 'category' => 'Haircuts & Styling', 'category_slug' => 'haircuts-styling', 'duration_minutes' => 40, 'price' => 32.00, 'buffer_minutes' => 10],

        ['key' => 'hair-spa-unisex', 'name' => 'Hair Spa', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 60, 'price' => 65.00, 'buffer_minutes' => 10],
        ['key' => 'keratin-treatment-unisex', 'name' => 'Keratin Treatment', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 90, 'price' => 120.00, 'buffer_minutes' => 15],
        ['key' => 'smoothening-unisex', 'name' => 'Smoothening', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 90, 'price' => 110.00, 'buffer_minutes' => 15],
        ['key' => 'rebonding-unisex', 'name' => 'Rebonding', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 120, 'price' => 140.00, 'buffer_minutes' => 15],
        ['key' => 'botox-treatment-unisex', 'name' => 'Botox Treatment', 'category' => 'Hair Treatments', 'category_slug' => 'hair-treatments', 'duration_minutes' => 90, 'price' => 130.00, 'buffer_minutes' => 15],

        ['key' => 'global-color-unisex', 'name' => 'Global Color', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 120, 'price' => 95.00, 'buffer_minutes' => 15],
        ['key' => 'root-touch-up-unisex', 'name' => 'Root Touch-up', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 60, 'price' => 55.00, 'buffer_minutes' => 10],
        ['key' => 'highlights-unisex', 'name' => 'Highlights', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 120, 'price' => 110.00, 'buffer_minutes' => 15],
        ['key' => 'balayage-unisex', 'name' => 'Balayage', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 150, 'price' => 140.00, 'buffer_minutes' => 15],
        ['key' => 'fashion-colors-unisex', 'name' => 'Fashion Colors', 'category' => 'Hair Coloring', 'category_slug' => 'hair-coloring', 'duration_minutes' => 120, 'price' => 125.00, 'buffer_minutes' => 15],

        ['key' => 'facial-unisex', 'name' => 'Facial', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 50, 'price' => 50.00, 'buffer_minutes' => 10],
        ['key' => 'cleanup-unisex', 'name' => 'Cleanup', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 40, 'price' => 38.00, 'buffer_minutes' => 10],
        ['key' => 'detan-unisex', 'name' => 'Detan', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 45, 'price' => 42.00, 'buffer_minutes' => 10],
        ['key' => 'skin-treatments-unisex', 'name' => 'Skin Treatments', 'category' => 'Skincare & Facials', 'category_slug' => 'skincare-facials', 'duration_minutes' => 60, 'price' => 60.00, 'buffer_minutes' => 10],

        ['key' => 'haircut-facial-combo-unisex', 'name' => 'Haircut + Facial Combo', 'category' => 'Grooming & Styling Packages', 'category_slug' => 'grooming-styling-packages', 'duration_minutes' => 90, 'price' => 75.00, 'buffer_minutes' => 10],
        ['key' => 'hair-spa-styling-package-unisex', 'name' => 'Hair Spa + Styling Package', 'category' => 'Grooming & Styling Packages', 'category_slug' => 'grooming-styling-packages', 'duration_minutes' => 110, 'price' => 95.00, 'buffer_minutes' => 15],

        ['key' => 'bridal-makeup-unisex', 'name' => 'Bridal Makeup', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 120, 'price' => 180.00, 'buffer_minutes' => 15],
        ['key' => 'party-makeup-unisex', 'name' => 'Party Makeup', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 90, 'price' => 120.00, 'buffer_minutes' => 15],
        ['key' => 'hairstyling-unisex', 'name' => 'Hairstyling', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 60, 'price' => 70.00, 'buffer_minutes' => 10],
        ['key' => 'saree-draping-unisex', 'name' => 'Saree Draping', 'category' => 'Special Occasion / Bridal Grooming', 'category_slug' => 'special-occasion-bridal-grooming', 'duration_minutes' => 40, 'price' => 50.00, 'buffer_minutes' => 10],
    ],
    'pet' => [
        ['key' => 'pet-bathing', 'name' => 'Bathing', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 5],
        ['key' => 'pet-shampoo-wash', 'name' => 'Shampoo Wash', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 30, 'price' => 22.00, 'buffer_minutes' => 5],
        ['key' => 'pet-conditioning', 'name' => 'Conditioning', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 5],
        ['key' => 'pet-towel-dry', 'name' => 'Towel Dry', 'category' => 'Bath & Basic Cleaning', 'category_slug' => 'bath-basic-cleaning', 'duration_minutes' => 15, 'price' => 10.00, 'buffer_minutes' => 5],

        ['key' => 'pet-full-body-haircut', 'name' => 'Full Body Haircut', 'category' => 'Haircut & Styling', 'category_slug' => 'haircut-styling', 'duration_minutes' => 60, 'price' => 35.00, 'buffer_minutes' => 10],
        ['key' => 'pet-breed-specific-haircut', 'name' => 'Breed-specific Haircut', 'category' => 'Haircut & Styling', 'category_slug' => 'haircut-styling', 'duration_minutes' => 70, 'price' => 45.00, 'buffer_minutes' => 10],
        ['key' => 'pet-trimming', 'name' => 'Trimming', 'category' => 'Haircut & Styling', 'category_slug' => 'haircut-styling', 'duration_minutes' => 30, 'price' => 20.00, 'buffer_minutes' => 5],

        ['key' => 'pet-nail-trimming', 'name' => 'Nail Trimming', 'category' => 'Nail & Paw Care', 'category_slug' => 'nail-paw-care', 'duration_minutes' => 20, 'price' => 12.00, 'buffer_minutes' => 5],
        ['key' => 'pet-paw-cleaning', 'name' => 'Paw Cleaning', 'category' => 'Nail & Paw Care', 'category_slug' => 'nail-paw-care', 'duration_minutes' => 20, 'price' => 14.00, 'buffer_minutes' => 5],
        ['key' => 'pet-paw-massage', 'name' => 'Paw Massage', 'category' => 'Nail & Paw Care', 'category_slug' => 'nail-paw-care', 'duration_minutes' => 20, 'price' => 16.00, 'buffer_minutes' => 5],

        ['key' => 'pet-ear-cleaning', 'name' => 'Ear Cleaning', 'category' => 'Ear & Hygiene Cleaning', 'category_slug' => 'ear-hygiene-cleaning', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 5],
        ['key' => 'pet-eye-cleaning', 'name' => 'Eye Cleaning', 'category' => 'Ear & Hygiene Cleaning', 'category_slug' => 'ear-hygiene-cleaning', 'duration_minutes' => 20, 'price' => 15.00, 'buffer_minutes' => 5],
        ['key' => 'pet-sanitary-cleaning', 'name' => 'Sanitary Cleaning', 'category' => 'Ear & Hygiene Cleaning', 'category_slug' => 'ear-hygiene-cleaning', 'duration_minutes' => 25, 'price' => 18.00, 'buffer_minutes' => 5],

        ['key' => 'pet-de-shedding-treatment', 'name' => 'De-shedding Treatment', 'category' => 'De-shedding & Coat Care', 'category_slug' => 'de-shedding-coat-care', 'duration_minutes' => 45, 'price' => 30.00, 'buffer_minutes' => 10],
        ['key' => 'pet-brushing', 'name' => 'Brushing', 'category' => 'De-shedding & Coat Care', 'category_slug' => 'de-shedding-coat-care', 'duration_minutes' => 25, 'price' => 15.00, 'buffer_minutes' => 5],
        ['key' => 'pet-coat-conditioning', 'name' => 'Coat Conditioning', 'category' => 'De-shedding & Coat Care', 'category_slug' => 'de-shedding-coat-care', 'duration_minutes' => 30, 'price' => 22.00, 'buffer_minutes' => 5],

        ['key' => 'pet-anti-tick-bath', 'name' => 'Anti-tick Bath', 'category' => 'Tick & Flea Treatment', 'category_slug' => 'tick-flea-treatment', 'duration_minutes' => 40, 'price' => 28.00, 'buffer_minutes' => 10],
        ['key' => 'pet-flea-removal-treatment', 'name' => 'Flea Removal Treatment', 'category' => 'Tick & Flea Treatment', 'category_slug' => 'tick-flea-treatment', 'duration_minutes' => 45, 'price' => 32.00, 'buffer_minutes' => 10],

        ['key' => 'pet-full-grooming-package', 'name' => 'Full Grooming Package', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 90, 'price' => 55.00, 'buffer_minutes' => 15],
        ['key' => 'pet-bath-haircut-combo', 'name' => 'Bath + Haircut Combo', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 80, 'price' => 50.00, 'buffer_minutes' => 10],
        ['key' => 'pet-spa-package', 'name' => 'Spa Package', 'category' => 'Grooming Packages', 'category_slug' => 'grooming-packages', 'duration_minutes' => 100, 'price' => 65.00, 'buffer_minutes' => 15],
    ],
];
