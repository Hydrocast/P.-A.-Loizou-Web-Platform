<?php

namespace App\Enums;

/**
 * Determines whether a product is visible to customers.
 *
 * Used for both standard and customizable products.
 * Inactive products are hidden from the catalog and cannot be added
 * to wishlists or carts. Products are never deleted from the database,
 * only deactivated, to preserve historical order references.
 */
enum ProductVisibilityStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';

    /**
     * Returns true if the product is visible to customers.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }
}