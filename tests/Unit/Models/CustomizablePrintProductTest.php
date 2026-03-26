<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductVisibilityStatus;
use App\Models\CartItem;
use App\Models\CustomizablePrintProduct;
use App\Models\OrderItem;
use App\Models\PricingTier;
use App\Models\SavedDesign;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the CustomizablePrintProduct model.
 *
 * Covers model configuration, relationship structure and data resolution,
 * and business logic for isActive() and tierForQuantity().
 *
 * tierForQuantity() boundary values:
 * - Quantity below minimum range: null
 * - Quantity at minimum boundary: returns tier
 * - Quantity within range: returns tier
 * - Quantity at maximum boundary: returns tier
 * - Quantity above maximum range: null
 *
 * NOTE — known bug: tierForQuantity() calls $tier->appliesToQuantity()
 * but PricingTier defines appliesTo(). The tierForQuantity() tests
 * document correct expected behaviour and will fail until the method
 * call is corrected in the model.
 */
class CustomizablePrintProductTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the customizable_print_products table. */
    public function model_uses_customizable_print_products_table(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertSame('customizable_print_products', $product->getTable());
    }

    #[Test]
    /** Primary key is product_id. */
    public function primary_key_is_product_id(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertSame('product_id', $product->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertSame('int', $product->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertTrue($product->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertFalse($product->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $product = new CustomizablePrintProduct();
        $fillable = $product->getFillable();

        $this->assertContains('product_name', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('image_reference', $fillable);
        $this->assertContains('visibility_status', $fillable);
        $this->assertContains('template_config', $fillable);
    }

    #[Test]
    /** visibility_status is cast to ProductVisibilityStatus enum. */
    public function visibility_status_cast_is_configured(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertSame(ProductVisibilityStatus::class, $product->getCasts()['visibility_status']);
    }

    #[Test]
    /** template_config is cast to array. */
    public function template_config_cast_is_configured(): void
    {
        $product = new CustomizablePrintProduct();

        $this->assertSame('array', $product->getCasts()['template_config']);
    }

    // -------------------------------------------------------------------------
    // Relationship structure – pricingTiers()
    // -------------------------------------------------------------------------

    #[Test]
    /** pricingTiers() returns a HasMany relation. */
    public function pricing_tiers_returns_has_many_relation(): void
    {
        $relation = (new CustomizablePrintProduct())->pricingTiers();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** pricingTiers() uses product_id as foreign key. */
    public function pricing_tiers_uses_product_id_as_foreign_key(): void
    {
        $relation = (new CustomizablePrintProduct())->pricingTiers();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** pricingTiers() relates to PricingTier model. */
    public function pricing_tiers_relates_to_pricing_tier_model(): void
    {
        $relation = (new CustomizablePrintProduct())->pricingTiers();

        $this->assertInstanceOf(PricingTier::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – savedDesigns()
    // -------------------------------------------------------------------------

    #[Test]
    /** savedDesigns() returns a HasMany relation. */
    public function saved_designs_returns_has_many_relation(): void
    {
        $relation = (new CustomizablePrintProduct())->savedDesigns();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** savedDesigns() uses product_id as foreign key. */
    public function saved_designs_uses_product_id_as_foreign_key(): void
    {
        $relation = (new CustomizablePrintProduct())->savedDesigns();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** savedDesigns() relates to SavedDesign model. */
    public function saved_designs_relates_to_saved_design_model(): void
    {
        $relation = (new CustomizablePrintProduct())->savedDesigns();

        $this->assertInstanceOf(SavedDesign::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – cartItems()
    // -------------------------------------------------------------------------

    #[Test]
    /** cartItems() returns a HasMany relation. */
    public function cart_items_returns_has_many_relation(): void
    {
        $relation = (new CustomizablePrintProduct())->cartItems();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** cartItems() uses product_id as foreign key. */
    public function cart_items_uses_product_id_as_foreign_key(): void
    {
        $relation = (new CustomizablePrintProduct())->cartItems();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** cartItems() relates to CartItem model. */
    public function cart_items_relates_to_cart_item_model(): void
    {
        $relation = (new CustomizablePrintProduct())->cartItems();

        $this->assertInstanceOf(CartItem::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship structure – orderItems()
    // -------------------------------------------------------------------------

    #[Test]
    /** orderItems() returns a HasMany relation. */
    public function order_items_returns_has_many_relation(): void
    {
        $relation = (new CustomizablePrintProduct())->orderItems();

        $this->assertInstanceOf(HasMany::class, $relation);
    }

    #[Test]
    /** orderItems() uses product_id as foreign key. */
    public function order_items_uses_product_id_as_foreign_key(): void
    {
        $relation = (new CustomizablePrintProduct())->orderItems();

        $this->assertSame('product_id', $relation->getForeignKeyName());
    }

    #[Test]
    /** orderItems() relates to OrderItem model. */
    public function order_items_relates_to_order_item_model(): void
    {
        $relation = (new CustomizablePrintProduct())->orderItems();

        $this->assertInstanceOf(OrderItem::class, $relation->getRelated());
    }

    // -------------------------------------------------------------------------
    // Relationship data resolution
    // -------------------------------------------------------------------------

    #[Test]
    /** pricingTiers() resolves to all tiers belonging to the product. */
    public function pricing_tiers_resolves_to_products_pricing_tiers(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        PricingTier::factory()->count(3)->create(['product_id' => $product->product_id]);

        $this->assertCount(3, $product->pricingTiers);
        $product->pricingTiers->each(
            fn ($tier) => $this->assertSame($product->product_id, $tier->product_id)
        );
    }

    #[Test]
    /** pricingTiers() excludes tiers belonging to other products. */
    public function pricing_tiers_excludes_tiers_for_other_products(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        PricingTier::factory()->count(2)->create(['product_id' => $product->product_id]);
        PricingTier::factory()->count(3)->create();

        $this->assertCount(2, $product->pricingTiers);
    }

    #[Test]
    /** pricingTiers() returns tiers ordered by minimum quantity ascending. */
    public function pricing_tiers_are_ordered_ascending_by_minimum_quantity(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        PricingTier::factory()->thirdTier()->create(['product_id' => $product->product_id]);
        PricingTier::factory()->firstTier()->create(['product_id' => $product->product_id]);
        PricingTier::factory()->secondTier()->create(['product_id' => $product->product_id]);

        $minimums = $product->pricingTiers->pluck('minimum_quantity')->all();

        $this->assertSame([1, 10, 50], $minimums);
    }

    #[Test]
    /** savedDesigns() resolves to all saved designs for the product. */
    public function saved_designs_resolves_to_products_saved_designs(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        SavedDesign::factory()->count(2)->create(['product_id' => $product->product_id]);

        $this->assertCount(2, $product->savedDesigns);
        $product->savedDesigns->each(
            fn ($design) => $this->assertSame($product->product_id, $design->product_id)
        );
    }

    #[Test]
    /** cartItems() resolves to all cart items referencing the product. */
    public function cart_items_resolves_to_products_cart_items(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        CartItem::factory()->count(2)->create(['product_id' => $product->product_id]);

        $this->assertCount(2, $product->cartItems);
        $product->cartItems->each(
            fn ($item) => $this->assertSame($product->product_id, $item->product_id)
        );
    }

    #[Test]
    /** orderItems() resolves to all order items referencing the product. */
    public function order_items_resolves_to_products_order_items(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        OrderItem::factory()->count(3)->create(['product_id' => $product->product_id]);

        $this->assertCount(3, $product->orderItems);
        $product->orderItems->each(
            fn ($item) => $this->assertSame($product->product_id, $item->product_id)
        );
    }

    // -------------------------------------------------------------------------
    // isActive()
    // -------------------------------------------------------------------------

    #[Test]
    /** isActive() returns true when visibility status is Active. */
    public function is_active_returns_true_for_active_product(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        $this->assertTrue($product->isActive());
    }

    #[Test]
    /** isActive() returns false when visibility status is Inactive. */
    public function is_active_returns_false_for_inactive_product(): void
    {
        $product = CustomizablePrintProduct::factory()->inactive()->create();

        $this->assertFalse($product->isActive());
    }

    // -------------------------------------------------------------------------
    // tierForQuantity()
    // -------------------------------------------------------------------------

    #[Test]
    /** tierForQuantity() returns null when product has no pricing tiers. */
    public function tier_for_quantity_returns_null_when_no_tiers_exist(): void
    {
        $product = CustomizablePrintProduct::factory()->create();

        $this->assertNull($product->tierForQuantity(5));
    }

    #[Test]
    /** tierForQuantity() returns null when quantity is below minimum range. */
    public function tier_for_quantity_returns_null_when_quantity_is_below_minimum(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        PricingTier::factory()->create([
            'product_id' => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
        ]);
        $product->load('pricingTiers');

        $this->assertNull($product->tierForQuantity(0));
    }

    #[Test]
    /** tierForQuantity() returns tier when quantity equals minimum boundary. */
    public function tier_for_quantity_returns_tier_when_quantity_equals_minimum_quantity(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $tier = PricingTier::factory()->create([
            'product_id' => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
        ]);
        $product->load('pricingTiers');

        $result = $product->tierForQuantity(1);

        $this->assertInstanceOf(PricingTier::class, $result);
        $this->assertSame($tier->tier_id, $result->tier_id);
    }

    #[Test]
    /** tierForQuantity() returns tier when quantity is within range. */
    public function tier_for_quantity_returns_tier_when_quantity_is_within_range(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $tier = PricingTier::factory()->create([
            'product_id' => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
        ]);
        $product->load('pricingTiers');

        $result = $product->tierForQuantity(5);

        $this->assertInstanceOf(PricingTier::class, $result);
        $this->assertSame($tier->tier_id, $result->tier_id);
    }

    #[Test]
    /** tierForQuantity() returns tier when quantity equals maximum boundary. */
    public function tier_for_quantity_returns_tier_when_quantity_equals_maximum_quantity(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $tier = PricingTier::factory()->create([
            'product_id' => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
        ]);
        $product->load('pricingTiers');

        $result = $product->tierForQuantity(9);

        $this->assertInstanceOf(PricingTier::class, $result);
        $this->assertSame($tier->tier_id, $result->tier_id);
    }

    #[Test]
    /** tierForQuantity() returns null when quantity exceeds maximum range. */
    public function tier_for_quantity_returns_null_when_quantity_exceeds_maximum(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        PricingTier::factory()->create([
            'product_id' => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
        ]);
        $product->load('pricingTiers');

        $this->assertNull($product->tierForQuantity(10));
    }

    #[Test]
    /** tierForQuantity() returns correct tier when multiple tiers exist. */
    public function tier_for_quantity_returns_correct_tier_for_multiple_tiers(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $firstTier = PricingTier::factory()->firstTier()->create(['product_id' => $product->product_id]);
        $secondTier = PricingTier::factory()->secondTier()->create(['product_id' => $product->product_id]);
        $product->load('pricingTiers');

        $this->assertSame($firstTier->tier_id, $product->tierForQuantity(5)->tier_id);
        $this->assertSame($secondTier->tier_id, $product->tierForQuantity(20)->tier_id);
    }
}