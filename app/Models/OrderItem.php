<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a single item within a submitted order.
 *
 * All pricing and design data is frozen at the time of order submission
 * and must not be modified thereafter.
 *
 * product_name is copied from the product record at submission so the
 * order item remains accurate even if the product is later renamed.
 *
 * unit_price and line_subtotal are transferred directly from checkout
 * and match the values the customer reviewed before submitting.
 *
 * design_snapshot is an immutable copy of the design JSON transferred
 * from the cart item. It is not a reference to a saved design.
 *
 * preview_image_reference is used for order detail thumbnails and
 * quick visual staff reference.
 *
 * print_file_reference stores the dedicated print-ready image reference
 * transferred from the cart item for staff production use.
 *
 * @property int $order_item_id
 * @property int $order_id
 * @property int $product_id
 * @property string $product_name
 * @property float $unit_price
 * @property int $quantity
 * @property float $line_subtotal
 * @property string $design_snapshot
 * @property string|null $preview_image_reference
 * @property string|null $print_file_reference
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $primaryKey = 'order_item_id';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'unit_price',
        'quantity',
        'line_subtotal',
        'design_snapshot',
        'preview_image_reference',
        'print_file_reference',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_subtotal' => 'decimal:2',
    ];

    /**
     * The order this item belongs to.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    /**
     * The customizable product referenced by this order item.
     * Retained for historical reference even if the product is deactivated.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CustomizablePrintProduct::class, 'product_id', 'product_id');
    }
}
