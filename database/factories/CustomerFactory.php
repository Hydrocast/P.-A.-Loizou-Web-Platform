<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Customer>
 *
 * Creates customer records with valid default values.
 * Provides states for account status (inactive, pending reset, expired reset)
 * and for boundary testing of full_name and phone_number.
 * Boundary values: full_name (2‑50), phone_number (8 digits).
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email'              => $this->faker->unique()->safeEmail(),
            'password'           => Hash::make('password'),
            'full_name'          => $this->faker->name(),
            'phone_number'       => $this->faker->numerify('########'),
            'account_status'     => AccountStatus::Active,
            'reset_token'        => null,
            'reset_token_expiry' => null,
        ];
    }

    // -------------------------------------------------------------------------
    // Account status states
    // -------------------------------------------------------------------------

    /** Set account status to Inactive. */
    public function inactive(): static
    {
        return $this->state(fn () => ['account_status' => AccountStatus::Inactive]);
    }

    /** Add a valid, non‑expired password reset token. */
    public function withPendingReset(): static
    {
        return $this->state(fn () => [
            'reset_token'        => Hash::make('reset-token-value'),
            'reset_token_expiry' => now()->addMinutes(60),
        ]);
    }

    /** Add an expired password reset token. */
    public function withExpiredReset(): static
    {
        return $this->state(fn () => [
            'reset_token'        => Hash::make('expired-token-value'),
            'reset_token_expiry' => now()->subMinutes(1),
        ]);
    }

    // -------------------------------------------------------------------------
    // Full name boundaries
    // -------------------------------------------------------------------------

    /** Full name of 1 character (below minimum) is invalid. */
    public function fullNameTooShort(): static
    {
        return $this->state(fn () => ['full_name' => 'A']);
    }

    /** Full name of 2 characters (minimum) is valid. */
    public function fullNameMinLength(): static
    {
        return $this->state(fn () => ['full_name' => 'Ab']);
    }

    /** Full name of 26 characters (in‑range) is valid. */
    public function fullNameMidLength(): static
    {
        return $this->state(fn () => ['full_name' => str_repeat('a', 26)]);
    }

    /** Full name of 50 characters (maximum) is valid. */
    public function fullNameMaxLength(): static
    {
        return $this->state(fn () => ['full_name' => str_repeat('a', 50)]);
    }

    /** Full name of 51 characters (above maximum) is invalid. */
    public function fullNameTooLong(): static
    {
        return $this->state(fn () => ['full_name' => str_repeat('a', 51)]);
    }

    // -------------------------------------------------------------------------
    // Phone number boundaries
    // -------------------------------------------------------------------------

    /** Phone number of 7 digits (below minimum) is invalid. */
    public function phoneTooShort(): static
    {
        return $this->state(fn () => ['phone_number' => '1234567']);
    }

    /** Phone number of exactly 8 digits (minimum) is valid. */
    public function phoneExactLength(): static
    {
        return $this->state(fn () => ['phone_number' => $this->faker->numerify('########')]);
    }

    /** Phone number of 9 digits (above maximum) is invalid. */
    public function phoneTooLong(): static
    {
        return $this->state(fn () => ['phone_number' => '123456789']);
    }

    /** Remove the phone number (set to null). */
    public function withoutPhone(): static
    {
        return $this->state(fn () => ['phone_number' => null]);
    }
}