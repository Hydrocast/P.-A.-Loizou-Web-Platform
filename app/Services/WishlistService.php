<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use App\Models\WishlistItem;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Manages customer wishlist operations.
 *
 * Both standard and customizable products may be saved to a wishlist.
 * The product_type discriminator identifies which table to query.
 *
 * When the wishlist is viewed, any items referencing deactivated products
 * are silently removed before the list is returned.
 */
class WishlistService
{
    /**
     * Adds a product to the customer's wishlist.
     *
     * Verifies the product is active before adding.
     *
     * @throws ValidationException if product inactive or already in wishlist.
     */
    public function addToWishlist(int $customerId, int $productId, ProductType $productType): WishlistItem
    {
        $this->validateProduct($productId, $productType);
        $this->checkDuplicate($customerId, $productId, $productType);

        return WishlistItem::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'product_type' => $productType,
            'date_added' => now(),
        ]);
    }

    /**
     * Removes a wishlist item.
     *
     * Verifies ownership before deletion.
     *
     * @throws ValidationException if item not found or doesn't belong to customer.
     */
    public function removeFromWishlist(int $customerId, int $wishlistItemId): void
    {
        $item = WishlistItem::where('wishlist_item_id', $wishlistItemId)
            ->where('customer_id', $customerId)
            ->first();

        if ($item === null) {
            throw ValidationException::withMessages([
                'wishlist_item_id' => 'This wishlist item could not be found.',
            ]);
        }

        $item->delete();
    }

    /**
     * Returns the customer's wishlist, pruning stale entries first.
     *
     * Items referencing products that are no longer active are deleted
     * before the list is returned.
     */
    public function getWishlist(int $customerId): Collection
    {
        $items = WishlistItem::where('customer_id', $customerId)
            ->orderByDesc('date_added')
            ->get();

        $staleIds = $items->filter(fn($item) => !$item->isProductAvailable())
            ->pluck('wishlist_item_id');

        if ($staleIds->isNotEmpty()) {
            WishlistItem::whereIn('wishlist_item_id', $staleIds)->delete();
        }

        return $items->filter(fn($item) => $item->isProductAvailable())->values();
    }

    /**
     * Returns true if the given product is already in the customer's wishlist.
     * Used for wishlist indicator on product detail pages.
     */
    public function isInWishlist(int $customerId, int $productId, ProductType $productType): bool
    {
        return WishlistItem::where('customer_id', $customerId)
            ->where('product_id', $productId)
            ->where('product_type', $productType)
            ->exists();
    }

    /**
     * Verifies the referenced product exists and is active.
     *
     * @throws ValidationException if product unavailable.
     */
    private function validateProduct(int $productId, ProductType $productType): void
    {
        $product = match ($productType) {
            ProductType::Standard => StandardProduct::find($productId),
            ProductType::Customizable => CustomizablePrintProduct::find($productId),
        };

        if ($product === null || !$product->isActive()) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not available.',
            ]);
        }
    }

    /**
     * Ensures the product is not already saved in the customer's wishlist.
     *
     * @throws ValidationException if duplicate entry would be created.
     */
    private function checkDuplicate(int $customerId, int $productId, ProductType $productType): void
    {
        if ($this->isInWishlist($customerId, $productId, $productType)) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is already in your wishlist.',
            ]);
        }
    }
}