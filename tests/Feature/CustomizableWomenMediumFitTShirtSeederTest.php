<?php

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Database\Seeders\CustomizableWomenMediumFitTShirtSeeder;

test('women medium fit t-shirt seeder creates the profile-backed product', function () {
    $this->seed(CustomizableWomenMediumFitTShirtSeeder::class);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'Personalized Women Medium Fit T-Shirt')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('tshirt-women-medium-fit')
        ->and($product?->image_reference)->toBe('/images/products/personalized-women-medium-fit-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Women medium fit t-shirts');
});

test('women medium fit t-shirt seeder is idempotent by product name', function () {
    CustomizablePrintProduct::query()->create([
        'product_name' => 'Personalized Women Medium Fit T-Shirt',
        'description' => 'Old description',
        'image_reference' => '/images/old.avif',
        'visibility_status' => ProductVisibilityStatus::Inactive,
        'design_profile_key' => null,
    ]);

    $this->seed(CustomizableWomenMediumFitTShirtSeeder::class);

    expect(
        CustomizablePrintProduct::query()
            ->where('product_name', 'Personalized Women Medium Fit T-Shirt')
            ->count()
    )->toBe(1);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'Personalized Women Medium Fit T-Shirt')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('tshirt-women-medium-fit')
        ->and($product?->image_reference)->toBe('/images/products/personalized-women-medium-fit-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});
