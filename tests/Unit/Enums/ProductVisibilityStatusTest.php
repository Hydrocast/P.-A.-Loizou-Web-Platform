<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ProductVisibilityStatus enum.
 *
 * Covers backing values, isActive() method, and model casting
 * on StandardProduct and CustomizablePrintProduct.
 */
class ProductVisibilityStatusTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Backing values
    // -------------------------------------------------------------------------

    #[Test]
    /** Active has the backing value 'Active'. */
    public function active_has_backing_value_of_active(): void
    {
        $this->assertSame('Active', ProductVisibilityStatus::Active->value);
    }

    #[Test]
    /** Inactive has the backing value 'Inactive'. */
    public function inactive_has_backing_value_of_inactive(): void
    {
        $this->assertSame('Inactive', ProductVisibilityStatus::Inactive->value);
    }

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    #[Test]
    /** Active visibility status returns true. */
    public function is_active_returns_true_for_active(): void
    {
        $this->assertTrue(ProductVisibilityStatus::Active->isActive());
    }

    #[Test]
    /** Inactive visibility status returns false. */
    public function is_active_returns_false_for_inactive(): void
    {
        $this->assertFalse(ProductVisibilityStatus::Inactive->isActive());
    }

    // -------------------------------------------------------------------------
    // Model casting — StandardProduct
    // -------------------------------------------------------------------------

    #[Test]
    /** StandardProduct visibility status is stored as the backing value string. */
    public function standard_product_visibility_status_is_stored_as_backing_value(): void
    {
        $product = StandardProduct::factory()->create([
            'visibility_status' => ProductVisibilityStatus::Inactive
        ]);

        $this->assertDatabaseHas('standard_products', [
            'product_id'        => $product->product_id,
            'visibility_status' => ProductVisibilityStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** StandardProduct visibility status is retrieved as a ProductVisibilityStatus instance. */
    public function standard_product_visibility_status_is_retrieved_as_enum_instance(): void
    {
        $product = StandardProduct::factory()->create([
            'visibility_status' => ProductVisibilityStatus::Inactive
        ]);
        
        $fresh   = StandardProduct::find($product->product_id);

        $this->assertInstanceOf(ProductVisibilityStatus::class, $fresh->visibility_status);
        $this->assertSame(ProductVisibilityStatus::Inactive, $fresh->visibility_status);
    }

    // -------------------------------------------------------------------------
    // Model casting — CustomizablePrintProduct
    // -------------------------------------------------------------------------

    #[Test]
    /** CustomizablePrintProduct visibility status is stored as the backing value string. */
    public function customizable_product_visibility_status_is_stored_as_backing_value(): void
    {
        $product = CustomizablePrintProduct::factory()->create([
            'visibility_status' => ProductVisibilityStatus::Inactive
        ]);

        $this->assertDatabaseHas('customizable_print_products', [
            'product_id'        => $product->product_id,
            'visibility_status' => ProductVisibilityStatus::Inactive->value,
        ]);
    }

    #[Test]
    /** CustomizablePrintProduct visibility status is retrieved as a ProductVisibilityStatus instance. */
    public function customizable_product_visibility_status_is_retrieved_as_enum_instance(): void
    {
        $product = CustomizablePrintProduct::factory()->create([
            'visibility_status' => ProductVisibilityStatus::Inactive
        ]);
        
        $fresh   = CustomizablePrintProduct::find($product->product_id);

        $this->assertInstanceOf(ProductVisibilityStatus::class, $fresh->visibility_status);
        $this->assertSame(ProductVisibilityStatus::Inactive, $fresh->visibility_status);
    }
}