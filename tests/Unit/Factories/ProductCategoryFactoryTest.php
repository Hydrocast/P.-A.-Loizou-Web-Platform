<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ProductCategoryFactory.
 *
 * Covers the default state, withoutDescription state, and boundary states for
 * category_name (2‑50) and description (1‑500, nullable).
 */
class ProductCategoryFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a ProductCategory record. */
    public function default_state_creates_product_category_record(): void
    {
        $category = ProductCategory::factory()->create();

        $this->assertInstanceOf(ProductCategory::class, $category);
        $this->assertDatabaseHas('product_categories', ['category_id' => $category->category_id]);
    }

    #[Test]
    /** Default state sets a non-empty category name. */
    public function default_state_sets_non_empty_category_name(): void
    {
        $category = ProductCategory::factory()->create();

        $this->assertNotEmpty($category->category_name);
    }

    // -------------------------------------------------------------------------
    // withoutDescription state
    // -------------------------------------------------------------------------

    #[Test]
    /** withoutDescription state sets description to null. */
    public function without_description_state_sets_description_to_null(): void
    {
        $category = ProductCategory::factory()->withoutDescription()->create();

        $this->assertNull($category->description);
    }

    // -------------------------------------------------------------------------
    // Category name boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** nameMinLength state sets category_name to 2 characters (minimum). */
    public function name_min_length_state_sets_name_to_two_characters(): void
    {
        $category = ProductCategory::factory()->nameMinLength()->create();

        $this->assertSame(2, strlen($category->category_name));
    }

    #[Test]
    /** nameMidLength state sets category_name to 26 characters (in‑range). */
    public function name_mid_length_state_sets_name_to_twenty_six_characters(): void
    {
        $category = ProductCategory::factory()->nameMidLength()->create();

        $this->assertSame(26, strlen($category->category_name));
    }

    #[Test]
    /** nameMaxLength state sets category_name to 50 characters (maximum). */
    public function name_max_length_state_sets_name_to_fifty_characters(): void
    {
        $category = ProductCategory::factory()->nameMaxLength()->create();

        $this->assertSame(50, strlen($category->category_name));
    }

    // -------------------------------------------------------------------------
    // Category name boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** nameTooShort state sets category_name to 1 character (below minimum). */
    public function name_too_short_state_sets_name_to_one_character(): void
    {
        $category = ProductCategory::factory()->nameTooShort()->make();

        $this->assertSame(1, strlen($category->category_name));
    }

    #[Test]
    /** nameTooLong state sets category_name to 51 characters (above maximum). */
    public function name_too_long_state_sets_name_to_fifty_one_characters(): void
    {
        $category = ProductCategory::factory()->nameTooLong()->make();

        $this->assertSame(51, strlen($category->category_name));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionMinLength state sets description to 1 character (minimum). */
    public function description_min_length_state_sets_description_to_one_character(): void
    {
        $category = ProductCategory::factory()->descriptionMinLength()->create();

        $this->assertSame(1, strlen($category->description));
    }

    #[Test]
    /** descriptionMidLength state sets description to 250 characters (in‑range). */
    public function description_mid_length_state_sets_description_to_two_hundred_fifty_characters(): void
    {
        $category = ProductCategory::factory()->descriptionMidLength()->create();

        $this->assertSame(250, strlen($category->description));
    }

    #[Test]
    /** descriptionMaxLength state sets description to 500 characters (maximum). */
    public function description_max_length_state_sets_description_to_five_hundred_characters(): void
    {
        $category = ProductCategory::factory()->descriptionMaxLength()->create();

        $this->assertSame(500, strlen($category->description));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionTooLong state sets description to 501 characters (above maximum). */
    public function description_too_long_state_sets_description_to_five_hundred_one_characters(): void
    {
        $category = ProductCategory::factory()->descriptionTooLong()->make();

        $this->assertSame(501, strlen($category->description));
    }
}