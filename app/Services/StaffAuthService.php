<?php

namespace App\Services;

use App\Models\Staff;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Handles authentication for staff members (Employees and Administrators).
 *
 * Staff authenticate by username via the dedicated 'staff' guard.
 * There is no self-registration for staff — accounts are created
 * exclusively by administrators.
 *
 * This service handles only authentication logic. Session management
 * is the responsibility of the calling controller.
 */
class StaffAuthService
{
    /**
     * Authenticates a staff member by username and password.
     *
     * Returns the Staff model on success. The caller must create the
     * authenticated session using Auth::guard('staff')->login().
     *
     * A single generic error is returned for both unknown username and
     * wrong password to prevent account enumeration. Inactive accounts
     * throw a distinct exception as required by the SRS.
     *
     * @throws ValidationException if credentials are invalid.
     * @throws AuthenticationException if the account is inactive.
     */
    public function login(string $username, string $password): Staff
    {
        $staff = Staff::where('username', $username)->first();

        if ($staff === null || !Hash::check($password, $staff->password)) {
            throw ValidationException::withMessages([
                'username' => 'The provided credentials are incorrect.',
            ]);
        }

        if (!$staff->isActive()) {
            throw new AuthenticationException('This account is inactive.');
        }

        return $staff;
    }

    /**
     * Terminates the current staff session.
     */
    public function logout(): void
    {
        Auth::guard('staff')->logout();
    }
}