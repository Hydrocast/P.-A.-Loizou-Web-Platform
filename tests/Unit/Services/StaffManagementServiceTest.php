<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\Staff;
use App\Services\StaffManagementService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for StaffManagementService.
 *
 * Covers staff account creation, updating, status changes, and listing.
 * Boundary values: password (8–64).
 * Business rules: no self-deactivation, last active administrator cannot be deactivated.
 */
class StaffManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private StaffManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StaffManagementService();
    }

    // -------------------------------------------------------------------------
    // createStaffAccount()
    // -------------------------------------------------------------------------

    #[Test]
    /** Creates an active employee account with all fields stored correctly. */
    public function create_staff_account_creates_active_account_with_correct_fields(): void
    {
        $staff = $this->service->createStaffAccount(
            username: 'jdoe',
            password: 'password123',
            role: StaffRole::Employee,
            fullName: 'Jane Doe',
        );

        $this->assertInstanceOf(Staff::class, $staff);
        $this->assertEquals('jdoe', $staff->username);
        $this->assertEquals('Jane Doe', $staff->full_name);
        $this->assertEquals(StaffRole::Employee, $staff->role);
        $this->assertEquals(AccountStatus::Active, $staff->account_status);
        $this->assertDatabaseHas('staff', ['username' => 'jdoe']);
    }

    #[Test]
    /** Password is stored as a hash, not plain text. */
    public function create_staff_account_stores_password_as_hash(): void
    {
        $staff = $this->service->createStaffAccount('user1', 'password123', StaffRole::Employee, null);
        $this->assertNotEquals('password123', $staff->password);
        $this->assertTrue(Hash::check('password123', $staff->password));
    }

    #[Test]
    /** Administrator role is accepted and stored correctly. */
    public function create_staff_account_accepts_administrator_role(): void
    {
        $staff = $this->service->createStaffAccount('admin1', 'password123', StaffRole::Administrator, 'Admin User');
        $this->assertEquals(StaffRole::Administrator, $staff->role);
    }

    #[Test]
    /** Null full name is accepted as an optional field. */
    public function create_staff_account_accepts_null_full_name(): void
    {
        $staff = $this->service->createStaffAccount('user2', 'password123', StaffRole::Employee, null);
        $this->assertNull($staff->full_name);
    }

    #[Test]
    /** Duplicate username throws ValidationException. */
    public function create_staff_account_throws_when_username_is_already_taken(): void
    {
        Staff::factory()->create(['username' => 'taken']);
        $this->expectException(ValidationException::class);
        $this->service->createStaffAccount('taken', 'password123', StaffRole::Employee, null);
    }

    // Password boundaries -----------------------------------------------------

    #[Test]
    /** Password of 7 characters (below minimum) is rejected. */
    public function create_staff_account_throws_when_password_is_seven_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStaffAccount('user3', str_repeat('a', 7), StaffRole::Employee, null);
    }

    #[Test]
    /** Password of 8 characters (minimum) is accepted. */
    public function create_staff_account_accepts_password_of_eight_characters(): void
    {
        $staff = $this->service->createStaffAccount('user4', str_repeat('a', 8), StaffRole::Employee, null);
        $this->assertNotNull($staff->staff_id);
    }

    #[Test]
    /** Password of 36 characters (in-range) is accepted. */
    public function create_staff_account_accepts_password_of_thirty_six_characters(): void
    {
        $staff = $this->service->createStaffAccount('user5', str_repeat('a', 36), StaffRole::Employee, null);
        $this->assertNotNull($staff->staff_id);
    }

    #[Test]
    /** Password of 64 characters (maximum) is accepted. */
    public function create_staff_account_accepts_password_of_sixty_four_characters(): void
    {
        $staff = $this->service->createStaffAccount('user6', str_repeat('a', 64), StaffRole::Employee, null);
        $this->assertNotNull($staff->staff_id);
    }

    #[Test]
    /** Password of 65 characters (above maximum) is rejected. */
    public function create_staff_account_throws_when_password_exceeds_sixty_four_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStaffAccount('user7', str_repeat('a', 65), StaffRole::Employee, null);
    }

    // -------------------------------------------------------------------------
    // updateStaffAccount()
    // -------------------------------------------------------------------------

    #[Test]
    /** Full name is updated to the new value. */
    public function update_staff_account_changes_full_name(): void
    {
        $staff = Staff::factory()->create(['full_name' => 'Old Name']);
        $this->service->updateStaffAccount($staff->staff_id, 'New Name', null);
        $staff->refresh();
        $this->assertEquals('New Name', $staff->full_name);
    }

    #[Test]
    /** Null full name clears the existing value. */
    public function update_staff_account_accepts_null_full_name(): void
    {
        $staff = Staff::factory()->create(['full_name' => 'Some Name']);
        $this->service->updateStaffAccount($staff->staff_id, null, null);
        $staff->refresh();
        $this->assertNull($staff->full_name);
    }

    #[Test]
    /** Password remains unchanged when null is passed for new password. */
    public function update_staff_account_leaves_password_unchanged_when_new_password_is_null(): void
    {
        $originalHash = Hash::make('original123');
        $staff = Staff::factory()->create(['password' => $originalHash]);
        $this->service->updateStaffAccount($staff->staff_id, 'Name', null);
        $staff->refresh();
        $this->assertTrue(Hash::check('original123', $staff->password));
    }

    #[Test]
    /** Password is replaced when a new password is provided. */
    public function update_staff_account_updates_password_when_new_password_is_provided(): void
    {
        $staff = Staff::factory()->create(['password' => Hash::make('oldpassword')]);
        $this->service->updateStaffAccount($staff->staff_id, 'Name', 'newpass12');
        $staff->refresh();
        $this->assertTrue(Hash::check('newpass12', $staff->password));
        $this->assertFalse(Hash::check('oldpassword', $staff->password));
    }

    #[Test]
    /** Non-existent staff ID throws ModelNotFoundException. */
    public function update_staff_account_throws_when_staff_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->updateStaffAccount(99999, 'Name', null);
    }

    // Password boundaries -----------------------------------------------------

    #[Test]
    /** New password of 7 characters (below minimum) is rejected. */
    public function update_staff_account_throws_when_new_password_is_seven_characters(): void
    {
        $staff = Staff::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->updateStaffAccount($staff->staff_id, 'Name', str_repeat('a', 7));
    }

    #[Test]
    /** New password of 8 characters (minimum) is accepted. */
    public function update_staff_account_accepts_new_password_of_eight_characters(): void
    {
        $staff = Staff::factory()->create();
        $this->service->updateStaffAccount($staff->staff_id, 'Name', str_repeat('a', 8));
        $staff->refresh();
        $this->assertTrue(Hash::check(str_repeat('a', 8), $staff->password));
    }

    #[Test]
    /** New password of 36 characters (in-range) is accepted. */
    public function update_staff_account_accepts_new_password_of_thirty_six_characters(): void
    {
        $staff = Staff::factory()->create();
        $this->service->updateStaffAccount($staff->staff_id, 'Name', str_repeat('a', 36));
        $staff->refresh();
        $this->assertTrue(Hash::check(str_repeat('a', 36), $staff->password));
    }

    #[Test]
    /** New password of 64 characters (maximum) is accepted. */
    public function update_staff_account_accepts_new_password_of_sixty_four_characters(): void
    {
        $staff = Staff::factory()->create();
        $this->service->updateStaffAccount($staff->staff_id, 'Name', str_repeat('a', 64));
        $staff->refresh();
        $this->assertTrue(Hash::check(str_repeat('a', 64), $staff->password));
    }

    #[Test]
    /** New password of 65 characters (above maximum) is rejected. */
    public function update_staff_account_throws_when_new_password_exceeds_sixty_four_characters(): void
    {
        $staff = Staff::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->updateStaffAccount($staff->staff_id, 'Name', str_repeat('a', 65));
    }

    // -------------------------------------------------------------------------
    // updateStaffStatus()
    // -------------------------------------------------------------------------

    #[Test]
    /** Inactive account is activated successfully. */
    public function update_staff_status_activates_inactive_account_successfully(): void
    {
        $admin  = Staff::factory()->administrator()->create();
        $target = Staff::factory()->inactive()->create();
        $this->service->updateStaffStatus($admin->staff_id, $target->staff_id, AccountStatus::Active);
        $target->refresh();
        $this->assertEquals(AccountStatus::Active, $target->account_status);
    }

    #[Test]
    /** Active employee account is deactivated when an active administrator remains. */
    public function update_staff_status_deactivates_employee_when_admin_remains(): void
    {
        $admin    = Staff::factory()->administrator()->create();
        $employee = Staff::factory()->create();
        $this->service->updateStaffStatus($admin->staff_id, $employee->staff_id, AccountStatus::Inactive);
        $employee->refresh();
        $this->assertEquals(AccountStatus::Inactive, $employee->account_status);
    }

    #[Test]
    /** Administrator account is deactivated when at least one other active administrator remains. */
    public function update_staff_status_deactivates_admin_when_another_active_admin_exists(): void
    {
        $actingAdmin = Staff::factory()->administrator()->create();
        $otherAdmin  = Staff::factory()->administrator()->create();
        $this->service->updateStaffStatus($actingAdmin->staff_id, $otherAdmin->staff_id, AccountStatus::Inactive);
        $otherAdmin->refresh();
        $this->assertEquals(AccountStatus::Inactive, $otherAdmin->account_status);
    }

    #[Test]
    /** Administrator cannot deactivate their own account. */
    public function update_staff_status_throws_when_admin_attempts_to_deactivate_own_account(): void
    {
        $admin = Staff::factory()->administrator()->create();
        $this->expectException(ValidationException::class);
        $this->service->updateStaffStatus($admin->staff_id, $admin->staff_id, AccountStatus::Inactive);
    }

    #[Test]
    /** The last remaining active administrator cannot be deactivated. */
    public function update_staff_status_throws_when_deactivating_the_last_active_administrator(): void
    {
        $target = Staff::factory()->administrator()->create();
        $actor  = Staff::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->updateStaffStatus($actor->staff_id, $target->staff_id, AccountStatus::Inactive);
    }

    #[Test]
    /** Setting an already inactive account to Inactive completes without exception. */
    public function update_staff_status_allows_deactivating_already_inactive_account(): void
    {
        $admin  = Staff::factory()->administrator()->create();
        $target = Staff::factory()->inactive()->create();

        $this->service->updateStaffStatus($admin->staff_id, $target->staff_id, AccountStatus::Inactive);
        $target->refresh();

        $this->assertEquals(AccountStatus::Inactive, $target->account_status);
    }

    #[Test]
    /** Non-existent staff ID throws ModelNotFoundException. */
    public function update_staff_status_throws_when_staff_does_not_exist(): void
    {
        $admin = Staff::factory()->administrator()->create();
        $this->expectException(ModelNotFoundException::class);
        $this->service->updateStaffStatus($admin->staff_id, 99999, AccountStatus::Inactive);
    }

    // -------------------------------------------------------------------------
    // getAllStaff()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns an empty collection when no staff accounts exist. */
    public function get_all_staff_returns_empty_collection_when_no_staff_exist(): void
    {
        $result = $this->service->getAllStaff();
        $this->assertCount(0, $result);
    }

    #[Test]
    /** Returns all staff accounts. */
    public function get_all_staff_returns_all_staff_accounts(): void
    {
        Staff::factory()->count(3)->create();
        $result = $this->service->getAllStaff();
        $this->assertCount(3, $result);
    }

    #[Test]
    /** Results are ordered alphabetically by username ascending. */
    public function get_all_staff_returns_staff_ordered_by_username_ascending(): void
    {
        Staff::factory()->create(['username' => 'zebra_user']);
        Staff::factory()->create(['username' => 'alpha_user']);
        Staff::factory()->create(['username' => 'mango_user']);
        $result = $this->service->getAllStaff();
        $this->assertEquals('alpha_user', $result->get(0)->username);
        $this->assertEquals('mango_user', $result->get(1)->username);
        $this->assertEquals('zebra_user', $result->get(2)->username);
    }
}