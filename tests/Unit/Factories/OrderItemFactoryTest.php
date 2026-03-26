<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for OrderItemFactory.
 *
 * Covers the default state, withoutPreview state, quantity boundary states (1‑99),
 * and unit_price boundary states (0–unbounded). The design_snapshot is a
 * FabricJS JSON string. Pricing data is frozen at order submission.
 */
class OrderItemFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates an OrderItem record. */
    public function default_state_creates_order_item_record(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertInstanceOf(OrderItem::class, $item);
        $this->assertDatabaseHas('order_items', ['order_item_id' => $item->order_item_id]);
    }

    #[Test]
    /** Default state creates a linked order. */
    public function default_state_creates_a_linked_order(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertNotNull($item->order_id);
        $this->assertDatabaseHas('customer_orders', ['order_id' => $item->order_id]);
    }

    #[Test]
    /** Default state creates a linked product. */
    public function default_state_creates_a_linked_product(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertNotNull($item->product_id);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $item->product_id]);
    }

    #[Test]
    /** Default state sets a non-null design snapshot. */
    public function default_state_sets_non_null_design_snapshot(): void
    {
        $item = OrderItem::factory()->create();

        $this->assertNotNull($item->design_snapshot);
    }

    // -------------------------------------------------------------------------
    // withoutPreview state
    // -------------------------------------------------------------------------

    #[Test]
    /** withoutPreview state sets preview_image_reference to null. */
    public function without_preview_state_sets_preview_image_reference_to_null(): void
    {
        $item = OrderItem::factory()->withoutPreview()->create();

        $this->assertNull($item->preview_image_reference);
    }

    // -------------------------------------------------------------------------
    // Quantity boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** quantityAtMin state sets quantity to 1 (minimum). */
    public function quantity_at_min_state_sets_quantity_to_one(): void
    {
        $item = OrderItem::factory()->quantityAtMin()->create();

        $this->assertSame(1, $item->quantity);
    }

    #[Test]
    /** quantityMidRange state sets quantity to 50 (in‑range). */
    public function quantity_mid_range_state_sets_quantity_to_fifty(): void
    {
        $item = OrderItem::factory()->quantityMidRange()->create();

        $this->assertSame(50, $item->quantity);
    }

    #[Test]
    /** quantityAtMax state sets quantity to 99 (maximum). */
    public function quantity_at_max_state_sets_quantity_to_ninety_nine(): void
    {
        $item = OrderItem::factory()->quantityAtMax()->create();

        $this->assertSame(99, $item->quantity);
    }

    // -------------------------------------------------------------------------
    // Quantity boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** quantityBelowMin state sets quantity to 0 (below minimum). */
    public function quantity_below_min_state_sets_quantity_to_zero(): void
    {
        $item = OrderItem::factory()->quantityBelowMin()->make();

        $this->assertSame(0, $item->quantity);
    }

    #[Test]
    /** quantityAboveMax state sets quantity to 100 (above maximum). */
    public function quantity_above_max_state_sets_quantity_to_one_hundred(): void
    {
        $item = OrderItem::factory()->quantityAboveMax()->make();

        $this->assertSame(100, $item->quantity);
    }

    // -------------------------------------------------------------------------
    // Unit price boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** freeItem state sets unit_price to 0.00 (minimum). */
    public function free_item_state_sets_unit_price_to_zero(): void
    {
        $item = OrderItem::factory()->freeItem()->create();

        $this->assertEquals(0.00, $item->unit_price);
    }

    #[Test]
    /** midPriceItem state sets unit_price to 15.00 (in‑range). */
    public function mid_price_item_state_sets_unit_price_to_fifteen(): void
    {
        $item = OrderItem::factory()->midPriceItem()->create();

        $this->assertEquals(15.00, $item->unit_price);
    }

    // -------------------------------------------------------------------------
    // Unit price boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** negativePriceItem state sets unit_price to -0.01 (below minimum). */
    public function negative_price_item_state_sets_unit_price_to_negative(): void
    {
        $item = OrderItem::factory()->negativePriceItem()->make();

        $this->assertEquals(-0.01, $item->unit_price);
    }
}