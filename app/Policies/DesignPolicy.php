<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\SavedDesign;

/**
 * Authorises saved design operations for authenticated customers.
 *
 * Customers may only access or delete designs that belong to their own
 * account. Saved designs are immutable snapshots, so no update permission
 * is granted for modifying an existing record.
 */
class DesignPolicy
{
    /**
     * Determine whether the customer can view the saved design.
     */
    public function view(Customer $customer, SavedDesign $savedDesign): bool
    {
        return $savedDesign->belongsToCustomer($customer->customer_id);
    }

    /**
     * Determine whether the customer can create a saved design.
     */
    public function create(Customer $customer): bool
    {
        return $customer->customer_id > 0;
    }

    /**
     * Saved designs are immutable.
     */
    public function update(Customer $customer, SavedDesign $savedDesign): bool
    {
        return false;
    }

    /**
     * Determine whether the customer can delete the saved design.
     */
    public function delete(Customer $customer, SavedDesign $savedDesign): bool
    {
        return $savedDesign->belongsToCustomer($customer->customer_id);
    }

    public function restore(Customer $customer, SavedDesign $savedDesign): bool
    {
        return false;
    }

    public function forceDelete(Customer $customer, SavedDesign $savedDesign): bool
    {
        return false;
    }
}