<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for CartItemFactory.
 *
 * Covers the default state, withoutPreview state, and boundary states for
 * quantity (1–99). The design_snapshot is a FabricJS JSON string.
 */
class CartItemFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a CartItem record. */
    public function default_state_creates_cart_item_record(): void
    {
        $item = CartItem::factory()->create();

        $this->assertInstanceOf(CartItem::class, $item);
        $this->assertDatabaseHas('cart_items', ['cart_item_id' => $item->cart_item_id]);
    }

    #[Test]
    /** Default state creates a linked shopping cart. */
    public function default_state_creates_a_linked_shopping_cart(): void
    {
        $item = CartItem::factory()->create();

        $this->assertNotNull($item->cart_id);
        $this->assertDatabaseHas('shopping_carts', ['cart_id' => $item->cart_id]);
    }

    #[Test]
    /** Default state creates a linked product. */
    public function default_state_creates_a_linked_product(): void
    {
        $item = CartItem::factory()->create();

        $this->assertNotNull($item->product_id);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $item->product_id]);
    }

    #[Test]
    /** Default state sets a non-null design snapshot. */
    public function default_state_sets_non_null_design_snapshot(): void
    {
        $item = CartItem::factory()->create();

        $this->assertNotNull($item->design_snapshot);
    }

    // -------------------------------------------------------------------------
    // withoutPreview state
    // -------------------------------------------------------------------------

    #[Test]
    /** withoutPreview state sets preview_image_reference to null. */
    public function without_preview_state_sets_preview_image_reference_to_null(): void
    {
        $item = CartItem::factory()->withoutPreview()->create();

        $this->assertNull($item->preview_image_reference);
    }

    // -------------------------------------------------------------------------
    // Quantity boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** quantityAtMin state sets quantity to 1 (minimum). */
    public function quantity_at_min_state_sets_quantity_to_one(): void
    {
        $item = CartItem::factory()->quantityAtMin()->create();

        $this->assertSame(1, $item->quantity);
    }

    #[Test]
    /** quantityMidRange state sets quantity to 50 (in‑range). */
    public function quantity_mid_range_state_sets_quantity_to_fifty(): void
    {
        $item = CartItem::factory()->quantityMidRange()->create();

        $this->assertSame(50, $item->quantity);
    }

    #[Test]
    /** quantityAtMax state sets quantity to 99 (maximum). */
    public function quantity_at_max_state_sets_quantity_to_ninety_nine(): void
    {
        $item = CartItem::factory()->quantityAtMax()->create();

        $this->assertSame(99, $item->quantity);
    }

    // -------------------------------------------------------------------------
    // Quantity boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** quantityBelowMin state sets quantity to 0 (below minimum). */
    public function quantity_below_min_state_sets_quantity_to_zero(): void
    {
        $item = CartItem::factory()->quantityBelowMin()->make();

        $this->assertSame(0, $item->quantity);
    }

    #[Test]
    /** quantityAboveMax state sets quantity to 100 (above maximum). */
    public function quantity_above_max_state_sets_quantity_to_one_hundred(): void
    {
        $item = CartItem::factory()->quantityAboveMax()->make();

        $this->assertSame(100, $item->quantity);
    }
}