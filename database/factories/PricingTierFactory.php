<?php

namespace Database\Factories;

use App\Models\CustomizablePrintProduct;
use App\Models\PricingTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PricingTier>
 *
 * Creates pricing tier records with valid default values.
 * Provides states for building multi‑tier structures (first, second, third)
 * and for testing boundary values of minimum_quantity, maximum_quantity,
 * and unit_price. Also includes invalid states for negative price and inverted ranges.
 * Boundary values: minimum_quantity (≥1), maximum_quantity (≥ min), unit_price (0‑100,000).
 */
class PricingTierFactory extends Factory
{
    protected $model = PricingTier::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id'       => CustomizablePrintProduct::factory(),
            'minimum_quantity' => 1,
            'maximum_quantity' => 99,
            'unit_price'       => $this->faker->randomFloat(2, 1.00, 100000.00),
        ];
    }

    // -------------------------------------------------------------------------
    // Structured multi‑tier states
    // -------------------------------------------------------------------------

    /** Set tier as first tier (1‑9 units, higher price). */
    public function firstTier(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
            'unit_price'       => $this->faker->randomFloat(2, 10.00, 100000.00),
        ]);
    }

    /** Set tier as second tier (10‑49 units, medium price). */
    public function secondTier(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 10,
            'maximum_quantity' => 49,
            'unit_price'       => $this->faker->randomFloat(2, 7.00, 9.99),
        ]);
    }

    /** Set tier as third tier (50+ units, lower price). */
    public function thirdTier(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 50,
            'maximum_quantity' => 9999,
            'unit_price'       => $this->faker->randomFloat(2, 1.00, 6.99),
        ]);
    }

    // -------------------------------------------------------------------------
    // Minimum quantity boundaries
    // -------------------------------------------------------------------------

    /** Minimum quantity of 0 (below minimum) is invalid. */
    public function minimumQuantityBelowMin(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 0,
            'maximum_quantity' => 10,
        ]);
    }

    /** Minimum quantity of 1 (minimum) is valid. */
    public function minimumQuantityAtMin(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 1,
            'maximum_quantity' => 99,
        ]);
    }

    /** Minimum quantity of 50 (in‑range) is valid. */
    public function minimumQuantityMidRange(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 50,
            'maximum_quantity' => 9999,
        ]);
    }

    // -------------------------------------------------------------------------
    // Maximum quantity boundaries
    // -------------------------------------------------------------------------

    /** Maximum quantity less than minimum (inverted range) is invalid. */
    public function maximumQuantityInverted(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 10,
            'maximum_quantity' => 9,
        ]);
    }

    /** Maximum quantity equal to minimum (single‑quantity tier) is valid. */
    public function maximumQuantityEqualToMinimum(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 5,
            'maximum_quantity' => 5,
        ]);
    }

    /** Maximum quantity one above minimum (adjacent range) is valid. */
    public function maximumQuantityAdjacentToMinimum(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 5,
            'maximum_quantity' => 6,
        ]);
    }

    /** Wide range (1‑9999) – typical for highest tier – is valid. */
    public function maximumQuantityWideRange(): static
    {
        return $this->state(fn () => [
            'minimum_quantity' => 1,
            'maximum_quantity' => 9999,
        ]);
    }

    // -------------------------------------------------------------------------
    // Unit price boundaries
    // -------------------------------------------------------------------------

    /** Unit price of -0.01 (below minimum) is invalid. */
    public function negativePriceTier(): static
    {
        return $this->state(fn () => ['unit_price' => -0.01]);
    }

    /** Unit price of 0.00 (minimum) is valid. */
    public function freeTier(): static
    {
        return $this->state(fn () => ['unit_price' => 0.00]);
    }

    /** Unit price of 15.00 (in‑range) is valid. */
    public function midPriceTier(): static
    {
        return $this->state(fn () => ['unit_price' => 15.00]);
    }

    /** Unit price of 100,000.00 (maximum) is valid. */
    public function maxPriceTier(): static
    {
        return $this->state(fn () => ['unit_price' => 100000.00]);
    }

    /** Unit price of 100,000.01 (above maximum) is invalid. */
    public function aboveMaxPriceTier(): static
    {
        return $this->state(fn () => ['unit_price' => 100000.01]);
    }
}