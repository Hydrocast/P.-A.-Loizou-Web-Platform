<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Represents a registered customer account.
 *
 * Customers authenticate using email and password. Account status determines
 * whether login is permitted. Reset tokens are stored in the database because
 * password reset links are accessed without an active session.
 *
 * @property int            $customer_id
 * @property string         $email
 * @property string         $password
 * @property string         $full_name
 * @property string|null    $phone_number
 * @property AccountStatus  $account_status
 * @property string|null    $reset_token
 * @property string|null    $reset_token_expiry
 */
class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'email',
        'password',
        'full_name',
        'phone_number',
        'account_status',
        'reset_token',
        'reset_token_expiry',
    ];

    protected $hidden = [
        'password',
        'reset_token',
        'reset_token_expiry',
    ];

    protected $casts = [
        'account_status' => AccountStatus::class,
        'reset_token_expiry' => 'datetime',
    ];

    /**
     * The customer's shopping cart. Each customer has at most one cart.
     */
    public function cart(): HasOne
    {
        return $this->hasOne(ShoppingCart::class, 'customer_id', 'customer_id');
    }

    /**
     * The customer's wishlist items.
     */
    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class, 'customer_id', 'customer_id');
    }

    /**
     * The customer's saved designs.
     */
    public function savedDesigns(): HasMany
    {
        return $this->hasMany(SavedDesign::class, 'customer_id', 'customer_id');
    }

    /**
     * The customer's submitted orders.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class, 'customer_id', 'customer_id');
    }

    /**
     * Returns the primary key column name for authentication.
     */
    public function getAuthIdentifierName(): string
    {
        return 'customer_id';
    }

    /**
     * Returns true if this account can log in.
     */
    public function isActive(): bool
    {
        return $this->account_status === AccountStatus::Active;
    }

    /**
     * Returns true if the provided reset token is valid and not expired.
     * Tokens expire after 60 minutes and are single-use.
     */
    public function isResetTokenValid(string $token): bool
    {
        return $this->reset_token !== null
            && \Illuminate\Support\Facades\Hash::check($token, $this->reset_token)
            && $this->reset_token_expiry !== null
            && now()->lessThanOrEqualTo($this->reset_token_expiry);
    }
}