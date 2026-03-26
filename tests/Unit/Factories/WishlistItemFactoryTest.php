<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductType;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for WishlistItemFactory.
 *
 * Covers the default state (standard product) and the customizable state.
 */
class WishlistItemFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a WishlistItem record. */
    public function default_state_creates_wishlist_item_record(): void
    {
        $item = WishlistItem::factory()->create();

        $this->assertInstanceOf(WishlistItem::class, $item);
        $this->assertDatabaseHas('wishlist_items', ['wishlist_item_id' => $item->wishlist_item_id]);
    }

    #[Test]
    /** Default state creates a linked customer. */
    public function default_state_creates_a_linked_customer(): void
    {
        $item = WishlistItem::factory()->create();

        $this->assertNotNull($item->customer_id);
        $this->assertDatabaseHas('customers', ['customer_id' => $item->customer_id]);
    }

    #[Test]
    /** Default state sets product_type to Standard. */
    public function default_state_sets_product_type_to_standard(): void
    {
        $item = WishlistItem::factory()->create();

        $this->assertInstanceOf(ProductType::class, $item->product_type);
        $this->assertSame(ProductType::Standard, $item->product_type);
    }

    #[Test]
    /** Default state creates a linked standard product. */
    public function default_state_creates_a_linked_standard_product(): void
    {
        $item = WishlistItem::factory()->create();

        $this->assertNotNull($item->product_id);
        $this->assertDatabaseHas('standard_products', ['product_id' => $item->product_id]);
    }

    // -------------------------------------------------------------------------
    // Customizable state
    // -------------------------------------------------------------------------

    #[Test]
    /** customizable state sets product_type to Customizable. */
    public function customizable_state_sets_product_type_to_customizable(): void
    {
        $item = WishlistItem::factory()->customizable()->create();

        $this->assertSame(ProductType::Customizable, $item->product_type);
        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_item_id' => $item->wishlist_item_id,
            'product_type'     => ProductType::Customizable->value,
        ]);
    }

    #[Test]
    /** customizable state creates a linked customizable product. */
    public function customizable_state_creates_a_linked_customizable_print_product(): void
    {
        $item = WishlistItem::factory()->customizable()->create();

        $this->assertNotNull($item->product_id);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $item->product_id]);
    }
}