<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CartItem;
use App\Models\CustomizablePrintProduct;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the CartItem model.
 *
 * Covers model configuration, relationship structure and data resolution.
 */
class CartItemTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the cart_items table. */
    public function model_uses_cart_items_table(): void
    {
        $item = new CartItem();

        $this->assertSame('cart_items', $item->getTable());
    }

    #[Test]
    /** Primary key is cart_item_id. */
    public function primary_key_is_cart_item_id(): void
    {
        $item = new CartItem();

        $this->assertSame('cart_item_id', $item->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $item = new CartItem();

        $this->assertSame('int', $item->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $item = new CartItem();

        $this->assertTrue($item->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $item = new CartItem();

        $this->assertFalse($item->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $item = new CartItem();
        $fillable = $item->getFillable();

        $this->assertContains('cart_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('design_snapshot', $fillable);
        $this->assertContains('preview_image_reference', $fillable);
        $this->assertContains('date_added', $fillable);
    }

    #[Test]
    /** date_added is cast to datetime. */
    public function date_added_cast_is_configured(): void
    {
        $item = new CartItem();

        $this->assertSame('datetime', $item->getCasts()['date_added']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – cart()
    // -------------------------------------------------------------------------

    #[Test]
    /** cart() returns a BelongsTo relation. */
    public function cart_returns_belongs_to_relation(): void
    {
        $relation = (new CartItem())->cart();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** cart() uses cart_id as foreign key. */
    public function cart_uses_cart_id_as_foreign_key(): void
    {
        $relation = (new CartItem())->cart();

        $this->assertSame('cart_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** cart() relates to ShoppingCart model. */
    public function cart_relates_to_shopping_cart_model(): void
    {
        $relation = (new CartItem())->cart();

        $this->assertInstanceOf(ShoppingCart::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – product()
    // -------------------------------------------------------------------------

    #[Test]
    /** product() returns a BelongsTo relation. */
    public function product_returns_belongs_to_relation(): void
    {
        $relation = (new CartItem())->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** product() uses product_id as foreign key. */
    public function product_uses_product_id_as_foreign_key(): void
    {
        $relation = (new CartItem())->product();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** product() relates to CustomizablePrintProduct model. */
    public function product_relates_to_customizable_print_product_model(): void
    {
        $relation = (new CartItem())->product();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** cart() resolves to the shopping cart that contains this item. */
    public function cart_resolves_to_the_owning_shopping_cart(): void
    {
        $cart = ShoppingCart::factory()->create();
        $item = CartItem::factory()->create(['cart_id' => $cart->cart_id]);

        $resolved = $item->cart;

        $this->assertInstanceOf(ShoppingCart::class, $resolved);
        $this->assertSame($cart->cart_id, $resolved->cart_id);
    }

    #[Test]
    /** product() resolves to the customizable product referenced by this item. */
    public function product_resolves_to_the_referenced_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $item = CartItem::factory()->create(['product_id' => $product->product_id]);

        $resolved = $item->product;

        $this->assertInstanceOf(CustomizablePrintProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }
}