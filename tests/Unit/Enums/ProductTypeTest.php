<?php

namespace Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductType;
use App\Models\CarouselSlide;
use App\Models\WishlistItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ProductType enum.
 *
 * Covers backing values and model casting on CarouselSlide and WishlistItem.
 * CarouselSlide.product_type is nullable; WishlistItem.product_type is required.
 */
class ProductTypeTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Backing values
    // -------------------------------------------------------------------------

    #[Test]
    /** Standard has the backing value 'standard'. */
    public function standard_has_backing_value_of_standard(): void
    {
        $this->assertSame('standard', ProductType::Standard->value);
    }

    #[Test]
    /** Customizable has the backing value 'customizable'. */
    public function customizable_has_backing_value_of_customizable(): void
    {
        $this->assertSame('customizable', ProductType::Customizable->value);
    }

    // -------------------------------------------------------------------------
    // Model casting — CarouselSlide
    // -------------------------------------------------------------------------

    #[Test]
    /** CarouselSlide product type is stored as the backing value string. */
    public function carousel_slide_product_type_is_stored_as_backing_value(): void
    {
        $slide = CarouselSlide::factory()->withStandardProduct()->create();

        $this->assertDatabaseHas('carousel_slides', [
            'slide_id'     => $slide->slide_id,
            'product_type' => ProductType::Standard->value,
        ]);
    }

    #[Test]
    /** CarouselSlide product type is retrieved as a ProductType instance. */
    public function carousel_slide_product_type_is_retrieved_as_enum_instance(): void
    {
        $slide = CarouselSlide::factory()->withStandardProduct()->create();
        $fresh = CarouselSlide::find($slide->slide_id);

        $this->assertInstanceOf(ProductType::class, $fresh->product_type);
        $this->assertSame(ProductType::Standard, $fresh->product_type);
    }

    #[Test]
    /** CarouselSlide product type remains null when no product is linked. */
    public function carousel_slide_product_type_remains_null_when_no_product_is_linked(): void
    {
        $slide = CarouselSlide::factory()->withoutProduct()->create();
        $fresh = CarouselSlide::find($slide->slide_id);

        $this->assertNull($fresh->product_type);
    }

    // -------------------------------------------------------------------------
    // Model casting — WishlistItem
    // -------------------------------------------------------------------------

    #[Test]
    /** WishlistItem product type is stored as the backing value string. */
    public function wishlist_item_product_type_is_stored_as_backing_value(): void
    {
        $item = WishlistItem::factory()->customizable()->create();

        $this->assertDatabaseHas('wishlist_items', [
            'wishlist_item_id' => $item->wishlist_item_id,
            'product_type'     => ProductType::Customizable->value,
        ]);
    }

    #[Test]
    /** WishlistItem product type is retrieved as a ProductType instance. */
    public function wishlist_item_product_type_is_retrieved_as_enum_instance(): void
    {
        $item  = WishlistItem::factory()->customizable()->create();
        $fresh = WishlistItem::find($item->wishlist_item_id);

        $this->assertInstanceOf(ProductType::class, $fresh->product_type);
        $this->assertSame(ProductType::Customizable, $fresh->product_type);
    }
}