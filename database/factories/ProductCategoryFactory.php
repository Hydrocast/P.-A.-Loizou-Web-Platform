<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCategory>
 *
 * Creates product categories with valid default values.
 * Provides states for testing category_name boundaries (2‑50 characters)
 * and description boundaries (max 500, nullable). Also includes a state
 * to omit the description.
 * Boundary values: category_name (2‑50), description (max 500, nullable).
 */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_name' => $this->faker->unique()->word() . '_' . $this->faker->lexify('???'),
            'description'   => $this->faker->optional(0.7)->sentence(),
        ];
    }

    /** Remove the description (set to null). */
    public function withoutDescription(): static
    {
        return $this->state(fn () => ['description' => null]);
    }

    // -------------------------------------------------------------------------
    // Category name boundaries
    // -------------------------------------------------------------------------

    /** Category name of 1 character (below minimum) is invalid. */
    public function nameTooShort(): static
    {
        return $this->state(fn () => ['category_name' => 'A']);
    }

    /** Category name of 2 characters (minimum) is valid. */
    public function nameMinLength(): static
    {
        return $this->state(fn () => ['category_name' => 'Ab']);
    }

    /** Category name of 26 characters (in‑range) is valid. */
    public function nameMidLength(): static
    {
        return $this->state(fn () => ['category_name' => str_repeat('a', 26)]);
    }

    /** Category name of 50 characters (maximum) is valid. */
    public function nameMaxLength(): static
    {
        return $this->state(fn () => ['category_name' => str_repeat('a', 50)]);
    }

    /** Category name of 51 characters (above maximum) is invalid. */
    public function nameTooLong(): static
    {
        return $this->state(fn () => ['category_name' => str_repeat('a', 51)]);
    }

    // -------------------------------------------------------------------------
    // Description boundaries
    // -------------------------------------------------------------------------

    /** Description of 1 character (minimum) is valid. */
    public function descriptionMinLength(): static
    {
        return $this->state(fn () => ['description' => 'a']);
    }

    /** Description of 250 characters (in‑range) is valid. */
    public function descriptionMidLength(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 250)]);
    }

    /** Description of 500 characters (maximum) is valid. */
    public function descriptionMaxLength(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 500)]);
    }

    /** Description of 501 characters (above maximum) is invalid. */
    public function descriptionTooLong(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 501)]);
    }
}