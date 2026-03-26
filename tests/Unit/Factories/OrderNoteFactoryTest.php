<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\OrderNote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for OrderNoteFactory.
 *
 * Covers the default state and boundary states for note_text (1‑1000).
 */
class OrderNoteFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates an OrderNote record. */
    public function default_state_creates_order_note_record(): void
    {
        $note = OrderNote::factory()->create();

        $this->assertInstanceOf(OrderNote::class, $note);
        $this->assertDatabaseHas('order_notes', ['note_id' => $note->note_id]);
    }

    #[Test]
    /** Default state creates a linked order. */
    public function default_state_creates_a_linked_order(): void
    {
        $note = OrderNote::factory()->create();

        $this->assertNotNull($note->order_id);
        $this->assertDatabaseHas('customer_orders', ['order_id' => $note->order_id]);
    }

    #[Test]
    /** Default state creates a linked staff member. */
    public function default_state_creates_a_linked_staff_member(): void
    {
        $note = OrderNote::factory()->create();

        $this->assertNotNull($note->staff_id);
        $this->assertDatabaseHas('staff', ['staff_id' => $note->staff_id]);
    }

    #[Test]
    /** Default state sets a non-empty note text. */
    public function default_state_sets_non_empty_note_text(): void
    {
        $note = OrderNote::factory()->create();

        $this->assertNotEmpty($note->note_text);
    }

    // -------------------------------------------------------------------------
    // Note text boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** noteTextMinLength state sets note_text to 1 character (minimum). */
    public function note_text_min_length_state_sets_note_text_to_one_character(): void
    {
        $note = OrderNote::factory()->noteTextMinLength()->create();

        $this->assertSame(1, strlen($note->note_text));
    }

    #[Test]
    /** noteTextMidLength state sets note_text to 500 characters (in‑range). */
    public function note_text_mid_length_state_sets_note_text_to_five_hundred_characters(): void
    {
        $note = OrderNote::factory()->noteTextMidLength()->create();

        $this->assertSame(500, strlen($note->note_text));
    }

    #[Test]
    /** noteTextMaxLength state sets note_text to 1000 characters (maximum). */
    public function note_text_max_length_state_sets_note_text_to_one_thousand_characters(): void
    {
        $note = OrderNote::factory()->noteTextMaxLength()->create();

        $this->assertSame(1000, strlen($note->note_text));
    }

    // -------------------------------------------------------------------------
    // Note text boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** noteTextEmpty state sets note_text to 0 characters (below minimum). */
    public function note_text_empty_state_sets_note_text_to_empty_string(): void
    {
        $note = OrderNote::factory()->noteTextEmpty()->make();

        $this->assertSame(0, strlen($note->note_text));
    }

    #[Test]
    /** noteTextTooLong state sets note_text to 1001 characters (above maximum). */
    public function note_text_too_long_state_sets_note_text_to_one_thousand_one_characters(): void
    {
        $note = OrderNote::factory()->noteTextTooLong()->make();

        $this->assertSame(1001, strlen($note->note_text));
    }
}