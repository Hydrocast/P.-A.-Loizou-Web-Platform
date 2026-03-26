<?php

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Database\Seeders\CustomizableKidsTShirtSeeder;

test('kids t-shirt seeder creates the profile-backed product', function () {
    $this->seed(CustomizableKidsTShirtSeeder::class);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'Personalized Kids T-Shirt')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('tshirt-kids')
        ->and($product?->image_reference)->toBe('/images/products/personalized-kids-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Kids t-shirts');
});

test('kids t-shirt seeder is idempotent by product name', function () {
    CustomizablePrintProduct::query()->create([
        'product_name' => 'Personalized Kids T-Shirt',
        'description' => 'Old description',
        'image_reference' => '/images/old.avif',
        'visibility_status' => ProductVisibilityStatus::Inactive,
        'design_profile_key' => null,
    ]);

    $this->seed(CustomizableKidsTShirtSeeder::class);

    expect(
        CustomizablePrintProduct::query()
            ->where('product_name', 'Personalized Kids T-Shirt')
            ->count()
    )->toBe(1);

    $product = CustomizablePrintProduct::query()
        ->where('product_name', 'Personalized Kids T-Shirt')
        ->first();

    expect($product)->not->toBeNull()
        ->and($product?->design_profile_key)->toBe('tshirt-kids')
        ->and($product?->image_reference)->toBe('/images/products/personalized-kids-tshirt.avif')
        ->and($product?->visibility_status)->toBe(ProductVisibilityStatus::Active)
        ->and($product?->description)->toContain('Features:');
});
