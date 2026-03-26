<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\SavedDesign;
use App\Models\ShoppingCart;
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for CartService.
 *
 * Covers cart retrieval, adding items, adding saved designs, updating
 * quantities, removing items, clearing the cart, retrieving cart contents,
 * and removing a product from all carts.
 * Boundary values: quantity (1–99).
 */
class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private CartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CartService();
    }

    // -------------------------------------------------------------------------
    // getCart()
    // -------------------------------------------------------------------------

    #[Test]
    /** Existing cart is returned with its items. */
    public function get_cart_returns_cart_for_customer(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->count(2)->create(['cart_id' => $cart->cart_id]);

        $result = $this->service->getCart($customer->customer_id);

        $this->assertEquals($cart->cart_id, $result->cart_id);
        $this->assertCount(2, $result->items);
    }

    #[Test]
    /** A new cart is created automatically when none exists. */
    public function get_cart_creates_cart_automatically_when_none_exists(): void
    {
        $customer = Customer::factory()->create();

        $result = $this->service->getCart($customer->customer_id);

        $this->assertInstanceOf(ShoppingCart::class, $result);
        $this->assertDatabaseHas('shopping_carts', ['customer_id' => $customer->customer_id]);
    }

    // -------------------------------------------------------------------------
    // addToCart()
    // -------------------------------------------------------------------------

    #[Test]
    /** A cart is created automatically when none exists before adding the item. */
    public function add_to_cart_creates_cart_automatically_if_none_exists(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();

        $this->service->addToCart(
            customerId: $customer->customer_id,
            productId: $product->product_id,
            quantity: 1,
            designSnapshot: '{"version":"5.3.0","objects":[]}',
            previewImageReference: null,
        );

        $this->assertDatabaseHas('shopping_carts', ['customer_id' => $customer->customer_id]);
        $this->assertDatabaseCount('cart_items', 1);
    }

    #[Test]
    /** Item is added to an existing cart with the correct quantity. */
    public function add_to_cart_adds_item_to_existing_cart(): void
    {
        $customer = Customer::factory()->create();
        ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $product  = CustomizablePrintProduct::factory()->create();

        $this->service->addToCart($customer->customer_id, $product->product_id, 3, '{}', null);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertEquals(3, CartItem::first()->quantity);
    }

    #[Test]
    /** Non-existent product throws ValidationException. */
    public function add_to_cart_throws_when_product_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->addToCart($customer->customer_id, 99999, 1, '{}', null);
    }

    #[Test]
    /** Inactive product throws ValidationException. */
    public function add_to_cart_throws_when_product_is_inactive(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->inactive()->create();

        $this->expectException(ValidationException::class);
        $this->service->addToCart($customer->customer_id, $product->product_id, 1, '{}', null);
    }

    // Quantity boundaries -----------------------------------------------------

    #[Test]
    /** Quantity of 0 (below minimum) is rejected. */
    public function add_to_cart_throws_when_quantity_is_zero(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->addToCart($customer->customer_id, $product->product_id, 0, '{}', null);
    }

    #[Test]
    /** Quantity of 1 (minimum) is accepted. */
    public function add_to_cart_accepts_quantity_of_one(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();
        $this->service->addToCart($customer->customer_id, $product->product_id, 1, '{}', null);
        $this->assertEquals(1, CartItem::first()->quantity);
    }

    #[Test]
    /** Quantity of 50 (in‑range) is accepted. */
    public function add_to_cart_accepts_quantity_of_fifty(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();
        $this->service->addToCart($customer->customer_id, $product->product_id, 50, '{}', null);
        $this->assertEquals(50, CartItem::first()->quantity);
    }

    #[Test]
    /** Quantity of 99 (maximum) is accepted. */
    public function add_to_cart_accepts_quantity_of_ninety_nine(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();
        $this->service->addToCart($customer->customer_id, $product->product_id, 99, '{}', null);
        $this->assertEquals(99, CartItem::first()->quantity);
    }

    #[Test]
    /** Quantity of 100 (above maximum) is rejected. */
    public function add_to_cart_throws_when_quantity_exceeds_ninety_nine(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->addToCart($customer->customer_id, $product->product_id, 100, '{}', null);
    }

    // -------------------------------------------------------------------------
    // addSavedDesignToCart()
    // -------------------------------------------------------------------------

    #[Test]
    /** Saved design is added to the cart with quantity fixed at 1. */
    public function add_saved_design_to_cart_adds_item_with_quantity_one(): void
    {
        $customer = Customer::factory()->create();
        $design   = SavedDesign::factory()->create(['customer_id' => $customer->customer_id]);

        $this->service->addSavedDesignToCart($customer->customer_id, $design->design_id);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertEquals(1, CartItem::first()->quantity);
    }

    #[Test]
    /** Design snapshot is copied verbatim from the saved design. */
    public function add_saved_design_to_cart_copies_design_snapshot(): void
    {
        $customer = Customer::factory()->create();
        $design   = SavedDesign::factory()->create([
            'customer_id' => $customer->customer_id,
            'design_data' => '{"version":"5.3.0","objects":[{"type":"textbox"}]}',
        ]);

        $this->service->addSavedDesignToCart($customer->customer_id, $design->design_id);

        $this->assertEquals($design->design_data, CartItem::first()->design_snapshot);
    }

    #[Test]
    /** Design belonging to another customer throws ValidationException. */
    public function add_saved_design_to_cart_throws_when_design_belongs_to_another_customer(): void
    {
        $owner    = Customer::factory()->create();
        $intruder = Customer::factory()->create();
        $design   = SavedDesign::factory()->create(['customer_id' => $owner->customer_id]);

        $this->expectException(ValidationException::class);
        $this->service->addSavedDesignToCart($intruder->customer_id, $design->design_id);
    }

    #[Test]
    /** Non-existent design throws ModelNotFoundException. */
    public function add_saved_design_to_cart_throws_when_design_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ModelNotFoundException::class);
        $this->service->addSavedDesignToCart($customer->customer_id, 99999);
    }

    #[Test]
    /** Design whose product is no longer available throws ValidationException. */
    public function add_saved_design_to_cart_throws_when_product_is_no_longer_available(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->inactive()->create();
        $design   = SavedDesign::factory()->create([
            'customer_id' => $customer->customer_id,
            'product_id'  => $product->product_id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->addSavedDesignToCart($customer->customer_id, $design->design_id);
    }

    // -------------------------------------------------------------------------
    // updateQuantity()
    // -------------------------------------------------------------------------

    #[Test]
    /** Quantity is updated to the new value on the correct item. */
    public function update_quantity_changes_item_quantity(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id, 'quantity' => 1]);

        $this->service->updateQuantity($customer->customer_id, $item->cart_item_id, 5);

        $item->refresh();
        $this->assertEquals(5, $item->quantity);
    }

    #[Test]
    /** Item belonging to another customer throws ValidationException. */
    public function update_quantity_throws_when_item_belongs_to_another_customer(): void
    {
        $owner    = Customer::factory()->create();
        $intruder = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $owner->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);

        $this->expectException(ValidationException::class);
        $this->service->updateQuantity($intruder->customer_id, $item->cart_item_id, 5);
    }

    // Quantity boundaries -----------------------------------------------------

    #[Test]
    /** Quantity of 0 (below minimum) is rejected. */
    public function update_quantity_throws_when_quantity_is_zero(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);
        $this->expectException(ValidationException::class);
        $this->service->updateQuantity($customer->customer_id, $item->cart_item_id, 0);
    }

    #[Test]
    /** Quantity of 1 (minimum) is accepted. */
    public function update_quantity_accepts_quantity_of_one(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);
        $this->service->updateQuantity($customer->customer_id, $item->cart_item_id, 1);
        $item->refresh();
        $this->assertEquals(1, $item->quantity);
    }

    #[Test]
    /** Quantity of 50 (in‑range) is accepted. */
    public function update_quantity_accepts_quantity_of_fifty(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);
        $this->service->updateQuantity($customer->customer_id, $item->cart_item_id, 50);
        $item->refresh();
        $this->assertEquals(50, $item->quantity);
    }

    #[Test]
    /** Quantity of 99 (maximum) is accepted. */
    public function update_quantity_accepts_quantity_of_ninety_nine(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);
        $this->service->updateQuantity($customer->customer_id, $item->cart_item_id, 99);
        $item->refresh();
        $this->assertEquals(99, $item->quantity);
    }

    #[Test]
    /** Quantity of 100 (above maximum) is rejected. */
    public function update_quantity_throws_when_quantity_exceeds_ninety_nine(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);
        $this->expectException(ValidationException::class);
        $this->service->updateQuantity($customer->customer_id, $item->cart_item_id, 100);
    }

    // -------------------------------------------------------------------------
    // removeFromCart()
    // -------------------------------------------------------------------------

    #[Test]
    /** Item is deleted from the database. */
    public function remove_from_cart_deletes_item(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);

        $this->service->removeFromCart($customer->customer_id, $item->cart_item_id);

        $this->assertDatabaseMissing('cart_items', ['cart_item_id' => $item->cart_item_id]);
    }

    #[Test]
    /** Item belonging to another customer throws ValidationException. */
    public function remove_from_cart_throws_when_item_belongs_to_another_customer(): void
    {
        $owner    = Customer::factory()->create();
        $intruder = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $owner->customer_id]);
        $item     = CartItem::factory()->create(['cart_id' => $cart->cart_id]);

        $this->expectException(ValidationException::class);
        $this->service->removeFromCart($intruder->customer_id, $item->cart_item_id);
    }

    #[Test]
    /** Non-existent item throws ValidationException. */
    public function remove_from_cart_throws_when_item_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->removeFromCart($customer->customer_id, 99999);
    }

    // -------------------------------------------------------------------------
    // clearCart()
    // -------------------------------------------------------------------------

    #[Test]
    /** All items are removed from the cart when it exists. */
    public function clear_cart_removes_all_items(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->count(3)->create(['cart_id' => $cart->cart_id]);

        $this->service->clearCart($customer->customer_id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    #[Test]
    /** Clearing a cart that does not exist completes without exception. */
    public function clear_cart_completes_silently_when_no_cart_exists(): void
    {
        $customer = Customer::factory()->create();
        $this->service->clearCart($customer->customer_id);
        $this->assertTrue(true);
    }

    // -------------------------------------------------------------------------
    // getCartContents()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns all items in the customer's cart. */
    public function get_cart_contents_returns_items_for_existing_cart(): void
    {
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->count(2)->create(['cart_id' => $cart->cart_id]);

        $contents = $this->service->getCartContents($customer->customer_id);

        $this->assertCount(2, $contents);
    }

    #[Test]
    /** Returns an empty collection when no cart exists for the customer. */
    public function get_cart_contents_returns_empty_collection_when_no_cart_exists(): void
    {
        $customer = Customer::factory()->create();

        $contents = $this->service->getCartContents($customer->customer_id);

        $this->assertCount(0, $contents);
    }

    // -------------------------------------------------------------------------
    // removeProductFromAllCarts()
    // -------------------------------------------------------------------------

    #[Test]
    /** All cart items referencing the given product are deleted. */
    public function remove_product_from_all_carts_deletes_matching_items(): void
    {
        $product  = CustomizablePrintProduct::factory()->create();
        $customer = Customer::factory()->create();
        $cart     = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->count(2)->create([
            'cart_id'    => $cart->cart_id,
            'product_id' => $product->product_id,
        ]);

        $this->service->removeProductFromAllCarts($product->product_id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    #[Test]
    /** Items referencing a different product are not affected. */
    public function remove_product_from_all_carts_leaves_other_items_intact(): void
    {
        $targetProduct = CustomizablePrintProduct::factory()->create();
        $otherProduct  = CustomizablePrintProduct::factory()->create();
        $customer      = Customer::factory()->create();
        $cart          = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);

        CartItem::factory()->create(['cart_id' => $cart->cart_id, 'product_id' => $targetProduct->product_id]);
        CartItem::factory()->create(['cart_id' => $cart->cart_id, 'product_id' => $otherProduct->product_id]);

        $this->service->removeProductFromAllCarts($targetProduct->product_id);

        $this->assertDatabaseCount('cart_items', 1);
        $this->assertDatabaseHas('cart_items', ['product_id' => $otherProduct->product_id]);
    }
}