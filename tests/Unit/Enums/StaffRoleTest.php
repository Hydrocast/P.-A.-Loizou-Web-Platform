<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\StaffRole;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for StaffRole enum.
 *
 * Covers backing values, isAdministrator() method, and model casting on Staff.
 */
class StaffRoleTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Backing values
    // -------------------------------------------------------------------------

    #[Test]
    /** Employee has the backing value 'Employee'. */
    public function employee_has_backing_value_of_employee(): void
    {
        $this->assertSame('Employee', StaffRole::Employee->value);
    }

    #[Test]
    /** Administrator has the backing value 'Administrator'. */
    public function administrator_has_backing_value_of_administrator(): void
    {
        $this->assertSame('Administrator', StaffRole::Administrator->value);
    }

    // -------------------------------------------------------------------------
    // isAdministrator()
    // -------------------------------------------------------------------------

    #[Test]
    /** Employee role returns false. */
    public function is_administrator_returns_false_for_employee(): void
    {
        $this->assertFalse(StaffRole::Employee->isAdministrator());
    }

    #[Test]
    /** Administrator role returns true. */
    public function is_administrator_returns_true_for_administrator(): void
    {
        $this->assertTrue(StaffRole::Administrator->isAdministrator());
    }

    // -------------------------------------------------------------------------
    // Model casting — Staff
    // -------------------------------------------------------------------------

    #[Test]
    /** Staff role is stored as the backing value string. */
    public function staff_role_is_stored_as_backing_value(): void
    {
        $staff = Staff::factory()->create([
            'role' => StaffRole::Administrator
        ]);

        $this->assertDatabaseHas('staff', [
            'staff_id' => $staff->staff_id,
            'role'     => StaffRole::Administrator->value,
        ]);
    }

    #[Test]
    /** Staff role is retrieved as a StaffRole instance. */
    public function staff_role_is_retrieved_as_enum_instance(): void
    {
        $staff = Staff::factory()->create([
            'role' => StaffRole::Administrator
        ]);
        
        $fresh = Staff::find($staff->staff_id);

        $this->assertInstanceOf(StaffRole::class, $fresh->role);
        $this->assertSame(StaffRole::Administrator, $fresh->role);
    }
}