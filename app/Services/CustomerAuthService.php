<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Handles all authentication and account operations for customers.
 *
 * Covers registration, login, password reset, and profile management.
 * Session management is delegated to Laravel's 'customer' auth guard.
 */
class CustomerAuthService
{
    public function __construct(
        private readonly EmailService $emailService,
    ) {}

    /**
     * Registers a new customer account.
     *
     * Validates input, checks email uniqueness, hashes the password,
     * persists the record including the optional phone number, and
     * dispatches the Registered event so welcome-email side effects
     * are handled by listeners.
     *
     * @throws ValidationException on validation failure or duplicate email.
     */
    public function register(string $email, string $password, string $fullName, ?string $phoneNumber): Customer
    {
        $this->validateEmail($email);
        $this->validatePassword($password);
        $this->validateFullName($fullName);

        if ($phoneNumber !== null) {
            $this->validatePhone($phoneNumber);
        }

        if (Customer::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => 'An account with this email address already exists.',
            ]);
        }

        $customer = Customer::create([
            'email' => $email,
            'password' => Hash::make($password),
            'full_name' => $fullName,
            'phone_number' => $phoneNumber,
            'account_status' => AccountStatus::Active,
        ]);

        event(new Registered($customer));

        return $customer;
    }

    /**
     * Authenticates a customer.
     *
     * Returns the customer account if credentials are valid.
     * Uses a validation-style error for all login failures so the
     * login form receives a normal session error bag.
     *
     * @throws ValidationException on invalid credentials or inactive account.
     */
    public function login(string $email, string $password): Customer
    {
        $customer = Customer::where('email', $email)->first();

        if ($customer === null || ! Hash::check($password, $customer->password) || ! $customer->isActive()) {
            throw ValidationException::withMessages([
                'email' => 'The provided credentials are incorrect.',
            ]);
        }

        return $customer;
    }

    /**
     * Initiates the password reset flow.
     *
     * Generates a reset token, stores its hash with a 60-minute expiry,
     * and sends the plain token by email. If the email is not registered,
     * this method returns silently to prevent account enumeration.
     */
    public function requestPasswordReset(string $email): void
    {
        $this->validateEmail($email);

        $customer = Customer::where('email', $email)->first();

        if ($customer === null) {
            return;
        }

        $plainToken = Str::random(64);

        $customer->update([
            'reset_token' => Hash::make($plainToken),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);

        $this->emailService->sendPasswordResetEmail($customer->email, $plainToken);
    }

    /**
     * Completes the password reset process.
     *
     * Validates the token, updates the password, clears the reset token,
     * and logs out all other active sessions for the account.
     *
     * @throws ValidationException if the token is invalid, expired, or
     *                             the new password fails validation.
     */
    public function resetPassword(string $email, string $token, string $newPassword): void
    {
        $this->validatePassword($newPassword);

        $customer = Customer::where('email', $email)->first();

        if ($customer === null || ! $customer->isResetTokenValid($token)) {
            throw ValidationException::withMessages([
                'token' => 'This password reset link is invalid or has expired.',
            ]);
        }

        $customer->update([
            'password' => Hash::make($newPassword),
            'reset_token' => null,
            'reset_token_expiry' => null,
        ]);

        Auth::guard('customer')->logoutOtherDevices($newPassword);
    }

    /**
     * Updates the customer's profile information.
     *
     * Only full_name and phone_number can be changed through this method.
     * Phone number is optional but must be exactly 8 digits when provided.
     *
     * @throws ValidationException on validation failure.
     */
    public function updateProfile(int $customerId, string $fullName, ?string $phoneNumber): void
    {
        $this->validateFullName($fullName);

        if ($phoneNumber !== null) {
            $this->validatePhone($phoneNumber);
        }

        Customer::findOrFail($customerId)->update([
            'full_name' => $fullName,
            'phone_number' => $phoneNumber,
        ]);
    }

    /**
     * Terminates the current customer session.
     */
    public function logout(): void
    {
        Auth::guard('customer')->logout();
    }

    /**
     * Validates email format and length.
     *
     * @throws ValidationException
     */
    private function validateEmail(string $email): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
            throw ValidationException::withMessages([
                'email' => 'Please provide a valid email address of no more than 100 characters.',
            ]);
        }
    }

    /**
     * Validates password length (8-64 characters).
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

    /**
     * Validates full name length (2-50 characters).
     *
     * @throws ValidationException
     */
    private function validateFullName(string $fullName): void
    {
        $length = mb_strlen(trim($fullName));

        if ($length < 2 || $length > 50) {
            throw ValidationException::withMessages([
                'full_name' => 'Full name must be between 2 and 50 characters.',
            ]);
        }
    }

    /**
     * Validates phone number format (exactly 8 digits).
     *
     * @throws ValidationException
     */
    private function validatePhone(string $phone): void
    {
        if (! preg_match('/^\d{8}$/', $phone)) {
            throw ValidationException::withMessages([
                'phone_number' => 'Phone number must be exactly 8 numeric digits.',
            ]);
        }
    }
}