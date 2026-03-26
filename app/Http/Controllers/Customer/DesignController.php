<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveDesignRequest;
use App\Models\CustomizablePrintProduct;
use App\Models\SavedDesign;
use App\Services\ClipartService;
use App\Services\DesignService;
use App\Services\ProductService;
use App\Support\DesignDocument;
use App\Support\DesignProfileRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles the customer design workspace and saved design operations.
 *
 * The design workspace is powered client-side. The controller is responsible
 * for:
 *   - validating product availability
 *   - loading clipart assets
 *   - resolving shared designer profile configuration
 *   - returning saved design snapshots
 *   - persisting final saved design records
 */
class DesignController extends Controller
{
    public function __construct(
        private DesignService $designService,
        private ProductService $productService,
        private ClipartService $clipartService,
    ) {}

    /**
     * Display a fresh design workspace for a customizable product.
     */
    public function workspace(Request $request, int $id): Response|RedirectResponse
    {
        $product = $this->productService->getActiveProduct($id, 'customizable');

        if (! $product) {
            return redirect()->route('catalog')
                ->with('error', 'Product not found or is no longer available.');
        }

        $profileKey = $product instanceof CustomizablePrintProduct
            ? $product->design_profile_key
            : null;

        $shirtColorOptions = DesignProfileRegistry::getWorkspaceColorOptions($profileKey);
        $workspaceOptions = DesignProfileRegistry::getWorkspaceOptions($profileKey);

        $selectedShirtColorId = DesignProfileRegistry::resolveSelectedColorId(
            $request->query('color'),
            $profileKey,
            $shirtColorOptions,
        );

        $selectedPrintSide = DesignProfileRegistry::resolveSelectedPrintSide(
            $request->query('print_side'),
            $workspaceOptions,
        );

        return Inertia::render('Customer/Design/DesignWorkspace', [
            'product' => $product,
            'clipart' => $this->clipartService->getAllClipart(),
            'templateConfig' => DesignProfileRegistry::getTemplateConfig($profileKey) ?? $product->template_config,
            'initialDesign' => null,
            'shirtColorOptions' => $shirtColorOptions,
            'selectedShirtColorId' => $selectedShirtColorId,
            'workspaceOptions' => $workspaceOptions,
            'selectedPrintSide' => $selectedPrintSide,
        ]);
    }

    /**
     * Display the authenticated customer's saved designs list.
     */
    public function index(): Response
    {
        $customerId = Auth::guard('customer')->id();

        $designs = $this->designService->getSavedDesigns($customerId);

        return Inertia::render('Customer/Account/SavedDesigns', [
            'designs' => $designs,
        ]);
    }

    /**
     * Persist a new immutable saved design snapshot.
     */
    public function store(SaveDesignRequest $request): RedirectResponse
    {
        $customerId = Auth::guard('customer')->id();
        $data = $request->validated();

        $storedDesignDocument = DesignDocument::encode(
            $data['design_data'],
            $data['customization_options'] ?? [],
        );

        $this->designService->saveDesign(
            $customerId,
            $data['product_id'],
            $data['design_name'],
            $storedDesignDocument,
            $data['preview_image_reference'] ?? null,
        );

        return back()->with('success', 'Design saved successfully.');
    }

    /**
     * Load an existing saved design into a new editable workspace session.
     *
     * The original saved design record remains unchanged. The workspace uses
     * the stored snapshot only as the starting point for a new editing session.
     */
    public function load(SavedDesign $design): Response|RedirectResponse
    {
        $this->authorize('view', $design);

        $customerId = Auth::guard('customer')->id();

        $loadedDesign = $this->designService->loadDesign(
            $customerId,
            $design->getKey()
        );

        $product = $loadedDesign->product;
        $profileKey = $product instanceof CustomizablePrintProduct
            ? $product->design_profile_key
            : null;

        $shirtColorOptions = DesignProfileRegistry::getWorkspaceColorOptions($profileKey);
        $workspaceOptions = DesignProfileRegistry::getWorkspaceOptions($profileKey);

        $storedCustomization = DesignDocument::extractCustomization($loadedDesign->design_data);

        $selectedShirtColorId = DesignProfileRegistry::resolveSelectedColorId(
            $storedCustomization['shirt_color']['id'] ?? null,
            $profileKey,
            $shirtColorOptions,
        );

        $selectedPrintSide = DesignProfileRegistry::resolveSelectedPrintSide(
            $storedCustomization['print_sides']['value'] ?? null,
            $workspaceOptions,
        );

        return Inertia::render('Customer/Design/DesignWorkspace', [
            'product' => $product,
            'clipart' => $this->clipartService->getAllClipart(),
            'templateConfig' => DesignProfileRegistry::getTemplateConfig($profileKey) ?? $product?->template_config,
            'initialDesign' => $loadedDesign,
            'shirtColorOptions' => $shirtColorOptions,
            'selectedShirtColorId' => $selectedShirtColorId,
            'workspaceOptions' => $workspaceOptions,
            'selectedPrintSide' => $selectedPrintSide,
        ]);
    }

    /**
     * Delete a saved design owned by the authenticated customer.
     */
    public function destroy(SavedDesign $design): RedirectResponse
    {
        $this->authorize('delete', $design);

        $design->delete();

        return redirect()->route('account.designs')
            ->with('success', 'Design deleted.');
    }
}