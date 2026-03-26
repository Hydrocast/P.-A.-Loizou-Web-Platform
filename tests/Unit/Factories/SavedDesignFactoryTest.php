<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\SavedDesign;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for SavedDesignFactory.
 *
 * Covers the default state, withoutPreview state, and boundary states for
 * design_name (1‑100). The design_data is a FabricJS JSON string.
 */
class SavedDesignFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a SavedDesign record. */
    public function default_state_creates_saved_design_record(): void
    {
        $design = SavedDesign::factory()->create();

        $this->assertInstanceOf(SavedDesign::class, $design);
        $this->assertDatabaseHas('saved_designs', ['design_id' => $design->design_id]);
    }

    #[Test]
    /** Default state creates a linked customer. */
    public function default_state_creates_a_linked_customer(): void
    {
        $design = SavedDesign::factory()->create();

        $this->assertNotNull($design->customer_id);
        $this->assertDatabaseHas('customers', ['customer_id' => $design->customer_id]);
    }

    #[Test]
    /** Default state creates a linked product. */
    public function default_state_creates_a_linked_product(): void
    {
        $design = SavedDesign::factory()->create();

        $this->assertNotNull($design->product_id);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $design->product_id]);
    }

    #[Test]
    /** Default state sets a non-null design data. */
    public function default_state_sets_non_null_design_data(): void
    {
        $design = SavedDesign::factory()->create();

        $this->assertNotNull($design->design_data);
    }

    // -------------------------------------------------------------------------
    // withoutPreview state
    // -------------------------------------------------------------------------

    #[Test]
    /** withoutPreview state sets preview_image_reference to null. */
    public function without_preview_state_sets_preview_image_reference_to_null(): void
    {
        $design = SavedDesign::factory()->withoutPreview()->create();

        $this->assertNull($design->preview_image_reference);
    }

    // -------------------------------------------------------------------------
    // Design name boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** designNameMinLength state sets design_name to 1 character (minimum). */
    public function design_name_min_length_state_sets_design_name_to_one_character(): void
    {
        $design = SavedDesign::factory()->designNameMinLength()->create();

        $this->assertSame(1, strlen($design->design_name));
    }

    #[Test]
    /** designNameMidLength state sets design_name to 50 characters (in‑range). */
    public function design_name_mid_length_state_sets_design_name_to_fifty_characters(): void
    {
        $design = SavedDesign::factory()->designNameMidLength()->create();

        $this->assertSame(50, strlen($design->design_name));
    }

    #[Test]
    /** designNameMaxLength state sets design_name to 100 characters (maximum). */
    public function design_name_max_length_state_sets_design_name_to_one_hundred_characters(): void
    {
        $design = SavedDesign::factory()->designNameMaxLength()->create();

        $this->assertSame(100, strlen($design->design_name));
    }

    // -------------------------------------------------------------------------
    // Design name boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** designNameEmpty state sets design_name to 0 characters (below minimum). */
    public function design_name_empty_state_sets_design_name_to_empty_string(): void
    {
        $design = SavedDesign::factory()->designNameEmpty()->make();

        $this->assertSame(0, strlen($design->design_name));
    }

    #[Test]
    /** designNameTooLong state sets design_name to 101 characters (above maximum). */
    public function design_name_too_long_state_sets_design_name_to_one_hundred_one_characters(): void
    {
        $design = SavedDesign::factory()->designNameTooLong()->make();

        $this->assertSame(101, strlen($design->design_name));
    }
}