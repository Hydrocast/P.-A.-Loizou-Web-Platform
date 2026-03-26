<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents a submitted customer order.
 *
 * All contact and pricing data is frozen at the moment of submission and
 * must never be recalculated or modified. Customer contact details are
 * copied from checkout, not referenced from the customer table, ensuring
 * the order remains accurate if the customer later updates their profile.
 *
 * All monetary values include VAT. vat_rate records the rate active at
 * order time. Orders are retained indefinitely for audit purposes.
 *
 * @property int         $order_id
 * @property int         $customer_id
 * @property string      $customer_name
 * @property string      $customer_email
 * @property string      $customer_phone
 * @property string      $order_creation_timestamp
 * @property OrderStatus $order_status
 * @property float       $net_amount
 * @property float       $vat_amount
 * @property float       $total_amount
 * @property float       $vat_rate
 * @property int|null    $assigned_staff_id
 * @property string|null $staff_assignment_date
 * @property string|null $pickup_notification_sent_at
 * @property int|null    $pickup_notification_sent_by_staff_id
 */
class CustomerOrder extends Model
{
    use HasFactory;

    protected $table = 'customer_orders';
    protected $primaryKey = 'order_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'order_creation_timestamp',
        'order_status',
        'net_amount',
        'vat_amount',
        'total_amount',
        'vat_rate',
        'assigned_staff_id',
        'staff_assignment_date',
        'pickup_notification_sent_at',
        'pickup_notification_sent_by_staff_id',
    ];

    protected $casts = [
        'order_status' => OrderStatus::class,
        'order_creation_timestamp' => 'datetime',
        'staff_assignment_date' => 'datetime',
        'pickup_notification_sent_at' => 'datetime',
        'net_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'vat_rate' => 'decimal:2',
    ];

    /**
     * The customer account that placed this order.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /**
     * The staff member currently assigned to this order.
     * May be null if unassigned.
     */
    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'assigned_staff_id', 'staff_id');
    }

    /**
     * The items contained in this order.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    /**
     * Internal staff notes associated with this order.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(OrderNote::class, 'order_id', 'order_id')
                    ->orderBy('note_timestamp');
    }

    /**
     * Returns true if the order is in a terminal state (Completed or Cancelled).
     * Terminal orders are excluded from the default staff order list.
     */
    public function isTerminal(): bool
    {
        return $this->order_status->isTerminal();
    }
}