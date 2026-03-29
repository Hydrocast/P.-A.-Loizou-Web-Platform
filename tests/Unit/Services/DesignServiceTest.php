<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\CustomizablePrintProduct;
use App\Models\OrderItem;
use App\Models\SavedDesign;
use App\Services\DesignService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for DesignService.
 *
 * Covers retrieval of saved designs, saving a new design, loading a design,
 * creating a cart snapshot, and exporting a print reference.
 * Boundary values: design name (1–100).
 */
class DesignServiceTest extends TestCase
{
    use RefreshDatabase;

    private DesignService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DesignService;
    }

    // -------------------------------------------------------------------------
    // getSavedDesigns()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns all designs belonging to the customer. */
    public function get_saved_designs_returns_all_designs_for_customer(): void
    {
        $customer = Customer::factory()->create();
        SavedDesign::factory()->count(3)->create(['customer_id' => $customer->customer_id]);

        $designs = $this->service->getSavedDesigns($customer->customer_id);

        $this->assertCount(3, $designs);
    }

    #[Test]
    /** Returns an empty collection when the customer has no designs. */
    public function get_saved_designs_returns_empty_when_none_exist(): void
    {
        $customer = Customer::factory()->create();
        $designs = $this->service->getSavedDesigns($customer->customer_id);
        $this->assertCount(0, $designs);
    }

    #[Test]
    /** Excludes designs belonging to other customers. */
    public function get_saved_designs_returns_only_designs_belonging_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $other = Customer::factory()->create();
        SavedDesign::factory()->create(['customer_id' => $customer->customer_id]);
        SavedDesign::factory()->create(['customer_id' => $other->customer_id]);

        $designs = $this->service->getSavedDesigns($customer->customer_id);

        $this->assertCount(1, $designs);
    }

    #[Test]
    /** Returns designs ordered by creation date descending. */
    public function get_saved_designs_returns_designs_ordered_by_date_created_descending(): void
    {
        $customer = Customer::factory()->create();
        $older = SavedDesign::factory()->create([
            'customer_id' => $customer->customer_id,
            'date_created' => now()->subDays(5),
        ]);
        $newer = SavedDesign::factory()->create([
            'customer_id' => $customer->customer_id,
            'date_created' => now(),
        ]);

        $designs = $this->service->getSavedDesigns($customer->customer_id);

        $this->assertEquals($newer->design_id, $designs->first()->design_id);
        $this->assertEquals($older->design_id, $designs->last()->design_id);
    }

    #[Test]
    /** Adds customization labels (shirt color, print sides, size) to each saved design. */
    public function get_saved_designs_enriches_customization_labels_including_size(): void
    {
        $customer = Customer::factory()->create();

        SavedDesign::factory()->create([
            'customer_id' => $customer->customer_id,
            'design_data' => json_encode([
                'schema_version' => 1,
                'canvas_json' => '{"version":"5.3.0","objects":[]}',
                'customization' => [
                    'shirt_color' => ['label' => 'Black'],
                    'print_sides' => ['label' => 'Front + Back'],
                    'size' => ['label' => 'XL'],
                ],
            ], JSON_UNESCAPED_SLASHES),
        ]);

        $designs = $this->service->getSavedDesigns($customer->customer_id);

        $this->assertSame('Black', $designs->first()?->shirt_color_label);
        $this->assertSame('Front + Back', $designs->first()?->print_sides_label);
        $this->assertSame('XL', $designs->first()?->size_label);
    }

    // -------------------------------------------------------------------------
    // saveDesign()
    // -------------------------------------------------------------------------

    #[Test]
    /** Persists a new saved design to the database. */
    public function save_design_persists_new_design(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();

        $design = $this->service->saveDesign(
            $customer->customer_id,
            $product->product_id,
            'My Design',
            '{"version":"5.3.0","objects":[]}',
            null,
            null,
        );

        $this->assertInstanceOf(SavedDesign::class, $design);
        $this->assertDatabaseHas('saved_designs', ['design_name' => 'My Design']);
    }

    #[Test]
    /** Design data is stored verbatim. */
    public function save_design_stores_design_data_verbatim(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();
        $fabricJson = '{"version":"5.3.0","objects":[{"type":"textbox","text":"Hello"}]}';

        $design = $this->service->saveDesign(
            $customer->customer_id, $product->product_id, 'My Design', $fabricJson, null, null
        );

        $this->assertEquals($fabricJson, $design->design_data);
    }

    #[Test]
    /** Preview image reference is stored when provided. */
    public function save_design_stores_preview_image_reference(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();

        $design = $this->service->saveDesign(
            $customer->customer_id, $product->product_id, 'My Design', '{}', 'previews/abc.png', null
        );

        $this->assertEquals('previews/abc.png', $design->preview_image_reference);
    }

    #[Test]
    /** Null preview image reference is accepted. */
    public function save_design_accepts_null_preview_image_reference(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();

        $design = $this->service->saveDesign(
            $customer->customer_id, $product->product_id, 'My Design', '{}', null, null
        );

        $this->assertNull($design->preview_image_reference);
    }

    #[Test]
    /** Print file reference is stored when provided. */
    public function save_design_stores_print_file_reference(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();

        $design = $this->service->saveDesign(
            $customer->customer_id, $product->product_id, 'My Design', '{}', null, 'prints/abc.png'
        );

        $this->assertEquals('prints/abc.png', $design->print_file_reference);
    }

    #[Test]
    /** Throws ValidationException when product does not exist. */
    public function save_design_throws_when_product_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->saveDesign($customer->customer_id, 99999, 'Design', '{}', null, null);
    }

    #[Test]
    /** Throws ValidationException when the product is inactive. */
    public function save_design_throws_when_product_is_inactive(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->inactive()->create();

        $this->expectException(ValidationException::class);
        $this->service->saveDesign($customer->customer_id, $product->product_id, 'Design', '{}', null, null);
    }

    // Design name boundaries --------------------------------------------------

    #[Test]
    /** Empty design name (below minimum) is rejected. */
    public function save_design_throws_when_design_name_is_empty(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->saveDesign($customer->customer_id, $product->product_id, '', '{}', null, null);
    }

    #[Test]
    /** Design name of 1 character (minimum) is accepted. */
    public function save_design_accepts_design_name_of_one_character(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();
        $design = $this->service->saveDesign($customer->customer_id, $product->product_id, 'A', '{}', null, null);
        $this->assertEquals('A', $design->design_name);
    }

    #[Test]
    /** Design name of 50 characters (in-range) is accepted. */
    public function save_design_accepts_design_name_of_fifty_characters(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();
        $name = str_repeat('a', 50);
        $design = $this->service->saveDesign($customer->customer_id, $product->product_id, $name, '{}', null, null);
        $this->assertEquals($name, $design->design_name);
    }

    #[Test]
    /** Design name of 100 characters (maximum) is accepted. */
    public function save_design_accepts_design_name_of_one_hundred_characters(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();
        $name = str_repeat('a', 100);
        $design = $this->service->saveDesign($customer->customer_id, $product->product_id, $name, '{}', null, null);
        $this->assertEquals($name, $design->design_name);
    }

    #[Test]
    /** Design name of 101 characters (above maximum) is rejected. */
    public function save_design_throws_when_design_name_exceeds_one_hundred_characters(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->saveDesign(
            $customer->customer_id, $product->product_id, str_repeat('a', 101), '{}', null, null
        );
    }

    // -------------------------------------------------------------------------
    // loadDesign()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns the design when the customer owns it. */
    public function load_design_returns_design_for_owner(): void
    {
        $customer = Customer::factory()->create();
        $design = SavedDesign::factory()->create(['customer_id' => $customer->customer_id]);

        $result = $this->service->loadDesign($customer->customer_id, $design->design_id);

        $this->assertEquals($design->design_id, $result->design_id);
    }

    #[Test]
    /** Throws ValidationException when the design belongs to a different customer. */
    public function load_design_throws_when_design_belongs_to_different_customer(): void
    {
        $owner = Customer::factory()->create();
        $intruder = Customer::factory()->create();
        $design = SavedDesign::factory()->create(['customer_id' => $owner->customer_id]);

        $this->expectException(ValidationException::class);
        $this->service->loadDesign($intruder->customer_id, $design->design_id);
    }

    #[Test]
    /** Throws ValidationException when the design does not exist. */
    public function load_design_throws_when_design_does_not_exist(): void
    {
        $customer = Customer::factory()->create();
        $this->expectException(ValidationException::class);
        $this->service->loadDesign($customer->customer_id, 99999);
    }

    #[Test]
    /** Throws ValidationException when the design's product is no longer available. */
    public function load_design_throws_when_product_is_no_longer_available(): void
    {
        $customer = Customer::factory()->create();
        $product = CustomizablePrintProduct::factory()->inactive()->create();
        $design = SavedDesign::factory()->create([
            'customer_id' => $customer->customer_id,
            'product_id' => $product->product_id,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->loadDesign($customer->customer_id, $design->design_id);
    }

    // -------------------------------------------------------------------------
    // createCartSnapshot()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns the design data unchanged. */
    public function create_cart_snapshot_returns_design_data_verbatim(): void
    {
        $fabricJson = '{"version":"5.3.0","objects":[{"type":"rect"}]}';
        $snapshot = $this->service->createCartSnapshot($fabricJson);
        $this->assertEquals($fabricJson, $snapshot);
    }

    #[Test]
    /** Returns an empty string unchanged. */
    public function create_cart_snapshot_returns_empty_string_unchanged(): void
    {
        $snapshot = $this->service->createCartSnapshot('');
        $this->assertEquals('', $snapshot);
    }

    // -------------------------------------------------------------------------
    // exportPrintReference()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns the order item for a valid ID. */
    public function export_print_reference_returns_order_item_for_valid_id(): void
    {
        $orderItem = OrderItem::factory()->create();
        $result = $this->service->exportPrintReference($orderItem->order_item_id);
        $this->assertInstanceOf(OrderItem::class, $result);
        $this->assertEquals($orderItem->order_item_id, $result->order_item_id);
    }

    #[Test]
    /** Throws ValidationException when the order item does not exist. */
    public function export_print_reference_throws_when_order_item_does_not_exist(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->exportPrintReference(99999);
    }
}
