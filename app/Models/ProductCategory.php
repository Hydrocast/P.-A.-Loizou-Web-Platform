<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Represents a logical grouping for standard products in the catalog.
 *
 * Categories apply only to standard products. Customizable print products
 * are never assigned a category.
 *
 * A category cannot be deleted while it contains active products.
 * This constraint is enforced at the application layer. When a category is
 * deleted, the database sets category_id to NULL on all associated products,
 * leaving them uncategorised.
 *
 * @property int         $category_id
 * @property string      $category_name
 * @property string|null $description
 */
class ProductCategory extends Model
{
    use HasFactory;

    protected $table = 'product_categories';
    protected $primaryKey = 'category_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'category_name',
        'description',
    ];

    /**
     * All standard products assigned to this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(StandardProduct::class, 'category_id', 'category_id');
    }

    /**
     * Returns true if this category contains at least one active product.
     * Used to enforce the deletion constraint.
     */
    public function containsActiveProducts(): bool
    {
        return $this->products()
                    ->where('visibility_status', 'Active')
                    ->exists();
    }
}