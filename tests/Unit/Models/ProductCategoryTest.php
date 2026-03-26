<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Models\ProductCategory;
use App\Models\StandardProduct;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the ProductCategory model.
 *
 * Covers model configuration, relationship structure and data resolution,
 * and business logic for containsActiveProducts().
 *
 * containsActiveProducts() boundary values:
 * - No products: false
 * - Only inactive products: false
 * - Exactly one active product: true
 * - Multiple active products: true
 * - Active products in other categories: not counted
 */
class ProductCategoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the product_categories table. */
    public function model_uses_product_categories_table(): void
    {
        $category = new ProductCategory();

        $this->assertSame('product_categories', $category->getTable());
    }

    #[Test]
    /** Primary key is category_id. */
    public function primary_key_is_category_id(): void
    {
        $category = new ProductCategory();

        $this->assertSame('category_id', $category->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $category = new ProductCategory();

        $this->assertSame('int', $category->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $category = new ProductCategory();

        $this->assertTrue($category->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $category = new ProductCategory();

        $this->assertFalse($category->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $category = new ProductCategory();
        $fillable = $category->getFillable();

        $this->assertContains('category_name', $fillable);
        $this->assertContains('description', $fillable);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – products()
    // -------------------------------------------------------------------------

    #[Test]
    /** products() returns a HasMany relation. */
    public function products_returns_has_many_relation(): void
    {
        $relation = (new ProductCategory())->products();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** products() uses category_id as foreign key. */
    public function products_uses_category_id_as_foreign_key(): void
    {
        $relation = (new ProductCategory())->products();

        $this->assertSame('category_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** products() relates to StandardProduct model. */
    public function products_relates_to_standard_product_model(): void
    {
        $relation = (new ProductCategory())->products();

        $this->assertInstanceOf(StandardProduct::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** products() resolves to all standard products in the category. */
    public function products_resolves_to_all_products_in_category(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->count(3)->create(['category_id' => $category->category_id]);

        $this->assertCount(3, $category->products);
        $category->products->each(
            fn ($product) => $this->assertSame($category->category_id, $product->category_id)
        );
    }

    #[Test]
    /** products() excludes products from other categories. */
    public function products_excludes_products_from_other_categories(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->count(2)->create(['category_id' => $category->category_id]);
        StandardProduct::factory()->count(3)->create();

        $this->assertCount(2, $category->products);
    }

    #[Test]
    /** products() resolves to empty collection when category has no products. */
    public function products_resolves_to_empty_collection_when_no_products_exist(): void
    {
        $category = ProductCategory::factory()->create();

        $this->assertCount(0, $category->products);
    }

    // -------------------------------------------------------------------------
    // containsActiveProducts()
    // -------------------------------------------------------------------------

    #[Test]
    /** containsActiveProducts() returns false when category has no products. */
    public function contains_active_products_returns_false_when_category_is_empty(): void
    {
        $category = ProductCategory::factory()->create();

        $this->assertFalse($category->containsActiveProducts());
    }

    #[Test]
    /** containsActiveProducts() returns false when all products are inactive. */
    public function contains_active_products_returns_false_when_all_products_are_inactive(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->count(3)->inactive()->create([
            'category_id' => $category->category_id,
        ]);

        $this->assertFalse($category->containsActiveProducts());
    }

    #[Test]
    /** containsActiveProducts() returns true when exactly one active product exists. */
    public function contains_active_products_returns_true_when_exactly_one_active_product_exists(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->create(['category_id' => $category->category_id]);

        $this->assertTrue($category->containsActiveProducts());
    }

    #[Test]
    /** containsActiveProducts() returns true when one active product exists among inactive. */
    public function contains_active_products_returns_true_when_one_active_product_exists_among_inactive(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->count(3)->inactive()->create([
            'category_id' => $category->category_id,
        ]);
        StandardProduct::factory()->create(['category_id' => $category->category_id]);

        $this->assertTrue($category->containsActiveProducts());
    }

    #[Test]
    /** containsActiveProducts() returns true when all products are active. */
    public function contains_active_products_returns_true_when_all_products_are_active(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->count(3)->create(['category_id' => $category->category_id]);

        $this->assertTrue($category->containsActiveProducts());
    }

    #[Test]
    /** containsActiveProducts() ignores active products from other categories. */
    public function contains_active_products_does_not_count_active_products_from_other_categories(): void
    {
        $category = ProductCategory::factory()->create();
        StandardProduct::factory()->count(3)->create();

        $this->assertFalse($category->containsActiveProducts());
    }
}