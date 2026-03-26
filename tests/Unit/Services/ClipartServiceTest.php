<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Clipart;
use App\Services\ClipartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Unit tests for ClipartService.
 *
 * Covers retrieval of all clipart and fetching a single clipart by ID.
 */
class ClipartServiceTest extends TestCase
{
    use RefreshDatabase;

    private ClipartService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ClipartService();
    }

    // -------------------------------------------------------------------------
    // getAllClipart()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns an empty collection when no clipart exists. */
    public function get_all_clipart_returns_empty_collection_when_table_is_empty(): void
    {
        $result = $this->service->getAllClipart();
        $this->assertCount(0, $result);
    }

    #[Test]
    /** Returns all items when the table is populated. */
    public function get_all_clipart_returns_all_items_when_table_is_populated(): void
    {
        Clipart::factory()->count(4)->create();
        $result = $this->service->getAllClipart();
        $this->assertCount(4, $result);
    }

    #[Test]
    /** Returns items ordered alphabetically by name. */
    public function get_all_clipart_returns_items_ordered_alphabetically_by_name(): void
    {
        Clipart::factory()->create(['clipart_name' => 'Zebra_aaa']);
        Clipart::factory()->create(['clipart_name' => 'Apple_aaa']);
        Clipart::factory()->create(['clipart_name' => 'Mango_aaa']);

        $result = $this->service->getAllClipart();

        $this->assertEquals('Apple_aaa', $result->get(0)->clipart_name);
        $this->assertEquals('Mango_aaa', $result->get(1)->clipart_name);
        $this->assertEquals('Zebra_aaa', $result->get(2)->clipart_name);
    }

    // -------------------------------------------------------------------------
    // getClipartById()
    // -------------------------------------------------------------------------

    #[Test]
    /** ID of 0 (below minimum) throws ModelNotFoundException. */
    public function get_clipart_by_id_throws_when_id_is_zero(): void
    {
        Clipart::factory()->count(3)->create();
        $this->expectException(ModelNotFoundException::class);
        $this->service->getClipartById(0);
    }

    #[Test]
    /** Minimum existing ID is accepted and returns the correct clipart. */
    public function get_clipart_by_id_returns_item_at_minimum_seeded_id(): void
    {
        $items = Clipart::factory()->count(3)->create();
        $minId = $items->min('clipart_id');

        $result = $this->service->getClipartById($minId);

        $this->assertEquals($minId, $result->clipart_id);
    }

    #[Test]
    /** In‑range existing ID is accepted and returns the correct clipart. */
    public function get_clipart_by_id_returns_item_at_mid_range_id(): void
    {
        $items    = Clipart::factory()->count(5)->create();
        $sorted   = $items->sortBy('clipart_id')->values();
        $middleId = $sorted->get(2)->clipart_id;

        $result = $this->service->getClipartById($middleId);

        $this->assertEquals($middleId, $result->clipart_id);
    }

    #[Test]
    /** Maximum existing ID is accepted and returns the correct clipart. */
    public function get_clipart_by_id_returns_item_at_maximum_seeded_id(): void
    {
        $items = Clipart::factory()->count(3)->create();
        $maxId = $items->max('clipart_id');

        $result = $this->service->getClipartById($maxId);

        $this->assertEquals($maxId, $result->clipart_id);
    }

    #[Test]
    /** ID one above maximum existing ID (above maximum) throws ModelNotFoundException. */
    public function get_clipart_by_id_throws_when_id_is_one_above_maximum_seeded_id(): void
    {
        $items    = Clipart::factory()->count(3)->create();
        $aboveMax = $items->max('clipart_id') + 1;

        $this->expectException(ModelNotFoundException::class);
        $this->service->getClipartById($aboveMax);
    }

    #[Test]
    /** Returns exactly the requested clipart and not any other. */
    public function get_clipart_by_id_returns_only_the_requested_item(): void
    {
        $target = Clipart::factory()->create(['clipart_name' => 'Target_aaa']);
        $other  = Clipart::factory()->create(['clipart_name' => 'Other_aaa']);

        $result = $this->service->getClipartById($target->clipart_id);

        $this->assertEquals($target->clipart_id, $result->clipart_id);
        $this->assertNotEquals($other->clipart_id, $result->clipart_id);
    }
}