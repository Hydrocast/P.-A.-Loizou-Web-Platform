<?php

namespace Database\Factories;

use App\Enums\ProductVisibilityStatus;
use App\Models\ProductCategory;
use App\Models\StandardProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StandardProduct>
 *
 * Creates standard products with valid default values.
 * Provides states for product visibility, category assignment,
 * image presence, and boundary testing of product_name, display_price, and description.
 * Boundary values: product_name (2‑100), display_price (0‑100,000), description (max 2000, nullable).
 */
class StandardProductFactory extends Factory
{
    protected $model = StandardProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_name'      => $this->faker->words(3, true),
            'description'       => $this->faker->optional(0.8)->paragraph(),
            'image_reference'   => $this->faker->optional(0.6)->imageUrl(800, 600, 'products'),
            'display_price'     => $this->faker->randomFloat(2, 0.50, 100000.00),
            'category_id'       => ProductCategory::factory(),
            'visibility_status' => ProductVisibilityStatus::Active,
        ];
    }

    // -------------------------------------------------------------------------
    // Visibility and category states
    // -------------------------------------------------------------------------

    /** Set product to inactive (hidden from customers). */
    public function inactive(): static
    {
        return $this->state(fn () => ['visibility_status' => ProductVisibilityStatus::Inactive]);
    }

    /** Remove category association (set category_id to null). */
    public function uncategorised(): static
    {
        return $this->state(fn () => ['category_id' => null]);
    }

    /** Remove the image reference (set to null). */
    public function withoutImage(): static
    {
        return $this->state(fn () => ['image_reference' => null]);
    }

    // -------------------------------------------------------------------------
    // Product name boundaries
    // -------------------------------------------------------------------------

    /** Product name of 1 character (below minimum) is invalid. */
    public function nameTooShort(): static
    {
        return $this->state(fn () => ['product_name' => 'A']);
    }

    /** Product name of 2 characters (minimum) is valid. */
    public function nameMinLength(): static
    {
        return $this->state(fn () => ['product_name' => 'Ab']);
    }

    /** Product name of 51 characters (in‑range) is valid. */
    public function nameMidLength(): static
    {
        return $this->state(fn () => ['product_name' => str_repeat('a', 51)]);
    }

    /** Product name of 100 characters (maximum) is valid. */
    public function nameMaxLength(): static
    {
        return $this->state(fn () => ['product_name' => str_repeat('a', 100)]);
    }

    /** Product name of 101 characters (above maximum) is invalid. */
    public function nameTooLong(): static
    {
        return $this->state(fn () => ['product_name' => str_repeat('a', 101)]);
    }

    // -------------------------------------------------------------------------
    // Display price boundaries
    // -------------------------------------------------------------------------

    /** Display price of -0.01 (below minimum) is invalid. */
    public function negativePrice(): static
    {
        return $this->state(fn () => ['display_price' => -0.01]);
    }

    /** Display price of 0.00 (minimum) is valid. */
    public function free(): static
    {
        return $this->state(fn () => ['display_price' => 0.00]);
    }

    /** Display price of 25.00 (in‑range) is valid. */
    public function midPrice(): static
    {
        return $this->state(fn () => ['display_price' => 25.00]);
    }

    /** Display price of 100,000.00 (maximum) is valid. */
    public function maxPrice(): static
    {
        return $this->state(fn () => ['display_price' => 100000.00]);
    }

    /** Display price of 100,000.01 (above maximum) is invalid. */
    public function aboveMaxPrice(): static
    {
        return $this->state(fn () => ['display_price' => 100000.01]);
    }

    // -------------------------------------------------------------------------
    // Description boundaries
    // -------------------------------------------------------------------------

    /** Description of 1 character (minimum) is valid. */
    public function descriptionMinLength(): static
    {
        return $this->state(fn () => ['description' => 'a']);
    }

    /** Description of 1000 characters (in‑range) is valid. */
    public function descriptionMidLength(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 1000)]);
    }

    /** Description of 2000 characters (maximum) is valid. */
    public function descriptionMaxLength(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 2000)]);
    }

    /** Description of 2001 characters (above maximum) is invalid. */
    public function descriptionTooLong(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 2001)]);
    }
}