<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for CustomizablePrintProductFactory.
 *
 * Covers the default state, visibility and image states, template config state,
 * and boundary states for product_name (2‑100) and description (1‑2000, nullable).
 */
class CustomizablePrintProductFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a CustomizablePrintProduct record. */
    public function default_state_creates_customizable_print_product_record(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $product);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $product->product_id]);
    }

    #[Test]
    /** Default state sets visibility_status to Active. */
    public function default_state_sets_visibility_status_to_active(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        $this->assertInstanceOf(ProductVisibilityStatus::class, $product->visibility_status);
        $this->assertSame(ProductVisibilityStatus::Active, $product->visibility_status);
    }

    #[Test]
    /** Default state sets template_config to null. */
    public function default_state_sets_template_config_to_null(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        $this->assertNull($product->template_config);
    }

    // -------------------------------------------------------------------------
    // Visibility and image states
    // -------------------------------------------------------------------------

    #[Test]
    /** inactive state sets visibility_status to Inactive. */
    public function inactive_state_sets_visibility_status_to_inactive(): void
    {
        $product = CustomizablePrintProduct::factory()->inactive()->create();

        $this->assertSame(ProductVisibilityStatus::Inactive, $product->visibility_status);
        $this->assertDatabaseHas('customizable_print_products', [
            'product_id'        => $product->product_id,
            'visibility_status' => ProductVisibilityStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** withoutImage state sets image_reference to null. */
    public function without_image_state_sets_image_reference_to_null(): void
    {
        $product = CustomizablePrintProduct::factory()->withoutImage()->create();

        $this->assertNull($product->image_reference);
    }

    #[Test]
    /** withTemplateConfig state sets template_config to a non-null array. */
    public function with_template_config_state_sets_template_config_to_non_null_array(): void
    {
        $product = CustomizablePrintProduct::factory()->withTemplateConfig()->create();

        $this->assertNotNull($product->template_config);
        $this->assertIsArray($product->template_config);
    }

    // -------------------------------------------------------------------------
    // Product name boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** nameMinLength state sets product_name to 2 characters (minimum). */
    public function name_min_length_state_sets_name_to_two_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->nameMinLength()->create();

        $this->assertSame(2, strlen($product->product_name));
    }

    #[Test]
    /** nameMidLength state sets product_name to 51 characters (in‑range). */
    public function name_mid_length_state_sets_name_to_fifty_one_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->nameMidLength()->create();

        $this->assertSame(51, strlen($product->product_name));
    }

    #[Test]
    /** nameMaxLength state sets product_name to 100 characters (maximum). */
    public function name_max_length_state_sets_name_to_one_hundred_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->nameMaxLength()->create();

        $this->assertSame(100, strlen($product->product_name));
    }

    // -------------------------------------------------------------------------
    // Product name boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** nameTooShort state sets product_name to 1 character (below minimum). */
    public function name_too_short_state_sets_name_to_one_character(): void
    {
        $product = CustomizablePrintProduct::factory()->nameTooShort()->make();

        $this->assertSame(1, strlen($product->product_name));
    }

    #[Test]
    /** nameTooLong state sets product_name to 101 characters (above maximum). */
    public function name_too_long_state_sets_name_to_one_hundred_one_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->nameTooLong()->make();

        $this->assertSame(101, strlen($product->product_name));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionMinLength state sets description to 1 character (minimum). */
    public function description_min_length_state_sets_description_to_one_character(): void
    {
        $product = CustomizablePrintProduct::factory()->descriptionMinLength()->create();

        $this->assertSame(1, strlen($product->description));
    }

    #[Test]
    /** descriptionMidLength state sets description to 1000 characters (in‑range). */
    public function description_mid_length_state_sets_description_to_one_thousand_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->descriptionMidLength()->create();

        $this->assertSame(1000, strlen($product->description));
    }

    #[Test]
    /** descriptionMaxLength state sets description to 2000 characters (maximum). */
    public function description_max_length_state_sets_description_to_two_thousand_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->descriptionMaxLength()->create();

        $this->assertSame(2000, strlen($product->description));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionTooLong state sets description to 2001 characters (above maximum). */
    public function description_too_long_state_sets_description_to_two_thousand_one_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->descriptionTooLong()->make();

        $this->assertSame(2001, strlen($product->description));
    }
}