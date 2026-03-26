<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\PricingTier;
use App\Models\ShoppingCart;
use App\Services\CartService;
use App\Services\CheckoutService;
use App\Services\PricingConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for CheckoutService.
 *
 * Covers checkout review and order submission, including VAT calculation,
 * pricing tier resolution, validation of customer data, and event dispatching.
 * Boundary values: name (2-50), phone (8 digits), email (valid format required).
 */
class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutService $service;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('business.vat_rate', 19.0);
        $this->service = new CheckoutService(
            new PricingConfigurationService(),
            new CartService(),
        );
    }

    // -------------------------------------------------------------------------
    // Helper: build a cart with one item for a customer.
    // -------------------------------------------------------------------------

    private function cartWithItem(
        Customer $customer,
        int $quantity = 5,
        float $unitPrice = 10.00,
    ): ShoppingCart {
        $product = CustomizablePrintProduct::factory()->create();

        PricingTier::factory()->create([
            'product_id'       => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9999,
            'unit_price'       => $unitPrice,
        ]);

        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->create([
            'cart_id'    => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity'   => $quantity,
        ]);

        return $cart;
    }

    // -------------------------------------------------------------------------
    // reviewCheckout()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns correct VAT-inclusive totals for a single item. */
    public function review_checkout_calculates_correct_totals(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer, quantity: 10, unitPrice: 10.00);

        $review = $this->service->reviewCheckout($customer->customer_id);

        $this->assertEquals(100.00, $review['cart_total']);
        $this->assertEquals(15.97, $review['vat_amount']);
        $this->assertEquals(84.03, $review['net_amount']);
        $this->assertEquals(19.0, $review['vat_rate']);
    }

    #[Test]
    /** Net amount and VAT amount always sum exactly to the cart total. */
    public function review_checkout_net_plus_vat_always_equals_cart_total(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer, quantity: 7, unitPrice: 13.33);

        $review = $this->service->reviewCheckout($customer->customer_id);

        $this->assertEquals(
            $review['cart_total'],
            round($review['net_amount'] + $review['vat_amount'], 2)
        );
    }

    #[Test]
    /** Throws when the cart exists but contains no items. */
    public function review_checkout_throws_when_cart_is_empty(): void
    {
        $customer = Customer::factory()->create();
        ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);

        $this->expectException(ValidationException::class);
        $this->service->reviewCheckout($customer->customer_id);
    }

    #[Test]
    /** Throws when no cart exists for the customer. */
    public function review_checkout_throws_when_no_cart_exists(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->reviewCheckout($customer->customer_id);
    }

    #[Test]
    /** Applies the pricing tier that matches the item quantity. */
    public function review_checkout_resolves_pricing_tier_based_on_quantity(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();

        PricingTier::factory()->create([
            'product_id'       => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
            'unit_price'       => 15.00,
        ]);
        PricingTier::factory()->create([
            'product_id'       => $product->product_id,
            'minimum_quantity' => 10,
            'maximum_quantity' => 9999,
            'unit_price'       => 10.00,
        ]);

        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->create([
            'cart_id'    => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity'   => 10,
        ]);

        $review = $this->service->reviewCheckout($customer->customer_id);
        $this->assertEquals(100.00, $review['cart_total']);
    }

    #[Test]
    /** Throws when no pricing tier covers the item quantity. */
    public function review_checkout_throws_when_no_pricing_tier_covers_item_quantity(): void
    {
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create();

        PricingTier::factory()->create([
            'product_id'       => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 5,
            'unit_price'       => 10.00,
        ]);

        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->create([
            'cart_id'    => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity'   => 10,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->reviewCheckout($customer->customer_id);
    }

    #[Test]
    /** Sums line subtotals across all items to produce the cart total. */
    public function review_checkout_sums_all_line_subtotals_for_cart_total(): void
    {
        $customer = Customer::factory()->create();
        $productA = CustomizablePrintProduct::factory()->create();
        $productB = CustomizablePrintProduct::factory()->create();

        PricingTier::factory()->create([
            'product_id' => $productA->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9999,
            'unit_price' => 10.00,
        ]);
        PricingTier::factory()->create([
            'product_id' => $productB->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9999,
            'unit_price' => 20.00,
        ]);

        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->create([
            'cart_id' => $cart->cart_id,
            'product_id' => $productA->product_id,
            'quantity' => 2,
        ]);
        CartItem::factory()->create([
            'cart_id' => $cart->cart_id,
            'product_id' => $productB->product_id,
            'quantity' => 3,
        ]);

        $review = $this->service->reviewCheckout($customer->customer_id);
        $this->assertEquals(80.00, $review['cart_total']);
    }

    // -------------------------------------------------------------------------
    // submitOrder()
    // -------------------------------------------------------------------------

    #[Test]
    /** Order is created with Pending status. */
    public function submit_order_creates_order_with_pending_status(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $order = $this->service->submitOrder(
            $customer->customer_id,
            'John Doe',
            'john@example.com',
            '12345678'
        );

        $this->assertEquals(OrderStatus::Pending, $order->order_status);
        $this->assertDatabaseHas('customer_orders', ['customer_id' => $customer->customer_id]);
    }

    #[Test]
    /** Cart is emptied after the order is submitted. */
    public function submit_order_clears_cart_after_order_created(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
        $this->assertDatabaseCount('cart_items', 0);
    }

    #[Test]
    /** OrderPlaced event is dispatched after the transaction commits. */
    public function submit_order_dispatches_order_placed_event(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
        Event::assertDispatched(OrderPlaced::class);
    }

    #[Test]
    /** All monetary values are frozen on the order record at submission time. */
    public function submit_order_freezes_all_monetary_values_on_order_record(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer, quantity: 5, unitPrice: 10.00);

        $order = $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');

        $this->assertEquals(50.00, $order->total_amount);
        $this->assertEquals(7.98, $order->vat_amount);
        $this->assertEquals(42.02, $order->net_amount);
        $this->assertEquals(19.0, $order->vat_rate);
    }

    #[Test]
    /** Order items are created from cart items with the correct quantities. */
    public function submit_order_creates_order_items_from_cart(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer, quantity: 5);

        $order = $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
        $this->assertCount(1, $order->items);
        $this->assertEquals(5, $order->items->first()->quantity);
    }

    #[Test]
    /** Product name is captured on the order item at submission time. */
    public function submit_order_freezes_product_name_on_order_item(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $product  = CustomizablePrintProduct::factory()->create(['product_name' => 'Business Cards']);

        PricingTier::factory()->create([
            'product_id'       => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9999,
            'unit_price'       => 10.00,
        ]);

        $cart = ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);
        CartItem::factory()->create([
            'cart_id'    => $cart->cart_id,
            'product_id' => $product->product_id,
            'quantity'   => 1,
        ]);

        $order = $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
        $this->assertEquals('Business Cards', $order->items->first()->product_name);
    }

    #[Test]
    /** Throws when the cart exists but contains no items. */
    public function submit_order_throws_when_cart_is_empty(): void
    {
        $customer = Customer::factory()->create();
        ShoppingCart::factory()->create(['customer_id' => $customer->customer_id]);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
    }

    #[Test]
    /** Throws when no cart exists for the customer. */
    public function submit_order_throws_when_no_cart_exists(): void
    {
        $customer = Customer::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
    }

    // Customer name boundaries ------------------------------------------------

    #[Test]
    /** Customer name of 1 character (below minimum) is rejected. */
    public function submit_order_throws_when_customer_name_is_one_character(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'A', 'j@e.com', '12345678');
    }

    #[Test]
    /** Customer name of 2 characters (minimum) is accepted. */
    public function submit_order_accepts_customer_name_of_two_characters(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $order = $this->service->submitOrder($customer->customer_id, 'Ab', 'j@e.com', '12345678');
        $this->assertEquals('Ab', $order->customer_name);
    }

    #[Test]
    /** Customer name of 26 characters (in-range) is accepted. */
    public function submit_order_accepts_customer_name_of_twenty_six_characters(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $name  = str_repeat('a', 26);
        $order = $this->service->submitOrder($customer->customer_id, $name, 'j@e.com', '12345678');

        $this->assertEquals($name, $order->customer_name);
    }

    #[Test]
    /** Customer name of 50 characters (maximum) is accepted. */
    public function submit_order_accepts_customer_name_of_fifty_characters(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $name  = str_repeat('a', 50);
        $order = $this->service->submitOrder($customer->customer_id, $name, 'j@e.com', '12345678');

        $this->assertEquals($name, $order->customer_name);
    }

    #[Test]
    /** Customer name of 51 characters (above maximum) is rejected. */
    public function submit_order_throws_when_customer_name_exceeds_fifty_characters(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, str_repeat('a', 51), 'j@e.com', '12345678');
    }

    // Email validation --------------------------------------------------------

    #[Test]
    /** Invalid email format is rejected. */
    public function submit_order_throws_when_customer_email_is_invalid(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', 'not-an-email', '12345678');
    }

    #[Test]
    /** Empty email string is rejected. */
    public function submit_order_throws_when_customer_email_is_empty(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', '', '12345678');
    }

    // Phone number boundaries -------------------------------------------------

    #[Test]
    /** Phone number of 7 digits (below minimum) is rejected. */
    public function submit_order_throws_when_phone_is_seven_digits(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '1234567');
    }

    #[Test]
    /** Phone number of exactly 8 digits (minimum) is accepted. */
    public function submit_order_accepts_eight_digit_phone(): void
    {
        Event::fake();
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $order = $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '12345678');
        $this->assertEquals('12345678', $order->customer_phone);
    }

    #[Test]
    /** Phone number of 9 digits (above maximum) is rejected. */
    public function submit_order_throws_when_phone_is_nine_digits(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '123456789');
    }

    #[Test]
    /** Non-digit characters in phone are rejected. */
    public function submit_order_throws_when_phone_contains_non_digit_characters(): void
    {
        $customer = Customer::factory()->create();
        $this->cartWithItem($customer);

        $this->expectException(ValidationException::class);
        $this->service->submitOrder($customer->customer_id, 'John Doe', 'j@e.com', '1234567a');
    }
}