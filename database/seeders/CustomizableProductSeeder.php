<?php

namespace Database\Seeders;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Database\Seeder;

/**
 * Seeds the initial customizable products required by the application.
 *
 * This seeder is idempotent: it updates an existing product when matched
 * by product_name, or creates it if it does not already exist.
 *
 * Run directly with:
 *   php artisan db:seed --class=CustomizableProductSeeder
 */
class CustomizableProductSeeder extends Seeder
{
    public function run(): void
    {
        CustomizablePrintProduct::updateOrCreate(
            ['product_name' => 'dolore in ut (Custom)'],
            [
                'description' => "Features:\n• 100% cotton\n• Soft feel\n• Available in sizes S–4XL\n• Unisex regular fit t-shirts\n• Available in 25 colours\n• Our T-shirts and prints are top quality and designed to last under proper care\n\nProper care:\n• Iron inside out\n\nIf you have any questions, please contact us. We are happy to help!",
                'image_reference' => '/images/products/personalized-unisex-regular-fit-adults-tshirt.avif',
                'visibility_status' => ProductVisibilityStatus::Active,
                'design_profile_key' => 'tshirt-classic',
            ]
        );

        $this->command->info('Customizable product seeded successfully.');
    }
}