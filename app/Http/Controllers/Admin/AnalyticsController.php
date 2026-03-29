<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\GenerateAnalyticsDashboardRequest;
use App\Services\AnalyticsService;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles sales analytics dashboard for administrators.
 *
 * This controller generates and displays analytics data including
 * sales summaries, trends, and performance metrics.
 * All methods require administrator privileges.
 */
class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analyticsService) {}

    // Render analytics dashboard with summary data for the given date range
    public function index(GenerateAnalyticsDashboardRequest $request): Response
    {
        $validated = $request->validated();

        $preset = $validated['preset'] ?? null;

        if ($preset) {
            [$startDate, $endDate] = $this->analyticsService->resolveDateRangeFromPreset($preset);
        } else {
            $startDate = ! empty($validated['start_date'])
                ? Carbon::parse($validated['start_date'])->startOfDay()
                : now()->subDays(29)->startOfDay();

            $endDate = ! empty($validated['end_date'])
                ? Carbon::parse($validated['end_date'])->endOfDay()
                : now()->endOfDay();

            $preset = ! empty($validated['start_date']) || ! empty($validated['end_date'])
                ? 'custom'
                : 'last_30_days';
        }

        $dashboard = $this->analyticsService->generateDashboard($startDate, $endDate);

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'analytics',
            'dashboard' => $dashboard,
            'analyticsFilters' => [
                'preset' => $preset,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
            ],
        ]);
    }
}
