<?php

namespace App\Policies;

use App\Models\Staff;

/**
 * Authorises staff account management operations.
 *
 * All staff account operations are restricted to administrators.
 * This policy checks only the role; deeper business rules (self-deactivation,
 * last-admin constraint) are enforced in StaffManagementService.
 *
 * The $requester parameter is the authenticated staff member.
 * Controllers must pass auth('staff')->user() explicitly.
 */
class StaffAccountPolicy
{
    /**
     * Allow administrators to view the staff list.
     */
    public function viewAny(Staff $requester): bool
    {
        return $requester->isAdministrator();
    }

    /**
     * Allow administrators to view a specific staff account.
     */
    public function view(Staff $requester, Staff $target): bool
    {
        return $requester->isAdministrator();
    }

    /**
     * Allow administrators to create a new staff account.
     */
    public function create(Staff $requester): bool
    {
        return $requester->isAdministrator();
    }

    /**
     * Allow administrators to update a staff account's editable fields.
     */
    public function update(Staff $requester, Staff $target): bool
    {
        return $requester->isAdministrator();
    }

    /**
     * Allow administrators to change a staff account's active/inactive status.
     *
     * This authorises the attempt. The self-deactivation and last-admin
    * constraints are enforced in StaffManagementService.
     */
    public function updateStatus(Staff $requester, Staff $target): bool
    {
        return $requester->isAdministrator();
    }

    /**
     * Allow administrators to reset another staff member's password.
     */
    public function resetPassword(Staff $requester, Staff $target): bool
    {
        return $requester->isAdministrator();
    }
}