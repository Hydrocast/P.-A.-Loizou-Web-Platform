<?php

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Database\Seeders\CustomizableUnisexAdultsHoodieSeeder;

test('unisex adults hoodie seeder creates the profile-backed product', function () {
    $this->seed(CustomizableUnisexAdultsHoodieSeeder::class);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'Personalized Unisex Adults Hoodie')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('hoodie-unisex')
        ->and($product?->image_reference)->toBe('/images/products/personalized-unisex-adults-hoodie.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});

test('unisex adults hoodie seeder is idempotent by product name', function () {
    CustomizablePrintProduct::query()->create([
        'product_name' => 'Personalized Unisex Adults Hoodie',
        'description' => 'Old description',
        'image_reference' => '/images/old.avif',
        'visibility_status' => ProductVisibilityStatus::Inactive,
        'design_profile_key' => null,
    ]);

    $this->seed(CustomizableUnisexAdultsHoodieSeeder::class);

    expect(
        CustomizablePrintProduct::query()
            ->where('product_name', 'Personalized Unisex Adults Hoodie')
            ->count()
    )->toBe(1);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'Personalized Unisex Adults Hoodie')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('hoodie-unisex')
        ->and($product?->image_reference)->toBe('/images/products/personalized-unisex-adults-hoodie.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});
