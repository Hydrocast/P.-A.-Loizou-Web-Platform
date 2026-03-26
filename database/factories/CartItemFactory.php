<?php

namespace Database\Factories;

use App\Models\CartItem;
use App\Models\CustomizablePrintProduct;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CartItem>
 *
 * Creates cart item records with valid default values.
 * Provides states for testing quantity boundaries and for omitting the preview image.
 * Boundary values: quantity (1‑99).
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'cart_id'                 => ShoppingCart::factory(),
            'product_id'              => CustomizablePrintProduct::factory(),
            'quantity'                => $this->faker->numberBetween(1, 99),
            'design_snapshot'         => $this->minimalFabricJson(),
            'preview_image_reference' => $this->faker->optional(0.8)->imageUrl(400, 300, 'previews'),
            'date_added'              => $this->faker->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /** Remove the preview image reference (set to null). */
    public function withoutPreview(): static
    {
        return $this->state(fn () => ['preview_image_reference' => null]);
    }

    // -------------------------------------------------------------------------
    // Quantity boundaries
    // -------------------------------------------------------------------------

    /** Quantity of 0 (below minimum) is invalid. */
    public function quantityBelowMin(): static
    {
        return $this->state(fn () => ['quantity' => 0]);
    }

    /** Quantity of 1 (minimum) is valid. */
    public function quantityAtMin(): static
    {
        return $this->state(fn () => ['quantity' => 1]);
    }

    /** Quantity of 50 (in‑range) is valid. */
    public function quantityMidRange(): static
    {
        return $this->state(fn () => ['quantity' => 50]);
    }

    /** Quantity of 99 (maximum) is valid. */
    public function quantityAtMax(): static
    {
        return $this->state(fn () => ['quantity' => 99]);
    }

    /** Quantity of 100 (above maximum) is invalid. */
    public function quantityAboveMax(): static
    {
        return $this->state(fn () => ['quantity' => 100]);
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