<?php

/**
 * Shared reusable designer profiles.
 *
 * A design profile represents a reusable customization setup that can be
 * assigned to one or more customizable products through the
 * customizable_print_products.design_profile_key column.
 *
 * This allows multiple catalog products to share the same:
 * - FabricJS template configuration
 * - available shirt colours
 * - mockup image set
 * - thumbnail image set
 *
 * while still keeping product-specific catalog data such as:
 * - product name
 * - description
 * - catalog image
 * - pricing tiers
 *
 * Profile structure:
 * - profile_name: human-readable label for developers/admins
 * - template_config: FabricJS workspace configuration
 * - default_color_key: default selected colour when none is explicitly chosen
 * - colors: selectable colour options shown in Product Detail and Design Workspace
 */
return [

    'tshirt-classic' => [
        'profile_name' => 'Classic T-Shirt',
        'template_config' => [
            'canvas_width' => 1200,
            'canvas_height' => 1400,
            'background_image' => null,
            'print_area' => [
                'left' => 0,
                'top' => 0,
                'width' => 1200,
                'height' => 1400,
            ],
        ],
        'workspace_options' => [
            'print_sides' => [
                'enabled' => true,
                'default' => 'front_only',
                'choices' => [
                    [
                        'value' => 'front_only',
                        'label' => 'Front Side',
                    ],
                    [
                        'value' => 'front_and_back',
                        'label' => 'Both Sides (Front and Back)',
                    ],
                ],
            ],
        ],
        'product_details' => [
            'type' => 'size_guide',
            'title' => 'Unisex T-Shirts Size Guide',
            'columns' => ['Size', 'Width', 'Length'],
            'rows' => [
                ['XS', '47 cm', '67 cm'],
                ['S', '50 cm', '69 cm'],
                ['M', '53 cm', '72 cm'],
                ['L', '56 cm', '74 cm'],
                ['XL', '59 cm', '76 cm'],
                ['XXL', '62 cm', '78 cm'],
                ['3XL', '65 cm', '81 cm'],
                ['4XL', '70 cm', '83 cm'],
            ],
        ],
        'default_color_key' => 'white',
        'colors' => [
            [
                'id' => 'white',
                'label' => 'White',
                'swatch_hex' => '#F5F5F5',
                'mockup_image_url' => '/images/designer/tshirts/white.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/white.webp',
            ],
            [
                'id' => 'ash',
                'label' => 'Ash',
                'swatch_hex' => '#D6D6D6',
                'mockup_image_url' => '/images/designer/tshirts/ash.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/ash.webp',
            ],
            [
                'id' => 'sport-grey',
                'label' => 'Sport Grey',
                'swatch_hex' => '#9CA3AF',
                'mockup_image_url' => '/images/designer/tshirts/sport-grey.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sport-grey.webp',
            ],
            [
                'id' => 'dark-grey',
                'label' => 'Dark Grey',
                'swatch_hex' => '#6B7280',
                'mockup_image_url' => '/images/designer/tshirts/dark-grey.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/dark-grey.webp',
            ],
            [
                'id' => 'black',
                'label' => 'Black',
                'swatch_hex' => '#1F2937',
                'mockup_image_url' => '/images/designer/tshirts/black.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/black.webp',
            ],
            [
                'id' => 'navy',
                'label' => 'Navy',
                'swatch_hex' => '#3730A3',
                'mockup_image_url' => '/images/designer/tshirts/navy.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/navy.webp',
            ],
            [
                'id' => 'royal-blue',
                'label' => 'Royal Blue',
                'swatch_hex' => '#2563EB',
                'mockup_image_url' => '/images/designer/tshirts/royal-blue.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/royal-blue.webp',
            ],
            [
                'id' => 'sky-blue',
                'label' => 'Sky Blue',
                'swatch_hex' => '#93C5FD',
                'mockup_image_url' => '/images/designer/tshirts/sky-blue.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sky-blue.webp',
            ],
            [
                'id' => 'atoll',
                'label' => 'Atoll',
                'swatch_hex' => '#22B8CF',
                'mockup_image_url' => '/images/designer/tshirts/atoll.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/atoll.webp',
            ],
            [
                'id' => 'real-turquoise',
                'label' => 'Real Turquoise',
                'swatch_hex' => '#14B8A6',
                'mockup_image_url' => '/images/designer/tshirts/real-turquoise.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/real-turquoise.webp',
            ],
            [
                'id' => 'bottle-green',
                'label' => 'Bottle Green',
                'swatch_hex' => '#166534',
                'mockup_image_url' => '/images/designer/tshirts/bottle-green.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/bottle-green.webp',
            ],
            [
                'id' => 'kelly-green',
                'label' => 'Kelly Green',
                'swatch_hex' => '#22C55E',
                'mockup_image_url' => '/images/designer/tshirts/kelly-green.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/kelly-green.webp',
            ],
            [
                'id' => 'pistachio',
                'label' => 'Pistachio',
                'swatch_hex' => '#9FD38C',
                'mockup_image_url' => '/images/designer/tshirts/pistachio.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/pistachio.webp',
            ],
            [
                'id' => 'sand',
                'label' => 'Sand',
                'swatch_hex' => '#D6C6A5',
                'mockup_image_url' => '/images/designer/tshirts/sand.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sand.webp',
            ],
            [
                'id' => 'natural',
                'label' => 'Natural',
                'swatch_hex' => '#E5E1D8',
                'mockup_image_url' => '/images/designer/tshirts/natural.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/natural.webp',
            ],
            [
                'id' => 'khaki',
                'label' => 'Khaki',
                'swatch_hex' => '#B8A47A',
                'mockup_image_url' => '/images/designer/tshirts/khaki.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/khaki.webp',
            ],
            [
                'id' => 'millenial-khaki',
                'label' => 'Millenial Khaki',
                'swatch_hex' => '#C2B280',
                'mockup_image_url' => '/images/designer/tshirts/millenial-khaki.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/millenial-khaki.webp',
            ],
            [
                'id' => 'orange',
                'label' => 'Orange',
                'swatch_hex' => '#EA580C',
                'mockup_image_url' => '/images/designer/tshirts/orange.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/orange.webp',
            ],
            [
                'id' => 'gold',
                'label' => 'Gold',
                'swatch_hex' => '#EAB308',
                'mockup_image_url' => '/images/designer/tshirts/gold.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/gold.webp',
            ],
            [
                'id' => 'red',
                'label' => 'Red',
                'swatch_hex' => '#DC2626',
                'mockup_image_url' => '/images/designer/tshirts/red.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/red.webp',
            ],
            [
                'id' => 'burgundy',
                'label' => 'Burgundy',
                'swatch_hex' => '#7F1D1D',
                'mockup_image_url' => '/images/designer/tshirts/burgundy.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/burgundy.webp',
            ],
            [
                'id' => 'millenial-pink',
                'label' => 'Millenial Pink',
                'swatch_hex' => '#E9C7C7',
                'mockup_image_url' => '/images/designer/tshirts/millenial-pink.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/millenial-pink.webp',
            ],
            [
                'id' => 'fuchsia',
                'label' => 'Fuchsia',
                'swatch_hex' => '#EC4899',
                'mockup_image_url' => '/images/designer/tshirts/fuchsia.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/fuchsia.webp',
            ],
            [
                'id' => 'urban-purple',
                'label' => 'Urban Purple',
                'swatch_hex' => '#6D28D9',
                'mockup_image_url' => '/images/designer/tshirts/urban-purple.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/urban-purple.webp',
            ],
            [
                'id' => 'denim',
                'label' => 'Denim',
                'swatch_hex' => '#64748B',
                'mockup_image_url' => '/images/designer/tshirts/denim.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/denim.webp',
            ],
        ],
    ],

    'tshirt-women-medium-fit' => [
        'profile_name' => 'Classic T-Shirt',
        'template_config' => [
            'canvas_width' => 1200,
            'canvas_height' => 1400,
            'background_image' => null,
            'print_area' => [
                'left' => 0,
                'top' => 0,
                'width' => 1200,
                'height' => 1400,
            ],
        ],
        'workspace_options' => [
            'print_sides' => [
                'enabled' => true,
                'default' => 'front_only',
                'choices' => [
                    [
                        'value' => 'front_only',
                        'label' => 'Front Side',
                    ],
                    [
                        'value' => 'front_and_back',
                        'label' => 'Both Sides (Front and Back)',
                    ],
                ],
            ],
        ],
        'product_details' => [
            'type' => 'size_guide',
            'title' => 'Women Medium Fit T-Shirts Size Chart',
            'columns' => ['Size', 'Width', 'Length'],
            'rows' => [
                ['XS', '41 cm', '58 cm'],
                ['S', '44 cm', '60 cm'],
                ['M', '47 cm', '62 cm'],
                ['L', '50 cm', '64 cm'],
                ['XL', '53 cm', '66 cm'],
                ['XXL', '56 cm', '68 cm'],
            ],
        ],
        'default_color_key' => 'white',
        'colors' => [
            [
                'id' => 'white',
                'label' => 'White',
                'swatch_hex' => '#F5F5F5',
                'mockup_image_url' => '/images/designer/tshirts/white.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/white.webp',
            ],
            [
                'id' => 'ash',
                'label' => 'Ash',
                'swatch_hex' => '#D6D6D6',
                'mockup_image_url' => '/images/designer/tshirts/ash.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/ash.webp',
            ],
            [
                'id' => 'sport-grey',
                'label' => 'Sport Grey',
                'swatch_hex' => '#9CA3AF',
                'mockup_image_url' => '/images/designer/tshirts/sport-grey.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sport-grey.webp',
            ],
            [
                'id' => 'dark-grey',
                'label' => 'Dark Grey',
                'swatch_hex' => '#6B7280',
                'mockup_image_url' => '/images/designer/tshirts/dark-grey.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/dark-grey.webp',
            ],
            [
                'id' => 'black',
                'label' => 'Black',
                'swatch_hex' => '#1F2937',
                'mockup_image_url' => '/images/designer/tshirts/black.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/black.webp',
            ],
            [
                'id' => 'navy',
                'label' => 'Navy',
                'swatch_hex' => '#3730A3',
                'mockup_image_url' => '/images/designer/tshirts/navy.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/navy.webp',
            ],
            [
                'id' => 'royal-blue',
                'label' => 'Royal Blue',
                'swatch_hex' => '#2563EB',
                'mockup_image_url' => '/images/designer/tshirts/royal-blue.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/royal-blue.webp',
            ],
            [
                'id' => 'sky-blue',
                'label' => 'Sky Blue',
                'swatch_hex' => '#93C5FD',
                'mockup_image_url' => '/images/designer/tshirts/sky-blue.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sky-blue.webp',
            ],
            [
                'id' => 'atoll',
                'label' => 'Atoll',
                'swatch_hex' => '#22B8CF',
                'mockup_image_url' => '/images/designer/tshirts/atoll.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/atoll.webp',
            ],
            [
                'id' => 'real-turquoise',
                'label' => 'Real Turquoise',
                'swatch_hex' => '#14B8A6',
                'mockup_image_url' => '/images/designer/tshirts/real-turquoise.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/real-turquoise.webp',
            ],
            [
                'id' => 'bottle-green',
                'label' => 'Bottle Green',
                'swatch_hex' => '#166534',
                'mockup_image_url' => '/images/designer/tshirts/bottle-green.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/bottle-green.webp',
            ],
            [
                'id' => 'kelly-green',
                'label' => 'Kelly Green',
                'swatch_hex' => '#22C55E',
                'mockup_image_url' => '/images/designer/tshirts/kelly-green.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/kelly-green.webp',
            ],
            [
                'id' => 'pistachio',
                'label' => 'Pistachio',
                'swatch_hex' => '#9FD38C',
                'mockup_image_url' => '/images/designer/tshirts/pistachio.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/pistachio.webp',
            ],
            [
                'id' => 'sand',
                'label' => 'Sand',
                'swatch_hex' => '#D6C6A5',
                'mockup_image_url' => '/images/designer/tshirts/sand.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sand.webp',
            ],
            [
                'id' => 'natural',
                'label' => 'Natural',
                'swatch_hex' => '#E5E1D8',
                'mockup_image_url' => '/images/designer/tshirts/natural.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/natural.webp',
            ],
            [
                'id' => 'khaki',
                'label' => 'Khaki',
                'swatch_hex' => '#B8A47A',
                'mockup_image_url' => '/images/designer/tshirts/khaki.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/khaki.webp',
            ],
            [
                'id' => 'millenial-khaki',
                'label' => 'Millenial Khaki',
                'swatch_hex' => '#C2B280',
                'mockup_image_url' => '/images/designer/tshirts/millenial-khaki.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/millenial-khaki.webp',
            ],
            [
                'id' => 'orange',
                'label' => 'Orange',
                'swatch_hex' => '#EA580C',
                'mockup_image_url' => '/images/designer/tshirts/orange.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/orange.webp',
            ],
            [
                'id' => 'gold',
                'label' => 'Gold',
                'swatch_hex' => '#EAB308',
                'mockup_image_url' => '/images/designer/tshirts/gold.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/gold.webp',
            ],
            [
                'id' => 'red',
                'label' => 'Red',
                'swatch_hex' => '#DC2626',
                'mockup_image_url' => '/images/designer/tshirts/red.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/red.webp',
            ],
            [
                'id' => 'burgundy',
                'label' => 'Burgundy',
                'swatch_hex' => '#7F1D1D',
                'mockup_image_url' => '/images/designer/tshirts/burgundy.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/burgundy.webp',
            ],
            [
                'id' => 'millenial-pink',
                'label' => 'Millenial Pink',
                'swatch_hex' => '#E9C7C7',
                'mockup_image_url' => '/images/designer/tshirts/millenial-pink.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/millenial-pink.webp',
            ],
            [
                'id' => 'fuchsia',
                'label' => 'Fuchsia',
                'swatch_hex' => '#EC4899',
                'mockup_image_url' => '/images/designer/tshirts/fuchsia.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/fuchsia.webp',
            ],
            [
                'id' => 'urban-purple',
                'label' => 'Urban Purple',
                'swatch_hex' => '#6D28D9',
                'mockup_image_url' => '/images/designer/tshirts/urban-purple.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/urban-purple.webp',
            ],
            [
                'id' => 'denim',
                'label' => 'Denim',
                'swatch_hex' => '#64748B',
                'mockup_image_url' => '/images/designer/tshirts/denim.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/denim.webp',
            ],
        ],
    ],

    'tshirt-kids' => [
        'profile_name' => 'Classic T-Shirt',
        'template_config' => [
            'canvas_width' => 1200,
            'canvas_height' => 1400,
            'background_image' => null,
            'print_area' => [
                'left' => 0,
                'top' => 0,
                'width' => 1200,
                'height' => 1400,
            ],
        ],
        'workspace_options' => [
            'print_sides' => [
                'enabled' => true,
                'default' => 'front_only',
                'choices' => [
                    [
                        'value' => 'front_only',
                        'label' => 'Front Side',
                    ],
                    [
                        'value' => 'front_and_back',
                        'label' => 'Both Sides (Front and Back)',
                    ],
                ],
            ],
        ],
        'product_details' => [
            'type' => 'size_guide',
            'title' => 'Kids T-Shirts Size Chart',
            'columns' => ['Size', 'Width', 'Length'],
            'rows' => [
                ['1/2', '17 cm', '42 cm'],
                ['3/4', '30 cm', '45 cm'],
                ['5/6', '33 cm', '48 cm'],
                ['7/8', '36 cm', '51 cm'],
                ['9/11', '39 cm', '54 cm'],
                ['12/14', '43 cm', '58 cm'],
            ],
        ],
        'default_color_key' => 'white',
        'colors' => [
            [
                'id' => 'white',
                'label' => 'White',
                'swatch_hex' => '#F5F5F5',
                'mockup_image_url' => '/images/designer/tshirts/white.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/white.webp',
            ],
            [
                'id' => 'ash',
                'label' => 'Ash',
                'swatch_hex' => '#D6D6D6',
                'mockup_image_url' => '/images/designer/tshirts/ash.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/ash.webp',
            ],
            [
                'id' => 'sport-grey',
                'label' => 'Sport Grey',
                'swatch_hex' => '#9CA3AF',
                'mockup_image_url' => '/images/designer/tshirts/sport-grey.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sport-grey.webp',
            ],
            [
                'id' => 'dark-grey',
                'label' => 'Dark Grey',
                'swatch_hex' => '#6B7280',
                'mockup_image_url' => '/images/designer/tshirts/dark-grey.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/dark-grey.webp',
            ],
            [
                'id' => 'black',
                'label' => 'Black',
                'swatch_hex' => '#1F2937',
                'mockup_image_url' => '/images/designer/tshirts/black.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/black.webp',
            ],
            [
                'id' => 'navy',
                'label' => 'Navy',
                'swatch_hex' => '#3730A3',
                'mockup_image_url' => '/images/designer/tshirts/navy.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/navy.webp',
            ],
            [
                'id' => 'royal-blue',
                'label' => 'Royal Blue',
                'swatch_hex' => '#2563EB',
                'mockup_image_url' => '/images/designer/tshirts/royal-blue.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/royal-blue.webp',
            ],
            [
                'id' => 'sky-blue',
                'label' => 'Sky Blue',
                'swatch_hex' => '#93C5FD',
                'mockup_image_url' => '/images/designer/tshirts/sky-blue.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sky-blue.webp',
            ],
            [
                'id' => 'atoll',
                'label' => 'Atoll',
                'swatch_hex' => '#22B8CF',
                'mockup_image_url' => '/images/designer/tshirts/atoll.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/atoll.webp',
            ],
            [
                'id' => 'real-turquoise',
                'label' => 'Real Turquoise',
                'swatch_hex' => '#14B8A6',
                'mockup_image_url' => '/images/designer/tshirts/real-turquoise.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/real-turquoise.webp',
            ],
            [
                'id' => 'bottle-green',
                'label' => 'Bottle Green',
                'swatch_hex' => '#166534',
                'mockup_image_url' => '/images/designer/tshirts/bottle-green.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/bottle-green.webp',
            ],
            [
                'id' => 'kelly-green',
                'label' => 'Kelly Green',
                'swatch_hex' => '#22C55E',
                'mockup_image_url' => '/images/designer/tshirts/kelly-green.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/kelly-green.webp',
            ],
            [
                'id' => 'pistachio',
                'label' => 'Pistachio',
                'swatch_hex' => '#9FD38C',
                'mockup_image_url' => '/images/designer/tshirts/pistachio.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/pistachio.webp',
            ],
            [
                'id' => 'sand',
                'label' => 'Sand',
                'swatch_hex' => '#D6C6A5',
                'mockup_image_url' => '/images/designer/tshirts/sand.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/sand.webp',
            ],
            [
                'id' => 'natural',
                'label' => 'Natural',
                'swatch_hex' => '#E5E1D8',
                'mockup_image_url' => '/images/designer/tshirts/natural.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/natural.webp',
            ],
            [
                'id' => 'khaki',
                'label' => 'Khaki',
                'swatch_hex' => '#B8A47A',
                'mockup_image_url' => '/images/designer/tshirts/khaki.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/khaki.webp',
            ],
            [
                'id' => 'millenial-khaki',
                'label' => 'Millenial Khaki',
                'swatch_hex' => '#C2B280',
                'mockup_image_url' => '/images/designer/tshirts/millenial-khaki.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/millenial-khaki.webp',
            ],
            [
                'id' => 'orange',
                'label' => 'Orange',
                'swatch_hex' => '#EA580C',
                'mockup_image_url' => '/images/designer/tshirts/orange.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/orange.webp',
            ],
            [
                'id' => 'gold',
                'label' => 'Gold',
                'swatch_hex' => '#EAB308',
                'mockup_image_url' => '/images/designer/tshirts/gold.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/gold.webp',
            ],
            [
                'id' => 'red',
                'label' => 'Red',
                'swatch_hex' => '#DC2626',
                'mockup_image_url' => '/images/designer/tshirts/red.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/red.webp',
            ],
            [
                'id' => 'burgundy',
                'label' => 'Burgundy',
                'swatch_hex' => '#7F1D1D',
                'mockup_image_url' => '/images/designer/tshirts/burgundy.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/burgundy.webp',
            ],
            [
                'id' => 'millenial-pink',
                'label' => 'Millenial Pink',
                'swatch_hex' => '#E9C7C7',
                'mockup_image_url' => '/images/designer/tshirts/millenial-pink.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/millenial-pink.webp',
            ],
            [
                'id' => 'fuchsia',
                'label' => 'Fuchsia',
                'swatch_hex' => '#EC4899',
                'mockup_image_url' => '/images/designer/tshirts/fuchsia.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/fuchsia.webp',
            ],
            [
                'id' => 'urban-purple',
                'label' => 'Urban Purple',
                'swatch_hex' => '#6D28D9',
                'mockup_image_url' => '/images/designer/tshirts/urban-purple.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/urban-purple.webp',
            ],
            [
                'id' => 'denim',
                'label' => 'Denim',
                'swatch_hex' => '#64748B',
                'mockup_image_url' => '/images/designer/tshirts/denim.webp',
                'thumbnail_image_url' => '/images/designer/tshirts/denim.webp',
            ],
        ],
    ],

];
