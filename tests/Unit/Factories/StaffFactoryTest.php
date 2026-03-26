<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for StaffFactory.
 *
 * Covers the default state, role and status states, and boundary states for
 * username (1‑100), and full_name (1‑100, nullable).
 */
class StaffFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a Staff record. */
    public function default_state_creates_staff_record(): void
    {
        $staff = Staff::factory()->create();

        $this->assertInstanceOf(Staff::class, $staff);
        $this->assertDatabaseHas('staff', ['staff_id' => $staff->staff_id]);
    }

    #[Test]
    /** Default state sets role to Employee. */
    public function default_state_sets_role_to_employee(): void
    {
        $staff = Staff::factory()->create();

        $this->assertInstanceOf(StaffRole::class, $staff->role);
        $this->assertSame(StaffRole::Employee, $staff->role);
    }

    #[Test]
    /** Default state sets account_status to Active. */
    public function default_state_sets_account_status_to_active(): void
    {
        $staff = Staff::factory()->create();

        $this->assertInstanceOf(AccountStatus::class, $staff->account_status);
        $this->assertSame(AccountStatus::Active, $staff->account_status);
    }

    // -------------------------------------------------------------------------
    // Role and status states
    // -------------------------------------------------------------------------

    #[Test]
    /** administrator state sets role to Administrator. */
    public function administrator_state_sets_role_to_administrator(): void
    {
        $staff = Staff::factory()->administrator()->create();

        $this->assertSame(StaffRole::Administrator, $staff->role);
        $this->assertDatabaseHas('staff', [
            'staff_id' => $staff->staff_id,
            'role'     => StaffRole::Administrator->value,
        ]);
    }

    #[Test]
    /** inactive state sets account_status to Inactive. */
    public function inactive_state_sets_account_status_to_inactive(): void
    {
        $staff = Staff::factory()->inactive()->create();

        $this->assertSame(AccountStatus::Inactive, $staff->account_status);
        $this->assertDatabaseHas('staff', [
            'staff_id'       => $staff->staff_id,
            'account_status' => AccountStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** withoutFullName state sets full_name to null. */
    public function without_full_name_state_sets_full_name_to_null(): void
    {
        $staff = Staff::factory()->withoutFullName()->create();

        $this->assertNull($staff->full_name);
    }

    // -------------------------------------------------------------------------
    // Username boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** usernameMinLength state sets username to 1 character (minimum). */
    public function username_min_length_state_sets_username_to_one_character(): void
    {
        $staff = Staff::factory()->usernameMinLength()->create();

        $this->assertSame(1, strlen($staff->username));
    }

    #[Test]
    /** usernameMidLength state sets username to 50 characters (in‑range). */
    public function username_mid_length_state_sets_username_to_fifty_characters(): void
    {
        $staff = Staff::factory()->usernameMidLength()->create();

        $this->assertSame(50, strlen($staff->username));
    }

    #[Test]
    /** usernameMaxLength state sets username to 100 characters (maximum). */
    public function username_max_length_state_sets_username_to_one_hundred_characters(): void
    {
        $staff = Staff::factory()->usernameMaxLength()->create();

        $this->assertSame(100, strlen($staff->username));
    }

    // -------------------------------------------------------------------------
    // Username boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** usernameEmpty state sets username to 0 characters (below minimum). */
    public function username_empty_state_sets_username_to_empty_string(): void
    {
        $staff = Staff::factory()->usernameEmpty()->make();

        $this->assertSame(0, strlen($staff->username));
    }

    #[Test]
    /** usernameTooLong state sets username to 101 characters (above maximum). */
    public function username_too_long_state_sets_username_to_one_hundred_one_characters(): void
    {
        $staff = Staff::factory()->usernameTooLong()->make();

        $this->assertSame(101, strlen($staff->username));
    }

    // -------------------------------------------------------------------------
    // Full name boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** fullNameMinLength state sets full_name to 1 character (minimum). */
    public function full_name_min_length_state_sets_full_name_to_one_character(): void
    {
        $staff = Staff::factory()->fullNameMinLength()->create();

        $this->assertSame(1, strlen($staff->full_name));
    }

    #[Test]
    /** fullNameMaxLength state sets full_name to 100 characters (maximum). */
    public function full_name_max_length_state_sets_full_name_to_one_hundred_characters(): void
    {
        $staff = Staff::factory()->fullNameMaxLength()->create();

        $this->assertSame(100, strlen($staff->full_name));
    }

    // -------------------------------------------------------------------------
    // Full name boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** fullNameTooLong state sets full_name to 101 characters (above maximum). */
    public function full_name_too_long_state_sets_full_name_to_one_hundred_one_characters(): void
    {
        $staff = Staff::factory()->fullNameTooLong()->make();

        $this->assertSame(101, strlen($staff->full_name));
    }
}