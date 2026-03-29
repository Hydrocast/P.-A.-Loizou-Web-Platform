<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single item within a customer's shopping cart.
 *
 * The design_snapshot is an immutable FabricJS JSON string captured when the
 * item was added. It cannot be modified after creation. To change a design,
 * the customer must create a new customisation and add it as a new cart item.
 *
 * The preview_image_reference stores the cloud URL of a PNG thumbnail generated
 * client‑side at the time of addition. It is used for the cart item thumbnail
 * and later transferred to the order item for staff print reference.
 *
 * @property int $cart_item_id
 * @property int $cart_id
 * @property int $product_id
 * @property int $quantity
 * @property string $design_snapshot
 * @property string|null $preview_image_reference
 * @property string $date_added
 */
class CartItem extends Model
{
    use HasFactory;

    protected $primaryKey = 'cart_item_id';

    public $timestamps = false;

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'design_snapshot',
        'preview_image_reference',
        'print_file_reference',
        'date_added',
    ];

    protected $casts = [
        'date_added' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * The cart this item belongs to.
     */
    public function cart(): BelongsTo
    {
        return $this->belongsTo(ShoppingCart::class, 'cart_id', 'cart_id');
    }

    /**
     * The customizable product referenced by this cart item.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CustomizablePrintProduct::class, 'product_id', 'product_id');
    }
}
