<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a saved association between a customer and a product.
 *
 * Both standard and customizable products may be added to a wishlist.
 * Because these product types are stored in separate tables, product_id alone
 * cannot identify the referenced product. The product_type column indicates
 * which table product_id refers to.
 *
 * Duplicate entries for the same customer and product are prevented by a
 * unique constraint on (customer_id, product_id, product_type).
 *
 * @property int         $wishlist_item_id
 * @property int         $customer_id
 * @property int         $product_id
 * @property ProductType $product_type
 * @property string      $date_added
 */
class WishlistItem extends Model
{
    use HasFactory;
    
    protected $table = 'wishlist_items';
    protected $primaryKey = 'wishlist_item_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'product_id',
        'product_type',
        'date_added',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
        'date_added' => 'datetime',
    ];

    /**
     * The customer who saved this wishlist item.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * Returns the resolved product model for this wishlist item.
     * Routes to the correct model based on product_type.
     * Returns null if the product no longer exists.
     */
    public function product(): StandardProduct|CustomizablePrintProduct|null
    {
        return match($this->product_type) {
            ProductType::Standard => StandardProduct::find($this->product_id),
            ProductType::Customizable => CustomizablePrintProduct::find($this->product_id),
        };
    }

    /**
     * Returns true if the referenced product still exists and is active.
     * Used when rendering the wishlist to filter out unavailable items.
     */
    public function isProductAvailable(): bool
    {
        $product = $this->product();
        return $product !== null && $product->isActive();
    }
}