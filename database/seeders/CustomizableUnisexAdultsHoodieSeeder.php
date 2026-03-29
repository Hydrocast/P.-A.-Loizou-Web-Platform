<?php

namespace Database\Seeders;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Database\Seeder;

class CustomizableUnisexAdultsHoodieSeeder extends Seeder
{
    /**
     * Seed the customizable unisex adults hoodie product.
     */
    public function run(): void
    {
        CustomizablePrintProduct::updateOrCreate(
            ['product_name' => 'Personalized Unisex Adults Hoodie'],
            [
                'description' => "Features:\n• 80% ring-spun combed cotton - 20% polyester\n• Available in sizes XS–5XL\n• Unisex Hoodies\n• Available in 7 colours\n• Our T-shirts and prints are top quality and designed to last under proper care\n\nProper care:\n• Iron inside out\n\nIf you have any questions, please contact us. We are happy to help.",
                'image_reference' => '/images/products/personalized-unisex-adults-hoodie.avif',
                'visibility_status' => ProductVisibilityStatus::Active,
                'design_profile_key' => 'hoodie-unisex',
            ]
        );
    }
}
