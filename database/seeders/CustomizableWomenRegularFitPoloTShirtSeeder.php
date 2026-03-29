<?php

namespace Database\Seeders;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Database\Seeder;

class CustomizableWomenRegularFitPoloTShirtSeeder extends Seeder
{
    /**
     * Seed the customizable women's regular fit polo t-shirt product.
     */
    public function run(): void
    {
        CustomizablePrintProduct::updateOrCreate(
            ['product_name' => "Women's Regular Fit Polo T-Shirt"],
            [
                'description' => "Features:\n• 100% Pre-Shrunk, ring-spun, combed cotton\n• Available in sizes S–XXL\n• Women Regular Fit Polo T-Shirts\n• Available in 23 colours\n• Our T-shirts and prints are top quality and designed to last under proper care\n\nProper care:\n• Iron inside out\n\nIf you have any questions, please contact us. We are happy to help.",
                'image_reference' => '/images/products/personalized-women-regular-fit-polo-tshirt.avif',
                'visibility_status' => ProductVisibilityStatus::Active,
                'design_profile_key' => 'polo-women-regular-fit',
            ]
        );
    }
}
