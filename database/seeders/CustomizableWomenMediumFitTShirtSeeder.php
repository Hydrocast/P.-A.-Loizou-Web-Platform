<?php

namespace Database\Seeders;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Database\Seeder;

class CustomizableWomenMediumFitTShirtSeeder extends Seeder
{
    /**
     * Seed the customizable women medium fit t-shirt product.
     */
    public function run(): void
    {
        CustomizablePrintProduct::updateOrCreate(
            ['product_name' => 'Personalized Women Medium Fit T-Shirt'],
            [
                'description' => "Features:\n• 100% cotton\n• Soft feel\n• Available in sizes XS–XXL\n• Women medium fit t-shirts\n• Available in 25 colours\n• Our T-shirts and prints are top quality and designed to last under proper care\n\nProper care:\n• Iron inside out\n\nIf you have any questions, please contact us. We are happy to help!",
                'image_reference' => '/images/products/personalized-women-medium-fit-tshirt.avif',
                'visibility_status' => ProductVisibilityStatus::Active,
                'design_profile_key' => 'tshirt-women-medium-fit',
            ]
        );
    }
}