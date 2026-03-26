<?php

namespace App\Enums;

/**
 * Defines the role assigned to a staff account.
 *
 * Employees can manage orders, products, and carousel slides.
 * Administrators have full access including staff management,
 * pricing configuration, and sales analytics.
 */
enum StaffRole: string
{
    case Employee = 'Employee';
    case Administrator = 'Administrator';

    /**
     * Returns true if the role has administrator privileges.
     */
    public function isAdministrator(): bool
    {
        return $this === self::Administrator;
    }
}