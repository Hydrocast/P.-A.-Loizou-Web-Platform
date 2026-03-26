<?php

use App\Models\CartItem;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomizablePrintProduct;
use App\Models\OrderItem;
use App\Models\PricingTier;
use App\Models\ShoppingCart;
use App\Support\DesignDocument;
use Inertia\Testing\AssertableInertia as Assert;

test('checkout review includes customization labels for each item', function () {
    $customer = Customer::factory()->create();

    $product = CustomizablePrintProduct::create([
        'product_name' => 'Classic Tee',
        'description' => 'Customizable t-shirt',
        'image_reference' => '/images/products/classic-tee.avif',
        'visibility_status' => 'Active',
        'design_profile_key' => 'tshirt-classic',
    ]);

    PricingTier::create([
        'product_id' => $product->product_id,
        'minimum_quantity' => 1,
        'maximum_quantity' => 9999,
        'unit_price' => 12.50,
    ]);

    $cart = ShoppingCart::factory()->create([
        'customer_id' => $customer->customer_id,
    ]);

    $snapshot = DesignDocument::encode(
        '{"version":"5.3.0","objects":[]}',
        [
            'shirt_color' => [
                'id' => 'black',
                'label' => 'Black',
            ],
            'print_sides' => [
                'value' => 'front-back',
                'label' => 'Front + Back',
            ],
        ],
    );

    CartItem::create([
        'cart_id' => $cart->cart_id,
        'product_id' => $product->product_id,
        'quantity' => 3,
        'design_snapshot' => $snapshot,
        'preview_image_reference' => null,
        'date_added' => now(),
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->get(route('checkout.review'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Customer/Checkout/CheckoutReview')
        ->has('checkout.items', 1)
        ->where('checkout.items.0.product_id', $product->product_id)
        ->where('checkout.items.0.shirt_color_label', 'Black')
        ->where('checkout.items.0.print_sides_label', 'Front + Back')
        ->where('checkout.items.0.product.product_name', 'Classic Tee')
    );
});

test('checkout confirmation includes customization labels for order items', function () {
    $customer = Customer::factory()->create();

    $product = CustomizablePrintProduct::create([
        'product_name' => 'Women Medium Fit Tee',
        'description' => 'Customizable women t-shirt',
        'image_reference' => '/images/products/women-medium-fit-tee.avif',
        'visibility_status' => 'Active',
        'design_profile_key' => 'tshirt-women-medium-fit',
    ]);

    $order = CustomerOrder::factory()->create([
        'customer_id' => $customer->customer_id,
    ]);

    $snapshot = DesignDocument::encode(
        '{"version":"5.3.0","objects":[]}',
        [
            'shirt_color' => [
                'id' => 'pink',
                'label' => 'Pink',
            ],
            'print_sides' => [
                'value' => 'front',
                'label' => 'Front Only',
            ],
        ],
    );

    OrderItem::create([
        'order_id' => $order->order_id,
        'product_id' => $product->product_id,
        'product_name' => 'Women Medium Fit Tee',
        'unit_price' => 14.00,
        'quantity' => 2,
        'line_subtotal' => 28.00,
        'design_snapshot' => $snapshot,
        'preview_image_reference' => null,
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->get(route('checkout.confirmation', $order->order_id));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Customer/Checkout/CheckoutConfirmation')
        ->where('order.order_id', $order->order_id)
        ->has('order.items', 1)
        ->where('order.items.0.product_name', 'Women Medium Fit Tee')
        ->where('order.items.0.shirt_color_label', 'Pink')
        ->where('order.items.0.print_sides_label', 'Front Only')
    );
});
