<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Staff;

/**
 * Authorises order-related operations for customers and staff.
 *
 * Customers may only view their own orders. Staff (any active) may perform
 * all order management tasks: viewing, status updates, notes, reassignment,
 * and print reference export.
 *
 * Laravel does not support multi-guard policies natively. Controllers must
 * pass the authenticated user explicitly:
 *   - Customer routes: auth('customer')->user()
 *   - Staff routes:    auth('staff')->user()
 */
class OrderPolicy
{
    // -------------------------------------------------------------------------
    // Customer authorisation
    // -------------------------------------------------------------------------

    /**
     * Allow any authenticated customer to view their order history.
     */
    public function viewOwnHistory(Customer $customer): bool
    {
        return true;
    }

    /**
     * Allow a customer to view a specific order only if they placed it.
     */
    public function viewOwn(Customer $customer, CustomerOrder $order): bool
    {
        return $order->customer_id === $customer->customer_id;
    }

    // -------------------------------------------------------------------------
    // Staff authorisation
    // -------------------------------------------------------------------------

    /**
     * Allow any active staff to view the order management console.
     */
    public function viewAny(Staff $staff): bool
    {
        return $staff->isActive();
    }

    /**
     * Allow any active staff to view a specific order's details.
     */
    public function view(Staff $staff, CustomerOrder $order): bool
    {
        return $staff->isActive();
    }

    /**
     * Allow any active staff to update an order's status.
     */
    public function updateStatus(Staff $staff, CustomerOrder $order): bool
    {
        return $staff->isActive();
    }

    /**
     * Allow any active staff to add an internal note to an order.
     */
    public function addNote(Staff $staff, CustomerOrder $order): bool
    {
        return $staff->isActive();
    }

    /**
     * Allow any active staff to reassign an order.
     */
    public function reassign(Staff $staff, CustomerOrder $order): bool
    {
        return $staff->isActive();
    }

    /**
     * Allow any active staff to export a print reference for an order item.
     */
    public function exportReference(Staff $staff, CustomerOrder $order): bool
    {
        return $staff->isActive();
    }
}