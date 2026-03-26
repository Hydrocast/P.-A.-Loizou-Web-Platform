<?php

namespace Database\Factories;

use App\Enums\ProductType;
use App\Models\CarouselSlide;
use App\Models\StandardProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarouselSlide>
 *
 * Creates carousel slide records with valid default values.
 * Provides states for product linking (standard/customizable/none),
 * and for testing title and description boundaries.
 * Boundary values: title (2‑50), description (max 100).
 *
 * When creating multiple slides, set display_sequence explicitly
 * using Laravel's sequence() helper to maintain gapless ordering.
 */
class CarouselSlideFactory extends Factory
{
    protected $model = CarouselSlide::class;

    public function definition(): array
    {
        return [
            'title'            => $this->faker->words(4, true),
            'description'      => $this->faker->optional(0.7)->sentence(),
            'image_reference'  => $this->faker->optional(0.8)->imageUrl(1200, 500, 'carousel'),
            'display_sequence' => 1,
            'product_id'       => null,
            'product_type'     => null,
        ];
    }

    // -------------------------------------------------------------------------
    // Product link states
    // -------------------------------------------------------------------------

    /** Link the slide to a standard product. */
    public function withStandardProduct(): static
    {
        return $this->state(fn () => [
            'product_id'   => StandardProduct::factory(),
            'product_type' => ProductType::Standard,
        ]);
    }

    /** Link the slide to a customizable print product. */
    public function withCustomizableProduct(): static
    {
        return $this->state(fn () => [
            'product_id'   => \App\Models\CustomizablePrintProduct::factory(),
            'product_type' => ProductType::Customizable,
        ]);
    }

    /** Set the slide to have no linked product. */
    public function withoutProduct(): static
    {
        return $this->state(fn () => [
            'title'        => 'No Product',
            'product_id'   => null,
            'product_type' => null,
        ]);
    }

    /** Remove the image reference (set to null). */
    public function withoutImage(): static
    {
        return $this->state(fn () => ['image_reference' => null]);
    }

    // -------------------------------------------------------------------------
    // Title boundaries
    // -------------------------------------------------------------------------

    /** Title of 1 character (below minimum) is invalid. */
    public function titleTooShort(): static
    {
        return $this->state(fn () => ['title' => 'A']);
    }

    /** Title of 2 characters (minimum) is valid. */
    public function titleMinLength(): static
    {
        return $this->state(fn () => ['title' => 'Ab']);
    }

    /** Title of 26 characters (in‑range) is valid. */
    public function titleMidLength(): static
    {
        return $this->state(fn () => ['title' => str_repeat('a', 26)]);
    }

    /** Title of 50 characters (maximum) is valid. */
    public function titleMaxLength(): static
    {
        return $this->state(fn () => ['title' => str_repeat('a', 50)]);
    }

    /** Title of 51 characters (above maximum) is invalid. */
    public function titleTooLong(): static
    {
        return $this->state(fn () => ['title' => str_repeat('a', 51)]);
    }

    // -------------------------------------------------------------------------
    // Description boundaries
    // -------------------------------------------------------------------------

    /** Description of 1 character (minimum) is valid. */
    public function descriptionMinLength(): static
    {
        return $this->state(fn () => ['description' => 'a']);
    }

    /** Description of 50 characters (in‑range) is valid. */
    public function descriptionMidLength(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 50)]);
    }

    /** Description of 100 characters (maximum) is valid. */
    public function descriptionMaxLength(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 100)]);
    }

    /** Description of 101 characters (above maximum) is invalid. */
    public function descriptionTooLong(): static
    {
        return $this->state(fn () => ['description' => str_repeat('a', 101)]);
    }

    /** Remove the description (set to null). */
    public function withoutDescription(): static
    {
        return $this->state(fn () => ['description' => null]);
    }
}