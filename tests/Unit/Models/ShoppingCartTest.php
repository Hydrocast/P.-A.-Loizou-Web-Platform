<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the ShoppingCart model.
 *
 * Covers model configuration, relationship structure and data resolution,
 * and business logic for isEmpty().
 *
 * isEmpty() boundary values:
 * - Cart has no items: true
 * - Cart has items: false
 */
class ShoppingCartTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the shopping_carts table. */
    public function model_uses_shopping_carts_table(): void
    {
        $cart = new ShoppingCart();

        $this->assertSame('shopping_carts', $cart->getTable());
    }

    #[Test]
    /** Primary key is cart_id. */
    public function primary_key_is_cart_id(): void
    {
        $cart = new ShoppingCart();

        $this->assertSame('cart_id', $cart->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $cart = new ShoppingCart();

        $this->assertSame('int', $cart->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $cart = new ShoppingCart();

        $this->assertTrue($cart->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $cart = new ShoppingCart();

        $this->assertFalse($cart->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $cart = new ShoppingCart();
        $fillable = $cart->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('last_updated', $fillable);
    }

    #[Test]
    /** last_updated is cast to datetime. */
    public function last_updated_cast_is_configured(): void
    {
        $cart = new ShoppingCart();

        $this->assertSame('datetime', $cart->getCasts()['last_updated']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – customer()
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() returns a BelongsTo relation. */
    public function customer_returns_belongs_to_relation(): void
    {
        $relation = (new ShoppingCart())->customer();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** customer() uses customer_id as foreign key. */
    public function customer_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new ShoppingCart())->customer();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** customer() relates to Customer model. */
    public function customer_relates_to_customer_model(): void
    {
        $relation = (new ShoppingCart())->customer();

        $this->assertInstanceOf(Customer::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – items()
    // -------------------------------------------------------------------------

    #[Test]
    /** items() returns a HasMany relation. */
    public function items_returns_has_many_relation(): void
    {
        $relation = (new ShoppingCart())->items();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** items() uses cart_id as foreign key. */
    public function items_uses_cart_id_as_foreign_key(): void
    {
        $relation = (new ShoppingCart())->items();

        $this->assertSame('cart_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** items() relates to CartItem model. */
    public function items_relates_to_cart_item_model(): void
    {
        $relation = (new ShoppingCart())->items();

        $this->assertInstanceOf(CartItem::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() resolves to the customer who owns this cart. */
    public function customer_resolves_to_the_owning_customer(): void
    {
        $customer = Customer::factory()->create();
        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);

        $resolved = $cart->customer;

        $this->assertInstanceOf(Customer::class, $resolved);
        $this->assertSame($customer->customer_id, $resolved->customer_id);
    }

    #[Test]
    /** items() resolves to all items in this cart. */
    public function items_resolves_to_the_carts_items(): void
    {
        $cart = ShoppingCart::factory()->create();
        CartItem::factory()->count(3)->create(['cart_id' => $cart->cart_id]);

        $this->assertCount(3, $cart->items);
        $cart->items->each(
            fn ($item) => $this->assertSame($cart->cart_id, $item->cart_id)
        );
    }

    #[Test]
    /** items() excludes items belonging to other carts. */
    public function items_excludes_items_from_other_carts(): void
    {
        $cart = ShoppingCart::factory()->create();
        CartItem::factory()->count(2)->create(['cart_id' => $cart->cart_id]);
        CartItem::factory()->count(3)->create();

        $this->assertCount(2, $cart->items);
    }

    // -------------------------------------------------------------------------
    // isEmpty()
    // -------------------------------------------------------------------------

    #[Test]
    /** isEmpty() returns true when cart has no items. */
    public function is_empty_returns_true_when_cart_has_no_items(): void
    {
        $cart = ShoppingCart::factory()->create();

        $this->assertTrue($cart->isEmpty());
    }

    #[Test]
    /** isEmpty() returns false when cart has items. */
    public function is_empty_returns_false_when_cart_has_items(): void
    {
        $cart = ShoppingCart::factory()->create();
        CartItem::factory()->create(['cart_id' => $cart->cart_id]);

        $this->assertFalse($cart->isEmpty());
    }
}