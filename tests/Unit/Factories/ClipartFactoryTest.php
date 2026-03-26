<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Clipart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ClipartFactory.
 *
 * Covers the default state.
 */
class ClipartFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a Clipart record. */
    public function default_state_creates_clipart_record(): void
    {
        $clipart = Clipart::factory()->create();

        $this->assertInstanceOf(Clipart::class, $clipart);
        $this->assertDatabaseHas('clipart', ['clipart_id' => $clipart->clipart_id]);
    }

    #[Test]
    /** Default state sets a non-empty clipart name. */
    public function default_state_sets_non_empty_clipart_name(): void
    {
        $clipart = Clipart::factory()->create();

        $this->assertNotEmpty($clipart->clipart_name);
    }

    #[Test]
    /** Default state sets a non-empty image reference. */
    public function default_state_sets_non_empty_image_reference(): void
    {
        $clipart = Clipart::factory()->create();

        $this->assertNotEmpty($clipart->image_reference);
    }
}