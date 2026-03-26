<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\OrderStatus;
use App\Events\OrderStatusChanged;
use App\Models\CustomerOrder;
use App\Models\Staff;
use App\Services\OrderProcessingService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for OrderProcessingService.
 *
 * Covers order listing, filtering, viewing, status updates, notes, and reassignment.
 * Boundary values: note text (1-1000).
 */
class OrderProcessingServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderProcessingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OrderProcessingService();
    }

    #[Test]
    /** Returns Pending, Processing, and ReadyForPickup orders. */
    public function get_active_orders_returns_pending_processing_and_ready_for_pickup_orders(): void
    {
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Processing]);
        CustomerOrder::factory()->create(['order_status' => OrderStatus::ReadyForPickup]);

        $result = $this->service->getActiveOrders();

        $this->assertCount(3, $result);
    }

    #[Test]
    /** Completed orders are excluded from active orders. */
    public function get_active_orders_excludes_completed_orders(): void
    {
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);
        CustomerOrder::factory()->completed()->create();

        $result = $this->service->getActiveOrders();

        $this->assertCount(1, $result);
        $this->assertEquals(OrderStatus::Pending, $result->first()->order_status);
    }

    #[Test]
    /** Cancelled orders are excluded from active orders. */
    public function get_active_orders_excludes_cancelled_orders(): void
    {
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);
        CustomerOrder::factory()->cancelled()->create();

        $result = $this->service->getActiveOrders();

        $this->assertCount(1, $result);
        $this->assertEquals(OrderStatus::Pending, $result->first()->order_status);
    }

    #[Test]
    /** Returns an empty collection when only Completed and Cancelled orders exist. */
    public function get_active_orders_returns_empty_when_only_terminal_orders_exist(): void
    {
        CustomerOrder::factory()->completed()->create();
        CustomerOrder::factory()->cancelled()->create();

        $result = $this->service->getActiveOrders();

        $this->assertCount(0, $result);
    }

    #[Test]
    /** Returns an empty collection when no orders exist. */
    public function get_active_orders_returns_empty_when_table_is_empty(): void
    {
        $result = $this->service->getActiveOrders();

        $this->assertCount(0, $result);
    }

    #[Test]
    /** All orders including Completed and Cancelled are returned when no filters are applied. */
    public function filter_orders_returns_all_orders_when_no_filters_are_applied(): void
    {
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);
        CustomerOrder::factory()->completed()->create();
        CustomerOrder::factory()->cancelled()->create();

        $result = $this->service->filterOrders(null, null, null);

        $this->assertCount(3, $result);
    }

    #[Test]
    /** Results are filtered to the specified status only. */
    public function filter_orders_filters_by_status(): void
    {
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);
        CustomerOrder::factory()->create(['order_status' => OrderStatus::Processing]);

        $result = $this->service->filterOrders(OrderStatus::Pending, null, null);

        $this->assertCount(1, $result);
        $this->assertEquals(OrderStatus::Pending, $result->first()->order_status);
    }

    #[Test]
    /** Results are filtered to orders within the given date range. */
    public function filter_orders_filters_by_date_range(): void
    {
        CustomerOrder::factory()->create(['order_creation_timestamp' => now()->subDays(10)]);
        CustomerOrder::factory()->create(['order_creation_timestamp' => now()->subDays(3)]);

        $result = $this->service->filterOrders(null, now()->subDays(5), now());

        $this->assertCount(1, $result);
    }

    #[Test]
    /** Results are filtered to orders created on or after the start date. */
    public function filter_orders_filters_by_start_date_only(): void
    {
        CustomerOrder::factory()->create(['order_creation_timestamp' => now()->subDays(10)]);
        CustomerOrder::factory()->create(['order_creation_timestamp' => now()->subDays(3)]);

        $result = $this->service->filterOrders(null, now()->subDays(5), null);

        $this->assertCount(1, $result);
    }

    #[Test]
    /** Results are filtered to orders created on or before the end date. */
    public function filter_orders_filters_by_end_date_only(): void
    {
        CustomerOrder::factory()->create(['order_creation_timestamp' => now()->subDays(10)]);
        CustomerOrder::factory()->create(['order_creation_timestamp' => now()->subDays(3)]);

        $result = $this->service->filterOrders(null, null, now()->subDays(5));

        $this->assertCount(1, $result);
    }

    #[Test]
    /** Status and date filters are combined with AND logic. */
    public function filter_orders_combines_status_and_date_filters_with_and_logic(): void
    {
        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Processing,
            'order_creation_timestamp' => now()->subDays(2),
        ]);
        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => now()->subDays(2),
        ]);
        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => now()->subDays(10),
        ]);

        $result = $this->service->filterOrders(OrderStatus::Pending, now()->subDays(5), now());

        $this->assertCount(1, $result);
        $this->assertEquals(OrderStatus::Pending, $result->first()->order_status);
    }

    #[Test]
    /** Returns the full order record for an existing ID. */
    public function view_order_details_returns_order_for_existing_id(): void
    {
        $order = CustomerOrder::factory()->create();

        $result = $this->service->viewOrderDetails($order->order_id);

        $this->assertEquals($order->order_id, $result->order_id);
    }

    #[Test]
    /** Throws ModelNotFoundException when the order does not exist. */
    public function view_order_details_throws_model_not_found_when_order_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->viewOrderDetails(99999);
    }

    #[Test]
    /** Transitions order status from Pending to Processing. */
    public function update_order_status_transitions_from_pending_to_processing(): void
    {
        Event::fake();
        $order = CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);

        $this->service->updateOrderStatus($order->order_id, OrderStatus::Processing);

        $this->assertEquals(OrderStatus::Processing, $order->refresh()->order_status);
    }

    #[Test]
    /** Transitions order status from Processing to ReadyForPickup. */
    public function update_order_status_transitions_from_processing_to_ready_for_pickup(): void
    {
        Event::fake();
        $order = CustomerOrder::factory()->create(['order_status' => OrderStatus::Processing]);

        $this->service->updateOrderStatus($order->order_id, OrderStatus::ReadyForPickup);

        $this->assertEquals(OrderStatus::ReadyForPickup, $order->refresh()->order_status);
    }

    #[Test]
    /** Transitions order status from ReadyForPickup to Completed. */
    public function update_order_status_transitions_from_ready_for_pickup_to_completed(): void
    {
        Event::fake();
        $order = CustomerOrder::factory()->create(['order_status' => OrderStatus::ReadyForPickup]);

        $this->service->updateOrderStatus($order->order_id, OrderStatus::Completed);

        $this->assertEquals(OrderStatus::Completed, $order->refresh()->order_status);
    }

    #[Test]
    /** Transitions order status from Pending to Cancelled. */
    public function update_order_status_can_cancel_order_from_pending(): void
    {
        Event::fake();
        $order = CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);

        $this->service->updateOrderStatus($order->order_id, OrderStatus::Cancelled);

        $this->assertEquals(OrderStatus::Cancelled, $order->refresh()->order_status);
    }

    #[Test]
    /** Dispatches OrderStatusChanged event with the correct order and new status. */
    public function update_order_status_dispatches_order_status_changed_event_with_correct_arguments(): void
    {
        Event::fake();
        $order = CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);

        $this->service->updateOrderStatus($order->order_id, OrderStatus::Processing);

        Event::assertDispatched(
            OrderStatusChanged::class,
            fn ($event) => $event->order->order_id === $order->order_id
                && $event->newStatus === OrderStatus::Processing,
        );
    }

    #[Test]
    /** Throws ModelNotFoundException when the order does not exist. */
    public function update_order_status_throws_model_not_found_when_order_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->updateOrderStatus(99999, OrderStatus::Processing);
    }

    #[Test]
    /** Persists a new note against the order. */
    public function add_order_note_persists_note_against_order(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->service->addOrderNote($order->order_id, $staff->staff_id, 'Customer called about order.');

        $this->assertDatabaseHas('order_notes', ['order_id' => $order->order_id]);
    }

    #[Test]
    /** Records the ID of the authoring staff member on the note. */
    public function add_order_note_records_authoring_staff_id(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->service->addOrderNote($order->order_id, $staff->staff_id, 'A note.');

        $this->assertDatabaseHas('order_notes', [
            'order_id' => $order->order_id,
            'staff_id' => $staff->staff_id,
        ]);
    }

    #[Test]
    /** Returns an OrderNote instance with the correct order and staff IDs. */
    public function add_order_note_returns_order_note_instance_with_correct_ids(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $note = $this->service->addOrderNote($order->order_id, $staff->staff_id, 'My note.');

        $this->assertEquals($order->order_id, $note->order_id);
        $this->assertEquals($staff->staff_id, $note->staff_id);
    }

    #[Test]
    /** Empty note text (below minimum) is rejected. */
    public function add_order_note_throws_when_note_text_is_empty(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->addOrderNote($order->order_id, $staff->staff_id, '');
    }

    #[Test]
    /** Whitespace-only note text is rejected. */
    public function add_order_note_throws_when_note_text_is_whitespace_only(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->addOrderNote($order->order_id, $staff->staff_id, '   ');
    }

    #[Test]
    /** Note of 1 character (minimum) is accepted. */
    public function add_order_note_accepts_note_of_one_character(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->service->addOrderNote($order->order_id, $staff->staff_id, 'a');

        $this->assertDatabaseCount('order_notes', 1);
    }

    #[Test]
    /** Note of 500 characters (in-range) is accepted. */
    public function add_order_note_accepts_note_of_five_hundred_characters(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->service->addOrderNote($order->order_id, $staff->staff_id, str_repeat('a', 500));

        $this->assertDatabaseCount('order_notes', 1);
    }

    #[Test]
    /** Note of 1000 characters (maximum) is accepted. */
    public function add_order_note_accepts_note_of_one_thousand_characters(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->service->addOrderNote($order->order_id, $staff->staff_id, str_repeat('a', 1000));

        $this->assertDatabaseCount('order_notes', 1);
    }

    #[Test]
    /** Note of 1001 characters (above maximum) is rejected. */
    public function add_order_note_throws_when_note_exceeds_one_thousand_characters(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->addOrderNote($order->order_id, $staff->staff_id, str_repeat('a', 1001));
    }

    #[Test]
    /** Throws ModelNotFoundException when the order does not exist. */
    public function add_order_note_throws_model_not_found_when_order_does_not_exist(): void
    {
        $staff = Staff::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->addOrderNote(99999, $staff->staff_id, 'A note.');
    }

    #[Test]
    /** Assigns an active staff member to the order and records the assignment date. */
    public function reassign_order_assigns_active_staff_member_to_order(): void
    {
        $assignee = Staff::factory()->create();
        $order = CustomerOrder::factory()->create(['assigned_staff_id' => null]);

        $this->service->reassignOrder($order->order_id, $assignee->staff_id);

        $order->refresh();
        $this->assertEquals($assignee->staff_id, $order->assigned_staff_id);
        $this->assertNotNull($order->staff_assignment_date);
    }

    #[Test]
    /** Throws ValidationException when the target staff member is inactive. */
    public function reassign_order_throws_when_target_staff_is_inactive(): void
    {
        $inactive = Staff::factory()->inactive()->create();
        $order = CustomerOrder::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->reassignOrder($order->order_id, $inactive->staff_id);
    }

    #[Test]
    /** Throws ValidationException when the target staff member does not exist. */
    public function reassign_order_throws_when_target_staff_does_not_exist(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service->reassignOrder($order->order_id, 99999);
    }

    #[Test]
    /** Throws ModelNotFoundException when the order does not exist. */
    public function reassign_order_throws_model_not_found_when_order_does_not_exist(): void
    {
        $assignee = Staff::factory()->create();

        $this->expectException(ModelNotFoundException::class);

        $this->service->reassignOrder(99999, $assignee->staff_id);
    }
}