<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\SavedDesign;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for the SavedDesign model.
 *
 * Covers model configuration, relationship structure and data resolution,
 * and business logic for belongsToCustomer() and isProductAvailable().
 *
 * belongsToCustomer() boundary values:
 * - Customer ID matches: true
 * - Customer ID differs: false
 *
 * isProductAvailable() boundary values:
 * - Product does not exist: false
 * - Product exists but is inactive: false
 * - Product exists and is active: true
 */
class SavedDesignTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the saved_designs table. */
    public function model_uses_saved_designs_table(): void
    {
        $design = new SavedDesign;

        $this->assertSame('saved_designs', $design->getTable());
    }

    #[Test]
    /** Primary key is design_id. */
    public function primary_key_is_design_id(): void
    {
        $design = new SavedDesign;

        $this->assertSame('design_id', $design->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $design = new SavedDesign;

        $this->assertSame('int', $design->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $design = new SavedDesign;

        $this->assertTrue($design->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $design = new SavedDesign;

        $this->assertFalse($design->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $design = new SavedDesign;
        $fillable = $design->getFillable();

        $this->assertContains('design_name', $fillable);
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('design_data', $fillable);
        $this->assertContains('preview_image_reference', $fillable);
        $this->assertContains('print_file_reference', $fillable);
        $this->assertContains('date_created', $fillable);
    }

    #[Test]
    /** date_created is cast to datetime. */
    public function date_created_cast_is_configured(): void
    {
        $design = new SavedDesign;

        $this->assertSame('datetime', $design->getCasts()['date_created']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – customer()
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() returns a BelongsTo relation. */
    public function customer_returns_belongs_to_relation(): void
    {
        $relation = (new SavedDesign)->customer();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** customer() uses customer_id as foreign key. */
    public function customer_uses_customer_id_as_foreign_key(): void
    {
        $relation = (new SavedDesign)->customer();

        $this->assertSame('customer_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** customer() relates to Customer model. */
    public function customer_relates_to_customer_model(): void
    {
        $relation = (new SavedDesign)->customer();

        $this->assertInstanceOf(Customer::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – product()
    // -------------------------------------------------------------------------

    #[Test]
    /** product() returns a BelongsTo relation. */
    public function product_returns_belongs_to_relation(): void
    {
        $relation = (new SavedDesign)->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** product() uses product_id as foreign key. */
    public function product_uses_product_id_as_foreign_key(): void
    {
        $relation = (new SavedDesign)->product();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** product() relates to CustomizablePrintProduct model. */
    public function product_relates_to_customizable_print_product_model(): void
    {
        $relation = (new SavedDesign)->product();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** customer() resolves to the customer who owns this design. */
    public function customer_resolves_to_the_owning_customer(): void
    {
        $customer = Customer::factory()->create();
        $design = SavedDesign::factory()->create(['customer_id' => $customer->customer_id]);

        $resolved = $design->customer;

        $this->assertInstanceOf(Customer::class, $resolved);
        $this->assertSame($customer->customer_id, $resolved->customer_id);
    }

    #[Test]
    /** product() resolves to the customizable product this design is based on. */
    public function product_resolves_to_the_referenced_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $design = SavedDesign::factory()->create(['product_id' => $product->product_id]);

        $resolved = $design->product;

        $this->assertInstanceOf(CustomizablePrintProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }

    // -------------------------------------------------------------------------
    // belongsToCustomer()
    // -------------------------------------------------------------------------

    #[Test]
    /** belongsToCustomer() returns true when customer ID matches. */
    public function belongs_to_customer_returns_true_when_customer_id_matches(): void
    {
        $design = SavedDesign::factory()->make(['customer_id' => 42]);

        $this->assertTrue($design->belongsToCustomer(42));
    }

    #[Test]
    /** belongsToCustomer() returns false when customer ID differs. */
    public function belongs_to_customer_returns_false_when_customer_id_differs(): void
    {
        $design = SavedDesign::factory()->make(['customer_id' => 42]);

        $this->assertFalse($design->belongsToCustomer(99));
    }

    // -------------------------------------------------------------------------
    // isProductAvailable()
    // -------------------------------------------------------------------------

    #[Test]
    /** isProductAvailable() returns false when product does not exist. */
    public function is_product_available_returns_false_when_product_does_not_exist(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $design = SavedDesign::factory()->create(['product_id' => $product->product_id]);
        $design->setRelation('product', null);

        $this->assertFalse($design->isProductAvailable());
    }

    #[Test]
    /** isProductAvailable() returns false when product is inactive. */
    public function is_product_available_returns_false_when_product_is_inactive(): void
    {
        $product = CustomizablePrintProduct::factory()->inactive()->create();
        $design = SavedDesign::factory()->create(['product_id' => $product->product_id]);

        $this->assertFalse($design->isProductAvailable());
    }

    #[Test]
    /** isProductAvailable() returns true when product is active. */
    public function is_product_available_returns_true_when_product_is_active(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $design = SavedDesign::factory()->create(['product_id' => $product->product_id]);

        $this->assertTrue($design->isProductAvailable());
    }
}
