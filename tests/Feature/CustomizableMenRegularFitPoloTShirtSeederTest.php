<?php

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Database\Seeders\CustomizableMenRegularFitPoloTShirtSeeder;

test('men regular fit polo t-shirt seeder creates the profile-backed product', function () {
    $this->seed(CustomizableMenRegularFitPoloTShirtSeeder::class);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', "Men's Regular Fit Polo T-Shirt")
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('polo-unisex')
        ->and($product?->image_reference)->toBe('/images/products/personalized-men-regular-fit-polo-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});

test('men regular fit polo t-shirt seeder is idempotent by product name', function () {
    CustomizablePrintProduct::query()->create([
        'product_name' => "Men's Regular Fit Polo T-Shirt",
        'description' => 'Old description',
        'image_reference' => '/images/old.avif',
        'visibility_status' => ProductVisibilityStatus::Inactive,
        'design_profile_key' => null,
    ]);

    $this->seed(CustomizableMenRegularFitPoloTShirtSeeder::class);

    expect(
        CustomizablePrintProduct::query()
            ->where('product_name', "Men's Regular Fit Polo T-Shirt")
            ->count()
    )->toBe(1);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', "Men's Regular Fit Polo T-Shirt")
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('polo-unisex')
        ->and($product?->image_reference)->toBe('/images/products/personalized-men-regular-fit-polo-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});
