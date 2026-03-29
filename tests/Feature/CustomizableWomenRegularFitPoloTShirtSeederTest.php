<?php

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Database\Seeders\CustomizableWomenRegularFitPoloTShirtSeeder;

test('women regular fit polo t-shirt seeder creates the profile-backed product', function () {
    $this->seed(CustomizableWomenRegularFitPoloTShirtSeeder::class);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', "Women's Regular Fit Polo T-Shirt")
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('polo-women-regular-fit')
        ->and($product?->image_reference)->toBe('/images/products/personalized-women-regular-fit-polo-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});

test('women regular fit polo t-shirt seeder is idempotent by product name', function () {
    CustomizablePrintProduct::query()->create([
        'product_name' => "Women's Regular Fit Polo T-Shirt",
        'description' => 'Old description',
        'image_reference' => '/images/old.avif',
        'visibility_status' => ProductVisibilityStatus::Inactive,
        'design_profile_key' => null,
    ]);

    $this->seed(CustomizableWomenRegularFitPoloTShirtSeeder::class);

    expect(
        CustomizablePrintProduct::query()
            ->where('product_name', "Women's Regular Fit Polo T-Shirt")
            ->count()
    )->toBe(1);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', "Women's Regular Fit Polo T-Shirt")
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('polo-women-regular-fit')
        ->and($product?->image_reference)->toBe('/images/products/personalized-women-regular-fit-polo-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});
