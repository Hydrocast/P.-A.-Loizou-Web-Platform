<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a customer-saved design snapshot.
 *
 * A saved design is an immutable record. Once created, design_data cannot
 * be modified. This immutability is enforced at the application layer.
 *
 * design_data holds the complete canvas state as a JSON string. It is
 * restored into the design workspace when the customer loads a saved design.
 *
 * preview_image_reference stores the cloud URL of a PNG thumbnail displayed
 * in the My Designs grid and copied to cart items when added to cart.
 *
 * Saved designs are retained for the lifetime of the customer account and
 * are removed when the account is deleted.
 *
 * @property int         $design_id
 * @property string      $design_name
 * @property int         $customer_id
 * @property int         $product_id
 * @property string      $design_data
 * @property string|null $preview_image_reference
 * @property string      $date_created
 */
class SavedDesign extends Model
{
    use HasFactory;
    
    protected $table = 'saved_designs';
    protected $primaryKey = 'design_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'design_name',
        'customer_id',
        'product_id',
        'design_data',
        'preview_image_reference',
        'date_created',
    ];

    protected $casts = [
        'date_created' => 'datetime',
    ];

    /**
     * The customer who created this saved design.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * The customizable product this design is based on.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(CustomizablePrintProduct::class, 'product_id', 'product_id');
    }

    /**
     * Returns true if this design belongs to the given customer.
     * Used in policies to prevent customers accessing others' designs.
     */
    public function belongsToCustomer(int $customerId): bool
    {
        return $this->customer_id === $customerId;
    }

    /**
     * Returns true if the referenced product is still active.
     * Required before loading a design into the workspace.
     */
    public function isProductAvailable(): bool
    {
        return $this->product !== null && $this->product->isActive();
    }
}