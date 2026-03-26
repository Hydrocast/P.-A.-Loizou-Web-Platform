<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\AccountStatus;
use App\Models\Customer;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for AccountStatus enum.
 *
 * Covers backing values, isActive() method, and model casting
 * on Customer and Staff.
 */
class AccountStatusTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Backing values
    // -------------------------------------------------------------------------

    #[Test]
    /** Active has the backing value 'Active'. */
    public function active_has_backing_value_of_active(): void
    {
        $this->assertSame('Active', AccountStatus::Active->value);
    }

    #[Test]
    /** Inactive has the backing value 'Inactive'. */
    public function inactive_has_backing_value_of_inactive(): void
    {
        $this->assertSame('Inactive', AccountStatus::Inactive->value);
    }

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    #[Test]
    /** Active status returns true. */
    public function is_active_returns_true_for_active(): void
    {
        $this->assertTrue(AccountStatus::Active->isActive());
    }

    #[Test]
    /** Inactive status returns false. */
    public function is_active_returns_false_for_inactive(): void
    {
        $this->assertFalse(AccountStatus::Inactive->isActive());
    }

    // -------------------------------------------------------------------------
    // Model casting — Customer
    // -------------------------------------------------------------------------

    #[Test]
    /** Customer account status is stored as the backing value string. */
    public function customer_account_status_is_stored_as_backing_value(): void
    {
        $customer = Customer::factory()->create([
            'account_status' => AccountStatus::Inactive
        ]);

        $this->assertDatabaseHas('customers', [
            'customer_id'    => $customer->customer_id,
            'account_status' => AccountStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** Customer account status is retrieved as an AccountStatus instance. */
    public function customer_account_status_is_retrieved_as_enum_instance(): void
    {
        $customer = Customer::factory()->create([
            'account_status' => AccountStatus::Inactive
        ]);
        
        $fresh = Customer::find($customer->customer_id);

        $this->assertInstanceOf(AccountStatus::class, $fresh->account_status);
        $this->assertSame(AccountStatus::Inactive, $fresh->account_status);
    }

    // -------------------------------------------------------------------------
    // Model casting — Staff
    // -------------------------------------------------------------------------

    #[Test]
    /** Staff account status is stored as the backing value string. */
    public function staff_account_status_is_stored_as_backing_value(): void
    {
        $staff = Staff::factory()->create([
            'account_status' => AccountStatus::Inactive
        ]);

        $this->assertDatabaseHas('staff', [
            'staff_id'       => $staff->staff_id,
            'account_status' => AccountStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** Staff account status is retrieved as an AccountStatus instance. */
    public function staff_account_status_is_retrieved_as_enum_instance(): void
    {
        $staff = Staff::factory()->create([
            'account_status' => AccountStatus::Inactive
        ]);
        
        $fresh = Staff::find($staff->staff_id);

        $this->assertInstanceOf(AccountStatus::class, $fresh->account_status);
        $this->assertSame(AccountStatus::Inactive, $fresh->account_status);
    }
}