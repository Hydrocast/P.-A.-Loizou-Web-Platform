<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ConfigurePricingTiersRequest;
use App\Models\CustomizablePrintProduct;
use App\Services\PricingConfigurationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles tiered pricing configuration for customizable products.
 *
 * This controller renders the pricing management screen, allows administrators
 * to search/select a customizable product, loads its existing pricing tiers,
 * and saves tier updates.
 */
class PricingConfigurationController extends Controller
{
    public function __construct(
        private PricingConfigurationService $pricingConfigurationService,
    ) {}

    /**
     * Render pricing management inside the existing staff dashboard shell.
     *
     * Supports:
     * - query search by exact product name (case-insensitive, trim-aware)
     * - direct product selection by product_id
     */
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
            'product_id' => ['nullable', 'integer', 'exists:customizable_print_products,product_id'],
        ]);

        $searchQuery = trim((string) ($validated['query'] ?? ''));
        $selectedProductId = isset($validated['product_id']) ? (int) $validated['product_id'] : null;

        $customizableProducts = CustomizablePrintProduct::query()
            ->where('visibility_status', 'Active')
            ->orderBy('product_name')
            ->get(['product_id', 'product_name']);

        if ($selectedProductId === null && $searchQuery !== '') {
            $normalizedQuery = mb_strtolower($searchQuery);

            $matchedProduct = $customizableProducts->first(function ($product) use ($normalizedQuery) {
                return mb_strtolower(trim($product->product_name)) === $normalizedQuery;
            });

            $selectedProductId = $matchedProduct?->product_id;
        }

        $selectedProduct = null;
        $tiers = collect();

        if ($selectedProductId !== null) {
            $selectedProduct = $customizableProducts->firstWhere('product_id', $selectedProductId);

            if ($selectedProduct !== null) {
                $tiers = $this->pricingConfigurationService
                    ->getPricingTiersForProduct($selectedProductId)
                    ->map(fn ($tier) => [
                        'minimum_quantity' => $tier->minimum_quantity,
                        'maximum_quantity' => $tier->maximum_quantity,
                        'unit_price' => (float) $tier->unit_price,
                    ])
                    ->values();
            }
        }

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'pricing',
            'customizableProducts' => $customizableProducts->map(fn ($product) => [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
            ])->values(),
            'existingTiers' => $tiers,
            'selectedProductId' => $selectedProduct?->product_id,
            'pricingFilters' => [
                'query' => $searchQuery !== '' ? $searchQuery : null,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    /**
     * Save updated pricing tiers for a customizable product.
     */
    public function update(ConfigurePricingTiersRequest $request, int $id): RedirectResponse
    {
        $this->pricingConfigurationService->configurePricingTiers(
            $id,
            $request->validated()['tiers'],
        );

        return redirect()
            ->route('staff.pricing.index', ['product_id' => $id])
            ->with('success', 'Pricing tiers updated successfully.');
    }
}