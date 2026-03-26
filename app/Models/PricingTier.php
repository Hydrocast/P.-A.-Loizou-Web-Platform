<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a single quantity-based pricing rule for a customizable product.
 *
 * All unit prices are VAT-inclusive. During checkout, the correct tier is
 * resolved by finding the tier where the cart item quantity falls within
 * [minimum_quantity, maximum_quantity].
 *
 * Tier structure constraints — no gaps, no overlaps, first tier starts at
 * quantity 1, maximum five tiers per product — are validated at the
 * application layer when tiers are configured.
 *
 * @property int   $tier_id
 * @property int   $product_id
 * @property int   $minimum_quantity
 * @property int   $maximum_quantity
 * @property float $unit_price
 */
class PricingTier extends Model
{
    use HasFactory;
    
    protected $table = 'pricing_tiers';
    protected $primaryKey = 'tier_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'minimum_quantity',
        'maximum_quantity',
        'unit_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
    ];

    /**
     * The customizable product this tier prices.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CustomizablePrintProduct::class, 'product_id', 'product_id');
    }

    /**
     * Returns true if this tier applies to the given quantity.
     * Used during checkout to resolve the correct tier.
     */
    public function appliesTo(int $quantity): bool
    {
        return $quantity >= $this->minimum_quantity
            && $quantity <= $this->maximum_quantity;
    }
}