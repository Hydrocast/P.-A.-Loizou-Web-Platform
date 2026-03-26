<?php

namespace App\Enums;

/**
 * Represents the active status of a customer or staff account.
 *
 * Used on both the customers table and the staff table.
 *
 * For customers, Inactive blocks login.
 * For staff, Inactive blocks login and removes the account from the
 * assignable staff list in the order management console. An administrator
 * cannot deactivate their own account, and the system must always retain
 * at least one active Administrator.
 */
enum AccountStatus: string
{
    case Active = 'Active';
    case Inactive = 'Inactive';

    /**
     * Returns true if the account is permitted to authenticate.
     */
    public function isActive(): bool
    {
        return $this === self::Active;
    }
}