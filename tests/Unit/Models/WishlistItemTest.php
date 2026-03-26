<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductType;
use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the WishlistItem model.
 *
 * Covers model configuration, the customer() relationship structure and data
 * resolution, and business logic for product() and isProductAvailable().
 *
 * product() is a manual resolution method (not a Laravel relation). It routes
 * to one of two product tables based on product_type. product_type is
 * non-nullable, so there is no null-guard case.
 *
 * product() boundary values:
 * - Standard type, product exists: returns StandardProduct
 * - Customizable type, product exists: returns CustomizablePrintProduct
 * - Standard type, product missing: null
 * - Customizable type, product missing: null
 *
 * isProductAvailable() boundary values:
 * - Product does not exist: false
 * - Product exists but is inactive: false
 * - Product exists and is active: true
 */
class WishlistItemTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the wishlist_items table. */
    public function model_uses_wishlist_items_table(): void
    {
        $item = new WishlistItem();

        $this->assertSame('wishlist_items', $item->getTable());
    }

    #[Test]
    /** Primary key is wishlist_item_id. */
    public function primary_key_is_wishlist_item_id(): void
    {
        $item = new WishlistItem();

        $this->assertSame('wishlist_item_id', $item->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $item = new WishlistItem();

        $this->assertSame('int', $item->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $item = new WishlistItem();

        $this->assertTrue($item->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $item = new WishlistItem();

        $this->assertFalse($item->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $item = new WishlistItem();
        $fillable = $item->getFillable();

        $this->assertContains('customer_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('product_type', $fillable);
        $this->assertContains('date_added', $fillable);
    }

    #[Test]
    /** product_type is cast to ProductType enum. */
    public function product_type_cast_is_configured(): void
    {
        $item = new WishlistItem();

        $this->assertSame(ProductType::class, $item->getCasts()['product_type']);
    }

    #[Test]
    /** date_added is cast to datetime. */
    public function date_added_cast_is_configured(): void
    {
        $item = new WishlistItem();

        $this->assertSame('datetime', $item->getCasts()['date_added']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – customer()
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() returns a BelongsTo relation. */
    public function customer_returns_belongs_to_relation(): void
    {
        $relation = (new WishlistItem())->customer();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** customer() uses customer_id as foreign key. */
    public function customer_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new WishlistItem())->customer();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** customer() relates to Customer model. */
    public function customer_relates_to_customer_model(): void
    {
        $relation = (new WishlistItem())->customer();

        $this->assertInstanceOf(Customer::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() resolves to the customer who owns this wishlist item. */
    public function customer_resolves_to_the_owning_customer(): void
    {
        $customer = Customer::factory()->create();
        $item = WishlistItem::factory()->create(['customer_id' => $customer->customer_id]);

        $resolved = $item->customer;

        $this->assertInstanceOf(Customer::class, $resolved);
        $this->assertSame($customer->customer_id, $resolved->customer_id);
    }

    // -------------------------------------------------------------------------
    // product()
    // -------------------------------------------------------------------------

    #[Test]
    /** product() returns StandardProduct when type is Standard and product exists. */
    public function product_returns_standard_product_when_type_is_standard(): void
    {
        $product = StandardProduct::factory()->create();
        $item = WishlistItem::factory()->create([
            'product_id' => $product->product_id,
        ]);

        $resolved = $item->product();

        $this->assertInstanceOf(StandardProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }

    #[Test]
    /** product() returns CustomizableProduct when type is Customizable and product exists. */
    public function product_returns_customizable_product_when_type_is_customizable(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $item = WishlistItem::factory()->customizable()->create([
            'product_id' => $product->product_id,
        ]);

        $resolved = $item->product();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }

    #[Test]
    /** product() returns null when Standard product does not exist. */
    public function product_returns_null_when_standard_product_does_not_exist(): void
    {
        $item = WishlistItem::factory()->create([
            'product_id' => 99999,
            'product_type' => ProductType::Standard,
        ]);

        $this->assertNull($item->product());
    }

    #[Test]
    /** product() returns null when Customizable product does not exist. */
    public function product_returns_null_when_customizable_product_does_not_exist(): void
    {
        $item = WishlistItem::factory()->create([
            'product_id' => 99999,
            'product_type' => ProductType::Customizable,
        ]);

        $this->assertNull($item->product());
    }

    // -------------------------------------------------------------------------
    // isProductAvailable()
    // -------------------------------------------------------------------------

    #[Test]
    /** isProductAvailable() returns false when product does not exist. */
    public function is_product_available_returns_false_when_product_does_not_exist(): void
    {
        $item = WishlistItem::factory()->create([
            'product_id' => 99999,
            'product_type' => ProductType::Standard,
        ]);

        $this->assertFalse($item->isProductAvailable());
    }

    #[Test]
    /** isProductAvailable() returns false when product is inactive. */
    public function is_product_available_returns_false_when_product_is_inactive(): void
    {
        $product = StandardProduct::factory()->inactive()->create();
        $item = WishlistItem::factory()->create([
            'product_id' => $product->product_id,
        ]);

        $this->assertFalse($item->isProductAvailable());
    }

    #[Test]
    /** isProductAvailable() returns true when product is active. */
    public function is_product_available_returns_true_when_product_is_active(): void
    {
        $product = StandardProduct::factory()->create();
        $item = WishlistItem::factory()->create([
            'product_id' => $product->product_id,
        ]);

        $this->assertTrue($item->isProductAvailable());
    }
}