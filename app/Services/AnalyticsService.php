<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Generates sales analytics data for the administrator dashboard.
 *
 * Metrics are calculated from orders within a given date range.
 * Revenue-related metrics use completed orders only, based on the frozen
 * total_amount field stored on each order at submission time.
 *
 * Status distribution includes all orders in the selected range so staff can
 * understand the operational mix of pending, processing, ready, completed,
 * and cancelled work.
 */
class AnalyticsService
{
    /**
     * Generate the sales dashboard for the specified date range.
     *
     * Returns an associative array with:
     *   - total_order_value        – Sum of total_amount for completed orders.
     *   - order_count              – Number of completed orders.
     *   - average_order_value      – Total value divided by completed order count.
     *   - average_items_per_order  – Total items divided by completed order count.
     *   - status_distribution      – Count of all orders per status in the range.
     *   - daily_sales              – Daily completed-order revenue series.
     *   - sales_summary            – Derived graph summary values.
     *   - start_date               – Requested start date.
     *   - end_date                 – Requested end date.
     *   - has_data                 – True if completed orders exist in range.
     *
     * @throws ValidationException if the end date precedes the start date.
     */
    public function generateDashboard(\DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $this->validateDateRange($startDate, $endDate);

        $orders = $this->fetchOrdersInRange($startDate, $endDate);

        return $this->calculateMetrics($orders, $startDate, $endDate);
    }

    /**
     * Resolve a concrete analytics date range from a supported preset key.
     *
     * Supported presets:
     *   - today
     *   - last_7_days
     *   - last_30_days
     *   - year_to_date
     *   - all_time
     */
    public function resolveDateRangeFromPreset(string $preset): array
    {
        $now = now();

        return match ($preset) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'last_7_days' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'last_30_days' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'year_to_date' => [
                $now->copy()->startOfYear()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'all_time' => [
                $this->resolveAllTimeStartDate(),
                $now->copy()->endOfDay(),
            ],
            default => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
        };
    }

    /**
     * Throw an exception if the end date is before the start date.
     *
     * @throws ValidationException
     */
    private function validateDateRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): void
    {
        if ($endDate < $startDate) {
            throw ValidationException::withMessages([
                'end_date' => 'The end date must be on or after the start date.',
            ]);
        }
    }

    /**
     * Retrieve all orders within the date range, with items eager-loaded.
     */
    private function fetchOrdersInRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate): Collection
    {
        return CustomerOrder::with('items')
            ->where('order_creation_timestamp', '>=', $startDate)
            ->where('order_creation_timestamp', '<=', $endDate)
            ->get();
    }

    /**
     * Calculate dashboard metrics from the retrieved orders.
     */
    private function calculateMetrics(Collection $orders, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $completedOrders = $orders->filter(
            fn (CustomerOrder $order) => $order->order_status === OrderStatus::Completed
        );

        $orderCount = $completedOrders->count();
        $totalOrderValue = round((float) $completedOrders->sum('total_amount'), 2);
        $totalItems = (int) $completedOrders->sum(
            fn (CustomerOrder $order) => (int) $order->items->sum('quantity')
        );

        $statusDistribution = $this->buildStatusDistribution($orders);
        $dailySales = $this->buildDailySalesSeries($completedOrders, $startDate, $endDate);
        $salesSummary = $this->buildSalesSummary($dailySales);

        return [
            'total_order_value' => $totalOrderValue,
            'order_count' => $orderCount,
            'average_order_value' => $orderCount > 0 ? round($totalOrderValue / $orderCount, 2) : 0,
            'average_items_per_order' => $orderCount > 0 ? round($totalItems / $orderCount, 2) : 0,
            'status_distribution' => $statusDistribution,
            'daily_sales' => $dailySales,
            'sales_summary' => $salesSummary,
            'start_date' => $startDate instanceof \DateTimeInterface ? $startDate->format('Y-m-d') : null,
            'end_date' => $endDate instanceof \DateTimeInterface ? $endDate->format('Y-m-d') : null,
            'has_data' => $orderCount > 0,
        ];
    }

    /**
     * Build a complete status distribution including zero-count statuses.
     */
    private function buildStatusDistribution(Collection $orders): array
    {
        $orderedStatuses = [
            'Pending',
            'Ready for Pickup',
            'Completed',
            'Cancelled',
            'Processing',
        ];

        $counts = $orders->groupBy(
            fn (CustomerOrder $order) => $order->order_status->value
        )->map->count();

        $distribution = [];

        foreach ($orderedStatuses as $status) {
            $distribution[$status] = (int) ($counts[$status] ?? 0);
        }

        return $distribution;
    }

    /**
     * Build a daily completed-sales series across the full selected range.
     *
     * Every date in the range is included, even if sales were zero.
     */
    private function buildDailySalesSeries(
        Collection $completedOrders,
        \DateTimeInterface $startDate,
        \DateTimeInterface $endDate
    ): array {
        $salesByDate = $completedOrders
            ->groupBy(fn (CustomerOrder $order) => $order->order_creation_timestamp->toDateString())
            ->map(fn (Collection $ordersForDate) => round((float) $ordersForDate->sum('total_amount'), 2));

        $period = CarbonPeriod::create(
            Carbon::parse($startDate)->startOfDay(),
            Carbon::parse($endDate)->startOfDay()
        );

        $series = [];

        foreach ($period as $date) {
            $dateKey = $date->toDateString();

            $series[] = [
                'date_key' => $dateKey,
                'date_label' => $date->format('M j'),
                'sales' => (float) ($salesByDate[$dateKey] ?? 0),
            ];
        }

        return $series;
    }

    /**
     * Build lightweight summary values for the sales graph card.
     */
    private function buildSalesSummary(array $dailySales): array
    {
        $dayCount = count($dailySales);
        $totalSales = array_sum(array_column($dailySales, 'sales'));
        $averageDailySales = $dayCount > 0 ? round($totalSales / $dayCount, 2) : 0;

        $peakDay = null;
        $lowDay = null;

        if ($dayCount > 0) {
            $peakDayData = collect($dailySales)->sortByDesc('sales')->first();
            $lowDayData = collect($dailySales)->sortBy('sales')->first();

            if ($peakDayData !== null) {
                $peakDay = [
                    'date_label' => $peakDayData['date_label'],
                    'sales' => (float) $peakDayData['sales'],
                ];
            }

            if ($lowDayData !== null) {
                $lowDay = [
                    'date_label' => $lowDayData['date_label'],
                    'sales' => (float) $lowDayData['sales'],
                ];
            }
        }

        return [
            'peak_day' => $peakDay,
            'low_day' => $lowDay,
            'average_daily_sales' => $averageDailySales,
        ];
    }

    /**
     * Resolve the earliest available order date for all-time analytics.
     *
     * Falls back to today when no orders exist yet.
     */
    private function resolveAllTimeStartDate(): Carbon
    {
        $earliestOrderTimestamp = CustomerOrder::query()->min('order_creation_timestamp');

        return $earliestOrderTimestamp
            ? Carbon::parse($earliestOrderTimestamp)->startOfDay()
            : now()->startOfDay();
    }
}
