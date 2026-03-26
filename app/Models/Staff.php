<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Represents a staff account for an Employee or Administrator.
 *
 * Staff authenticate via username and password through the dedicated staff
 * auth guard. This is a completely separate model from Customer.
 *
 * Staff accounts are created and managed exclusively by administrators.
 * The system must always retain at least one active Administrator account.
 * This constraint is enforced at the application layer.
 *
 * @property int            $staff_id
 * @property string         $username
 * @property string         $password
 * @property StaffRole      $role
 * @property string|null    $full_name
 * @property AccountStatus  $account_status
 */
class Staff extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'staff';
    protected $primaryKey = 'staff_id';
    protected $keyType = 'int';
    public $incrementing = true;
    public $timestamps = true;

    protected $fillable = [
        'username',
        'password',
        'role',
        'full_name',
        'account_status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'role' => StaffRole::class,
        'account_status' => AccountStatus::class,
    ];

    /**
     * Orders currently assigned to this staff member.
     */
    public function assignedOrders(): HasMany
    {
        return $this->hasMany(CustomerOrder::class, 'assigned_staff_id', 'staff_id');
    }

    /**
     * Internal notes written by this staff member.
     */
    public function orderNotes(): HasMany
    {
        return $this->hasMany(OrderNote::class, 'staff_id', 'staff_id');
    }

    /**
     * Returns the primary key column name for authentication.
     */
    public function getAuthIdentifierName(): string
    {
        return 'staff_id';
    }

    /**
     * Returns true if this account can log in.
     */
    public function isActive(): bool
    {
        return $this->account_status === AccountStatus::Active;
    }

    /**
     * Returns true if this staff member has administrator privileges.
     */
    public function isAdministrator(): bool
    {
        return $this->role === StaffRole::Administrator;
    }
}