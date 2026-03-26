<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for OrderStatus enum.
 *
 * Covers backing values, label(), isTerminal(), next(), isEditable(),
 * isAssignable(), values(), and model casting on CustomerOrder.
 */
class OrderStatusTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    /** Pending has the backing value 'Pending'. */
    public function pending_has_backing_value_of_pending(): void
    {
        $this->assertSame('Pending', OrderStatus::Pending->value);
    }

    #[Test]
    /** Processing has the backing value 'Processing'. */
    public function processing_has_backing_value_of_processing(): void
    {
        $this->assertSame('Processing', OrderStatus::Processing->value);
    }

    #[Test]
    /** ReadyForPickup has the backing value 'Ready for Pickup'. */
    public function ready_for_pickup_has_backing_value_of_ready_for_pickup(): void
    {
        $this->assertSame('Ready for Pickup', OrderStatus::ReadyForPickup->value);
    }

    #[Test]
    /** Completed has the backing value 'Completed'. */
    public function completed_has_backing_value_of_completed(): void
    {
        $this->assertSame('Completed', OrderStatus::Completed->value);
    }

    #[Test]
    /** Cancelled has the backing value 'Cancelled'. */
    public function cancelled_has_backing_value_of_cancelled(): void
    {
        $this->assertSame('Cancelled', OrderStatus::Cancelled->value);
    }

    #[Test]
    /** Pending returns the label 'Pending'. */
    public function label_returns_pending_for_pending(): void
    {
        $this->assertSame('Pending', OrderStatus::Pending->label());
    }

    #[Test]
    /** Processing returns the label 'Processing'. */
    public function label_returns_processing_for_processing(): void
    {
        $this->assertSame('Processing', OrderStatus::Processing->label());
    }

    #[Test]
    /** ReadyForPickup returns the label 'Ready for Pickup'. */
    public function label_returns_ready_for_pickup_for_ready_for_pickup(): void
    {
        $this->assertSame('Ready for Pickup', OrderStatus::ReadyForPickup->label());
    }

    #[Test]
    /** Completed returns the label 'Completed'. */
    public function label_returns_completed_for_completed(): void
    {
        $this->assertSame('Completed', OrderStatus::Completed->label());
    }

    #[Test]
    /** Cancelled returns the label 'Cancelled'. */
    public function label_returns_cancelled_for_cancelled(): void
    {
        $this->assertSame('Cancelled', OrderStatus::Cancelled->label());
    }

    #[Test]
    /** Pending is not a terminal status. */
    public function is_terminal_returns_false_for_pending(): void
    {
        $this->assertFalse(OrderStatus::Pending->isTerminal());
    }

    #[Test]
    /** Processing is not a terminal status. */
    public function is_terminal_returns_false_for_processing(): void
    {
        $this->assertFalse(OrderStatus::Processing->isTerminal());
    }

    #[Test]
    /** ReadyForPickup is not a terminal status. */
    public function is_terminal_returns_false_for_ready_for_pickup(): void
    {
        $this->assertFalse(OrderStatus::ReadyForPickup->isTerminal());
    }

    #[Test]
    /** Completed is a terminal status. */
    public function is_terminal_returns_true_for_completed(): void
    {
        $this->assertTrue(OrderStatus::Completed->isTerminal());
    }

    #[Test]
    /** Cancelled is a terminal status. */
    public function is_terminal_returns_true_for_cancelled(): void
    {
        $this->assertTrue(OrderStatus::Cancelled->isTerminal());
    }

    #[Test]
    /** The next status after Pending is Processing. */
    public function next_returns_processing_for_pending(): void
    {
        $this->assertSame(OrderStatus::Processing, OrderStatus::Pending->next());
    }

    #[Test]
    /** The next status after Processing is ReadyForPickup. */
    public function next_returns_ready_for_pickup_for_processing(): void
    {
        $this->assertSame(OrderStatus::ReadyForPickup, OrderStatus::Processing->next());
    }

    #[Test]
    /** The next status after ReadyForPickup is Completed. */
    public function next_returns_completed_for_ready_for_pickup(): void
    {
        $this->assertSame(OrderStatus::Completed, OrderStatus::ReadyForPickup->next());
    }

    #[Test]
    /** Completed has no next status. */
    public function next_returns_null_for_completed(): void
    {
        $this->assertNull(OrderStatus::Completed->next());
    }

    #[Test]
    /** Cancelled has no next status. */
    public function next_returns_null_for_cancelled(): void
    {
        $this->assertNull(OrderStatus::Cancelled->next());
    }

    #[Test]
    /** Pending orders are editable. */
    public function is_editable_returns_true_for_pending(): void
    {
        $this->assertTrue(OrderStatus::Pending->isEditable());
    }

    #[Test]
    /** Processing orders are not editable. */
    public function is_editable_returns_false_for_processing(): void
    {
        $this->assertFalse(OrderStatus::Processing->isEditable());
    }

    #[Test]
    /** ReadyForPickup orders are not editable. */
    public function is_editable_returns_false_for_ready_for_pickup(): void
    {
        $this->assertFalse(OrderStatus::ReadyForPickup->isEditable());
    }

    #[Test]
    /** Completed orders are not editable. */
    public function is_editable_returns_false_for_completed(): void
    {
        $this->assertFalse(OrderStatus::Completed->isEditable());
    }

    #[Test]
    /** Cancelled orders are not editable. */
    public function is_editable_returns_false_for_cancelled(): void
    {
        $this->assertFalse(OrderStatus::Cancelled->isEditable());
    }

    #[Test]
    /** Pending orders are assignable to staff. */
    public function is_assignable_returns_true_for_pending(): void
    {
        $this->assertTrue(OrderStatus::Pending->isAssignable());
    }

    #[Test]
    /** Processing orders are assignable to staff. */
    public function is_assignable_returns_true_for_processing(): void
    {
        $this->assertTrue(OrderStatus::Processing->isAssignable());
    }

    #[Test]
    /** ReadyForPickup orders are not assignable to staff. */
    public function is_assignable_returns_false_for_ready_for_pickup(): void
    {
        $this->assertFalse(OrderStatus::ReadyForPickup->isAssignable());
    }

    #[Test]
    /** Completed orders are not assignable to staff. */
    public function is_assignable_returns_false_for_completed(): void
    {
        $this->assertFalse(OrderStatus::Completed->isAssignable());
    }

    #[Test]
    /** Cancelled orders are not assignable to staff. */
    public function is_assignable_returns_false_for_cancelled(): void
    {
        $this->assertFalse(OrderStatus::Cancelled->isAssignable());
    }

    #[Test]
    /** Returns all backing values as an array. */
    public function values_returns_array_of_all_backing_values(): void
    {
        $this->assertSame([
            'Pending',
            'Processing',
            'Ready for Pickup',
            'Completed',
            'Cancelled',
        ], OrderStatus::values());
    }

    #[Test]
    /** Order status is stored as the backing value string. */
    public function order_status_is_stored_as_backing_value(): void
    {
        $order = CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Completed,
        ]);

        $this->assertDatabaseHas('customer_orders', [
            'order_id' => $order->order_id,
            'order_status' => OrderStatus::Completed->value,
        ]);
    }

    #[Test]
    /** Order status is retrieved as an OrderStatus instance. */
    public function order_status_is_retrieved_as_enum_instance(): void
    {
        $order = CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Completed,
        ]);

        $fresh = CustomerOrder::find($order->order_id);

        $this->assertInstanceOf(OrderStatus::class, $fresh->order_status);
        $this->assertSame(OrderStatus::Completed, $fresh->order_status);
    }
}