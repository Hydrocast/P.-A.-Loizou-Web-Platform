<?php

namespace App\Models;

use App\Enums\ProductVisibilityStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Represents a browse-only retail product in the catalog.
 *
 * Standard products are displayed for customer reference only. They cannot
 * be added to the shopping cart or ordered through the platform.
 * display_price is a VAT-inclusive reference price shown in the catalog
 * and is not used in any checkout calculation.
 *
 * Standard products may optionally belong to one ProductCategory. If the
 * assigned category is deleted, category_id is set to NULL by the database,
 * leaving the product uncategorised.
 *
 * Products are never deleted from the database. Deactivation via
 * visibility_status is the only mechanism for removing a product from
 * customer view, preserving historical order references.
 *
 * @property int              $product_id
 * @property string           $product_name
 * @property string|null      $description
 * @property string|null      $image_reference
 * @property ProductVisibilityStatus $visibility_status
 * @property int|null         $category_id
 * @property float            $display_price
 */
class StandardProduct extends Model
{
    use HasFactory;

    protected $table = 'standard_products';
    protected $primaryKey = 'product_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'product_name',
        'description',
        'image_reference',
        'visibility_status',
        'category_id',
        'display_price',
    ];

    protected $casts = [
        'visibility_status' => ProductVisibilityStatus::class,
        'display_price' => 'decimal:2',
    ];

    /**
     * The category this product belongs to.
     * Returns null if the product is uncategorised.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id', 'category_id');
    }

    /**
     * Returns true if this product is visible to customers in the catalog.
     */
    public function isActive(): bool
    {
        return $this->visibility_status === ProductVisibilityStatus::Active;
    }
}