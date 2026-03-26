<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CustomerOrder;
use App\Models\OrderNote;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the OrderNote model.
 *
 * Covers model configuration, and both relationship structures and data
 * resolution for order() and staff().
 */
class OrderNoteTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the order_notes table. */
    public function model_uses_order_notes_table(): void
    {
        $note = new OrderNote();

        $this->assertSame('order_notes', $note->getTable());
    }

    #[Test]
    /** Primary key is note_id. */
    public function primary_key_is_note_id(): void
    {
        $note = new OrderNote();

        $this->assertSame('note_id', $note->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $note = new OrderNote();

        $this->assertSame('int', $note->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $note = new OrderNote();

        $this->assertTrue($note->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $note = new OrderNote();

        $this->assertFalse($note->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $note = new OrderNote();
        $fillable = $note->getFillable();

        $this->assertContains('order_id', $fillable);
        $this->assertContains('staff_id', $fillable);
        $this->assertContains('note_text', $fillable);
        $this->assertContains('note_timestamp', $fillable);
    }

    #[Test]
    /** note_timestamp is cast to datetime. */
    public function note_timestamp_cast_is_configured(): void
    {
        $note = new OrderNote();

        $this->assertSame('datetime', $note->getCasts()['note_timestamp']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – order()
    // -------------------------------------------------------------------------

    #[Test]
    /** order() returns a BelongsTo relation. */
    public function order_returns_belongs_to_relation(): void
    {
        $relation = (new OrderNote())->order();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** order() uses order_id as foreign key. */
    public function order_uses_order_id_as_foreign_key(): void
    {
        $relation = (new OrderNote())->order();

        $this->assertSame('order_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** order() relates to CustomerOrder model. */
    public function order_relates_to_customer_order_model(): void
    {
        $relation = (new OrderNote())->order();

        $this->assertInstanceOf(CustomerOrder::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – staff()
    // -------------------------------------------------------------------------

    #[Test]
    /** staff() returns a BelongsTo relation. */
    public function staff_returns_belongs_to_relation(): void
    {
        $relation = (new OrderNote())->staff();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** staff() uses staff_id as foreign key. */
    public function staff_uses_staff_id_as_foreign_key(): void
    {
        $relation = (new OrderNote())->staff();

        $this->assertSame('staff_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** staff() relates to Staff model. */
    public function staff_relates_to_staff_model(): void
    {
        $relation = (new OrderNote())->staff();

        $this->assertInstanceOf(Staff::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** order() resolves to the customer order this note belongs to. */
    public function order_resolves_to_the_associated_customer_order(): void
    {
        $order = CustomerOrder::factory()->create();
        $note = OrderNote::factory()->create(['order_id' => $order->order_id]);

        $resolved = $note->order;

        $this->assertInstanceOf(CustomerOrder::class, $resolved);
        $this->assertSame($order->order_id, $resolved->order_id);
    }

    #[Test]
    /** staff() resolves to the staff member who wrote this note. */
    public function staff_resolves_to_the_authoring_staff_member(): void
    {
        $staff = Staff::factory()->create();
        $note = OrderNote::factory()->create(['staff_id' => $staff->staff_id]);

        $resolved = $note->staff;

        $this->assertInstanceOf(Staff::class, $resolved);
        $this->assertSame($staff->staff_id, $resolved->staff_id);
    }
}