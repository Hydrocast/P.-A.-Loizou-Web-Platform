<?php

namespace Tests\Unit\Services;

use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use App\Models\OrderItem;
use App\Services\AnalyticsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Unit tests for AnalyticsService.
 *
 * Covers dashboard generation, metric calculations, date filtering, and date validation.
 * Boundary values: date ranges (start <= end, start/end boundaries).
 */
class AnalyticsServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnalyticsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnalyticsService;
    }

    // -------------------------------------------------------------------------
    // Return structure
    // -------------------------------------------------------------------------

    #[Test]
    /** Dashboard array contains all 8 required keys. */
    public function generate_dashboard_returns_array_with_all_required_keys(): void
    {
        $result = $this->service->generateDashboard(now()->subDay(), now());

        $expectedKeys = [
            'total_order_value',
            'order_count',
            'average_order_value',
            'average_items_per_order',
            'status_distribution',
            'start_date',
            'end_date',
            'has_data',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $result);
        }
    }

    #[Test]
    /** start_date and end_date are returned as Y-m-d strings derived from inputs. */
    public function generate_dashboard_echoes_start_and_end_date_in_result(): void
    {
        $start = now()->subDays(3);
        $end = now();

        $result = $this->service->generateDashboard($start, $end);

        $this->assertSame($start->format('Y-m-d'), $result['start_date']);
        $this->assertSame($end->format('Y-m-d'), $result['end_date']);
    }

    // -------------------------------------------------------------------------
    // Completed order metrics
    // -------------------------------------------------------------------------

    #[Test]
    /** Only orders with Completed status contribute to order count. */
    public function generate_dashboard_counts_only_completed_orders_in_order_count(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Processing,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->cancelled()->create([
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(1, $result['order_count']);
    }

    #[Test]
    /** Total order value sums total_amount only from completed orders. */
    public function generate_dashboard_sums_total_amount_of_completed_orders(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 100.00,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'total_amount' => 999.00,
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(150.00, $result['total_order_value']);
    }

    #[Test]
    /** Average order value equals total order value divided by completed order count. */
    public function generate_dashboard_calculates_correct_average_order_value(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 100.00,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->completed()->create([
            'total_amount' => 200.00,
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(150.00, $result['average_order_value']);
    }

    #[Test]
    /** Average items per order equals total item quantities divided by completed order count. */
    public function generate_dashboard_calculates_correct_average_items_per_order(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        $order1 = CustomerOrder::factory()->completed()->create([
            'order_creation_timestamp' => $withinRange,
        ]);
        OrderItem::factory()->create(['order_id' => $order1->order_id, 'quantity' => 4]);

        $order2 = CustomerOrder::factory()->completed()->create([
            'order_creation_timestamp' => $withinRange,
        ]);
        OrderItem::factory()->create(['order_id' => $order2->order_id, 'quantity' => 6]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(5, $result['average_items_per_order']);
    }

    #[Test]
    /** Average items per order is 0 when there are no completed orders. */
    public function generate_dashboard_returns_zero_average_items_when_no_completed_orders(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(0, $result['average_items_per_order']);
    }

    // -------------------------------------------------------------------------
    // has_data flag
    // -------------------------------------------------------------------------

    #[Test]
    /** has_data is false and all metrics are zero when no completed orders exist in range. */
    public function generate_dashboard_has_data_is_false_when_no_completed_orders_in_range(): void
    {
        $now = now();
        $start = $now->copy()->subDay();

        $result = $this->service->generateDashboard($start, $now);

        $this->assertFalse($result['has_data']);
        $this->assertEquals(0, $result['order_count']);
        $this->assertEquals(0, $result['total_order_value']);
        $this->assertEquals(0, $result['average_order_value']);
    }

    #[Test]
    /** has_data is true when at least one completed order exists in range. */
    public function generate_dashboard_has_data_is_true_when_completed_order_exists_in_range(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertTrue($result['has_data']);
    }

    // -------------------------------------------------------------------------
    // Status distribution
    // -------------------------------------------------------------------------

    #[Test]
    /** Status distribution counts all orders by status, not just completed ones. */
    public function generate_dashboard_status_distribution_counts_orders_by_status(): void
    {
        $now = now();
        $start = $now->copy()->subDay();
        $withinRange = $now->copy()->subHours(2);

        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => $withinRange,
        ]);
        CustomerOrder::factory()->completed()->create([
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(2, $result['status_distribution']['Pending']);
        $this->assertEquals(1, $result['status_distribution']['Completed']);
    }

    #[Test]
    /** Status distribution includes all configured statuses with zero values when no orders exist in range. */
    public function generate_dashboard_status_distribution_is_empty_when_no_orders_in_range(): void
    {
        $now = now();
        $start = $now->copy()->subDay();

        $result = $this->service->generateDashboard($start, $now);

        $this->assertSame([
            'Pending' => 0,
            'Ready for Pickup' => 0,
            'Completed' => 0,
            'Cancelled' => 0,
            'Processing' => 0,
        ], $result['status_distribution']);
    }

    #[Test]
    /** Orders outside the selected range yield a zero-filled status distribution. */
    public function generate_dashboard_status_distribution_excludes_orders_outside_range(): void
    {
        $now = now();
        $start = $now->copy()->subDays(5);

        CustomerOrder::factory()->create([
            'order_status' => OrderStatus::Pending,
            'order_creation_timestamp' => $now->copy()->subDays(30),
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertSame([
            'Pending' => 0,
            'Ready for Pickup' => 0,
            'Completed' => 0,
            'Cancelled' => 0,
            'Processing' => 0,
        ], $result['status_distribution']);
    }

    // -------------------------------------------------------------------------
    // Date range validation - boundary value analysis
    // -------------------------------------------------------------------------

    #[Test]
    /** End date before start date is rejected. */
    public function generate_dashboard_throws_when_end_date_is_before_start_date(): void
    {
        $this->expectException(ValidationException::class);
        $this->service->generateDashboard(now(), now()->subDay());
    }

    #[Test]
    /** Same start and end date (single-day range) is accepted. */
    public function generate_dashboard_accepts_same_start_and_end_date(): void
    {
        $now = now();
        $withinRange = $now->copy()->setTime(12, 0, 0);

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 75.00,
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard(
            $now->copy()->startOfDay(),
            $now->copy()->endOfDay(),
        );

        $this->assertEquals(1, $result['order_count']);
    }

    #[Test]
    /** Normal multi-day range is accepted. */
    public function generate_dashboard_accepts_multi_day_range(): void
    {
        $now = now();
        $withinRange = $now->copy()->subDays(3);

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 75.00,
            'order_creation_timestamp' => $withinRange,
        ]);

        $result = $this->service->generateDashboard($now->copy()->subDays(5), $now);

        $this->assertEquals(1, $result['order_count']);
    }

    #[Test]
    /** Orders with a timestamp exactly at the start boundary are included. */
    public function generate_dashboard_includes_orders_at_start_boundary(): void
    {
        $now = now();
        $start = $now->copy()->subDays(5)->startOfDay();
        $exactlyAtStart = $start->copy();

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $exactlyAtStart,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(1, $result['order_count']);
    }

    #[Test]
    /** Orders with a timestamp exactly at the end boundary are included. */
    public function generate_dashboard_includes_orders_at_end_boundary(): void
    {
        $now = now();
        $start = $now->copy()->subDays(5)->startOfDay();
        $exactlyAtEnd = $now->copy();

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $exactlyAtEnd,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(1, $result['order_count']);
    }

    #[Test]
    /** Orders with a timestamp just before the start boundary are excluded. */
    public function generate_dashboard_excludes_orders_just_before_start(): void
    {
        $now = now();
        $start = $now->copy()->subDays(5)->startOfDay();
        $justBeforeStart = $start->copy()->subSecond();

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $justBeforeStart,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(0, $result['order_count']);
    }

    #[Test]
    /** Orders with a timestamp just after the end boundary are excluded. */
    public function generate_dashboard_excludes_orders_just_after_end(): void
    {
        $now = now();
        $start = $now->copy()->subDays(5)->startOfDay();
        $justAfterEnd = $now->copy()->addSecond();

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $justAfterEnd,
        ]);

        $result = $this->service->generateDashboard($start, $now);

        $this->assertEquals(0, $result['order_count']);
    }

    #[Test]
    /** Orders at both the start and end boundaries are both included. */
    public function generate_dashboard_includes_orders_on_start_and_end_date_boundaries(): void
    {
        $now = now();

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $now->copy()->subDays(5)->startOfDay(),
        ]);
        CustomerOrder::factory()->completed()->create([
            'total_amount' => 50.00,
            'order_creation_timestamp' => $now->copy()->endOfDay(),
        ]);

        $result = $this->service->generateDashboard(
            $now->copy()->subDays(5)->startOfDay(),
            $now->copy()->endOfDay(),
        );

        $this->assertEquals(2, $result['order_count']);
    }

    // -------------------------------------------------------------------------
    // Date range filtering
    // -------------------------------------------------------------------------

    #[Test]
    /** Completed orders far outside the date range are excluded from all metrics. */
    public function generate_dashboard_excludes_completed_orders_outside_date_range(): void
    {
        $now = now();

        CustomerOrder::factory()->completed()->create([
            'total_amount' => 999.00,
            'order_creation_timestamp' => $now->copy()->subDays(30),
        ]);

        $result = $this->service->generateDashboard($now->copy()->subDays(5), $now);

        $this->assertEquals(0, $result['order_count']);
        $this->assertFalse($result['has_data']);
    }
}
