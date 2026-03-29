<?php

namespace Tests\Unit\Models;

use App\Models\CustomerOrder;
use App\Models\CustomizablePrintProduct;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for the OrderItem model.
 *
 * Covers model configuration and both relationship structures and data
 * resolution for order() and product().
 */
class OrderItemTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the order_items table. */
    public function model_uses_order_items_table(): void
    {
        $item = new OrderItem;

        $this->assertSame('order_items', $item->getTable());
    }

    #[Test]
    /** Primary key is order_item_id. */
    public function primary_key_is_order_item_id(): void
    {
        $item = new OrderItem;

        $this->assertSame('order_item_id', $item->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $item = new OrderItem;

        $this->assertSame('int', $item->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $item = new OrderItem;

        $this->assertTrue($item->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $item = new OrderItem;

        $this->assertFalse($item->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $item = new OrderItem;
        $fillable = $item->getFillable();

        $this->assertContains('order_id', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('product_name', $fillable);
        $this->assertContains('unit_price', $fillable);
        $this->assertContains('quantity', $fillable);
        $this->assertContains('line_subtotal', $fillable);
        $this->assertContains('design_snapshot', $fillable);
        $this->assertContains('preview_image_reference', $fillable);
        $this->assertContains('print_file_reference', $fillable);
    }

    #[Test]
    /** unit_price is cast to decimal with 2 places. */
    public function unit_price_cast_is_configured(): void
    {
        $item = new OrderItem;

        $this->assertSame('decimal:2', $item->getCasts()['unit_price']);
    }

    #[Test]
    /** line_subtotal is cast to decimal with 2 places. */
    public function line_subtotal_cast_is_configured(): void
    {
        $item = new OrderItem;

        $this->assertSame('decimal:2', $item->getCasts()['line_subtotal']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – order()
    // -------------------------------------------------------------------------

    #[Test]
    /** order() returns a BelongsTo relation. */
    public function order_returns_belongs_to_relation(): void
    {
        $relation = (new OrderItem)->order();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** order() uses order_id as foreign key. */
    public function order_uses_order_id_as_foreign_key(): void
    {
        $relation = (new OrderItem)->order();

        $this->assertSame('order_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** order() relates to CustomerOrder model. */
    public function order_relates_to_customer_order_model(): void
    {
        $relation = (new OrderItem)->order();

        $this->assertInstanceOf(CustomerOrder::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – product()
    // -------------------------------------------------------------------------

    #[Test]
    /** product() returns a BelongsTo relation. */
    public function product_returns_belongs_to_relation(): void
    {
        $relation = (new OrderItem)->product();

        $this->assertInstanceOf(BelongsTo::class, $relation);
    }

    #[Test]
    /** product() uses product_id as foreign key. */
    public function product_uses_product_id_as_foreign_key(): void
    {
        $relation = (new OrderItem)->product();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** product() relates to CustomizablePrintProduct model. */
    public function product_relates_to_customizable_print_product_model(): void
    {
        $relation = (new OrderItem)->product();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** order() resolves to the customer order this item belongs to. */
    public function order_resolves_to_the_containing_customer_order(): void
    {
        $order = CustomerOrder::factory()->create();
        $item = OrderItem::factory()->create(['order_id' => $order->order_id]);

        $resolved = $item->order;

        $this->assertInstanceOf(CustomerOrder::class, $resolved);
        $this->assertSame($order->order_id, $resolved->order_id);
    }

    #[Test]
    /** product() resolves to the customizable product referenced by this item. */
    public function product_resolves_to_the_referenced_customizable_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $item = OrderItem::factory()->create(['product_id' => $product->product_id]);

        $resolved = $item->product;

        $this->assertInstanceOf(CustomizablePrintProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }
}
