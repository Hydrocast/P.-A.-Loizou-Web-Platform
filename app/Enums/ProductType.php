<?php

namespace App\Enums;

/**
 * Distinguishes between standard and customizable products in polymorphic relationships.
 *
 * Used in wishlist_items and carousel_slides to identify which product table
 * (standard_products or customizable_print_products) the product_id references.
 */
enum ProductType: string
{
    case Standard = 'standard';
    case Customizable = 'customizable';
}