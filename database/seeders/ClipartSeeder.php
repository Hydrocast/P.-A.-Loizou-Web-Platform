<?php

namespace Database\Seeders;

use App\Models\Clipart;
use Illuminate\Database\Seeder;

/**
 * Seeds the clipart library with an initial set of clipart items.
 *
 * Clipart is defined at deployment and updated by re‑running this seeder.
 * Image files must be placed in the appropriate storage directory.
 *
 * This seeder is idempotent: it uses firstOrCreate keyed on clipart_name.
 * New items are inserted; existing items are left unchanged.
 *
 * Run this seeder when populating a fresh environment:
 *   php artisan db:seed --class=ClipartSeeder
 */
class ClipartSeeder extends Seeder
{
    /**
     * The clipart items to seed. Each entry must have a clipart_name and image_reference.
     * The image_reference should be a path relative to the configured clipart storage disk.
     */
    private array $clipartItems = [

        // Stars & Shapes
        ['clipart_name' => 'Star',           'image_reference' => 'clipart/shapes/star.png'],
        ['clipart_name' => 'Heart',          'image_reference' => 'clipart/shapes/heart.png'],
        ['clipart_name' => 'Circle',         'image_reference' => 'clipart/shapes/circle.png'],
        ['clipart_name' => 'Arrow Right',    'image_reference' => 'clipart/shapes/arrow-right.png'],
        ['clipart_name' => 'Arrow Left',     'image_reference' => 'clipart/shapes/arrow-left.png'],
        ['clipart_name' => 'Checkmark',      'image_reference' => 'clipart/shapes/checkmark.png'],
        ['clipart_name' => 'Banner Ribbon',  'image_reference' => 'clipart/shapes/banner-ribbon.png'],
        ['clipart_name' => 'Crown',          'image_reference' => 'clipart/shapes/crown.png'],

        // Nature
        ['clipart_name' => 'Sun',            'image_reference' => 'clipart/nature/sun.png'],
        ['clipart_name' => 'Flower',         'image_reference' => 'clipart/nature/flower.png'],
        ['clipart_name' => 'Leaf',           'image_reference' => 'clipart/nature/leaf.png'],
        ['clipart_name' => 'Tree',           'image_reference' => 'clipart/nature/tree.png'],
        ['clipart_name' => 'Snowflake',      'image_reference' => 'clipart/nature/snowflake.png'],
        ['clipart_name' => 'Rainbow',        'image_reference' => 'clipart/nature/rainbow.png'],

        // Celebrations
        ['clipart_name' => 'Balloon',        'image_reference' => 'clipart/celebrations/balloon.png'],
        ['clipart_name' => 'Birthday Cake',  'image_reference' => 'clipart/celebrations/birthday-cake.png'],
        ['clipart_name' => 'Confetti',       'image_reference' => 'clipart/celebrations/confetti.png'],
        ['clipart_name' => 'Gift Box',       'image_reference' => 'clipart/celebrations/gift-box.png'],
        ['clipart_name' => 'Party Hat',      'image_reference' => 'clipart/celebrations/party-hat.png'],
        ['clipart_name' => 'Champagne',      'image_reference' => 'clipart/celebrations/champagne.png'],

        // Business & Professional
        ['clipart_name' => 'Briefcase',      'image_reference' => 'clipart/business/briefcase.png'],
        ['clipart_name' => 'Trophy',         'image_reference' => 'clipart/business/trophy.png'],
        ['clipart_name' => 'Handshake',      'image_reference' => 'clipart/business/handshake.png'],
        ['clipart_name' => 'Lightbulb',      'image_reference' => 'clipart/business/lightbulb.png'],
        ['clipart_name' => 'Magnifier',      'image_reference' => 'clipart/business/magnifier.png'],

    ];

    /**
     * Execute the seeder: create clipart records if they don't already exist.
     */
    public function run(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->clipartItems as $item) {
            $result = Clipart::firstOrCreate(
                ['clipart_name' => $item['clipart_name']],
                ['image_reference' => $item['image_reference']]
            );

            $result->wasRecentlyCreated ? $created++ : $skipped++;
        }

        $this->command->info("Clipart seeded: {$created} created, {$skipped} already existed.");
    }
}