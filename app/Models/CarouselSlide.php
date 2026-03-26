<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a single promotional slide in the homepage carousel.
 *
 * A slide may optionally link to a product. If linked, the product_type
 * indicates whether it links to a standard product or a customizable product.
 *
 * image_reference stores an optional custom slide image. When null, the slide
 * may fall back to the linked product image if the linked product has one.
 *
 * @property int              $slide_id
 * @property string           $title
 * @property string|null      $description
 * @property string|null      $image_reference
 * @property int|null         $product_id
 * @property ProductType|null $product_type
 * @property int              $display_sequence
 */
class CarouselSlide extends Model
{
    use HasFactory;

    protected $table = 'carousel_slides';
    protected $primaryKey = 'slide_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'title',
        'description',
        'image_reference',
        'product_id',
        'product_type',
        'display_sequence',
    ];

    protected $casts = [
        'product_type' => ProductType::class,
    ];

    /**
     * Returns the product linked to this slide, or null if none exists.
     */
    public function linkedProduct(): StandardProduct|CustomizablePrintProduct|null
    {
        if ($this->product_id === null || $this->product_type === null) {
            return null;
        }

        return match($this->product_type) {
            ProductType::Standard => StandardProduct::find($this->product_id),
            ProductType::Customizable => CustomizablePrintProduct::find($this->product_id),
        };
    }

    /**
     * Returns true if this slide is linked to a product.
     */
    public function hasProductLink(): bool
    {
        return $this->product_id !== null;
    }
}