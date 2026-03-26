<?php

namespace Database\Seeders;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Database\Seeder;

class CustomizableKidsTShirtSeeder extends Seeder
{
    /**
     * Seed the customizable kids t-shirt product.
     */
    public function run(): void
    {
        CustomizablePrintProduct::updateOrCreate(
            ['product_name' => 'Personalized Kids T-Shirt'],
            [
                'description' => "Features:\n• 100% cotton\n• Soft feel\n• Available in sizes 1/2–12/14\n• Kids t-shirts\n• Available in 25 colours\n• Our T-shirts and prints are top quality and designed to last under proper care\n\nProper care:\n• Iron inside out\n\nIf you have any questions, please contact us. We are happy to help!",
                'image_reference' => '/images/products/personalized-kids-tshirt.avif',
                'visibility_status' => ProductVisibilityStatus::Active,
                'design_profile_key' => 'tshirt-kids',
            ]
        );
    }
}