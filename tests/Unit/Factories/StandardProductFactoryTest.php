<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductVisibilityStatus;
use App\Models\StandardProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for StandardProductFactory.
 *
 * Covers the default state, visibility, category, and image states, and boundary
 * states for product_name (2‑100), display_price (0‑100,000), and
 * description (1‑2000, nullable).
 */
class StandardProductFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a StandardProduct record. */
    public function default_state_creates_standard_product_record(): void
    {
        $product = StandardProduct::factory()->create();

        $this->assertInstanceOf(StandardProduct::class, $product);
        $this->assertDatabaseHas('standard_products', ['product_id' => $product->product_id]);
    }

    #[Test]
    /** Default state sets visibility_status to Active. */
    public function default_state_sets_visibility_status_to_active(): void
    {
        $product = StandardProduct::factory()->create();

        $this->assertInstanceOf(ProductVisibilityStatus::class, $product->visibility_status);
        $this->assertSame(ProductVisibilityStatus::Active, $product->visibility_status);
    }

    #[Test]
    /** Default state creates a linked category. */
    public function default_state_creates_a_linked_category(): void
    {
        $product = StandardProduct::factory()->create();

        $this->assertNotNull($product->category_id);
        $this->assertDatabaseHas('product_categories', ['category_id' => $product->category_id]);
    }

    // -------------------------------------------------------------------------
    // Visibility, category, and image states
    // -------------------------------------------------------------------------

    #[Test]
    /** inactive state sets visibility_status to Inactive. */
    public function inactive_state_sets_visibility_status_to_inactive(): void
    {
        $product = StandardProduct::factory()->inactive()->create();

        $this->assertSame(ProductVisibilityStatus::Inactive, $product->visibility_status);
        $this->assertDatabaseHas('standard_products', [
            'product_id'        => $product->product_id,
            'visibility_status' => ProductVisibilityStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** uncategorised state sets category_id to null. */
    public function uncategorised_state_sets_category_id_to_null(): void
    {
        $product = StandardProduct::factory()->uncategorised()->create();

        $this->assertNull($product->category_id);
    }

    #[Test]
    /** withoutImage state sets image_reference to null. */
    public function without_image_state_sets_image_reference_to_null(): void
    {
        $product = StandardProduct::factory()->withoutImage()->create();

        $this->assertNull($product->image_reference);
    }

    // -------------------------------------------------------------------------
    // Product name boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** nameMinLength state sets product_name to 2 characters (minimum). */
    public function name_min_length_state_sets_name_to_two_characters(): void
    {
        $product = StandardProduct::factory()->nameMinLength()->create();

        $this->assertSame(2, strlen($product->product_name));
    }

    #[Test]
    /** nameMidLength state sets product_name to 51 characters (in‑range). */
    public function name_mid_length_state_sets_name_to_fifty_one_characters(): void
    {
        $product = StandardProduct::factory()->nameMidLength()->create();

        $this->assertSame(51, strlen($product->product_name));
    }

    #[Test]
    /** nameMaxLength state sets product_name to 100 characters (maximum). */
    public function name_max_length_state_sets_name_to_one_hundred_characters(): void
    {
        $product = StandardProduct::factory()->nameMaxLength()->create();

        $this->assertSame(100, strlen($product->product_name));
    }

    // -------------------------------------------------------------------------
    // Product name boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** nameTooShort state sets product_name to 1 character (below minimum). */
    public function name_too_short_state_sets_name_to_one_character(): void
    {
        $product = StandardProduct::factory()->nameTooShort()->make();

        $this->assertSame(1, strlen($product->product_name));
    }

    #[Test]
    /** nameTooLong state sets product_name to 101 characters (above maximum). */
    public function name_too_long_state_sets_name_to_one_hundred_one_characters(): void
    {
        $product = StandardProduct::factory()->nameTooLong()->make();

        $this->assertSame(101, strlen($product->product_name));
    }

    // -------------------------------------------------------------------------
    // Display price boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** free state sets display_price to 0.00 (minimum). */
    public function free_state_sets_display_price_to_zero(): void
    {
        $product = StandardProduct::factory()->free()->create();

        $this->assertEquals(0.00, $product->display_price);
    }

    #[Test]
    /** midPrice state sets display_price to 25.00 (in‑range). */
    public function mid_price_state_sets_display_price_to_twenty_five(): void
    {
        $product = StandardProduct::factory()->midPrice()->create();

        $this->assertEquals(25.00, $product->display_price);
    }

    #[Test]
    /** maxPrice state sets display_price to 100,000.00 (maximum). */
    public function max_price_state_sets_display_price_to_one_hundred_thousand(): void
    {
        $product = StandardProduct::factory()->maxPrice()->create();

        $this->assertEquals(100000.00, $product->display_price);
    }

    // -------------------------------------------------------------------------
    // Display price boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** negativePrice state sets display_price to -0.01 (below minimum). */
    public function negative_price_state_sets_display_price_to_negative(): void
    {
        $product = StandardProduct::factory()->negativePrice()->make();

        $this->assertEquals(-0.01, $product->display_price);
    }

    #[Test]
    /** aboveMaxPrice state sets display_price to 100,000.01 (above maximum). */
    public function above_max_price_state_sets_display_price_above_maximum(): void
    {
        $product = StandardProduct::factory()->aboveMaxPrice()->make();

        $this->assertEquals(100000.01, $product->display_price);
    }

    // -------------------------------------------------------------------------
    // Description boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionMinLength state sets description to 1 character (minimum). */
    public function description_min_length_state_sets_description_to_one_character(): void
    {
        $product = StandardProduct::factory()->descriptionMinLength()->create();

        $this->assertSame(1, strlen($product->description));
    }

    #[Test]
    /** descriptionMidLength state sets description to 1000 characters (in‑range). */
    public function description_mid_length_state_sets_description_to_one_thousand_characters(): void
    {
        $product = StandardProduct::factory()->descriptionMidLength()->create();

        $this->assertSame(1000, strlen($product->description));
    }

    #[Test]
    /** descriptionMaxLength state sets description to 2000 characters (maximum). */
    public function description_max_length_state_sets_description_to_two_thousand_characters(): void
    {
        $product = StandardProduct::factory()->descriptionMaxLength()->create();

        $this->assertSame(2000, strlen($product->description));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionTooLong state sets description to 2001 characters (above maximum). */
    public function description_too_long_state_sets_description_to_two_thousand_one_characters(): void
    {
        $product = StandardProduct::factory()->descriptionTooLong()->make();

        $this->assertSame(2001, strlen($product->description));
    }
}