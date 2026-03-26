<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\CustomizablePrintProduct;
use App\Models\SavedDesign;
use App\Models\ShoppingCart;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Manages the customer shopping cart.
 *
 * Each customer has at most one persistent cart. Items contain immutable
 * design snapshots that cannot be modified after creation.
 *
 * Price calculations are not performed here. Pricing is resolved during
 * checkout review in CheckoutService.
 *
 * The cart is cleared by CheckoutService after successful order submission.
 */
class CartService
{
    /**
     * Retrieve the customer's cart. Creates one if it doesn't exist.
     */
    public function getCart(int $customerId): ShoppingCart
    {
        return $this->getOrCreateCart($customerId);
    }

    /**
     * Add a freshly customized product to the cart.
     *
     * Verifies product availability and quantity, then stores an immutable
     * snapshot of the design state.
     *
     * @throws ValidationException if product unavailable or quantity invalid
     */
    public function addToCart(
        int $customerId,
        int $productId,
        int $quantity,
        string $designSnapshot,
        ?string $previewImageReference,
    ): void {
        $this->validateProduct($productId);
        $this->validateQuantity($quantity);

        $cart = $this->getOrCreateCart($customerId);

        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $productId,
            'quantity' => $quantity,
            'design_snapshot' => $designSnapshot,
            'preview_image_reference' => $previewImageReference,
            'date_added' => now(),
        ]);

        $cart->update(['last_updated' => now()]);
    }

    /**
     * Add a previously saved design to the cart with quantity 1.
     *
     * Verifies customer owns the design and the product is still active.
     *
     * @throws ValidationException if design unavailable or doesn't belong to customer
     */
    public function addSavedDesignToCart(int $customerId, int $designId): void
    {
        $design = SavedDesign::with('product')->findOrFail($designId);

        if ($design->customer_id !== $customerId) {
            throw ValidationException::withMessages([
                'design_id' => 'This design could not be found.',
            ]);
        }

        if (!$design->isProductAvailable()) {
            throw ValidationException::withMessages([
                'design_id' => 'The product for this design is no longer available for ordering.',
            ]);
        }

        $cart = $this->getOrCreateCart($customerId);

        CartItem::create([
            'cart_id' => $cart->cart_id,
            'product_id' => $design->product_id,
            'quantity' => 1,
            'design_snapshot' => $design->design_data,
            'preview_image_reference' => $design->preview_image_reference,
            'date_added' => now(),
        ]);

        $cart->update(['last_updated' => now()]);
    }

    /**
     * Update the quantity of an existing cart item.
     *
     * @throws ValidationException if item not in customer's cart or quantity invalid
     */
    public function updateQuantity(int $customerId, int $cartItemId, int $quantity): void
    {
        $this->validateQuantity($quantity);

        $item = $this->resolveCartItem($customerId, $cartItemId);

        $item->update(['quantity' => $quantity]);
        $item->cart->update(['last_updated' => now()]);
    }

    /**
     * Remove a single item from the customer's cart.
     *
     * @throws ValidationException if item not in customer's cart
     */
    public function removeFromCart(int $customerId, int $cartItemId): void
    {
        $item = $this->resolveCartItem($customerId, $cartItemId);

        $item->delete();
        $item->cart->update(['last_updated' => now()]);
    }

    /**
     * Remove all items from the customer's cart.
     * Called by CheckoutService after successful order submission.
     */
    public function clearCart(int $customerId): void
    {
        $cart = ShoppingCart::where('customer_id', $customerId)->first();

        if ($cart !== null) {
            $cart->items()->delete();
            $cart->update(['last_updated' => now()]);
        }
    }

    /**
     * Retrieve all cart items for the customer's active cart.
     * Returns empty collection if no cart exists.
     */
    public function getCartContents(int $customerId): \Illuminate\Support\Collection
    {
        $cart = ShoppingCart::where('customer_id', $customerId)->first();

        if ($cart === null) {
            return collect();
        }

        return $cart->items()->with('product')->get();
    }

    /**
     * Remove all cart items referencing a specific product.
     * Called when a product is deactivated.
     */
    public function removeProductFromAllCarts(int $productId): void
    {
        CartItem::where('product_id', $productId)->delete();
    }

    /**
     * Retrieve or create the cart for the given customer.
     * Enforces one-cart-per-customer constraint.
     */
    private function getOrCreateCart(int $customerId): ShoppingCart
    {
        return ShoppingCart::firstOrCreate(
            ['customer_id' => $customerId],
            ['last_updated' => now()],
        );
    }

    /**
     * Verify that the product exists and is active for ordering.
     *
     * @throws ValidationException if product unavailable
     */
    private function validateProduct(int $productId): void
    {
        $product = CustomizablePrintProduct::find($productId);

        if ($product === null || !$product->isActive()) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not available for ordering.',
            ]);
        }
    }

    /**
     * Verify that quantity is between 1 and 99.
     *
     * @throws ValidationException
     */
    private function validateQuantity(int $quantity): void
    {
        if ($quantity < 1 || $quantity > 99) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be between 1 and 99.',
            ]);
        }
    }

    /**
     * Resolve a cart item by ID and verify it belongs to the given customer.
     *
     * @throws ValidationException if item not found in customer's cart
     */
    private function resolveCartItem(int $customerId, int $cartItemId): CartItem
    {
        $item = CartItem::with('cart')
            ->where('cart_item_id', $cartItemId)
            ->whereHas('cart', fn($q) => $q->where('customer_id', $customerId))
            ->first();

        if ($item === null) {
            throw ValidationException::withMessages([
                'cart_item_id' => 'This cart item could not be found.',
            ]);
        }

        return $item;
    }
}