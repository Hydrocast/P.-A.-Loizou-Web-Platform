<?php

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use Inertia\Testing\AssertableInertia as Assert;

test('authenticated customer sees only their orders with items', function () {
    $customer = Customer::factory()->create();
    $otherCustomer = Customer::factory()->create();

    $customerOrder = CustomerOrder::factory()->create([
        'customer_id' => $customer->customer_id,
    ]);

    $otherOrder = CustomerOrder::factory()->create([
        'customer_id' => $otherCustomer->customer_id,
    ]);

    $firstItem = OrderItem::factory()->create([
        'order_id' => $customerOrder->order_id,
        'product_name' => 'Primary Product',
    ]);

    $secondItem = OrderItem::factory()->create([
        'order_id' => $customerOrder->order_id,
        'product_name' => 'Secondary Product',
    ]);

    OrderItem::factory()->create([
        'order_id' => $otherOrder->order_id,
    ]);

    $response = $this->actingAs($customer, 'customer')
        ->get(route('account.orders'));

    $response->assertOk();

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Customer/Account/OrderHistory')
        ->has('orders', 1)
        ->where('orders.0.order_id', $customerOrder->order_id)
        ->has('orders.0.items', 2)
        ->where('orders.0.items.0.order_item_id', $firstItem->order_item_id)
        ->where('orders.0.items.0.product_name', $firstItem->product_name)
        ->where('orders.0.items.1.order_item_id', $secondItem->order_item_id)
        ->where('orders.0.items.1.product_name', $secondItem->product_name)
    );
});
