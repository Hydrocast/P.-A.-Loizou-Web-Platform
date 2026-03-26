<?php

namespace Tests\Unit\Models;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductType;
use App\Models\CarouselSlide;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for the CarouselSlide model.
 *
 * Covers model configuration and business logic for linkedProduct()
 * and hasProductLink().
 *
 * linkedProduct() boundary values:
 * - product_id null: null
 * - product_type null: null
 * - Standard type, product exists: returns StandardProduct
 * - Customizable type, product exists: returns CustomizablePrintProduct
 * - Standard type, product missing: null
 * - Customizable type, product missing: null
 *
 * hasProductLink() boundary values:
 * - product_id null: false
 * - product_id not null: true
 */
class CarouselSlideTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Model configuration
    // -------------------------------------------------------------------------

    #[Test]
    /** Model uses the carousel_slides table. */
    public function model_uses_carousel_slides_table(): void
    {
        $slide = new CarouselSlide();

        $this->assertSame('carousel_slides', $slide->getTable());
    }

    #[Test]
    /** Primary key is slide_id. */
    public function primary_key_is_slide_id(): void
    {
        $slide = new CarouselSlide();

        $this->assertSame('slide_id', $slide->getKeyName());
    }

    #[Test]
    /** Primary key type is integer. */
    public function primary_key_type_is_integer(): void
    {
        $slide = new CarouselSlide();

        $this->assertSame('int', $slide->getKeyType());
    }

    #[Test]
    /** Primary key is auto-incrementing. */
    public function primary_key_is_auto_incrementing(): void
    {
        $slide = new CarouselSlide();

        $this->assertTrue($slide->incrementing);
    }

    #[Test]
    /** Timestamps are disabled. */
    public function timestamps_are_disabled(): void
    {
        $slide = new CarouselSlide();

        $this->assertFalse($slide->timestamps);
    }

    #[Test]
    /** Fillable array contains expected fields. */
    public function fillable_contains_expected_fields(): void
    {
        $slide = new CarouselSlide();
        $fillable = $slide->getFillable();

        $this->assertContains('title', $fillable);
        $this->assertContains('description', $fillable);
        $this->assertContains('image_reference', $fillable);
        $this->assertContains('product_id', $fillable);
        $this->assertContains('product_type', $fillable);
        $this->assertContains('display_sequence', $fillable);
    }

    #[Test]
    /** product_type is cast to ProductType enum. */
    public function product_type_cast_is_configured(): void
    {
        $slide = new CarouselSlide();

        $this->assertSame(ProductType::class, $slide->getCasts()['product_type']);
    }

    // -------------------------------------------------------------------------
    // linkedProduct()
    // -------------------------------------------------------------------------

    #[Test]
    /** linkedProduct() returns null when product_id is null. */
    public function linked_product_returns_null_when_product_id_is_null(): void
    {
        $slide = CarouselSlide::factory()->withoutProduct()->create();

        $this->assertNull($slide->linkedProduct());
    }

    #[Test]
    /** linkedProduct() returns null when product_type is null. */
    public function linked_product_returns_null_when_product_type_is_null(): void
    {
        $slide = CarouselSlide::factory()->create([
            'product_id' => 1,
            'product_type' => null,
        ]);

        $this->assertNull($slide->linkedProduct());
    }

    #[Test]
    /** linkedProduct() returns StandardProduct when type is Standard and product exists. */
    public function linked_product_returns_standard_product_when_type_is_standard(): void
    {
        $product = StandardProduct::factory()->create();
        $slide = CarouselSlide::factory()->withStandardProduct()->create([
            'product_id' => $product->product_id,
        ]);

        $resolved = $slide->linkedProduct();

        $this->assertInstanceOf(StandardProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }

    #[Test]
    /** linkedProduct() returns CustomizableProduct when type is Customizable and product exists. */
    public function linked_product_returns_customizable_product_when_type_is_customizable(): void
    {
        $product = CustomizablePrintProduct::factory()->create();
        $slide = CarouselSlide::factory()->withCustomizableProduct()->create([
            'product_id' => $product->product_id,
        ]);

        $resolved = $slide->linkedProduct();

        $this->assertInstanceOf(CustomizablePrintProduct::class, $resolved);
        $this->assertSame($product->product_id, $resolved->product_id);
    }

    #[Test]
    /** linkedProduct() returns null when Standard product does not exist. */
    public function linked_product_returns_null_when_standard_product_does_not_exist(): void
    {
        $slide = CarouselSlide::factory()->create([
            'product_id' => 99999,
            'product_type' => ProductType::Standard,
        ]);

        $this->assertNull($slide->linkedProduct());
    }

    #[Test]
    /** linkedProduct() returns null when Customizable product does not exist. */
    public function linked_product_returns_null_when_customizable_product_does_not_exist(): void
    {
        $slide = CarouselSlide::factory()->create([
            'product_id' => 99999,
            'product_type' => ProductType::Customizable,
        ]);

        $this->assertNull($slide->linkedProduct());
    }

    // -------------------------------------------------------------------------
    // hasProductLink()
    // -------------------------------------------------------------------------

    #[Test]
    /** hasProductLink() returns false when product_id is null. */
    public function has_product_link_returns_false_when_product_id_is_null(): void
    {
        $slide = CarouselSlide::factory()->make([
            'product_id' => null,
        ]);

        $this->assertFalse($slide->hasProductLink());
    }

    #[Test]
    /** hasProductLink() returns true when product_id is set. */
    public function has_product_link_returns_true_when_product_id_is_set(): void
    {
        $slide = CarouselSlide::factory()->make([
            'product_id' => 1,
        ]);

        $this->assertTrue($slide->hasProductLink());
    }
}