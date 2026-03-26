<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use App\Models\Staff;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for CustomerOrderFactory.
 *
 * Covers the default state, all status states, assignedTo state, monetary states,
 * and boundary states for customer_name (2-50) and customer_phone (8 digits).
 */
class CustomerOrderFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a CustomerOrder record. */
    public function default_state_creates_customer_order_record(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->assertInstanceOf(CustomerOrder::class, $order);
        $this->assertDatabaseHas('customer_orders', ['order_id' => $order->order_id]);
    }

    #[Test]
    /** Default state creates a linked customer. */
    public function default_state_creates_a_linked_customer(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->assertNotNull($order->customer_id);
        $this->assertDatabaseHas('customers', ['customer_id' => $order->customer_id]);
    }

    #[Test]
    /** Default state sets order_status to Pending. */
    public function default_state_sets_order_status_to_pending(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->assertInstanceOf(OrderStatus::class, $order->order_status);
        $this->assertSame(OrderStatus::Pending, $order->order_status);
    }

    #[Test]
    /** Default state sets assigned_staff_id to null. */
    public function default_state_sets_assigned_staff_id_to_null(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->assertNull($order->assigned_staff_id);
    }

    #[Test]
    /** Default state sets staff_assignment_date to null. */
    public function default_state_sets_staff_assignment_date_to_null(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->assertNull($order->staff_assignment_date);
    }

    // -------------------------------------------------------------------------
    // Status states
    // -------------------------------------------------------------------------

    #[Test]
    /** processing state sets order_status to Processing. */
    public function processing_state_sets_order_status_to_processing(): void
    {
        $order = CustomerOrder::factory()->processing()->create();

        $this->assertSame(OrderStatus::Processing, $order->order_status);
        $this->assertDatabaseHas('customer_orders', [
            'order_id'     => $order->order_id,
            'order_status' => OrderStatus::Processing->value,
        ]);
    }

    #[Test]
    /** readyForPickup state sets order_status to ReadyForPickup. */
    public function ready_for_pickup_state_sets_order_status_to_ready_for_pickup(): void
    {
        $order = CustomerOrder::factory()->readyForPickup()->create();

        $this->assertSame(OrderStatus::ReadyForPickup, $order->order_status);
        $this->assertDatabaseHas('customer_orders', [
            'order_id'     => $order->order_id,
            'order_status' => OrderStatus::ReadyForPickup->value,
        ]);
    }

    #[Test]
    /** completed state sets order_status to Completed. */
    public function completed_state_sets_order_status_to_completed(): void
    {
        $order = CustomerOrder::factory()->completed()->create();

        $this->assertSame(OrderStatus::Completed, $order->order_status);
    }

    #[Test]
    /** cancelled state sets order_status to Cancelled. */
    public function cancelled_state_sets_order_status_to_cancelled(): void
    {
        $order = CustomerOrder::factory()->cancelled()->create();

        $this->assertSame(OrderStatus::Cancelled, $order->order_status);
    }

    // -------------------------------------------------------------------------
    // assignedTo state
    // -------------------------------------------------------------------------

    #[Test]
    /** assignedTo state sets assigned_staff_id to the given staff member. */
    public function assigned_to_state_sets_assigned_staff_id(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->assignedTo($staff)->create();

        $this->assertSame($staff->staff_id, $order->assigned_staff_id);
    }

    #[Test]
    /** assignedTo state sets a non-null staff_assignment_date. */
    public function assigned_to_state_sets_non_null_staff_assignment_date(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->assignedTo($staff)->create();

        $this->assertNotNull($order->staff_assignment_date);
    }

    // -------------------------------------------------------------------------
    // Monetary states
    // -------------------------------------------------------------------------

    #[Test]
    /** zeroTotal state sets all monetary fields to zero. */
    public function zero_total_state_sets_all_monetary_fields_to_zero(): void
    {
        $order = CustomerOrder::factory()->zeroTotal()->create();

        $this->assertEquals(0.00, $order->net_amount);
        $this->assertEquals(0.00, $order->vat_amount);
        $this->assertEquals(0.00, $order->total_amount);
    }

    #[Test]
    /** minimalTotal state sets total_amount to 0.01 (minimum). */
    public function minimal_total_state_sets_total_amount_to_one_cent(): void
    {
        $order = CustomerOrder::factory()->minimalTotal()->create();

        $this->assertEquals(0.01, $order->total_amount);
    }

    #[Test]
    /** typicalTotal state sets total_amount to 100.00 (typical). */
    public function typical_total_state_sets_total_amount_to_one_hundred(): void
    {
        $order = CustomerOrder::factory()->typicalTotal()->create();

        $this->assertEquals(100.00, $order->total_amount);
    }

    // -------------------------------------------------------------------------
    // Phone boundaries - valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** phoneExactLength state sets customer_phone to 8 digits (minimum). */
    public function phone_exact_length_state_sets_customer_phone_to_eight_digits(): void
    {
        $order = CustomerOrder::factory()->phoneExactLength()->create();

        $this->assertSame(8, strlen($order->customer_phone));
    }

    // -------------------------------------------------------------------------
    // Phone boundaries - invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** phoneTooShort state sets customer_phone to 7 digits (below minimum). */
    public function phone_too_short_state_sets_customer_phone_to_seven_digits(): void
    {
        $order = CustomerOrder::factory()->phoneTooShort()->make();

        $this->assertSame(7, strlen($order->customer_phone));
    }

    #[Test]
    /** phoneTooLong state sets customer_phone to 9 digits (above maximum). */
    public function phone_too_long_state_sets_customer_phone_to_nine_digits(): void
    {
        $order = CustomerOrder::factory()->phoneTooLong()->make();

        $this->assertSame(9, strlen($order->customer_phone));
    }

    // -------------------------------------------------------------------------
    // Customer name boundaries - valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** customerNameMinLength state sets customer_name to 2 characters (minimum). */
    public function customer_name_min_length_state_sets_customer_name_to_two_characters(): void
    {
        $order = CustomerOrder::factory()->customerNameMinLength()->create();

        $this->assertSame(2, strlen($order->customer_name));
    }

    #[Test]
    /** customerNameMidLength state sets customer_name to 26 characters (in-range). */
    public function customer_name_mid_length_state_sets_customer_name_to_twenty_six_characters(): void
    {
        $order = CustomerOrder::factory()->customerNameMidLength()->create();

        $this->assertSame(26, strlen($order->customer_name));
    }

    #[Test]
    /** customerNameMaxLength state sets customer_name to 50 characters (maximum). */
    public function customer_name_max_length_state_sets_customer_name_to_fifty_characters(): void
    {
        $order = CustomerOrder::factory()->customerNameMaxLength()->create();

        $this->assertSame(50, strlen($order->customer_name));
    }

    // -------------------------------------------------------------------------
    // Customer name boundaries - invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** customerNameTooShort state sets customer_name to 1 character (below minimum). */
    public function customer_name_too_short_state_sets_customer_name_to_one_character(): void
    {
        $order = CustomerOrder::factory()->customerNameTooShort()->make();

        $this->assertSame(1, strlen($order->customer_name));
    }

    #[Test]
    /** customerNameTooLong state sets customer_name to 51 characters (above maximum). */
    public function customer_name_too_long_state_sets_customer_name_to_fifty_one_characters(): void
    {
        $order = CustomerOrder::factory()->customerNameTooLong()->make();

        $this->assertSame(51, strlen($order->customer_name));
    }
}