<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use Illuminate\Validation\ValidationException;

/**
 * Generates sales analytics data for the administrator dashboard.
 *
 * Metrics are calculated from completed orders within a given date range.
 * All monetary values are taken from the frozen total_amount field on each order,
 * which is VAT‑inclusive as stored at submission.
 */
class AnalyticsService
{
    /**
     * Generate the sales dashboard for the specified date range.
     *
     * Returns an associative array with:
     *   - total_order_value        – Sum of total_amount for completed orders.
     *   - order_count              – Number of completed orders.
     *   - average_order_value      – Total value divided by order count (0 if none).
     *   - average_items_per_order  – Total items divided by order count (0 if none).
     *   - status_distribution      – Count of orders per status.
     *   - start_date               – Requested start date.
     *   - end_date                 – Requested end date.
     *   - has_data                 – True if there are completed orders.
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
     * Retrieve all orders within the date range, with items eager‑loaded.
     */
    private function fetchOrdersInRange(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        return CustomerOrder::with('items')
            ->where('order_creation_timestamp', '>=', $startDate)
            ->where('order_creation_timestamp', '<=', $endDate)
            ->get();
    }

    /**
     * Calculate dashboard metrics from the retrieved orders.
     */
    private function calculateMetrics($orders, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        $completedOrders = $orders->filter(fn($o) => $o->order_status === OrderStatus::Completed);
        $orderCount      = $completedOrders->count();
        $totalOrderValue = round($completedOrders->sum('total_amount'), 2);
        $totalItems      = $completedOrders->sum(fn ($o) => $o->items->sum('quantity'));

        $statusDistribution = $orders->groupBy(fn ($o) => $o->order_status->value)
            ->map->count()
            ->toArray();

        return [
            'total_order_value'       => $totalOrderValue,
            'order_count'             => $orderCount,
            'average_order_value'     => $orderCount > 0 ? round($totalOrderValue / $orderCount, 2) : 0,
            'average_items_per_order' => $orderCount > 0 ? round($totalItems / $orderCount, 2) : 0,
            'status_distribution'     => $statusDistribution,
            'start_date'              => $startDate,
            'end_date'                => $endDate,
            'has_data'                => $orderCount > 0,
        ];
    }
}