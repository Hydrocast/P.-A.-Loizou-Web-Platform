<?php

namespace Tests\Unit\Services;

use PHPUnit\Framework\Attributes\Test;
use App\Models\CustomizablePrintProduct;
use App\Models\PricingTier;
use App\Services\PricingConfigurationService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Unit tests for PricingConfigurationService.
 *
 * Covers configuration and validation of tiered pricing for customizable products.
 * Boundary values: tiers (1–5), minimum quantity (≥1), maximum quantity (≥ min),
 * unit price (0–100,000).
 */
class PricingConfigurationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PricingConfigurationService();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeProduct(): CustomizablePrintProduct
    {
        return CustomizablePrintProduct::factory()->create();
    }

    private function singleTier(float $price = 10.00): array
    {
        return [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => $price],
        ];
    }

    // -------------------------------------------------------------------------
    // configurePricingTiers() – happy paths
    // -------------------------------------------------------------------------

    #[Test]
    /** Single tier covering all quantities is accepted. */
    public function configure_pricing_tiers_accepts_single_tier_covering_all_quantities(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, $this->singleTier());
        $this->assertDatabaseCount('pricing_tiers', 1);
    }

    #[Test]
    /** Three contiguous tiers are accepted. */
    public function configure_pricing_tiers_accepts_three_contiguous_tiers(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 49,   'unit_price' => 10.00],
            ['minimum_quantity' => 50, 'maximum_quantity' => 9999, 'unit_price' =>  7.00],
        ]);
        $this->assertDatabaseCount('pricing_tiers', 3);
    }

    #[Test]
    /** Five tiers (maximum allowed) are accepted. */
    public function configure_pricing_tiers_accepts_five_tiers(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,   'maximum_quantity' => 9,    'unit_price' => 20.00],
            ['minimum_quantity' => 10,  'maximum_quantity' => 24,   'unit_price' => 16.00],
            ['minimum_quantity' => 25,  'maximum_quantity' => 49,   'unit_price' => 13.00],
            ['minimum_quantity' => 50,  'maximum_quantity' => 99,   'unit_price' => 10.00],
            ['minimum_quantity' => 100, 'maximum_quantity' => 9999, 'unit_price' =>  7.00],
        ]);
        $this->assertDatabaseCount('pricing_tiers', 5);
    }

    #[Test]
    /** Existing tiers are replaced atomically with the new configuration. */
    public function configure_pricing_tiers_replaces_existing_tiers(): void
    {
        $product = $this->makeProduct();
        PricingTier::factory()->firstTier()->create(['product_id' => $product->product_id]);
        PricingTier::factory()->secondTier()->create(['product_id' => $product->product_id]);

        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => 5.00],
        ]);

        $this->assertDatabaseCount('pricing_tiers', 1);
        $this->assertEquals(5.00, (float) PricingTier::first()->unit_price);
    }

    #[Test]
    /** Tiers submitted in any order are accepted and stored correctly. */
    public function configure_pricing_tiers_accepts_tiers_submitted_in_unsorted_order(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
        ]);
        $this->assertDatabaseCount('pricing_tiers', 2);
    }

    // -------------------------------------------------------------------------
    // Tier count validation
    // -------------------------------------------------------------------------

    #[Test]
    /** Zero tiers (empty array) is rejected. */
    public function configure_pricing_tiers_throws_when_tier_array_is_empty(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, []);
    }

    #[Test]
    /** Six tiers (above maximum) is rejected. */
    public function configure_pricing_tiers_throws_when_six_tiers_are_provided(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 20.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 19,   'unit_price' => 18.00],
            ['minimum_quantity' => 20, 'maximum_quantity' => 29,   'unit_price' => 16.00],
            ['minimum_quantity' => 30, 'maximum_quantity' => 39,   'unit_price' => 14.00],
            ['minimum_quantity' => 40, 'maximum_quantity' => 49,   'unit_price' => 12.00],
            ['minimum_quantity' => 50, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
    }

    // -------------------------------------------------------------------------
    // First-tier start validation
    // -------------------------------------------------------------------------

    #[Test]
    /** First tier not starting at minimum quantity 1 is rejected. */
    public function configure_pricing_tiers_throws_when_first_tier_does_not_start_at_one(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 2, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
    }

    // -------------------------------------------------------------------------
    // Gap validation
    // -------------------------------------------------------------------------

    #[Test]
    /** Gap between tiers (non-contiguous) is rejected. */
    public function configure_pricing_tiers_throws_when_there_is_a_gap_between_tiers(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 11, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
    }

    #[Test]
    /** Perfectly contiguous tiers are accepted. */
    public function configure_pricing_tiers_accepts_perfectly_contiguous_tiers(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 10,   'unit_price' => 15.00],
            ['minimum_quantity' => 11, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
        $this->assertDatabaseCount('pricing_tiers', 2);
    }

    // -------------------------------------------------------------------------
    // Overlap validation (caught by same contiguity check)
    // -------------------------------------------------------------------------

    #[Test]
    /** Overlapping quantity ranges are rejected. */
    public function configure_pricing_tiers_throws_when_tiers_overlap(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 20,   'unit_price' => 15.00],
            ['minimum_quantity' => 15, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
    }

    // -------------------------------------------------------------------------
    // Per-tier field validation
    // -------------------------------------------------------------------------

    #[Test]
    /** Minimum quantity of 0 (below minimum) is rejected. */
    public function configure_pricing_tiers_throws_when_minimum_quantity_is_zero(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 0, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
    }

    #[Test]
    /** Minimum quantity of 1 (minimum) is accepted. */
    public function configure_pricing_tiers_accepts_minimum_quantity_of_one(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, $this->singleTier());
        $this->assertEquals(1, PricingTier::first()->minimum_quantity);
    }

    #[Test]
    /** Minimum quantity of 50 (in-range) in a non-first tier is accepted. */
    public function configure_pricing_tiers_accepts_mid_range_minimum_quantity_in_non_first_tier(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 49,   'unit_price' => 15.00],
            ['minimum_quantity' => 50, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
        $tiers = $this->service->getPricingTiersForProduct($product->product_id);
        $this->assertEquals(50, $tiers->last()->minimum_quantity);
    }

    #[Test]
    /** Maximum quantity less than minimum quantity is rejected. */
    public function configure_pricing_tiers_throws_when_maximum_quantity_is_less_than_minimum(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 10, 'maximum_quantity' => 9, 'unit_price' => 10.00],
        ]);
    }

    #[Test]
    /** Single-quantity tier where maximum equals minimum is accepted. */
    public function configure_pricing_tiers_accepts_single_quantity_tier(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 1,    'unit_price' => 50.00],
            ['minimum_quantity' => 2, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
        $this->assertDatabaseCount('pricing_tiers', 2);
    }

    #[Test]
    /** Tier where maximum is exactly one above minimum is accepted. */
    public function configure_pricing_tiers_accepts_tier_spanning_exactly_two_quantities(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 2,    'unit_price' => 50.00],
            ['minimum_quantity' => 3, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
        $this->assertDatabaseCount('pricing_tiers', 2);
    }

    #[Test]
    /** Wide range tier spanning quantities 1 to 9999 is accepted. */
    public function configure_pricing_tiers_accepts_wide_range_tier(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);
        $tier = PricingTier::first();
        $this->assertEquals(1,    $tier->minimum_quantity);
        $this->assertEquals(9999, $tier->maximum_quantity);
    }

    #[Test]
    /** Negative unit price is rejected. */
    public function configure_pricing_tiers_throws_when_unit_price_is_negative(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => -0.01],
        ]);
    }

    #[Test]
    /** Unit price of 0.00 (minimum) is accepted. */
    public function configure_pricing_tiers_accepts_zero_unit_price(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => 0.00],
        ]);
        $this->assertEquals(0.00, (float) PricingTier::first()->unit_price);
    }

    #[Test]
    /** Unit price of 15.00 (in-range) is accepted. */
    public function configure_pricing_tiers_accepts_mid_range_unit_price(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => 15.00],
        ]);
        $this->assertEquals(15.00, (float) PricingTier::first()->unit_price);
    }

    #[Test]
    /** Unit price of 100,000.00 (maximum) is accepted. */
    public function configure_pricing_tiers_accepts_maximum_unit_price(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => 100000.00],
        ]);
        $this->assertEquals(100000.00, (float) PricingTier::first()->unit_price);
    }

    #[Test]
    /** Unit price of 100,000.01 (above maximum) is rejected. */
    public function configure_pricing_tiers_throws_when_unit_price_exceeds_maximum(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1, 'maximum_quantity' => 9999, 'unit_price' => 100000.01],
        ]);
    }

    // -------------------------------------------------------------------------
    // Product existence
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns the product when it exists. */
    public function check_product_exists_returns_the_product_when_found(): void
    {
        $product = $this->makeProduct();
        $result  = $this->service->checkProductExistsAndIsCustomizable($product->product_id);
        $this->assertEquals($product->product_id, $result->product_id);
    }

    #[Test]
    /** Throws ModelNotFoundException when product does not exist. */
    public function configure_pricing_tiers_throws_when_product_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->service->configurePricingTiers(99999, $this->singleTier());
    }

    // -------------------------------------------------------------------------
    // getPricingTiersForProduct()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns all tiers for the product. */
    public function get_pricing_tiers_for_product_returns_all_tiers(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);

        $tiers = $this->service->getPricingTiersForProduct($product->product_id);
        $this->assertCount(2, $tiers);
    }

    #[Test]
    /** Returns tiers ordered by minimum quantity ascending. */
    public function get_pricing_tiers_for_product_returns_tiers_ordered_by_minimum_quantity(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
        ]);

        $tiers = $this->service->getPricingTiersForProduct($product->product_id);

        $this->assertEquals(1,  $tiers->first()->minimum_quantity);
        $this->assertEquals(10, $tiers->last()->minimum_quantity);
    }

    #[Test]
    /** Returns an empty collection when no tiers are configured. */
    public function get_pricing_tiers_for_product_returns_empty_when_none_configured(): void
    {
        $product = $this->makeProduct();
        $tiers   = $this->service->getPricingTiersForProduct($product->product_id);
        $this->assertCount(0, $tiers);
    }

    // -------------------------------------------------------------------------
    // findTierForQuantity()
    // -------------------------------------------------------------------------

    #[Test]
    /** Returns the correct tier for an in-range quantity. */
    public function find_tier_for_quantity_returns_correct_tier_for_mid_range_quantity(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);

        $tier = $this->service->findTierForQuantity($product->product_id, 50);
        $this->assertEquals(10.00, (float) $tier->unit_price);
    }

    #[Test]
    /** Resolves the correct tier at the lower boundary of a tier range. */
    public function find_tier_for_quantity_resolves_tier_at_lower_boundary(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);

        $tier = $this->service->findTierForQuantity($product->product_id, 1);
        $this->assertEquals(15.00, (float) $tier->unit_price);
    }

    #[Test]
    /** Resolves the correct tier at the upper boundary of a tier range. */
    public function find_tier_for_quantity_resolves_tier_at_upper_boundary(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);

        $tier = $this->service->findTierForQuantity($product->product_id, 9);
        $this->assertEquals(15.00, (float) $tier->unit_price);
    }

    #[Test]
    /** Transitions to the next tier for a quantity one above the tier boundary. */
    public function find_tier_for_quantity_transitions_to_next_tier_one_above_boundary(): void
    {
        $product = $this->makeProduct();
        $this->service->configurePricingTiers($product->product_id, [
            ['minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00],
            ['minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00],
        ]);

        $tier = $this->service->findTierForQuantity($product->product_id, 10);
        $this->assertEquals(10.00, (float) $tier->unit_price);
    }

    #[Test]
    /** Throws when no tier is configured for the product. */
    public function find_tier_for_quantity_throws_when_no_tier_exists_for_product(): void
    {
        $product = $this->makeProduct();
        $this->expectException(ValidationException::class);
        $this->service->findTierForQuantity($product->product_id, 5);
    }

    #[Test]
    /** Throws when the quantity exceeds all configured tier ranges. */
    public function find_tier_for_quantity_throws_when_quantity_exceeds_all_configured_ranges(): void
    {
        $product = $this->makeProduct();
        PricingTier::factory()->create([
            'product_id'       => $product->product_id,
            'minimum_quantity' => 1,
            'maximum_quantity' => 9,
            'unit_price'       => 15.00,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->findTierForQuantity($product->product_id, 10);
    }

    // -------------------------------------------------------------------------
    // validateTierStructure() – direct testing
    // -------------------------------------------------------------------------

    #[Test]
    /** Valid contiguous tier set is accepted. */
    public function validate_tier_structure_accepts_valid_contiguous_tiers(): void
    {
        $tiers = [
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 1,  'maximum_quantity' => 9,    'unit_price' => 15.00]),
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 10, 'maximum_quantity' => 9999, 'unit_price' => 10.00]),
        ];

        $this->service->validateTierStructure($tiers);
        $this->assertTrue(true);
    }

    #[Test]
    /** More than five tiers is rejected. */
    public function validate_tier_structure_throws_when_more_than_five_tiers(): void
    {
        $tiers = array_map(
            fn($i) => new PricingTier([
                'product_id'       => 1,
                'minimum_quantity' => ($i * 10) + 1,
                'maximum_quantity' => ($i + 1) * 10,
                'unit_price'       => 5.00,
            ]),
            range(0, 5)
        );

        $this->expectException(ValidationException::class);
        $this->service->validateTierStructure($tiers);
    }

    #[Test]
    /** First tier not starting at minimum quantity 1 is rejected. */
    public function validate_tier_structure_throws_when_first_tier_minimum_is_not_one(): void
    {
        $tiers = [
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 2, 'maximum_quantity' => 9999, 'unit_price' => 10.00]),
        ];

        $this->expectException(ValidationException::class);
        $this->service->validateTierStructure($tiers);
    }

    // -------------------------------------------------------------------------
    // validateTiersIndividually() – direct testing
    // -------------------------------------------------------------------------

    #[Test]
    /** Valid individual tier is accepted. */
    public function validate_tiers_individually_accepts_valid_tiers(): void
    {
        $tiers = [
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 1, 'maximum_quantity' => 99, 'unit_price' => 5.00]),
        ];

        $this->service->validateTiersIndividually($tiers);
        $this->assertTrue(true);
    }

    #[Test]
    /** Minimum quantity of 0 is rejected. */
    public function validate_tiers_individually_throws_when_minimum_quantity_is_zero(): void
    {
        $tiers = [
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 0, 'maximum_quantity' => 9, 'unit_price' => 10.00]),
        ];

        $this->expectException(ValidationException::class);
        $this->service->validateTiersIndividually($tiers);
    }

    #[Test]
    /** Maximum quantity less than minimum quantity is rejected. */
    public function validate_tiers_individually_throws_when_maximum_is_less_than_minimum(): void
    {
        $tiers = [
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 10, 'maximum_quantity' => 5, 'unit_price' => 10.00]),
        ];

        $this->expectException(ValidationException::class);
        $this->service->validateTiersIndividually($tiers);
    }

    #[Test]
    /** Negative unit price is rejected. */
    public function validate_tiers_individually_throws_when_unit_price_is_negative(): void
    {
        $tiers = [
            new PricingTier(['product_id' => 1, 'minimum_quantity' => 1, 'maximum_quantity' => 9, 'unit_price' => -0.01]),
        ];

        $this->expectException(ValidationException::class);
        $this->service->validateTiersIndividually($tiers);
    }
}