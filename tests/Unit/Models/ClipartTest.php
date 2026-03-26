<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Clipart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the Clipart model.
 *
 * Covers model configuration. Clipart has no relationships.
 */
class ClipartTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the clipart table. */
    public function model_uses_clipart_table(): void
    {
        $clipart = new Clipart();

        $this->assertSame('clipart', $clipart->getTable());
    }

    #[Test]
    /** Primary key is clipart_id. */
    public function primary_key_is_clipart_id(): void
    {
        $clipart = new Clipart();

        $this->assertSame('clipart_id', $clipart->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $clipart = new Clipart();

        $this->assertSame('int', $clipart->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $clipart = new Clipart();

        $this->assertTrue($clipart->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $clipart = new Clipart();

        $this->assertFalse($clipart->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $clipart = new Clipart();
        $fillable = $clipart->getFillable();

        $this->assertContains('clipart_name', $fillable);
        $this->assertContains('image_reference', $fillable);
    }
}