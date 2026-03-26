<?php

namespace Tests\Unit\Factories;

use PHPUnit\Framework\Attributes\Test;
use App\Enums\ProductType;
use App\Models\CarouselSlide;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for CarouselSlideFactory.
 *
 * Covers the default state, product link states, image and description states,
 * and boundary states for title (2‑50) and description (1‑100, nullable).
 */
class CarouselSlideFactoryTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Default state
    // -------------------------------------------------------------------------

    #[Test]
    /** Default state creates a CarouselSlide record. */
    public function default_state_creates_carousel_slide_record(): void
    {
        $slide = CarouselSlide::factory()->create();

        $this->assertInstanceOf(CarouselSlide::class, $slide);
        $this->assertDatabaseHas('carousel_slides', ['slide_id' => $slide->slide_id]);
    }

    #[Test]
    /** Default state sets product_id to null. */
    public function default_state_sets_product_id_to_null(): void
    {
        $slide = CarouselSlide::factory()->create();

        $this->assertNull($slide->product_id);
    }

    #[Test]
    /** Default state sets product_type to null. */
    public function default_state_sets_product_type_to_null(): void
    {
        $slide = CarouselSlide::factory()->create();

        $this->assertNull($slide->product_type);
    }

    // -------------------------------------------------------------------------
    // Product link states
    // -------------------------------------------------------------------------

    #[Test]
    /** withStandardProduct state sets product_type to Standard. */
    public function with_standard_product_state_sets_product_type_to_standard(): void
    {
        $slide = CarouselSlide::factory()->withStandardProduct()->create();

        $this->assertInstanceOf(ProductType::class, $slide->product_type);
        $this->assertSame(ProductType::Standard, $slide->product_type);
    }

    #[Test]
    /** withStandardProduct state creates a linked standard product. */
    public function with_standard_product_state_creates_a_linked_standard_product(): void
    {
        $slide = CarouselSlide::factory()->withStandardProduct()->create();

        $this->assertNotNull($slide->product_id);
        $this->assertDatabaseHas('standard_products', ['product_id' => $slide->product_id]);
    }

    #[Test]
    /** withCustomizableProduct state sets product_type to Customizable. */
    public function with_customizable_product_state_sets_product_type_to_customizable(): void
    {
        $slide = CarouselSlide::factory()->withCustomizableProduct()->create();

        $this->assertSame(ProductType::Customizable, $slide->product_type);
        $this->assertDatabaseHas('carousel_slides', [
            'slide_id'     => $slide->slide_id,
            'product_type' => ProductType::Customizable->value,
        ]);
    }

    #[Test]
    /** withCustomizableProduct state creates a linked customizable product. */
    public function with_customizable_product_state_creates_a_linked_customizable_print_product(): void
    {
        $slide = CarouselSlide::factory()->withCustomizableProduct()->create();

        $this->assertNotNull($slide->product_id);
        $this->assertDatabaseHas('customizable_print_products', ['product_id' => $slide->product_id]);
    }

    #[Test]
    /** withoutProduct state sets product_id to null. */
    public function without_product_state_sets_product_id_to_null(): void
    {
        $slide = CarouselSlide::factory()->withoutProduct()->create();

        $this->assertNull($slide->product_id);
    }

    #[Test]
    /** withoutProduct state sets product_type to null. */
    public function without_product_state_sets_product_type_to_null(): void
    {
        $slide = CarouselSlide::factory()->withoutProduct()->create();

        $this->assertNull($slide->product_type);
    }

    // -------------------------------------------------------------------------
    // Image and description states
    // -------------------------------------------------------------------------

    #[Test]
    /** withoutImage state sets image_reference to null. */
    public function without_image_state_sets_image_reference_to_null(): void
    {
        $slide = CarouselSlide::factory()->withoutImage()->create();

        $this->assertNull($slide->image_reference);
    }

    #[Test]
    /** withoutDescription state sets description to null. */
    public function without_description_state_sets_description_to_null(): void
    {
        $slide = CarouselSlide::factory()->withoutDescription()->create();

        $this->assertNull($slide->description);
    }

    // -------------------------------------------------------------------------
    // Title boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** titleMinLength state sets title to 2 characters (minimum). */
    public function title_min_length_state_sets_title_to_two_characters(): void
    {
        $slide = CarouselSlide::factory()->titleMinLength()->create();

        $this->assertSame(2, strlen($slide->title));
    }

    #[Test]
    /** titleMidLength state sets title to 26 characters (in‑range). */
    public function title_mid_length_state_sets_title_to_twenty_six_characters(): void
    {
        $slide = CarouselSlide::factory()->titleMidLength()->create();

        $this->assertSame(26, strlen($slide->title));
    }

    #[Test]
    /** titleMaxLength state sets title to 50 characters (maximum). */
    public function title_max_length_state_sets_title_to_fifty_characters(): void
    {
        $slide = CarouselSlide::factory()->titleMaxLength()->create();

        $this->assertSame(50, strlen($slide->title));
    }

    // -------------------------------------------------------------------------
    // Title boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** titleTooShort state sets title to 1 character (below minimum). */
    public function title_too_short_state_sets_title_to_one_character(): void
    {
        $slide = CarouselSlide::factory()->titleTooShort()->make();

        $this->assertSame(1, strlen($slide->title));
    }

    #[Test]
    /** titleTooLong state sets title to 51 characters (above maximum). */
    public function title_too_long_state_sets_title_to_fifty_one_characters(): void
    {
        $slide = CarouselSlide::factory()->titleTooLong()->make();

        $this->assertSame(51, strlen($slide->title));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – valid (create)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionMinLength state sets description to 1 character (minimum). */
    public function description_min_length_state_sets_description_to_one_character(): void
    {
        $slide = CarouselSlide::factory()->descriptionMinLength()->create();

        $this->assertSame(1, strlen($slide->description));
    }

    #[Test]
    /** descriptionMidLength state sets description to 50 characters (in‑range). */
    public function description_mid_length_state_sets_description_to_fifty_characters(): void
    {
        $slide = CarouselSlide::factory()->descriptionMidLength()->create();

        $this->assertSame(50, strlen($slide->description));
    }

    #[Test]
    /** descriptionMaxLength state sets description to 100 characters (maximum). */
    public function description_max_length_state_sets_description_to_one_hundred_characters(): void
    {
        $slide = CarouselSlide::factory()->descriptionMaxLength()->create();

        $this->assertSame(100, strlen($slide->description));
    }

    // -------------------------------------------------------------------------
    // Description boundaries – invalid (make)
    // -------------------------------------------------------------------------

    #[Test]
    /** descriptionTooLong state sets description to 101 characters (above maximum). */
    public function description_too_long_state_sets_description_to_one_hundred_one_characters(): void
    {
        $slide = CarouselSlide::factory()->descriptionTooLong()->make();

        $this->assertSame(101, strlen($slide->description));
    }
}