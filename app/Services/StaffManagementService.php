<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Handles administrator-only operations on staff accounts.
 *
 * Enforces two business rules:
 *   1. An administrator cannot deactivate their own account.
 *   2. At least one active Administrator must exist at all times.
 *
 * The acting administrator's ID is passed into write operations so that
 * self-modification can be detected.
 */
class StaffManagementService
{
    /**
     * Creates a new staff account.
     * The account is immediately active and usable upon creation.
     *
     * @throws ValidationException if username taken or password invalid.
     */
    public function createStaffAccount(
        string $username,
        string $password,
        StaffRole $role,
        ?string $fullName,
    ): Staff {
        $this->validatePassword($password);
        $this->validateUsernameUnique($username);

        $staff = Staff::create([
            'username' => $username,
            'password' => Hash::make($password),
            'role' => $role,
            'full_name' => $fullName,
            'account_status' => AccountStatus::Active,
        ]);

        Log::info('Staff account created.', [
            'staff_id' => $staff->staff_id,
            'username' => $staff->username,
            'role' => $staff->role->value,
            'account_status' => $staff->account_status->value,
        ]);

        return $staff;
    }

    /**
     * Updates editable fields on a staff account.
     *
     * Editable fields are full_name and an optional new password.
     * Pass null for newPassword to leave the existing password unchanged.
     *
     * @throws ValidationException if new password fails validation.
     */
    public function updateStaffAccount(
        int $staffId,
        ?string $fullName,
        ?string $newPassword
    ): void {
        $staff = Staff::findOrFail($staffId);

        $updates = [
            'full_name' => $fullName,
        ];

        $passwordChanged = false;

        if ($newPassword !== null && $newPassword !== '') {
            $this->validatePassword($newPassword);
            $updates['password'] = Hash::make($newPassword);
            $passwordChanged = true;
        }

        $staff->update($updates);

        Log::info('Staff account updated.', [
            'staff_id' => $staff->staff_id,
            'username' => $staff->username,
            'full_name_updated' => true,
            'password_changed' => $passwordChanged,
        ]);
    }

    /**
     * Changes the account_status of a staff member.
     *
     * Enforces business rule constraints before applying the change.
     *
     * @param int $actingAdminId ID of administrator making the change
     * @param int $staffId ID of staff account to update
     * @param AccountStatus $newStatus Status to apply
     *
     * @throws ValidationException if a business rule would be violated.
     */
    public function updateStaffStatus(int $actingAdminId, int $staffId, AccountStatus $newStatus): void
    {
        $staff = Staff::findOrFail($staffId);
        $oldStatus = $staff->account_status;

        if ($newStatus === AccountStatus::Inactive) {
            $this->enforceDeactivationRules($actingAdminId, $staff);
        }

        $staff->update(['account_status' => $newStatus]);

        Log::warning('Staff account status changed.', [
            'acting_admin_id' => $actingAdminId,
            'target_staff_id' => $staff->staff_id,
            'username' => $staff->username,
            'old_status' => $oldStatus->value,
            'new_status' => $newStatus->value,
        ]);
    }

    /**
     * Returns all staff accounts ordered by username.
     */
    public function getAllStaff(): Collection
    {
        return Staff::orderBy('username')->get();
    }

    /**
     * Enforces both deactivation constraints before a status change.
     *
     * @throws ValidationException if either constraint would be violated.
     */
    private function enforceDeactivationRules(int $actingAdminId, Staff $target): void
    {
        if ($target->staff_id === $actingAdminId) {
            throw ValidationException::withMessages([
                'account_status' => 'You cannot deactivate your own account.',
            ]);
        }

        if ($target->isAdministrator() && !$this->atLeastOneAdminRemains($target->staff_id)) {
            throw ValidationException::withMessages([
                'account_status' => 'At least one active Administrator account must remain in the system.',
            ]);
        }
    }

    /**
     * Returns true if at least one active Administrator other than the
     * given staff_id exists.
     */
    private function atLeastOneAdminRemains(int $excludingStaffId): bool
    {
        return Staff::where('role', StaffRole::Administrator)
            ->where('account_status', AccountStatus::Active)
            ->where('staff_id', '!=', $excludingStaffId)
            ->exists();
    }

    /**
     * Validates that username is not already taken.
     *
     * @throws ValidationException
     */
    private function validateUsernameUnique(string $username): void
    {
        if (Staff::where('username', $username)->exists()) {
            throw ValidationException::withMessages([
                'username' => 'This username is already taken.',
            ]);
        }
    }

    /**
     * Validates password meets length requirements (8-64 characters).
     *
     * @throws ValidationException
     */
    private function validatePassword(string $password): void
    {
        $length = strlen($password);

        if ($length < 8 || $length > 64) {
            throw ValidationException::withMessages([
                'password' => 'Password must be between 8 and 64 characters.',
            ]);
        }
    }
}