<?php

namespace Database\Factories;

use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<Staff>
 *
 * Creates staff records with valid default values.
 * Provides states for role, status, and boundary values for username and full name.
 * Boundary values: username (1‑100), full_name (1‑100).
 *
 * Password validation is tested by passing raw strings directly to service methods.
 */
class StaffFactory extends Factory
{
    protected $model = Staff::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'username'       => $this->faker->unique()->userName(),
            'password'       => Hash::make('password'),
            'role'           => StaffRole::Employee,
            'full_name'      => $this->faker->name(),
            'account_status' => AccountStatus::Active,
        ];
    }

    // -------------------------------------------------------------------------
    // Role and status states
    // -------------------------------------------------------------------------

    /** Set role to Administrator. */
    public function administrator(): static
    {
        return $this->state(fn () => ['role' => StaffRole::Administrator]);
    }

    /** Set account status to Inactive. */
    public function inactive(): static
    {
        return $this->state(fn () => ['account_status' => AccountStatus::Inactive]);
    }

    /** Set full name to null. */
    public function withoutFullName(): static
    {
        return $this->state(fn () => ['full_name' => null]);
    }

    // -------------------------------------------------------------------------
    // Username boundaries
    // -------------------------------------------------------------------------

    /** Username of 0 characters (below minimum) is invalid. */
    public function usernameEmpty(): static
    {
        return $this->state(fn () => ['username' => '']);
    }

    /** Username of 1 character (minimum) is valid. */
    public function usernameMinLength(): static
    {
        return $this->state(fn () => ['username' => 'a']);
    }

    /** Username of 50 characters (in‑range) is valid. */
    public function usernameMidLength(): static
    {
        return $this->state(fn () => ['username' => str_repeat('a', 50)]);
    }

    /** Username of 100 characters (maximum) is valid. */
    public function usernameMaxLength(): static
    {
        return $this->state(fn () => ['username' => str_repeat('a', 100)]);
    }

    /** Username of 101 characters (above maximum) is invalid. */
    public function usernameTooLong(): static
    {
        return $this->state(fn () => ['username' => str_repeat('a', 101)]);
    }

    // -------------------------------------------------------------------------
    // Full name boundaries
    // -------------------------------------------------------------------------

    /** Full name of 1 character (minimum) is valid. */
    public function fullNameMinLength(): static
    {
        return $this->state(fn () => ['full_name' => 'A']);
    }

    /** Full name of 100 characters (maximum) is valid. */
    public function fullNameMaxLength(): static
    {
        return $this->state(fn () => ['full_name' => str_repeat('a', 100)]);
    }

    /** Full name of 101 characters (above maximum) is invalid. */
    public function fullNameTooLong(): static
    {
        return $this->state(fn () => ['full_name' => str_repeat('a', 101)]);
    }
}