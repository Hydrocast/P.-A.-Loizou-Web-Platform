<?php

namespace Database\Factories;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomizablePrintProduct>
 *
 * Creates customizable print products with valid default values.
 * Provides states for product visibility, image presence, template
 * profile selection, and boundary testing of product_name and description.
 * Boundary values: product_name (2‑100), description (max 2000, nullable).
 */
class CustomizablePrintProductFactory extends Factory
{
    protected $model = CustomizablePrintProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_name'      => $this->faker->words(3, true) . ' (Custom)',
            'description'       => $this->faker->optional(0.8)->paragraph(),
            'image_reference'   => $this->faker->optional(0.6)->imageUrl(800, 600, 'products'),
            'design_profile_key' => null,
            'visibility_status' => ProductVisibilityStatus::Active,
        ];
    }

    // -------------------------------------------------------------------------
    // Visibility and image states
    // -------------------------------------------------------------------------

    /** Set product to inactive (hidden from customers). */
    public function inactive(): static
    {
        return $this->state(fn () => ['visibility_status' => ProductVisibilityStatus::Inactive]);
    }

    /** Remove the image reference (set to null). */
    public function withoutImage(): static
    {
        return $this->state(fn () => ['image_reference' => null]);
    }

    /** Add a sample design profile key (non-null). */
    public function withTemplateConfig(): static
    {
        return $this->state(fn () => [
            'design_profile_key' => 'tshirt-classic',
        ]);
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