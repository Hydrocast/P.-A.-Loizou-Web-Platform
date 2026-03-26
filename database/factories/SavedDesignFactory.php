<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\SavedDesign;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedDesign>
 *
 * Creates saved design records with valid default values.
 * Provides states for testing design_name boundaries (1‑100 characters)
 * and for omitting the preview image.
 * Boundary values: design_name (1‑100).
 *
 * Designs are immutable – no update states are provided.
 */
class SavedDesignFactory extends Factory
{
    protected $model = SavedDesign::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id'             => Customer::factory(),
            'product_id'              => CustomizablePrintProduct::factory(),
            'design_name'             => $this->faker->words(3, true),
            'design_data'             => $this->minimalFabricJson(),
            'preview_image_reference' => $this->faker->optional(0.8)->imageUrl(400, 300, 'designs'),
            'date_created'            => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    /** Remove the preview image reference (set to null). */
    public function withoutPreview(): static
    {
        return $this->state(fn () => ['preview_image_reference' => null]);
    }

    // -------------------------------------------------------------------------
    // Design name boundaries
    // -------------------------------------------------------------------------

    /** Design name of 0 characters (below minimum) is invalid. */
    public function designNameEmpty(): static
    {
        return $this->state(fn () => ['design_name' => '']);
    }

    /** Design name of 1 character (minimum) is valid. */
    public function designNameMinLength(): static
    {
        return $this->state(fn () => ['design_name' => 'A']);
    }

    /** Design name of 50 characters (in‑range) is valid. */
    public function designNameMidLength(): static
    {
        return $this->state(fn () => ['design_name' => str_repeat('a', 50)]);
    }

    /** Design name of 100 characters (maximum) is valid. */
    public function designNameMaxLength(): static
    {
        return $this->state(fn () => ['design_name' => str_repeat('a', 100)]);
    }

    /** Design name of 101 characters (above maximum) is invalid. */
    public function designNameTooLong(): static
    {
        return $this->state(fn () => ['design_name' => str_repeat('a', 101)]);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /** Generate a minimal FabricJS JSON string for the design snapshot. */
    private function minimalFabricJson(): string
    {
        return json_encode([
            'version'    => '5.3.0',
            'objects'    => [[
                'type'     => 'textbox',
                'left'     => 100,
                'top'      => 100,
                'width'    => 200,
                'height'   => 50,
                'text'     => $this->faker->words(3, true),
                'fontSize' => 24,
                'fill'     => '#000000',
            ]],
            'background' => '#ffffff',
        ]);
    }
}