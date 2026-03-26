<?php

namespace App\Policies;

use App\Models\CartItem;
use App\Models\Customer;

/**
 * Authorises cart item operations for authenticated customers.
 *
 * A customer may only modify or remove items that belong to their own cart.
 * Ownership is determined by following the cart relationship to its customer_id.
 *
 * To avoid extra database queries, the cart relationship should be eager-loaded
 * when retrieving multiple cart items for policy checks.
 *
 * Staff have no cart item operations in the SRS, so no staff methods are defined.
 *
 * The $customer parameter is the authenticated customer making the request.
 * Controllers must pass auth('customer')->user() explicitly.
 */
class CartItemPolicy
{
    /**
     * Determine whether the customer can update the quantity of a cart item.
     */
    public function update(Customer $customer, CartItem $cartItem): bool
    {
        return $this->customerOwnsItem($customer, $cartItem);
    }

    /**
     * Determine whether the customer can remove a cart item.
     */
    public function delete(Customer $customer, CartItem $cartItem): bool
    {
        return $this->customerOwnsItem($customer, $cartItem);
    }

    /**
     * Verify that the cart item belongs to the given customer.
     */
    private function customerOwnsItem(Customer $customer, CartItem $cartItem): bool
    {
        return $cartItem->cart !== null
            && $cartItem->cart->customer_id === $customer->customer_id;
    }
}