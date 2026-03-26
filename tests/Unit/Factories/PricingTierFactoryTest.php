<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\PricingTier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for PricingTierFactory.
 *
 * Covers the default state, structured multi-tier states, and boundary states
 * for minimum_quantity (≥1), maximum_quantity (relative to minimum), and
 * unit_price (0‑100,000).
 */
class PricingTierFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a PricingTier record. */
    public function default_state_creates_pricing_tier_record(): void
    {
        $tier = PricingTier::factory()->create();

        $this->assertInstanceOf(PricingTier::class, $tier);
        $this->assertDatabaseHas('pricing_tiers', ['tier_id' => $tier->tier_id]);
    }

    #[Test]
    /** Default state creates a linked product. */
    public function default_state_creates_a_linked_product(): void
    {
        $tier = PricingTier::factory()->create();

        $this->assertNotNull($tier->product_id);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $tier->product_id]);
    }

    #[Test]
    /** Default state sets minimum_quantity to 1. */
    public function default_state_sets_minimum_quantity_to_one(): void
    {
        $tier = PricingTier::factory()->create();

        $this->assertSame(1, $tier->minimum_quantity);
    }

    // -------------------------------------------------------------------------
    // Structured multi-tier states
    // -------------------------------------------------------------------------

    #[Test]
    /** firstTier state sets range to 1‑9. */
    public function first_tier_state_sets_range_to_one_through_nine(): void
    {
        $tier = PricingTier::factory()->firstTier()->create();

        $this->assertSame(1, $tier->minimum_quantity);
        $this->assertSame(9, $tier->maximum_quantity);
    }

    #[Test]
    /** secondTier state sets range to 10‑49. */
    public function second_tier_state_sets_range_to_ten_through_forty_nine(): void
    {
        $tier = PricingTier::factory()->secondTier()->create();

        $this->assertSame(10, $tier->minimum_quantity);
        $this->assertSame(49, $tier->maximum_quantity);
    }

    #[Test]
    /** thirdTier state sets range to 50‑9999. */
    public function third_tier_state_sets_range_to_fifty_through_nine_thousand_nine_hundred_ninety_nine(): void
    {
        $tier = PricingTier::factory()->thirdTier()->create();

        $this->assertSame(50, $tier->minimum_quantity);
        $this->assertSame(9999, $tier->maximum_quantity);
    }

    // -------------------------------------------------------------------------
    // Minimum quantity boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** minimumQuantityAtMin state sets minimum_quantity to 1 (minimum). */
    public function minimum_quantity_at_min_state_sets_minimum_quantity_to_one(): void
    {
        $tier = PricingTier::factory()->minimumQuantityAtMin()->create();

        $this->assertSame(1, $tier->minimum_quantity);
    }

    #[Test]
    /** minimumQuantityMidRange state sets minimum_quantity to 50 (in‑range). */
    public function minimum_quantity_mid_range_state_sets_minimum_quantity_to_fifty(): void
    {
        $tier = PricingTier::factory()->minimumQuantityMidRange()->create();

        $this->assertSame(50, $tier->minimum_quantity);
    }

    // -------------------------------------------------------------------------
    // Maximum quantity boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** maximumQuantityEqualToMinimum state sets both quantities to 5 (single‑unit tier). */
    public function maximum_quantity_equal_to_minimum_state_sets_both_to_five(): void
    {
        $tier = PricingTier::factory()->maximumQuantityEqualToMinimum()->create();

        $this->assertSame(5, $tier->minimum_quantity);
        $this->assertSame(5, $tier->maximum_quantity);
    }

    #[Test]
    /** maximumQuantityAdjacentToMinimum state sets min to 5, max to 6. */
    public function maximum_quantity_adjacent_to_minimum_state_sets_min_five_max_six(): void
    {
        $tier = PricingTier::factory()->maximumQuantityAdjacentToMinimum()->create();

        $this->assertSame(5, $tier->minimum_quantity);
        $this->assertSame(6, $tier->maximum_quantity);
    }

    #[Test]
    /** maximumQuantityWideRange state sets min to 1, max to 9999. */
    public function maximum_quantity_wide_range_state_sets_min_one_max_nine_thousand_nine_hundred_ninety_nine(): void
    {
        $tier = PricingTier::factory()->maximumQuantityWideRange()->create();

        $this->assertSame(1, $tier->minimum_quantity);
        $this->assertSame(9999, $tier->maximum_quantity);
    }

    // -------------------------------------------------------------------------
    // Maximum quantity boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** maximumQuantityInverted state sets max below min (invalid). */
    public function maximum_quantity_inverted_state_sets_max_below_min(): void
    {
        $tier = PricingTier::factory()->maximumQuantityInverted()->make();

        $this->assertLessThan($tier->minimum_quantity, $tier->maximum_quantity);
    }

    // -------------------------------------------------------------------------
    // Minimum quantity boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** minimumQuantityBelowMin state sets minimum_quantity to 0 (below minimum). */
    public function minimum_quantity_below_min_state_sets_minimum_quantity_to_zero(): void
    {
        $tier = PricingTier::factory()->minimumQuantityBelowMin()->make();

        $this->assertSame(0, $tier->minimum_quantity);
    }

    // -------------------------------------------------------------------------
    // Unit price boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** freeTier state sets unit_price to 0.00 (minimum). */
    public function free_tier_state_sets_unit_price_to_zero(): void
    {
        $tier = PricingTier::factory()->freeTier()->create();

        $this->assertEquals(0.00, $tier->unit_price);
    }

    #[Test]
    /** midPriceTier state sets unit_price to 15.00 (in‑range). */
    public function mid_price_tier_state_sets_unit_price_to_fifteen(): void
    {
        $tier = PricingTier::factory()->midPriceTier()->create();

        $this->assertEquals(15.00, $tier->unit_price);
    }

    #[Test]
    /** maxPriceTier state sets unit_price to 100,000.00 (maximum). */
    public function max_price_tier_state_sets_unit_price_to_one_hundred_thousand(): void
    {
        $tier = PricingTier::factory()->maxPriceTier()->create();

        $this->assertEquals(100000.00, $tier->unit_price);
    }

    // -------------------------------------------------------------------------
    // Unit price boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** negativePriceTier state sets unit_price to -0.01 (below minimum). */
    public function negative_price_tier_state_sets_unit_price_to_negative(): void
    {
        $tier = PricingTier::factory()->negativePriceTier()->make();

        $this->assertEquals(-0.01, $tier->unit_price);
    }

    #[Test]
    /** aboveMaxPriceTier state sets unit_price to 100,000.01 (above maximum). */
    public function above_max_price_tier_state_sets_unit_price_above_maximum(): void
    {
        $tier = PricingTier::factory()->aboveMaxPriceTier()->make();

        $this->assertEquals(100000.01, $tier->unit_price);
    }
}