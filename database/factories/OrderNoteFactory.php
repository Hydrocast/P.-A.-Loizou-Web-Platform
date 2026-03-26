<?php

namespace Database\Factories;

use App\Models\CustomerOrder;
use App\Models\OrderNote;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderNote>
 *
 * Creates order notes with valid default values.
 * Provides states for testing note_text boundaries.
 * Boundary values: note_text (1‑1000).
 */
class OrderNoteFactory extends Factory
{
    protected $model = OrderNote::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id'       => CustomerOrder::factory(),
            'staff_id'       => Staff::factory(),
            'note_text'      => $this->faker->sentences(2, true),
            'note_timestamp' => $this->faker->dateTimeBetween('-3 months', 'now'),
        ];
    }

    // -------------------------------------------------------------------------
    // Note text boundaries
    // -------------------------------------------------------------------------

    /** Note text of 0 characters (below minimum) is invalid. */
    public function noteTextEmpty(): static
    {
        return $this->state(fn () => ['note_text' => '']);
    }

    /** Note text of 1 character (minimum) is valid. */
    public function noteTextMinLength(): static
    {
        return $this->state(fn () => ['note_text' => 'a']);
    }

    /** Note text of 500 characters (in‑range) is valid. */
    public function noteTextMidLength(): static
    {
        return $this->state(fn () => ['note_text' => str_repeat('a', 500)]);
    }

    /** Note text of 1000 characters (maximum) is valid. */
    public function noteTextMaxLength(): static
    {
        return $this->state(fn () => ['note_text' => str_repeat('a', 1000)]);
    }

    /** Note text of 1001 characters (above maximum) is invalid. */
    public function noteTextTooLong(): static
    {
        return $this->state(fn () => ['note_text' => str_repeat('a', 1001)]);
    }
}