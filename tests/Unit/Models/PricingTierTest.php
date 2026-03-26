<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CustomizablePrintProduct;
use App\Models\PricingTier;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the PricingTier model.
 *
 * Covers model configuration, the product() relationship structure and data
 * resolution, and business logic for appliesTo().
 *
 * appliesTo() boundary values (tier [5, 10]):
 * - Quantity below minimum: false
 * - Quantity at minimum: true
 * - Quantity within range: true
 * - Quantity at maximum: true
 * - Quantity above maximum: false
 */
class PricingTierTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the pricing_tiers table. */
    public function model_uses_pricing_tiers_table(): void
    {
        $tier = new PricingTier();

        $this->assertSame('pricing_tiers', $tier->getTable());
    }

    #[Test]
    /** Primary key is tier_id. */
    public function primary_key_is_tier_id(): void
    {
        $tier = new PricingTier();

        $this->assertSame('tier_id', $tier->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $tier = new PricingTier();

        $this->assertSame('int', $tier->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $tier = new PricingTier();

        $this->assertTrue($tier->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $tier = new PricingTier();

        $this->assertFalse($tier->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $tier = new PricingTier();
        $fillable = $tier->getFillable();

        $this->assertContains('product_id', $fillable);
        $this->assertContains('minimum_quantity', $fillable);
        $this->assertContains('maximum_quantity', $fillable);
        $this->assertContains('unit_price', $fillable);
    }

    #[Test]
    /** unit_price is cast to decimal with 2 places. */
    public function unit_price_cast_is_configured(): void
    {
        $tier = new PricingTier();

        $this->assertSame('decimal:2', $tier->getCasts()['unit_price']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – product()
    // -------------------------------------------------------------------------

    #[Test]
    /** product() returns a BelongsTo relation. */
    public function product_returns_belongs_to_relation(): void
    {
        $relation = (new PricingTier())->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** product() uses product_id as foreign key. */
    public function product_uses_product_id_as_foreign_key(): void
    {
        $relation = (new PricingTier())->product();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** product() relates to CustomizablePrintProduct model. */
    public function product_relates_to_customizable_print_product_model(): void
    {
        $relation = (new PricingTier())->product();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** product() resolves to the customizable product that owns this tier. */
    public function product_resolves_to_the_owning_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $tier = PricingTier::factory()->create(['product_id' => $product->product_id]);

        $resolved = $tier->product;

        $this->assertInstanceOf(CustomizablePrintProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }

    // -------------------------------------------------------------------------
    // appliesTo()
    // -------------------------------------------------------------------------

    #[Test]
    /** appliesTo() returns false when quantity is below minimum. */
    public function applies_to_returns_false_when_quantity_is_below_minimum(): void
    {
        $tier = PricingTier::factory()->make([
            'minimum_quantity' => 5,
            'maximum_quantity' => 10,
        ]);

        $this->assertFalse($tier->appliesTo(4));
    }

    #[Test]
    /** appliesTo() returns true when quantity equals minimum. */
    public function applies_to_returns_true_when_quantity_equals_minimum_quantity(): void
    {
        $tier = PricingTier::factory()->make([
            'minimum_quantity' => 5,
            'maximum_quantity' => 10,
        ]);

        $this->assertTrue($tier->appliesTo(5));
    }

    #[Test]
    /** appliesTo() returns true when quantity is within range. */
    public function applies_to_returns_true_when_quantity_is_within_range(): void
    {
        $tier = PricingTier::factory()->make([
            'minimum_quantity' => 5,
            'maximum_quantity' => 10,
        ]);

        $this->assertTrue($tier->appliesTo(7));
    }

    #[Test]
    /** appliesTo() returns true when quantity equals maximum. */
    public function applies_to_returns_true_when_quantity_equals_maximum_quantity(): void
    {
        $tier = PricingTier::factory()->make([
            'minimum_quantity' => 5,
            'maximum_quantity' => 10,
        ]);

        $this->assertTrue($tier->appliesTo(10));
    }

    #[Test]
    /** appliesTo() returns false when quantity exceeds maximum. */
    public function applies_to_returns_false_when_quantity_exceeds_maximum(): void
    {
        $tier = PricingTier::factory()->make([
            'minimum_quantity' => 5,
            'maximum_quantity' => 10,
        ]);

        $this->assertFalse($tier->appliesTo(11));
    }
}