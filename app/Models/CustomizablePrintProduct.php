<?php

namespace App\Models;

use App\Enums\ProductVisibilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a product that customers can customise before ordering.
 *
 * Customizable print products do not have a display price or category.
 * Prices are set through quantity-based pricing tiers,
 * and these products cannot be assigned to any category.
 *
 * Shared customizable-product behavior is resolved through design_profile_key.
 * This links the product to a reusable design profile that defines things such as:
 * - the designer workspace configuration
 * - available shirt/mockup colors
 * - default color selection
 * - other shared profile-based display settings
 *
 * Product-specific catalog data such as name, description, image, and visibility
 * remain stored directly on this model.
 *
 * Products are never deleted from the database. Deactivation is the
 * only way to remove a product from customer view.
 *
 * @property int                     $product_id
 * @property string                  $product_name
 * @property string|null             $description
 * @property string|null             $image_reference
 * @property ProductVisibilityStatus $visibility_status
 * @property string|null             $design_profile_key
 */
class CustomizablePrintProduct extends Model
{
    use HasFactory;

    protected $table      = 'customizable_print_products';
    protected $primaryKey = 'product_id';
    public    $timestamps = false;

    protected $fillable = [
        'product_name',
        'description',
        'image_reference',
        'visibility_status',
        'design_profile_key',
    ];

    protected $casts = [
        'visibility_status' => ProductVisibilityStatus::class,
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The quantity-based pricing tiers for this product.
     * A product can have up to five tiers.
     * Ordered by minimum quantity to ensure consistent tier selection at checkout.
     */
    public function pricingTiers(): HasMany
    {
        return $this->hasMany(PricingTier::class, 'product_id', 'product_id')
                    ->orderBy('minimum_quantity');
    }

    /**
     * Saved designs created from this product.
     */
    public function savedDesigns(): HasMany
    {
        return $this->hasMany(SavedDesign::class, 'product_id', 'product_id');
    }

    /**
     * Cart items that contain this product.
     */
    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class, 'product_id', 'product_id');
    }

    /**
     * Order items that reference this product.
     * These records are kept permanently for order history.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    // -------------------------------------------------------------------------
    // Business logic helpers
    // -------------------------------------------------------------------------

    /**
     * Checks if this product is visible and available for ordering.
     */
    public function isActive(): bool
    {
        return $this->visibility_status === ProductVisibilityStatus::Active;
    }

    /**
     * Finds the pricing tier that applies to the given quantity.
     * Returns null if no tier covers that quantity.
     * Used by PricingService during checkout.
     */
    public function tierForQuantity(int $quantity): ?PricingTier
    {
        return $this->pricingTiers
                    ->first(fn (PricingTier $tier) => $tier->appliesTo($quantity));
    }
}