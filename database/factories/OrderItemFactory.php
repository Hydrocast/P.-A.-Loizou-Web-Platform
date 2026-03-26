<?php

namespace Database\Factories;

use App\Models\CustomerOrder;
use App\Models\CustomizablePrintProduct;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 *
 * Creates order items with valid default values.
 * Provides states for quantity boundaries (0, 1, 50, 99, 100),
 * unit price boundaries (negative, zero, mid‑range), and a helper
 * to set explicit quantity.
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity     = $this->faker->numberBetween(1, 99);
        $unitPrice    = round($this->faker->randomFloat(2, 1.00, 50.00), 2);
        $lineSubtotal = round($unitPrice * $quantity, 2);

        return [
            'order_id'                => CustomerOrder::factory(),
            'product_id'              => CustomizablePrintProduct::factory(),
            'product_name'            => $this->faker->words(3, true),
            'quantity'                => $quantity,
            'unit_price'              => $unitPrice,
            'line_subtotal'           => $lineSubtotal,
            'design_snapshot'         => $this->minimalFabricJson(),
            'preview_image_reference' => $this->faker->optional(0.8)->imageUrl(400, 300, 'previews'),
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
        return $this->state(fn () => [
            'quantity'      => 0,
            'line_subtotal' => 0.00,
        ]);
    }

    /** Quantity of 1 (minimum) is valid. */
    public function quantityAtMin(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $attributes['unit_price'];
            return [
                'quantity'      => 1,
                'line_subtotal' => round($unitPrice * 1, 2),
            ];
        });
    }

    /** Quantity of 50 (in‑range) is valid. */
    public function quantityMidRange(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $attributes['unit_price'];
            return [
                'quantity'      => 50,
                'line_subtotal' => round($unitPrice * 50, 2),
            ];
        });
    }

    /** Quantity of 99 (maximum) is valid. */
    public function quantityAtMax(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $attributes['unit_price'];
            return [
                'quantity'      => 99,
                'line_subtotal' => round($unitPrice * 99, 2),
            ];
        });
    }

    /** Quantity of 100 (above maximum) is invalid. */
    public function quantityAboveMax(): static
    {
        return $this->state(function (array $attributes) {
            $unitPrice = $attributes['unit_price'];
            return [
                'quantity'      => 100,
                'line_subtotal' => round($unitPrice * 100, 2),
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Unit price boundaries
    // -------------------------------------------------------------------------

    /** Unit price of -0.01 (below minimum) is invalid. */
    public function negativePriceItem(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'unit_price'    => -0.01,
                'line_subtotal' => round(-0.01 * $attributes['quantity'], 2),
            ];
        });
    }

    /** Unit price of 0.00 (minimum) is valid (free item). */
    public function freeItem(): static
    {
        return $this->state(fn () => [
            'unit_price'    => 0.00,
            'line_subtotal' => 0.00,
        ]);
    }

    /** Unit price of 15.00 (in‑range) is valid. */
    public function midPriceItem(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'unit_price'    => 15.00,
                'line_subtotal' => round(15.00 * $attributes['quantity'], 2),
            ];
        });
    }

    // -------------------------------------------------------------------------
    // Helper methods
    // -------------------------------------------------------------------------

    /** Set a specific quantity and recalculate line subtotal. */
    public function withQuantity(int $quantity): static
    {
        return $this->state(function (array $attributes) use ($quantity) {
            $unitPrice = $attributes['unit_price'] ?? round($this->faker->randomFloat(2, 1.00, 50.00), 2);
            return [
                'quantity'      => $quantity,
                'line_subtotal' => round($unitPrice * $quantity, 2),
            ];
        });
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