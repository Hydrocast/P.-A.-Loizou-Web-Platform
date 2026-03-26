<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\OrderStatus;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use App\Models\OrderNote;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the CustomerOrder model.
 *
 * Covers model configuration, relationship structure and data resolution,
 * and business logic for isTerminal().
 *
 * isTerminal() delegates entirely to OrderStatus::isTerminal().
 * Boundary values mirror OrderStatusTest:
 * - Pending: false
 * - Processing: false
 * - ReadyForPickup: false
 * - Completed: true
 * - Cancelled: true
 */
class CustomerOrderTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the customer_orders table. */
    public function model_uses_customer_orders_table(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('customer_orders', $order->getTable());
    }

    #[Test]
    /** Primary key is order_id. */
    public function primary_key_is_order_id(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('order_id', $order->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('int', $order->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $order = new CustomerOrder();

        $this->assertTrue($order->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $order = new CustomerOrder();

        $this->assertFalse($order->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $order = new CustomerOrder();
        $fillable = $order->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('customer_name', $fillable);
        $this->assertContains('customer_email', $fillable);
        $this->assertContains('customer_phone', $fillable);
        $this->assertContains('order_creation_timestamp', $fillable);
        $this->assertContains('order_status', $fillable);
        $this->assertContains('net_amount', $fillable);
        $this->assertContains('vat_amount', $fillable);
        $this->assertContains('total_amount', $fillable);
        $this->assertContains('vat_rate', $fillable);
        $this->assertContains('assigned_staff_id', $fillable);
        $this->assertContains('staff_assignment_date', $fillable);
    }

    #[Test]
    /** order_status is cast to OrderStatus enum. */
    public function order_status_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame(OrderStatus::class, $order->getCasts()['order_status']);
    }

    #[Test]
    /** order_creation_timestamp is cast to datetime. */
    public function order_creation_timestamp_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('datetime', $order->getCasts()['order_creation_timestamp']);
    }

    #[Test]
    /** staff_assignment_date is cast to datetime. */
    public function staff_assignment_date_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('datetime', $order->getCasts()['staff_assignment_date']);
    }

    #[Test]
    /** net_amount is cast to decimal with 2 places. */
    public function net_amount_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('decimal:2', $order->getCasts()['net_amount']);
    }

    #[Test]
    /** vat_amount is cast to decimal with 2 places. */
    public function vat_amount_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('decimal:2', $order->getCasts()['vat_amount']);
    }

    #[Test]
    /** total_amount is cast to decimal with 2 places. */
    public function total_amount_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('decimal:2', $order->getCasts()['total_amount']);
    }

    #[Test]
    /** vat_rate is cast to decimal with 2 places. */
    public function vat_rate_cast_is_configured(): void
    {
        $order = new CustomerOrder();

        $this->assertSame('decimal:2', $order->getCasts()['vat_rate']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – customer()
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() returns a BelongsTo relation. */
    public function customer_returns_belongs_to_relation(): void
    {
        $relation = (new CustomerOrder())->customer();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** customer() uses customer_id as foreign key. */
    public function customer_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new CustomerOrder())->customer();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** customer() relates to Customer model. */
    public function customer_relates_to_customer_model(): void
    {
        $relation = (new CustomerOrder())->customer();

        $this->assertInstanceOf(Customer::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – assignedStaff()
    // -------------------------------------------------------------------------

    #[Test]
    /** assignedStaff() returns a BelongsTo relation. */
    public function assigned_staff_returns_belongs_to_relation(): void
    {
        $relation = (new CustomerOrder())->assignedStaff();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** assignedStaff() uses assigned_staff_id as foreign key. */
    public function assigned_staff_uses_assigned_staff_id_as_foreign_key(): void
    {
        $relation = (new CustomerOrder())->assignedStaff();

        $this->assertSame('assigned_staff_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** assignedStaff() relates to Staff model. */
    public function assigned_staff_relates_to_staff_model(): void
    {
        $relation = (new CustomerOrder())->assignedStaff();

        $this->assertInstanceOf(Staff::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – items()
    // -------------------------------------------------------------------------

    #[Test]
    /** items() returns a HasMany relation. */
    public function items_returns_has_many_relation(): void
    {
        $relation = (new CustomerOrder())->items();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** items() uses order_id as foreign key. */
    public function items_uses_order_id_as_foreign_key(): void
    {
        $relation = (new CustomerOrder())->items();

        $this->assertSame('order_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** items() relates to OrderItem model. */
    public function items_relates_to_order_item_model(): void
    {
        $relation = (new CustomerOrder())->items();

        $this->assertInstanceOf(OrderItem::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – notes()
    // -------------------------------------------------------------------------

    #[Test]
    /** notes() returns a HasMany relation. */
    public function notes_returns_has_many_relation(): void
    {
        $relation = (new CustomerOrder())->notes();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** notes() uses order_id as foreign key. */
    public function notes_uses_order_id_as_foreign_key(): void
    {
        $relation = (new CustomerOrder())->notes();

        $this->assertSame('order_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** notes() relates to OrderNote model. */
    public function notes_relates_to_order_note_model(): void
    {
        $relation = (new CustomerOrder())->notes();

        $this->assertInstanceOf(OrderNote::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() resolves to the customer who placed this order. */
    public function customer_resolves_to_the_placing_customer(): void
    {
        $customer = Customer::factory()->create();
        $order = CustomerOrder::factory()->create(['customer_id' => $customer->customer_id]);

        $resolved = $order->customer;

        $this->assertInstanceOf(Customer::class, $resolved);
        $this->assertSame($customer->customer_id, $resolved->customer_id);
    }

    #[Test]
    /** assignedStaff() resolves to the staff member assigned to this order. */
    public function assigned_staff_resolves_to_the_assigned_staff_member(): void
    {
        $staff = Staff::factory()->create();
        $order = CustomerOrder::factory()->assignedTo($staff)->create();

        $resolved = $order->assignedStaff;

        $this->assertInstanceOf(Staff::class, $resolved);
        $this->assertSame($staff->staff_id, $resolved->staff_id);
    }

    #[Test]
    /** assignedStaff() resolves to null when order has no assigned staff. */
    public function assigned_staff_resolves_to_null_when_unassigned(): void
    {
        $order = CustomerOrder::factory()->create();

        $this->assertNull($order->assignedStaff);
    }

    #[Test]
    /** items() resolves to all items in this order. */
    public function items_resolves_to_the_orders_items(): void
    {
        $order = CustomerOrder::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->order_id]);

        $this->assertCount(3, $order->items);
        $order->items->each(
            fn ($item) => $this->assertSame($order->order_id, $item->order_id)
        );
    }

    #[Test]
    /** items() excludes items belonging to other orders. */
    public function items_excludes_items_from_other_orders(): void
    {
        $order = CustomerOrder::factory()->create();
        OrderItem::factory()->count(2)->create(['order_id' => $order->order_id]);
        OrderItem::factory()->count(3)->create();

        $this->assertCount(2, $order->items);
    }

    #[Test]
    /** notes() resolves to all notes associated with this order. */
    public function notes_resolves_to_the_orders_notes(): void
    {
        $order = CustomerOrder::factory()->create();
        OrderNote::factory()->count(2)->create(['order_id' => $order->order_id]);

        $this->assertCount(2, $order->notes);
        $order->notes->each(
            fn ($note) => $this->assertSame($order->order_id, $note->order_id)
        );
    }

    #[Test]
    /** notes() excludes notes belonging to other orders. */
    public function notes_excludes_notes_from_other_orders(): void
    {
        $order = CustomerOrder::factory()->create();
        OrderNote::factory()->count(2)->create(['order_id' => $order->order_id]);
        OrderNote::factory()->count(3)->create();

        $this->assertCount(2, $order->notes);
    }

    #[Test]
    /** notes() returns notes ordered ascending by note_timestamp. */
    public function notes_are_ordered_ascending_by_note_timestamp(): void
    {
        $order = CustomerOrder::factory()->create();

        OrderNote::factory()->create([
            'order_id' => $order->order_id,
            'note_timestamp' => now()->addMinutes(10),
        ]);
        OrderNote::factory()->create([
            'order_id' => $order->order_id,
            'note_timestamp' => now(),
        ]);
        OrderNote::factory()->create([
            'order_id' => $order->order_id,
            'note_timestamp' => now()->addMinutes(5),
        ]);

        $timestamps = $order->notes->pluck('note_timestamp');

        $this->assertTrue($timestamps[0]->lessThan($timestamps[1]));
        $this->assertTrue($timestamps[1]->lessThan($timestamps[2]));
    }

    // -------------------------------------------------------------------------
    // isTerminal()
    // -------------------------------------------------------------------------

    #[Test]
    /** isTerminal() returns false when order status is Pending. */
    public function is_terminal_returns_false_for_pending_status(): void
    {
        $order = CustomerOrder::factory()->create(['order_status' => OrderStatus::Pending]);
    
        $this->assertFalse($order->isTerminal());
    }

    #[Test]
    /** isTerminal() returns false when order status is Processing. */
    public function is_terminal_returns_false_for_processing_status(): void
    {
        $order = CustomerOrder::factory()->processing()->create();

        $this->assertFalse($order->isTerminal());
    }

    #[Test]
    /** isTerminal() returns false when order status is ReadyForPickup. */
    public function is_terminal_returns_false_for_ready_for_pickup_status(): void
    {
        $order = CustomerOrder::factory()->readyForPickup()->create();

        $this->assertFalse($order->isTerminal());
    }

    #[Test]
    /** isTerminal() returns true when order status is Completed. */
    public function is_terminal_returns_true_for_completed_status(): void
    {
        $order = CustomerOrder::factory()->completed()->create();

        $this->assertTrue($order->isTerminal());
    }

    #[Test]
    /** isTerminal() returns true when order status is Cancelled. */
    public function is_terminal_returns_true_for_cancelled_status(): void
    {
        $order = CustomerOrder::factory()->cancelled()->create();

        $this->assertTrue($order->isTerminal());
    }
}