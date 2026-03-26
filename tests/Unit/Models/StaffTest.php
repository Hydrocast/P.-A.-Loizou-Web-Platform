<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\AccountStatus;
use App\Enums\StaffRole;
use App\Models\CustomerOrder;
use App\Models\OrderNote;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the Staff model.
 *
 * Covers model configuration, hidden field serialisation, authentication
 * configuration, relationship structure and data resolution, and business
 * logic for isActive() and isAdministrator().
 */
class StaffTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the staff table. */
    public function model_uses_staff_table(): void
    {
        $staff = new Staff();

        $this->assertSame('staff', $staff->getTable());
    }

    #[Test]
    /** Primary key is staff_id. */
    public function primary_key_is_staff_id(): void
    {
        $staff = new Staff();

        $this->assertSame('staff_id', $staff->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $staff = new Staff();

        $this->assertSame('int', $staff->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $staff = new Staff();

        $this->assertTrue($staff->incrementing);
    }

    #[Test]
    /** Timestamps are enabled. */
    public function timestamps_are_enabled(): void
    {
        $staff = new Staff();

        $this->assertTrue($staff->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $staff = new Staff();
        $fillable = $staff->getFillable();

        $this->assertContains('username', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
        $this->assertContains('full_name', $fillable);
        $this->assertContains('account_status', $fillable);
    }

    #[Test]
    /** role is cast to StaffRole enum. */
    public function role_cast_is_configured(): void
    {
        $staff = new Staff();

        $this->assertSame(StaffRole::class, $staff->getCasts()['role']);
    }

    #[Test]
    /** account_status is cast to AccountStatus enum. */
    public function account_status_cast_is_configured(): void
    {
        $staff = new Staff();

        $this->assertSame(AccountStatus::class, $staff->getCasts()['account_status']);
    }

    // -------------------------------------------------------------------------
    // Hidden field serialisation
    // -------------------------------------------------------------------------

    #[Test]
    /** password is hidden from array serialisation. */
    public function password_is_hidden_from_array_serialisation(): void
    {
        $staff = Staff::factory()->create();
        $array = $staff->toArray();

        $this->assertArrayNotHasKey('password', $array);
    }

    // -------------------------------------------------------------------------
    // Authentication configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** getAuthIdentifierName returns staff_id. */
    public function get_auth_identifier_name_returns_staff_id(): void
    {
        $staff = new Staff();

        $this->assertSame('staff_id', $staff->getAuthIdentifierName());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – assignedOrders()
    // -------------------------------------------------------------------------

    #[Test]
    /** assignedOrders() returns a HasMany relation. */
    public function assigned_orders_returns_has_many_relation(): void
    {
        $relation = (new Staff())->assignedOrders();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** assignedOrders() uses assigned_staff_id as foreign key. */
    public function assigned_orders_uses_assigned_staff_id_as_foreign_key(): void
    {
        $relation = (new Staff())->assignedOrders();

        $this->assertSame('assigned_staff_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** assignedOrders() relates to CustomerOrder model. */
    public function assigned_orders_relates_to_customer_order_model(): void
    {
        $relation = (new Staff())->assignedOrders();

        $this->assertInstanceOf(CustomerOrder::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – orderNotes()
    // -------------------------------------------------------------------------

    #[Test]
    /** orderNotes() returns a HasMany relation. */
    public function order_notes_returns_has_many_relation(): void
    {
        $relation = (new Staff())->orderNotes();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** orderNotes() uses staff_id as foreign key. */
    public function order_notes_uses_staff_id_as_foreign_key(): void
    {
        $relation = (new Staff())->orderNotes();

        $this->assertSame('staff_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** orderNotes() relates to OrderNote model. */
    public function order_notes_relates_to_order_note_model(): void
    {
        $relation = (new Staff())->orderNotes();

        $this->assertInstanceOf(OrderNote::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** assignedOrders() resolves to orders assigned to the staff member. */
    public function assigned_orders_resolves_to_orders_assigned_to_staff(): void
    {
        $staff = Staff::factory()->create();
        CustomerOrder::factory()->count(3)->create(['assigned_staff_id' => $staff->staff_id]);

        $this->assertCount(3, $staff->assignedOrders);
        $staff->assignedOrders->each(
            fn ($order) => $this->assertSame($staff->staff_id, $order->assigned_staff_id)
        );
    }

    #[Test]
    /** assignedOrders() excludes orders assigned to other staff. */
    public function assigned_orders_excludes_orders_assigned_to_other_staff(): void
    {
        $staff = Staff::factory()->create();
        CustomerOrder::factory()->count(2)->create(['assigned_staff_id' => $staff->staff_id]);
        CustomerOrder::factory()->count(3)->create();

        $this->assertCount(2, $staff->assignedOrders);
    }

    #[Test]
    /** assignedOrders() resolves to empty collection when no orders are assigned. */
    public function assigned_orders_resolves_to_empty_collection_when_no_orders_assigned(): void
    {
        $staff = Staff::factory()->create();

        $this->assertCount(0, $staff->assignedOrders);
    }

    #[Test]
    /** orderNotes() resolves to notes written by the staff member. */
    public function order_notes_resolves_to_notes_written_by_staff(): void
    {
        $staff = Staff::factory()->create();
        OrderNote::factory()->count(4)->create(['staff_id' => $staff->staff_id]);

        $this->assertCount(4, $staff->orderNotes);
        $staff->orderNotes->each(
            fn ($note) => $this->assertSame($staff->staff_id, $note->staff_id)
        );
    }

    #[Test]
    /** orderNotes() excludes notes written by other staff. */
    public function order_notes_excludes_notes_written_by_other_staff(): void
    {
        $staff = Staff::factory()->create();
        OrderNote::factory()->count(2)->create(['staff_id' => $staff->staff_id]);
        OrderNote::factory()->count(3)->create();

        $this->assertCount(2, $staff->orderNotes);
    }

    #[Test]
    /** orderNotes() resolves to empty collection when no notes exist. */
    public function order_notes_resolves_to_empty_collection_when_no_notes_exist(): void
    {
        $staff = Staff::factory()->create();

        $this->assertCount(0, $staff->orderNotes);
    }

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    #[Test]
    /** isActive() returns true when account status is Active. */
    public function is_active_returns_true_for_active_account(): void
    {
        $staff = Staff::factory()->create();

        $this->assertTrue($staff->isActive());
    }

    #[Test]
    /** isActive() returns false when account status is Inactive. */
    public function is_active_returns_false_for_inactive_account(): void
    {
        $staff = Staff::factory()->inactive()->create();

        $this->assertFalse($staff->isActive());
    }

    // -------------------------------------------------------------------------
    // isAdministrator()
    // -------------------------------------------------------------------------

    #[Test]
    /** isAdministrator() returns false when role is Employee. */
    public function is_administrator_returns_false_for_employee_role(): void
    {
        $staff = Staff::factory()->create();

        $this->assertFalse($staff->isAdministrator());
    }

    #[Test]
    /** isAdministrator() returns true when role is Administrator. */
    public function is_administrator_returns_true_for_administrator_role(): void
    {
        $staff = Staff::factory()->administrator()->create();

        $this->assertTrue($staff->isAdministrator());
    }
}