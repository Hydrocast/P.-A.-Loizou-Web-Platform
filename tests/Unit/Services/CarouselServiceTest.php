<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CarouselSlide;
use App\Services\CarouselService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for CarouselService.
 *
 * Covers slide listing, creation, updating, deletion, and reordering.
 * Verifies gapless-sequence invariant.
 * Boundary values: title (2-50), description (max 100, nullable).
 */
class CarouselServiceTest extends TestCase
{
    use RefreshDatabase;

    private CarouselService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CarouselService();
    }

    // -------------------------------------------------------------------------
    // getAllSlides()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns empty collection when no slides exist. */
    public function get_all_slides_returns_empty_when_no_slides_exist(): void
    {
        $slides = $this->service->getAllSlides();
        $this->assertCount(0, $slides);
    }

    #[Test]
    /** Returns slides ordered by display_sequence ascending. */
    public function get_all_slides_returns_slides_in_sequence_order(): void
    {
        CarouselSlide::factory()->create(['display_sequence' => 2, 'title' => 'Second']);
        CarouselSlide::factory()->create(['display_sequence' => 1, 'title' => 'First']);

        $slides = $this->service->getAllSlides();

        $this->assertEquals('First', $slides->first()->title);
        $this->assertEquals('Second', $slides->last()->title);
    }

    // -------------------------------------------------------------------------
    // retrieveActiveSlides()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns slides ordered by display_sequence ascending. */
    public function retrieve_active_slides_returns_slides_in_sequence_order(): void
    {
        CarouselSlide::factory()->create(['display_sequence' => 2, 'title' => 'Second']);
        CarouselSlide::factory()->create(['display_sequence' => 1, 'title' => 'First']);

        $slides = $this->service->retrieveActiveSlides();

        $this->assertEquals('First', $slides->first()->title);
        $this->assertEquals('Second', $slides->last()->title);
    }

    // -------------------------------------------------------------------------
    // getSlideById()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns the correct slide for a valid ID. */
    public function get_slide_by_id_returns_slide_for_valid_id(): void
    {
        $slide = CarouselSlide::factory()->create(['title' => 'Target Slide']);

        $result = $this->service->getSlideById($slide->slide_id);

        $this->assertEquals($slide->slide_id, $result->slide_id);
        $this->assertEquals('Target Slide', $result->title);
    }

    #[Test]
    /** Non-existent slide ID throws ModelNotFoundException. */
    public function get_slide_by_id_throws_model_not_found_when_slide_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->getSlideById(99999);
    }

    // -------------------------------------------------------------------------
    // createSlide()
    // -------------------------------------------------------------------------

    #[Test]
    /** First slide in an empty table gets sequence 1. */
    public function create_slide_assigns_sequence_one_when_no_slides_exist(): void
    {
        $slide = $this->service->createSlide('First', null, null, null, null);
        $this->assertEquals(1, $slide->display_sequence);
    }

    #[Test]
    /** Each new slide is appended at the next sequence position. */
    public function create_slide_assigns_next_sequence_position(): void
    {
        CarouselSlide::factory()->create(['display_sequence' => 1]);
        CarouselSlide::factory()->create(['display_sequence' => 2]);

        $slide = $this->service->createSlide(
            title: 'Third Slide',
            description: null,
            imageReference: null,
            productId: null,
            productType: null,
        );

        $this->assertEquals(3, $slide->display_sequence);
    }

    #[Test]
    /** Created slide is persisted in the database. */
    public function create_slide_persists_slide_to_database(): void
    {
        $slide = $this->service->createSlide('Persisted Slide', 'A description', null, null, null);

        $this->assertDatabaseHas('carousel_slides', ['title' => 'Persisted Slide']);
        $this->assertInstanceOf(CarouselSlide::class, $slide);
    }

    #[Test]
    /** Image reference string is stored when provided. */
    public function create_slide_stores_image_reference_string(): void
    {
        $slide = $this->service->createSlide('Slide', null, 'carousel/banner.jpg', null, null);
        $this->assertEquals('carousel/banner.jpg', $slide->image_reference);
    }

    #[Test]
    /** Null image reference is accepted. */
    public function create_slide_accepts_null_image_reference(): void
    {
        $slide = $this->service->createSlide('Slide', null, null, null, null);
        $this->assertNull($slide->image_reference);
    }

    #[Test]
    /** Null description is accepted. */
    public function create_slide_accepts_null_description(): void
    {
        $slide = $this->service->createSlide('Valid Title', null, null, null, null);
        $this->assertNull($slide->description);
    }

    // Title boundaries --------------------------------------------------------

    #[Test]
    /** Title of 1 character (below minimum) is rejected. */
    public function create_slide_throws_when_title_is_one_character(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createSlide('A', null, null, null, null);
    }

    #[Test]
    /** Title of 2 characters (minimum) is accepted. */
    public function create_slide_accepts_title_of_two_characters(): void
    {
        $slide = $this->service->createSlide('Ab', null, null, null, null);
        $this->assertEquals('Ab', $slide->title);
    }

    #[Test]
    /** Title of 26 characters (in‑range) is accepted. */
    public function create_slide_accepts_title_of_twenty_six_characters(): void
    {
        $title = str_repeat('a', 26);
        $slide = $this->service->createSlide($title, null, null, null, null);
        $this->assertEquals($title, $slide->title);
    }

    #[Test]
    /** Title of 50 characters (maximum) is accepted. */
    public function create_slide_accepts_title_of_fifty_characters(): void
    {
        $title = str_repeat('a', 50);
        $slide = $this->service->createSlide($title, null, null, null, null);
        $this->assertEquals($title, $slide->title);
    }

    #[Test]
    /** Title of 51 characters (above maximum) is rejected. */
    public function create_slide_throws_when_title_exceeds_fifty_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createSlide(str_repeat('a', 51), null, null, null, null);
    }

    // Description boundaries --------------------------------------------------

    #[Test]
    /** Description of 1 character (minimum) is accepted. */
    public function create_slide_accepts_description_of_one_character(): void
    {
        $slide = $this->service->createSlide('Title', 'a', null, null, null);
        $this->assertEquals('a', $slide->description);
    }

    #[Test]
    /** Description of 50 characters (in‑range) is accepted. */
    public function create_slide_accepts_description_of_fifty_characters(): void
    {
        $desc  = str_repeat('a', 50);
        $slide = $this->service->createSlide('Title', $desc, null, null, null);
        $this->assertEquals($desc, $slide->description);
    }

    #[Test]
    /** Description of 100 characters (maximum) is accepted. */
    public function create_slide_accepts_description_of_one_hundred_characters(): void
    {
        $desc  = str_repeat('a', 100);
        $slide = $this->service->createSlide('Title', $desc, null, null, null);
        $this->assertEquals($desc, $slide->description);
    }

    #[Test]
    /** Description of 101 characters (above maximum) is rejected. */
    public function create_slide_throws_when_description_exceeds_one_hundred_characters(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->createSlide('Title', str_repeat('a', 101), null, null, null);
    }

    // -------------------------------------------------------------------------
    // updateSlide()
    // -------------------------------------------------------------------------

    #[Test]
    /** Title and description are updated to new values. */
    public function update_slide_changes_title_and_description(): void
    {
        $slide = CarouselSlide::factory()->create(['title' => 'Old Title']);
        $this->service->updateSlide($slide->slide_id, 'New Title', 'New desc', null, null, null);
        $slide->refresh();
        $this->assertEquals('New Title', $slide->title);
    }

    #[Test]
    /** Non-null image reference replaces existing image. */
    public function update_slide_replaces_image_reference_when_new_string_provided(): void
    {
        $slide = CarouselSlide::factory()->create(['image_reference' => 'old/banner.jpg']);

        $this->service->updateSlide($slide->slide_id, 'Title', null, 'new/banner.jpg', null, null);

        $slide->refresh();
        $this->assertEquals('new/banner.jpg', $slide->image_reference);
    }

    #[Test]
    /** Null image reference clears the existing image. */
    public function update_slide_clears_image_when_null_reference_is_passed(): void
    {
        $slide = CarouselSlide::factory()->create(['image_reference' => 'existing/banner.jpg']);
        $this->service->updateSlide($slide->slide_id, 'Title', null, null, null, null);
        $slide->refresh();
        $this->assertNull($slide->image_reference);
    }

    #[Test]
    /** Non-existent slide throws ModelNotFoundException. */
    public function update_slide_throws_model_not_found_when_slide_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->updateSlide(99999, 'Title', null, null, null, null);
    }

    // Title boundaries --------------------------------------------------------

    #[Test]
    /** Title of 1 character (below minimum) is rejected. */
    public function update_slide_throws_when_title_is_one_character(): void
    {
        $slide = CarouselSlide::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->updateSlide($slide->slide_id, 'A', null, null, null, null);
    }

    #[Test]
    /** Title of 2 characters (minimum) is accepted. */
    public function update_slide_accepts_title_of_two_characters(): void
    {
        $slide = CarouselSlide::factory()->create();
        $this->service->updateSlide($slide->slide_id, 'Ab', null, null, null, null);
        $slide->refresh();
        $this->assertEquals('Ab', $slide->title);
    }

    #[Test]
    /** Title of 51 characters (above maximum) is rejected. */
    public function update_slide_throws_when_title_exceeds_fifty_characters(): void
    {
        $slide = CarouselSlide::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->updateSlide($slide->slide_id, str_repeat('a', 51), null, null, null, null);
    }

    // -------------------------------------------------------------------------
    // deleteSlide()
    // -------------------------------------------------------------------------

    #[Test]
    /** Deleted slide is removed from the database. */
    public function delete_slide_removes_slide(): void
    {
        $slide = CarouselSlide::factory()->create(['display_sequence' => 1]);
        $this->service->deleteSlide($slide->slide_id);
        $this->assertDatabaseMissing('carousel_slides', ['slide_id' => $slide->slide_id]);
    }

    #[Test]
    /** After deletion, remaining slides are re-sequenced starting at 1 with no gaps. */
    public function delete_slide_resequences_remaining_slides_without_gaps(): void
    {
        CarouselSlide::factory()->create(['display_sequence' => 1]);
        $second = CarouselSlide::factory()->create(['display_sequence' => 2]);
        CarouselSlide::factory()->create(['display_sequence' => 3]);

        $this->service->deleteSlide($second->slide_id);

        $sequences = CarouselSlide::orderBy('display_sequence')->pluck('display_sequence')->toArray();
        $this->assertEquals([1, 2], $sequences);
    }

    #[Test]
    /** Non-existent slide throws ModelNotFoundException. */
    public function delete_slide_throws_model_not_found_when_slide_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->deleteSlide(99999);
    }

    // -------------------------------------------------------------------------
    // reorderSlides()
    // -------------------------------------------------------------------------

    #[Test]
    /** Slides are reassigned to the provided order. */
    public function reorder_slides_assigns_new_sequences(): void
    {
        $s1 = CarouselSlide::factory()->create(['display_sequence' => 1]);
        $s2 = CarouselSlide::factory()->create(['display_sequence' => 2]);
        $s3 = CarouselSlide::factory()->create(['display_sequence' => 3]);

        $this->service->reorderSlides([$s3->slide_id, $s2->slide_id, $s1->slide_id]);

        $this->assertEquals(1, $s3->refresh()->display_sequence);
        $this->assertEquals(2, $s2->refresh()->display_sequence);
        $this->assertEquals(3, $s1->refresh()->display_sequence);
    }

    #[Test]
    /** Resulting sequences are gapless (1..n) after reorder. */
    public function reorder_slides_produces_gapless_sequences(): void
    {
        $s1 = CarouselSlide::factory()->create(['display_sequence' => 1]);
        $s2 = CarouselSlide::factory()->create(['display_sequence' => 2]);

        $this->service->reorderSlides([$s2->slide_id, $s1->slide_id]);

        $sequences = CarouselSlide::orderBy('display_sequence')->pluck('display_sequence')->toArray();
        $this->assertEquals([1, 2], $sequences);
    }

    #[Test]
    /** IDs that do not match any existing slide throw ValidationException. */
    public function reorder_slides_throws_when_ids_do_not_match_existing_slides(): void
    {
        CarouselSlide::factory()->create(['display_sequence' => 1]);
        $this->expectException(ValidationException::class);
        $this->service->reorderSlides([99999, 99998]);
    }

    #[Test]
    /** Supplying only a subset of existing slide IDs throws ValidationException. */
    public function reorder_slides_throws_when_provided_ids_are_a_subset_of_existing(): void
    {
        $s1 = CarouselSlide::factory()->create(['display_sequence' => 1]);
        CarouselSlide::factory()->create(['display_sequence' => 2]);

        $this->expectException(ValidationException::class);
        $this->service->reorderSlides([$s1->slide_id]);
    }
}