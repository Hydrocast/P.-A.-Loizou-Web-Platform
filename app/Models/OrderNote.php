<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Represents an internal staff note associated with an order.
 *
 * Notes are write-once records used for operational communication and
 * internal audit purposes. Once created, a note is not edited or deleted
 * independently of its order.
 *
 * @property int    $note_id
 * @property int    $order_id
 * @property string $note_text
 * @property int    $staff_id
 * @property string $note_timestamp
 */
class OrderNote extends Model
{
    use HasFactory;
    
    protected $table = 'order_notes';
    protected $primaryKey = 'note_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'staff_id',
        'note_text',
        'note_timestamp',
    ];

    protected $casts = [
        'note_timestamp' => 'datetime',
    ];

    /**
     * The order this note is associated with.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(CustomerOrder::class, 'order_id', 'order_id');
    }

    /**
     * The staff member who wrote this note.
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id', 'staff_id');
    }
}