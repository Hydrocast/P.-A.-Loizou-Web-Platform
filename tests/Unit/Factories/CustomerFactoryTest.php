<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\AccountStatus;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for CustomerFactory.
 *
 * Covers the default state, account status states, reset token states,
 * and boundary states for full_name (2‑50) and phone_number (8 digits, nullable).
 */
class CustomerFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a Customer record. */
    public function default_state_creates_customer_record(): void
    {
        $customer = Customer::factory()->create();

        $this->assertInstanceOf(Customer::class, $customer);
        $this->assertDatabaseHas('customers', ['customer_id' => $customer->customer_id]);
    }

    #[Test]
    /** Default state sets account_status to Active. */
    public function default_state_sets_account_status_to_active(): void
    {
        $customer = Customer::factory()->create();

        $this->assertInstanceOf(AccountStatus::class, $customer->account_status);
        $this->assertSame(AccountStatus::Active, $customer->account_status);
    }

    #[Test]
    /** Default state sets reset_token to null. */
    public function default_state_sets_reset_token_to_null(): void
    {
        $customer = Customer::factory()->create();

        $this->assertNull($customer->reset_token);
    }

    #[Test]
    /** Default state sets reset_token_expiry to null. */
    public function default_state_sets_reset_token_expiry_to_null(): void
    {
        $customer = Customer::factory()->create();

        $this->assertNull($customer->reset_token_expiry);
    }

    // -------------------------------------------------------------------------
    // Account status states
    // -------------------------------------------------------------------------

    #[Test]
    /** inactive state sets account_status to Inactive. */
    public function inactive_state_sets_account_status_to_inactive(): void
    {
        $customer = Customer::factory()->inactive()->create();

        $this->assertSame(AccountStatus::Inactive, $customer->account_status);
        $this->assertDatabaseHas('customers', [
            'customer_id'    => $customer->customer_id,
            'account_status' => AccountStatus::Inactive->value,
        ]);
    }

    // -------------------------------------------------------------------------
    // Reset token states
    // -------------------------------------------------------------------------

    #[Test]
    /** withPendingReset state sets a non-null reset token. */
    public function with_pending_reset_state_sets_non_null_reset_token(): void
    {
        $customer = Customer::factory()->withPendingReset()->create();

        $this->assertNotNull($customer->reset_token);
    }

    #[Test]
    /** withPendingReset state sets reset_token_expiry to a future timestamp. */
    public function with_pending_reset_state_sets_reset_token_expiry_in_the_future(): void
    {
        $customer = Customer::factory()->withPendingReset()->create();

        $this->assertTrue($customer->reset_token_expiry->isFuture());
    }

    #[Test]
    /** withExpiredReset state sets a non-null reset token. */
    public function with_expired_reset_state_sets_non_null_reset_token(): void
    {
        $customer = Customer::factory()->withExpiredReset()->create();

        $this->assertNotNull($customer->reset_token);
    }

    #[Test]
    /** withExpiredReset state sets reset_token_expiry to a past timestamp. */
    public function with_expired_reset_state_sets_reset_token_expiry_in_the_past(): void
    {
        $customer = Customer::factory()->withExpiredReset()->create();

        $this->assertTrue($customer->reset_token_expiry->isPast());
    }

    // -------------------------------------------------------------------------
    // Phone number states
    // -------------------------------------------------------------------------

    #[Test]
    /** withoutPhone state sets phone_number to null. */
    public function without_phone_state_sets_phone_number_to_null(): void
    {
        $customer = Customer::factory()->withoutPhone()->create();

        $this->assertNull($customer->phone_number);
    }

    #[Test]
    /** phoneExactLength state sets phone_number to 8 digits (minimum). */
    public function phone_exact_length_state_sets_phone_number_to_eight_digits(): void
    {
        $customer = Customer::factory()->phoneExactLength()->create();

        $this->assertSame(8, strlen($customer->phone_number));
    }

    // -------------------------------------------------------------------------
    // Phone number boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** phoneTooShort state sets phone_number to 7 digits (below minimum). */
    public function phone_too_short_state_sets_phone_number_to_seven_digits(): void
    {
        $customer = Customer::factory()->phoneTooShort()->make();

        $this->assertSame(7, strlen($customer->phone_number));
    }

    #[Test]
    /** phoneTooLong state sets phone_number to 9 digits (above maximum). */
    public function phone_too_long_state_sets_phone_number_to_nine_digits(): void
    {
        $customer = Customer::factory()->phoneTooLong()->make();

        $this->assertSame(9, strlen($customer->phone_number));
    }

    // -------------------------------------------------------------------------
    // Full name boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** fullNameMinLength state sets full_name to 2 characters (minimum). */
    public function full_name_min_length_state_sets_full_name_to_two_characters(): void
    {
        $customer = Customer::factory()->fullNameMinLength()->create();

        $this->assertSame(2, strlen($customer->full_name));
    }

    #[Test]
    /** fullNameMidLength state sets full_name to 26 characters (in‑range). */
    public function full_name_mid_length_state_sets_full_name_to_twenty_six_characters(): void
    {
        $customer = Customer::factory()->fullNameMidLength()->create();

        $this->assertSame(26, strlen($customer->full_name));
    }

    #[Test]
    /** fullNameMaxLength state sets full_name to 50 characters (maximum). */
    public function full_name_max_length_state_sets_full_name_to_fifty_characters(): void
    {
        $customer = Customer::factory()->fullNameMaxLength()->create();

        $this->assertSame(50, strlen($customer->full_name));
    }

    // -------------------------------------------------------------------------
    // Full name boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** fullNameTooShort state sets full_name to 1 character (below minimum). */
    public function full_name_too_short_state_sets_full_name_to_one_character(): void
    {
        $customer = Customer::factory()->fullNameTooShort()->make();

        $this->assertSame(1, strlen($customer->full_name));
    }

    #[Test]
    /** fullNameTooLong state sets full_name to 51 characters (above maximum). */
    public function full_name_too_long_state_sets_full_name_to_fifty_one_characters(): void
    {
        $customer = Customer::factory()->fullNameTooLong()->make();

        $this->assertSame(51, strlen($customer->full_name));
    }
}