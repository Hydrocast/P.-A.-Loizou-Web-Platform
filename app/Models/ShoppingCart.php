<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a customer's persistent shopping cart.
 *
 * Each customer has at most one cart, enforced by the unique constraint
 * on customer_id. The cart persists across sessions.
 *
 * Price calculations are not performed on the cart. Pricing is calculated
 * exclusively during checkout review.
 *
 * @property int    $cart_id
 * @property int    $customer_id
 * @property string $last_updated
 */
class ShoppingCart extends Model
{
    use HasFactory;
    
    protected $table = 'shopping_carts';
    protected $primaryKey = 'cart_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'last_updated',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];

    /**
     * The customer who owns this cart.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * The items currently in this cart.
     */
    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class, 'cart_id', 'cart_id');
    }

    /**
     * Returns true if the cart contains no items.
     */
    public function isEmpty(): bool
    {
        return !$this->items()->exists();
    }
}