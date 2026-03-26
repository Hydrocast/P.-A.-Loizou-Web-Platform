<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\Customer;
use App\Models\StandardProduct;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WishlistItem>
 *
 * Creates wishlist items with valid default values.
 * Provides states for product type (standard/customizable).
 *
 * The product_id and product_type are kept consistent through state methods.
 */
class WishlistItemFactory extends Factory
{
    protected $model = WishlistItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'  => Customer::factory(),
            'product_id'   => StandardProduct::factory(),
            'product_type' => ProductType::Standard,
            'date_added'   => $this->faker->dateTimeBetween('-6 months', 'now'),
        ];
    }

    /** Link item to a customizable product instead of a standard one. */
    public function customizable(): static
    {
        return $this->state(fn () => [
            'product_id'   => \App\Models\CustomizablePrintProduct::factory(),
            'product_type' => ProductType::Customizable,
        ]);
    }
}