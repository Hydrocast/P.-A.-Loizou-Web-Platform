<?php

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Database\Seeders\CustomizableUnisexTShirtSeeder;

test('customizable unisex t-shirt seeder creates the initial profile-backed product', function () {
    $this->seed(CustomizableUnisexTShirtSeeder::class);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'dolore in ut (Custom)')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('tshirt-classic')
        ->and($product?->image_reference)->toBe('/images/products/personalized-unisex-regular-fit-adults-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active);
});

test('customizable unisex t-shirt seeder is idempotent by product name', function () {
    CustomizablePrintProduct::query()->create([
        'product_name' => 'dolore in ut (Custom)',
        'description' => 'Old description',
        'image_reference' => '/images/old.avif',
        'visibility_status' => ProductVisibilityStatus::Inactive,
        'design_profile_key' => null,
    ]);

    $this->seed(CustomizableUnisexTShirtSeeder::class);

    expect(
        CustomizablePrintProduct::query()
            ->where('product_name', 'dolore in ut (Custom)')
            ->count()
    )->toBe(1);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'dolore in ut (Custom)')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('tshirt-classic')
        ->and($product?->image_reference)->toBe('/images/products/personalized-unisex-regular-fit-adults-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});
