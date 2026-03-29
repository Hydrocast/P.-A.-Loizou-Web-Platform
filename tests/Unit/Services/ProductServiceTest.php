<?php

namespace Tests\Unit\Services;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use App\Models\ProductCategory;
use App\Models\StandardProduct;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for ProductService.
 *
 * Covers product search, filtering, retrieval, creation, editing,
 * visibility toggling, and category management.
 * Boundary values: product name (2–100), display price (0–100,000),
 * description (max 2000), category name (2–50), search query (max 50).
 */
class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ProductService;
    }

    // -------------------------------------------------------------------------
    // searchProducts()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns matching active products by name. */
    public function search_products_returns_matching_active_products(): void
    {
        StandardProduct::factory()->create(['product_name' => 'Custom Business Card']);
        StandardProduct::factory()->create(['product_name' => 'Wedding Invitation']);

        $results = $this->service->searchProducts('Business');

        $this->assertCount(1, $results);
        $this->assertEquals('Custom Business Card', $results->first()->product_name);
    }

    #[Test]
    /** Returns an empty collection when no matching product is found. */
    public function search_products_returns_empty_when_no_match(): void
    {
        StandardProduct::factory()->create(['product_name' => 'Flyer']);
        $results = $this->service->searchProducts('nonexistent');
        $this->assertCount(0, $results);
    }

    #[Test]
    /** Inactive products are excluded from search results. */
    public function search_products_excludes_inactive_products(): void
    {
        StandardProduct::factory()->inactive()->create(['product_name' => 'Hidden Card']);
        $results = $this->service->searchProducts('Hidden');
        $this->assertCount(0, $results);
    }

    #[Test]
    /** Both standard and customizable products are included in search results. */
    public function search_products_includes_both_product_types(): void
    {
        StandardProduct::factory()->create(['product_name' => 'Print Flyer']);
        CustomizablePrintProduct::factory()->create(['product_name' => 'Print Poster']);

        $results = $this->service->searchProducts('Print');
        $this->assertCount(2, $results);
    }

    #[Test]
    /** Customizable search results are ordered by product_id ascending. */
    public function search_products_orders_customizable_results_by_product_id(): void
    {
        $first = CustomizablePrintProduct::factory()->create([
            'product_name' => 'Orderable Custom A',
            'description' => 'Shared keyword',
        ]);
        $second = CustomizablePrintProduct::factory()->create([
            'product_name' => 'Orderable Custom B',
            'description' => 'Shared keyword',
        ]);

        $results = $this->service->searchProducts('Orderable Custom');

        $this->assertSame($first->product_id, $results->first()->product_id);
        $this->assertSame($second->product_id, $results->last()->product_id);
    }

    #[Test]
    /** Matches products by description field in addition to name. */
    public function search_products_matches_against_description(): void
    {
        StandardProduct::factory()->create([
            'product_name' => 'Generic Product',
            'description' => 'Perfect for weddings',
        ]);

        $results = $this->service->searchProducts('weddings');
        $this->assertCount(1, $results);
    }

    #[Test]
    /** Empty query returns all active products. */
    public function search_products_accepts_empty_query_and_returns_active_products(): void
    {
        StandardProduct::factory()->count(2)->create();
        $results = $this->service->searchProducts('');
        $this->assertCount(2, $results);
    }

    #[Test]
    /** Query of 1 character is accepted. */
    public function search_products_accepts_single_character_query(): void
    {
        StandardProduct::factory()->create(['product_name' => 'Abc']);
        $results = $this->service->searchProducts('A');
        $this->assertCount(1, $results);
    }

    #[Test]
    /** Query of 25 characters (in-range) is accepted. */
    public function search_products_accepts_mid_range_query_length(): void
    {
        $results = $this->service->searchProducts(str_repeat('z', 25));
        $this->assertCount(0, $results);
    }

    #[Test]
    /** Query of 50 characters (maximum) is accepted. */
    public function search_products_accepts_query_of_exactly_fifty_characters(): void
    {
        $results = $this->service->searchProducts(str_repeat('z', 50));
        $this->assertCount(0, $results);
    }

    #[Test]
    /** Query of 51 characters (above maximum) is rejected. */
    public function search_products_throws_when_query_exceeds_fifty_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->searchProducts(str_repeat('z', 51));
    }

    // -------------------------------------------------------------------------
    // filterProducts()
    // -------------------------------------------------------------------------

    #[Test]
    /** All active products are returned when no filters are applied. */
    public function filter_products_returns_all_active_products_when_no_filters_applied(): void
    {
        StandardProduct::factory()->count(2)->create();
        CustomizablePrintProduct::factory()->create();

        $results = $this->service->filterProducts(null, null, null);
        $this->assertCount(3, $results);
    }

    #[Test]
    /** Filters results to standard products in the specified category. */
    public function filter_products_filters_by_category(): void
    {
        $cat = ProductCategory::factory()->create();
        StandardProduct::factory()->create(['category_id' => $cat->category_id]);
        StandardProduct::factory()->create();

        $results = $this->service->filterProducts($cat->category_id, null, null);
        $this->assertCount(1, $results);
    }

    #[Test]
    /** Filters by standard product type only. */
    public function filter_products_filters_by_standard_product_type(): void
    {
        StandardProduct::factory()->count(2)->create();
        CustomizablePrintProduct::factory()->create();

        $results = $this->service->filterProducts(null, 'standard', null);
        $this->assertCount(2, $results);
    }

    #[Test]
    /** Filters by customizable product type only. */
    public function filter_products_filters_by_customizable_product_type(): void
    {
        StandardProduct::factory()->create();
        CustomizablePrintProduct::factory()->count(2)->create();

        $results = $this->service->filterProducts(null, 'customizable', null);
        $this->assertCount(2, $results);
    }

    #[Test]
    /** Customizable filter results are ordered by product_id ascending. */
    public function filter_products_orders_customizable_results_by_product_id(): void
    {
        $first = CustomizablePrintProduct::factory()->create(['product_name' => 'Custom One']);
        $second = CustomizablePrintProduct::factory()->create(['product_name' => 'Custom Two']);

        $results = $this->service->filterProducts(null, 'customizable', null);

        $this->assertSame($first->product_id, $results->first()->product_id);
        $this->assertSame($second->product_id, $results->last()->product_id);
    }

    #[Test]
    /** Inactive products are excluded regardless of filters applied. */
    public function filter_products_excludes_inactive_products(): void
    {
        StandardProduct::factory()->create();
        StandardProduct::factory()->inactive()->create();

        $results = $this->service->filterProducts(null, null, null);
        $this->assertCount(1, $results);
    }

    #[Test]
    /** Category and product type filters combine with AND logic. */
    public function filter_products_applies_all_filters_with_and_logic(): void
    {
        $cat = ProductCategory::factory()->create();
        StandardProduct::factory()->create(['category_id' => $cat->category_id]);
        StandardProduct::factory()->create();
        CustomizablePrintProduct::factory()->create();

        $results = $this->service->filterProducts($cat->category_id, 'standard', null);
        $this->assertCount(1, $results);
    }

    #[Test]
    /** Sorts standard products by price ascending. */
    public function filter_products_sorts_standard_products_by_price_ascending(): void
    {
        StandardProduct::factory()->create(['product_name' => 'Expensive', 'display_price' => 100.00]);
        StandardProduct::factory()->create(['product_name' => 'Cheap',     'display_price' => 5.00]);

        $results = $this->service->filterProducts(null, 'standard', 'asc');

        $this->assertEquals('Cheap', $results->first()->product_name);
        $this->assertEquals('Expensive', $results->last()->product_name);
    }

    #[Test]
    /** Sorts standard products by price descending. */
    public function filter_products_sorts_standard_products_by_price_descending(): void
    {
        StandardProduct::factory()->create(['product_name' => 'Cheap',     'display_price' => 5.00]);
        StandardProduct::factory()->create(['product_name' => 'Expensive', 'display_price' => 100.00]);

        $results = $this->service->filterProducts(null, 'standard', 'desc');

        $this->assertEquals('Expensive', $results->first()->product_name);
        $this->assertEquals('Cheap', $results->last()->product_name);
    }

    // -------------------------------------------------------------------------
    // getActiveProduct()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns an active standard product for a valid ID. */
    public function get_active_product_returns_standard_product(): void
    {
        $product = StandardProduct::factory()->create();
        $result = $this->service->getActiveProduct($product->product_id, 'standard');
        $this->assertNotNull($result);
        $this->assertEquals($product->product_id, $result->product_id);
    }

    #[Test]
    /** Returns an active customizable product for a valid ID. */
    public function get_active_product_returns_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $result = $this->service->getActiveProduct($product->product_id, 'customizable');
        $this->assertNotNull($result);
        $this->assertEquals($product->product_id, $result->product_id);
    }

    #[Test]
    /** Returns null when the product does not exist. */
    public function get_active_product_returns_null_when_product_does_not_exist(): void
    {
        $result = $this->service->getActiveProduct(99999, 'standard');
        $this->assertNull($result);
    }

    #[Test]
    /** Returns null when the product is inactive. */
    public function get_active_product_returns_null_when_product_is_inactive(): void
    {
        $product = StandardProduct::factory()->inactive()->create();
        $result = $this->service->getActiveProduct($product->product_id, 'standard');
        $this->assertNull($result);
    }

    #[Test]
    /** Returns null for an unknown product type. */
    public function get_active_product_returns_null_for_unknown_product_type(): void
    {
        $product = StandardProduct::factory()->create();
        $result = $this->service->getActiveProduct($product->product_id, 'invalid_type');
        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // createStandardProduct()
    // -------------------------------------------------------------------------

    #[Test]
    /** Persists a standard product with Active status. */
    public function create_standard_product_persists_product_with_active_status(): void
    {
        $cat = ProductCategory::factory()->create();
        $product = $this->service->createStandardProduct('Test Product', $cat->category_id, 9.99, 'A description', null);

        $this->assertDatabaseHas('standard_products', ['product_name' => 'Test Product']);
        $this->assertEquals(ProductVisibilityStatus::Active, $product->visibility_status);
    }

    #[Test]
    /** Image reference string is stored when provided. */
    public function create_standard_product_stores_image_reference_url(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 5.00, null, 'products/abc123.jpg');
        $this->assertEquals('products/abc123.jpg', $product->image_reference);
    }

    #[Test]
    /** Null image reference is accepted. */
    public function create_standard_product_accepts_null_image_reference(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 5.00, null, null);
        $this->assertNull($product->image_reference);
    }

    #[Test]
    /** Null category is accepted. */
    public function create_standard_product_accepts_null_category(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 5.00, null, null);
        $this->assertNull($product->category_id);
    }

    // Product name boundaries -------------------------------------------------

    #[Test]
    /** Product name of 1 character (below minimum) is rejected. */
    public function create_standard_product_throws_when_name_is_one_character(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStandardProduct('A', null, 5.00, null, null);
    }

    #[Test]
    /** Product name of 2 characters (minimum) is accepted. */
    public function create_standard_product_accepts_name_of_two_characters(): void
    {
        $product = $this->service->createStandardProduct('Ab', null, 5.00, null, null);
        $this->assertEquals('Ab', $product->product_name);
    }

    #[Test]
    /** Product name of 51 characters (in-range) is accepted. */
    public function create_standard_product_accepts_name_of_fifty_one_characters(): void
    {
        $name = str_repeat('a', 51);
        $product = $this->service->createStandardProduct($name, null, 5.00, null, null);
        $this->assertEquals($name, $product->product_name);
    }

    #[Test]
    /** Product name of 100 characters (maximum) is accepted. */
    public function create_standard_product_accepts_name_of_one_hundred_characters(): void
    {
        $name = str_repeat('a', 100);
        $product = $this->service->createStandardProduct($name, null, 5.00, null, null);
        $this->assertEquals($name, $product->product_name);
    }

    #[Test]
    /** Product name of 101 characters (above maximum) is rejected. */
    public function create_standard_product_throws_when_name_exceeds_one_hundred_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStandardProduct(str_repeat('a', 101), null, 5.00, null, null);
    }

    // Price boundaries --------------------------------------------------------

    #[Test]
    /** Price of -0.01 (below minimum) is rejected. */
    public function create_standard_product_throws_when_price_is_negative(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStandardProduct('Product', null, -0.01, null, null);
    }

    #[Test]
    /** Price of 0.00 (minimum) is accepted. */
    public function create_standard_product_accepts_zero_price(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 0.00, null, null);
        $this->assertEquals(0.00, (float) $product->display_price);
    }

    #[Test]
    /** Price of 25.00 (in-range) is accepted. */
    public function create_standard_product_accepts_mid_range_price(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 25.00, null, null);
        $this->assertEquals(25.00, (float) $product->display_price);
    }

    #[Test]
    /** Price of 100,000.00 (maximum) is accepted. */
    public function create_standard_product_accepts_maximum_price(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 100000.00, null, null);
        $this->assertEquals(100000.00, (float) $product->display_price);
    }

    #[Test]
    /** Price of 100,000.01 (above maximum) is rejected. */
    public function create_standard_product_throws_when_price_exceeds_maximum(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStandardProduct('Product', null, 100000.01, null, null);
    }

    // Description boundaries --------------------------------------------------

    #[Test]
    /** Null description is accepted. */
    public function create_standard_product_accepts_null_description(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 5.00, null, null);
        $this->assertNull($product->description);
    }

    #[Test]
    /** Description of 1 character is accepted. */
    public function create_standard_product_accepts_description_of_one_character(): void
    {
        $product = $this->service->createStandardProduct('Product', null, 5.00, 'a', null);
        $this->assertEquals('a', $product->description);
    }

    #[Test]
    /** Description of 1000 characters (in-range) is accepted. */
    public function create_standard_product_accepts_description_of_one_thousand_characters(): void
    {
        $desc = str_repeat('a', 1000);
        $product = $this->service->createStandardProduct('Product', null, 5.00, $desc, null);
        $this->assertEquals($desc, $product->description);
    }

    #[Test]
    /** Description of 2000 characters (maximum) is accepted. */
    public function create_standard_product_accepts_description_of_two_thousand_characters(): void
    {
        $desc = str_repeat('a', 2000);
        $product = $this->service->createStandardProduct('Product', null, 5.00, $desc, null);
        $this->assertEquals($desc, $product->description);
    }

    #[Test]
    /** Description of 2001 characters (above maximum) is rejected. */
    public function create_standard_product_throws_when_description_exceeds_two_thousand_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createStandardProduct('Product', null, 5.00, str_repeat('a', 2001), null);
    }

    // -------------------------------------------------------------------------
    // editStandardProduct()
    // -------------------------------------------------------------------------

    #[Test]
    /** Updates name, price, and description to the new values. */
    public function edit_standard_product_updates_name_price_and_description(): void
    {
        $product = StandardProduct::factory()->create();

        $this->service->editStandardProduct($product->product_id, 'Updated Name', null, 19.99, 'Updated description', null);
        $product->refresh();

        $this->assertEquals('Updated Name', $product->product_name);
        $this->assertEquals(19.99, (float) $product->display_price);
        $this->assertEquals('Updated description', $product->description);
    }

    #[Test]
    /** Non-null image reference replaces the existing one. */
    public function edit_standard_product_replaces_image_when_new_reference_provided(): void
    {
        $product = StandardProduct::factory()->create(['image_reference' => 'old/image.jpg']);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, null, 'new/image.jpg');
        $product->refresh();
        $this->assertEquals('new/image.jpg', $product->image_reference);
    }

    #[Test]
    /** Null image reference leaves the existing image unchanged. */
    public function edit_standard_product_retains_existing_image_when_null_provided(): void
    {
        $product = StandardProduct::factory()->create(['image_reference' => 'existing/image.jpg']);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, null, null);
        $product->refresh();
        $this->assertEquals('existing/image.jpg', $product->image_reference);
    }

    #[Test]
    /** Non-existent product ID throws ModelNotFoundException. */
    public function edit_standard_product_throws_when_product_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->editStandardProduct(99999, 'Name', null, 5.00, null, null);
    }

    // Product name boundaries -------------------------------------------------

    #[Test]
    /** Product name of 1 character (below minimum) is rejected. */
    public function edit_standard_product_throws_when_name_is_one_character(): void
    {
        $product = StandardProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editStandardProduct($product->product_id, 'A', null, 5.00, null, null);
    }

    #[Test]
    /** Product name of 2 characters (minimum) is accepted. */
    public function edit_standard_product_accepts_name_of_two_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $this->service->editStandardProduct($product->product_id, 'Ab', null, 5.00, null, null);
        $product->refresh();
        $this->assertEquals('Ab', $product->product_name);
    }

    #[Test]
    /** Product name of 51 characters (in-range) is accepted. */
    public function edit_standard_product_accepts_name_of_fifty_one_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $name = str_repeat('a', 51);
        $this->service->editStandardProduct($product->product_id, $name, null, 5.00, null, null);
        $product->refresh();
        $this->assertEquals($name, $product->product_name);
    }

    #[Test]
    /** Product name of 100 characters (maximum) is accepted. */
    public function edit_standard_product_accepts_name_of_one_hundred_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $name = str_repeat('a', 100);
        $this->service->editStandardProduct($product->product_id, $name, null, 5.00, null, null);
        $product->refresh();
        $this->assertEquals($name, $product->product_name);
    }

    #[Test]
    /** Product name of 101 characters (above maximum) is rejected. */
    public function edit_standard_product_throws_when_name_exceeds_one_hundred_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editStandardProduct($product->product_id, str_repeat('a', 101), null, 5.00, null, null);
    }

    // Price boundaries --------------------------------------------------------

    #[Test]
    /** Price of -0.01 (below minimum) is rejected. */
    public function edit_standard_product_throws_when_price_is_negative(): void
    {
        $product = StandardProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editStandardProduct($product->product_id, 'Name', null, -0.01, null, null);
    }

    #[Test]
    /** Price of 0.00 (minimum) is accepted. */
    public function edit_standard_product_accepts_zero_price(): void
    {
        $product = StandardProduct::factory()->create();
        $this->service->editStandardProduct($product->product_id, 'Name', null, 0.00, null, null);
        $product->refresh();
        $this->assertEquals(0.00, (float) $product->display_price);
    }

    #[Test]
    /** Price of 25.00 (in-range) is accepted. */
    public function edit_standard_product_accepts_mid_range_price(): void
    {
        $product = StandardProduct::factory()->create();
        $this->service->editStandardProduct($product->product_id, 'Name', null, 25.00, null, null);
        $product->refresh();
        $this->assertEquals(25.00, (float) $product->display_price);
    }

    #[Test]
    /** Price of 100,000.00 (maximum) is accepted. */
    public function edit_standard_product_accepts_maximum_price(): void
    {
        $product = StandardProduct::factory()->create();
        $this->service->editStandardProduct($product->product_id, 'Name', null, 100000.00, null, null);
        $product->refresh();
        $this->assertEquals(100000.00, (float) $product->display_price);
    }

    #[Test]
    /** Price of 100,000.01 (above maximum) is rejected. */
    public function edit_standard_product_throws_when_price_exceeds_maximum(): void
    {
        $product = StandardProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 100000.01, null, null);
    }

    // Description boundaries --------------------------------------------------

    #[Test]
    /** Null description is accepted. */
    public function edit_standard_product_accepts_null_description(): void
    {
        $product = StandardProduct::factory()->create(['description' => 'Old desc']);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, null, null);
        $product->refresh();
        $this->assertNull($product->description);
    }

    #[Test]
    /** Description of 1 character is accepted. */
    public function edit_standard_product_accepts_description_of_one_character(): void
    {
        $product = StandardProduct::factory()->create();
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, 'a', null);
        $product->refresh();
        $this->assertEquals('a', $product->description);
    }

    #[Test]
    /** Description of 1000 characters (in-range) is accepted. */
    public function edit_standard_product_accepts_description_of_one_thousand_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $desc = str_repeat('a', 1000);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, $desc, null);
        $product->refresh();
        $this->assertEquals($desc, $product->description);
    }

    #[Test]
    /** Description of 2000 characters (maximum) is accepted. */
    public function edit_standard_product_accepts_description_of_two_thousand_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $desc = str_repeat('a', 2000);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, $desc, null);
        $product->refresh();
        $this->assertEquals($desc, $product->description);
    }

    #[Test]
    /** Description of 2001 characters (above maximum) is rejected. */
    public function edit_standard_product_throws_when_description_exceeds_two_thousand_characters(): void
    {
        $product = StandardProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editStandardProduct($product->product_id, 'Name', null, 5.00, str_repeat('a', 2001), null);
    }

    // -------------------------------------------------------------------------
    // editCustomizableProduct()
    // -------------------------------------------------------------------------

    #[Test]
    /** Updates name and description to the new values. */
    public function edit_customizable_product_updates_name_and_description(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        $this->service->editCustomizableProduct($product->product_id, 'Updated Custom', 'New description', null);
        $product->refresh();

        $this->assertEquals('Updated Custom', $product->product_name);
        $this->assertEquals('New description', $product->description);
    }

    #[Test]
    /** Non-null image reference replaces the existing one. */
    public function edit_customizable_product_replaces_image_when_new_reference_provided(): void
    {
        $product = CustomizablePrintProduct::factory()->create(['image_reference' => 'old/img.jpg']);
        $this->service->editCustomizableProduct($product->product_id, 'Name', null, 'new/img.jpg');
        $product->refresh();
        $this->assertEquals('new/img.jpg', $product->image_reference);
    }

    #[Test]
    /** Null image reference leaves the existing image unchanged. */
    public function edit_customizable_product_retains_existing_image_when_null_provided(): void
    {
        $product = CustomizablePrintProduct::factory()->create(['image_reference' => 'keep/img.jpg']);
        $this->service->editCustomizableProduct($product->product_id, 'Name', null, null);
        $product->refresh();
        $this->assertEquals('keep/img.jpg', $product->image_reference);
    }

    #[Test]
    /** Non-existent product ID throws ModelNotFoundException. */
    public function edit_customizable_product_throws_when_product_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->editCustomizableProduct(99999, 'Name', null, null);
    }

    // Product name boundaries -------------------------------------------------

    #[Test]
    /** Product name of 1 character (below minimum) is rejected. */
    public function edit_customizable_product_throws_when_name_is_one_character(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editCustomizableProduct($product->product_id, 'A', null, null);
    }

    #[Test]
    /** Product name of 2 characters (minimum) is accepted. */
    public function edit_customizable_product_accepts_name_of_two_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $this->service->editCustomizableProduct($product->product_id, 'Ab', null, null);
        $product->refresh();
        $this->assertEquals('Ab', $product->product_name);
    }

    #[Test]
    /** Product name of 51 characters (in-range) is accepted. */
    public function edit_customizable_product_accepts_name_of_fifty_one_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $name = str_repeat('a', 51);
        $this->service->editCustomizableProduct($product->product_id, $name, null, null);
        $product->refresh();
        $this->assertEquals($name, $product->product_name);
    }

    #[Test]
    /** Product name of 100 characters (maximum) is accepted. */
    public function edit_customizable_product_accepts_name_of_one_hundred_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $name = str_repeat('a', 100);
        $this->service->editCustomizableProduct($product->product_id, $name, null, null);
        $product->refresh();
        $this->assertEquals($name, $product->product_name);
    }

    #[Test]
    /** Product name of 101 characters (above maximum) is rejected. */
    public function edit_customizable_product_throws_when_name_exceeds_one_hundred_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editCustomizableProduct($product->product_id, str_repeat('a', 101), null, null);
    }

    // Description boundaries --------------------------------------------------

    #[Test]
    /** Null description is accepted. */
    public function edit_customizable_product_accepts_null_description(): void
    {
        $product = CustomizablePrintProduct::factory()->create(['description' => 'Old desc']);
        $this->service->editCustomizableProduct($product->product_id, 'Name', null, null);
        $product->refresh();
        $this->assertNull($product->description);
    }

    #[Test]
    /** Description of 1 character is accepted. */
    public function edit_customizable_product_accepts_description_of_one_character(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $this->service->editCustomizableProduct($product->product_id, 'Name', 'a', null);
        $product->refresh();
        $this->assertEquals('a', $product->description);
    }

    #[Test]
    /** Description of 1000 characters (in-range) is accepted. */
    public function edit_customizable_product_accepts_description_of_one_thousand_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $desc = str_repeat('a', 1000);
        $this->service->editCustomizableProduct($product->product_id, 'Name', $desc, null);
        $product->refresh();
        $this->assertEquals($desc, $product->description);
    }

    #[Test]
    /** Description of 2000 characters (maximum) is accepted. */
    public function edit_customizable_product_accepts_description_of_two_thousand_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $desc = str_repeat('a', 2000);
        $this->service->editCustomizableProduct($product->product_id, 'Name', $desc, null);
        $product->refresh();
        $this->assertEquals($desc, $product->description);
    }

    #[Test]
    /** Description of 2001 characters (above maximum) is rejected. */
    public function edit_customizable_product_throws_when_description_exceeds_two_thousand_characters(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editCustomizableProduct($product->product_id, 'Name', str_repeat('a', 2001), null);
    }

    // -------------------------------------------------------------------------
    // setVisibility()
    // -------------------------------------------------------------------------

    #[Test]
    /** Deactivates an active standard product. */
    public function set_visibility_deactivates_standard_product(): void
    {
        $product = StandardProduct::factory()->create();
        $this->service->setVisibility($product->product_id, 'standard', ProductVisibilityStatus::Inactive);
        $product->refresh();
        $this->assertEquals(ProductVisibilityStatus::Inactive, $product->visibility_status);
    }

    #[Test]
    /** Reactivates an inactive standard product. */
    public function set_visibility_activates_inactive_standard_product(): void
    {
        $product = StandardProduct::factory()->inactive()->create();
        $this->service->setVisibility($product->product_id, 'standard', ProductVisibilityStatus::Active);
        $product->refresh();
        $this->assertEquals(ProductVisibilityStatus::Active, $product->visibility_status);
    }

    #[Test]
    /** Deactivates an active customizable product. */
    public function set_visibility_deactivates_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $this->service->setVisibility($product->product_id, 'customizable', ProductVisibilityStatus::Inactive);
        $product->refresh();
        $this->assertEquals(ProductVisibilityStatus::Inactive, $product->visibility_status);
    }

    #[Test]
    /** Reactivates an inactive customizable product. */
    public function set_visibility_activates_inactive_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->inactive()->create();
        $this->service->setVisibility($product->product_id, 'customizable', ProductVisibilityStatus::Active);
        $product->refresh();
        $this->assertEquals(ProductVisibilityStatus::Active, $product->visibility_status);
    }

    // -------------------------------------------------------------------------
    // createCategory()
    // -------------------------------------------------------------------------

    #[Test]
    /** Persists a new product category. */
    public function create_category_persists_category(): void
    {
        $cat = $this->service->createCategory('Business Cards', 'For business use');
        $this->assertInstanceOf(ProductCategory::class, $cat);
        $this->assertDatabaseHas('product_categories', ['category_name' => 'Business Cards']);
    }

    #[Test]
    /** Null description is accepted. */
    public function create_category_accepts_null_description(): void
    {
        $cat = $this->service->createCategory('Flyers', null);
        $this->assertNull($cat->description);
    }

    #[Test]
    /** Duplicate category name throws ValidationException. */
    public function create_category_throws_when_name_already_exists(): void
    {
        ProductCategory::factory()->create(['category_name' => 'Existing']);
        $this->expectException(ValidationException::class);
        $this->service->createCategory('Existing', null);
    }

    // Category name boundaries ------------------------------------------------

    #[Test]
    /** Category name of 1 character (below minimum) is rejected. */
    public function create_category_throws_when_name_is_one_character(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createCategory('A', null);
    }

    #[Test]
    /** Category name of 2 characters (minimum) is accepted. */
    public function create_category_accepts_name_of_two_characters(): void
    {
        $cat = $this->service->createCategory('Ab', null);
        $this->assertEquals('Ab', $cat->category_name);
    }

    #[Test]
    /** Category name of 26 characters (in-range) is accepted. */
    public function create_category_accepts_name_of_twenty_six_characters(): void
    {
        $name = str_repeat('a', 26);
        $cat = $this->service->createCategory($name, null);
        $this->assertEquals($name, $cat->category_name);
    }

    #[Test]
    /** Category name of 50 characters (maximum) is accepted. */
    public function create_category_accepts_name_of_fifty_characters(): void
    {
        $name = str_repeat('a', 50);
        $cat = $this->service->createCategory($name, null);
        $this->assertEquals($name, $cat->category_name);
    }

    #[Test]
    /** Category name of 51 characters (above maximum) is rejected. */
    public function create_category_throws_when_name_exceeds_fifty_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createCategory(str_repeat('a', 51), null);
    }

    // -------------------------------------------------------------------------
    // editCategory()
    // -------------------------------------------------------------------------

    #[Test]
    /** Updates category name and description to the new values. */
    public function edit_category_updates_name_and_description(): void
    {
        $cat = ProductCategory::factory()->create(['category_name' => 'Old Name']);
        $this->service->editCategory($cat->category_id, 'New Name', 'New desc');
        $cat->refresh();
        $this->assertEquals('New Name', $cat->category_name);
        $this->assertEquals('New desc', $cat->description);
    }

    #[Test]
    /** Category can be saved with its existing name without triggering a duplicate error. */
    public function edit_category_accepts_same_name_as_current(): void
    {
        $cat = ProductCategory::factory()->create(['category_name' => 'Same Name']);
        $this->service->editCategory($cat->category_id, 'Same Name', null);
        $cat->refresh();
        $this->assertEquals('Same Name', $cat->category_name);
    }

    #[Test]
    /** Name already used by another category throws ValidationException. */
    public function edit_category_throws_when_name_is_taken_by_another_category(): void
    {
        ProductCategory::factory()->create(['category_name' => 'Taken']);
        $cat = ProductCategory::factory()->create(['category_name' => 'Mine']);
        $this->expectException(ValidationException::class);
        $this->service->editCategory($cat->category_id, 'Taken', null);
    }

    #[Test]
    /** Non-existent category ID throws ModelNotFoundException. */
    public function edit_category_throws_when_category_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->editCategory(99999, 'Name', null);
    }

    // Category name boundaries ------------------------------------------------

    #[Test]
    /** Category name of 1 character (below minimum) is rejected. */
    public function edit_category_throws_when_name_is_one_character(): void
    {
        $cat = ProductCategory::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editCategory($cat->category_id, 'A', null);
    }

    #[Test]
    /** Category name of 2 characters (minimum) is accepted. */
    public function edit_category_accepts_name_of_two_characters(): void
    {
        $cat = ProductCategory::factory()->create();
        $this->service->editCategory($cat->category_id, 'Ab', null);
        $cat->refresh();
        $this->assertEquals('Ab', $cat->category_name);
    }

    #[Test]
    /** Category name of 26 characters (in-range) is accepted. */
    public function edit_category_accepts_name_of_twenty_six_characters(): void
    {
        $cat = ProductCategory::factory()->create();
        $name = str_repeat('a', 26);
        $this->service->editCategory($cat->category_id, $name, null);
        $cat->refresh();
        $this->assertEquals($name, $cat->category_name);
    }

    #[Test]
    /** Category name of 50 characters (maximum) is accepted. */
    public function edit_category_accepts_name_of_fifty_characters(): void
    {
        $cat = ProductCategory::factory()->create();
        $name = str_repeat('a', 50);
        $this->service->editCategory($cat->category_id, $name, null);
        $cat->refresh();
        $this->assertEquals($name, $cat->category_name);
    }

    #[Test]
    /** Category name of 51 characters (above maximum) is rejected. */
    public function edit_category_throws_when_name_exceeds_fifty_characters(): void
    {
        $cat = ProductCategory::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->editCategory($cat->category_id, str_repeat('a', 51), null);
    }

    // -------------------------------------------------------------------------
    // deleteCategory()
    // -------------------------------------------------------------------------

    #[Test]
    /** Empty category is deleted from the database. */
    public function delete_category_removes_empty_category(): void
    {
        $cat = ProductCategory::factory()->create();
        $this->service->deleteCategory($cat->category_id);
        $this->assertDatabaseMissing('product_categories', ['category_id' => $cat->category_id]);
    }

    #[Test]
    /** Category containing active products throws ValidationException. */
    public function delete_category_throws_when_category_contains_active_products(): void
    {
        $cat = ProductCategory::factory()->create();
        StandardProduct::factory()->create(['category_id' => $cat->category_id]);
        $this->expectException(ValidationException::class);
        $this->service->deleteCategory($cat->category_id);
    }

    #[Test]
    /** Deletion succeeds when only inactive products are in the category, and their category reference is cleared. */
    public function delete_category_succeeds_when_only_inactive_products_exist_and_nullifies_category(): void
    {
        $cat = ProductCategory::factory()->create();
        $product = StandardProduct::factory()->inactive()->create(['category_id' => $cat->category_id]);

        $this->service->deleteCategory($cat->category_id);

        $this->assertDatabaseMissing('product_categories', ['category_id' => $cat->category_id]);
        $product->refresh();
        $this->assertNull($product->category_id);
    }

    #[Test]
    /** Non-existent category ID throws ModelNotFoundException. */
    public function delete_category_throws_when_category_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->deleteCategory(99999);
    }

    // -------------------------------------------------------------------------
    // getAllCategories()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns all categories ordered alphabetically by name. */
    public function get_all_categories_returns_all_categories_ordered_alphabetically(): void
    {
        ProductCategory::factory()->create(['category_name' => 'Zebra']);
        ProductCategory::factory()->create(['category_name' => 'Apple']);
        ProductCategory::factory()->create(['category_name' => 'Mango']);

        $results = $this->service->getAllCategories();

        $this->assertCount(3, $results);
        $this->assertEquals('Apple', $results->first()->category_name);
        $this->assertEquals('Mango', $results->get(1)->category_name);
        $this->assertEquals('Zebra', $results->last()->category_name);
    }

    #[Test]
    /** Returns an empty collection when no categories exist. */
    public function get_all_categories_returns_empty_when_no_categories_exist(): void
    {
        $results = $this->service->getAllCategories();
        $this->assertCount(0, $results);
    }
}
