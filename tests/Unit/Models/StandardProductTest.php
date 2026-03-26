<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductVisibilityStatus;
use App\Models\ProductCategory;
use App\Models\StandardProduct;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the StandardProduct model.
 *
 * Covers model configuration, cast configuration, relationship structure
 * and data resolution for category(), and business logic for isActive().
 */
class StandardProductTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the standard_products table. */
    public function model_uses_standard_products_table(): void
    {
        $product = new StandardProduct();

        $this->assertSame('standard_products', $product->getTable());
    }

    #[Test]
    /** Primary key is product_id. */
    public function primary_key_is_product_id(): void
    {
        $product = new StandardProduct();

        $this->assertSame('product_id', $product->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $product = new StandardProduct();

        $this->assertSame('int', $product->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $product = new StandardProduct();

        $this->assertTrue($product->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $product = new StandardProduct();

        $this->assertFalse($product->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $product = new StandardProduct();
        $fillable = $product->getFillable();

        $this->assertContains('product_name', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('image_reference', $fillable);
        $this->assertContains('visibility_status', $fillable);
        $this->assertContains('category_id', $fillable);
        $this->assertContains('display_price', $fillable);
    }

    // -------------------------------------------------------------------------
    // Cast configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** visibility_status is cast to ProductVisibilityStatus enum. */
    public function visibility_status_cast_is_configured(): void
    {
        $product = new StandardProduct();

        $this->assertSame(ProductVisibilityStatus::class, $product->getCasts()['visibility_status']);
    }

    #[Test]
    /** display_price is cast to decimal with 2 places. */
    public function display_price_cast_is_configured(): void
    {
        $product = new StandardProduct();

        $this->assertSame('decimal:2', $product->getCasts()['display_price']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – category()
    // -------------------------------------------------------------------------

    #[Test]
    /** category() returns a BelongsTo relation. */
    public function category_returns_belongs_to_relation(): void
    {
        $relation = (new StandardProduct())->category();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** category() uses category_id as foreign key. */
    public function category_uses_category_id_as_foreign_key(): void
    {
        $relation = (new StandardProduct())->category();

        $this->assertSame('category_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** category() uses category_id as owner key. */
    public function category_uses_category_id_as_owner_key(): void
    {
        $relation = (new StandardProduct())->category();

        $this->assertSame('category_id', $relation->getOwnerKeyName());
    }

    #[Test]
    /** category() relates to ProductCategory model. */
    public function category_relates_to_product_category_model(): void
    {
        $relation = (new StandardProduct())->category();

        $this->assertInstanceOf(ProductCategory::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** category() resolves to the assigned product category. */
    public function category_resolves_to_the_assigned_product_category(): void
    {
        $category = ProductCategory::factory()->create();
        $product = StandardProduct::factory()->create(['category_id' => $category->category_id]);

        $resolved = $product->category;

        $this->assertInstanceOf(ProductCategory::class, $resolved);
        $this->assertSame($category->category_id, $resolved->category_id);
    }

    #[Test]
    /** category() resolves to null when product is uncategorised. */
    public function category_resolves_to_null_when_product_is_uncategorised(): void
    {
        $product = StandardProduct::factory()->uncategorised()->create();

        $this->assertNull($product->category);
    }

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    #[Test]
    /** isActive() returns true when visibility status is Active. */
    public function is_active_returns_true_when_visibility_status_is_active(): void
    {
        $product = StandardProduct::factory()->create();

        $this->assertTrue($product->isActive());
    }

    #[Test]
    /** isActive() returns false when visibility status is Inactive. */
    public function is_active_returns_false_when_visibility_status_is_inactive(): void
    {
        $product = StandardProduct::factory()->inactive()->create();

        $this->assertFalse($product->isActive());
    }
}