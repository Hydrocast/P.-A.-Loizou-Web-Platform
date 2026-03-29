<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateStandardProductRequest;
use App\Models\CustomizablePrintProduct;
use App\Models\ProductCategory;
use App\Models\StandardProduct;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductManagementController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:50'],
            'product_type' => ['nullable', 'in:standard,customizable'],
            'category_id' => ['nullable', 'integer', 'exists:product_categories,category_id'],
            'visibility_status' => ['nullable', 'in:Active,Inactive'],
        ]);

        $searchQuery = trim($validated['query'] ?? '');
        $productType = $validated['product_type'] ?? null;
        $categoryId = isset($validated['category_id']) ? (int) $validated['category_id'] : null;
        $visibilityStatus = $validated['visibility_status'] ?? null;

        $products = collect();

        if ($productType === null || $productType === 'standard') {
            $standardQuery = StandardProduct::query();

            if ($categoryId !== null) {
                $standardQuery->where('category_id', $categoryId);
            }

            if ($visibilityStatus !== null) {
                $standardQuery->where('visibility_status', $visibilityStatus);
            }

            if ($searchQuery !== '') {
                $standardQuery->where(function ($query) use ($searchQuery) {
                    $query->where('product_name', 'like', '%'.$searchQuery.'%')
                        ->orWhere('description', 'like', '%'.$searchQuery.'%');
                });
            }

            $standardProducts = $standardQuery
                ->orderBy('product_name')
                ->get()
                ->map(function ($product) {
                    return [
                        'productId' => $product->product_id,
                        'productName' => $product->product_name,
                        'description' => $product->description ?? '',
                        'type' => 'Standard',
                        'categoryId' => $product->category_id,
                        'displayPrice' => $product->display_price !== null ? (float) $product->display_price : null,
                        'visibilityStatus' => $product->visibility_status->value,
                        'imageReference' => $product->image_reference,
                        'imageUrl' => $this->resolveImageUrl($product->image_reference),
                    ];
                });

            $products = $products->concat($standardProducts);
        }

        if ($productType === null || $productType === 'customizable') {
            $customizableQuery = CustomizablePrintProduct::query();

            if ($visibilityStatus !== null) {
                $customizableQuery->where('visibility_status', $visibilityStatus);
            }

            if ($searchQuery !== '') {
                $customizableQuery->where(function ($query) use ($searchQuery) {
                    $query->where('product_name', 'like', '%'.$searchQuery.'%')
                        ->orWhere('description', 'like', '%'.$searchQuery.'%');
                });
            }

            $customizableProducts = $customizableQuery
                ->orderBy('product_name')
                ->get()
                ->map(function ($product) {
                    return [
                        'productId' => $product->product_id,
                        'productName' => $product->product_name,
                        'description' => $product->description ?? '',
                        'type' => 'Customizable',
                        'categoryId' => null,
                        'displayPrice' => 0,
                        'visibilityStatus' => $product->visibility_status->value,
                        'imageReference' => $product->image_reference,
                        'imageUrl' => $this->resolveImageUrl($product->image_reference),
                    ];
                });

            $products = $products->concat($customizableProducts);
        }

        $categories = ProductCategory::query()
            ->orderBy('category_name')
            ->get()
            ->map(fn ($category) => [
                'categoryId' => $category->category_id,
                'categoryName' => $category->category_name,
            ])
            ->values();

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'products',
            'products' => $products->values(),
            'categories' => $categories,
            'productFilters' => [
                'query' => $searchQuery,
                'product_type' => $productType,
                'category_id' => $categoryId,
                'visibility_status' => $visibilityStatus,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function store(CreateStandardProductRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $imageReference = null;

        try {
            if ($request->hasFile('image')) {
                $imageReference = $request->file('image')->store('product-images', 'public');
            }

            $this->productService->createStandardProduct(
                $data['product_name'],
                $data['category_id'] ?? null,
                isset($data['display_price']) ? (float) $data['display_price'] : null,
                $data['description'] ?? null,
                $imageReference,
            );
        } catch (\Throwable $exception) {
            if ($imageReference !== null) {
                $this->deleteStoredImage($imageReference);
            }

            throw $exception;
        }

        return redirect()
            ->route('staff.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $validatedReturnFilters = $request->validate([
            'return_filters' => ['nullable', 'array'],
            'return_filters.query' => ['nullable', 'string', 'max:50'],
            'return_filters.product_type' => ['nullable', 'in:standard,customizable'],
            'return_filters.category_id' => ['nullable', 'integer', 'exists:product_categories,category_id'],
            'return_filters.visibility_status' => ['nullable', 'in:Active,Inactive'],
        ]);

        $returnFilters = $validatedReturnFilters['return_filters'] ?? [];

        $redirectFilters = [
            'query' => filled($returnFilters['query'] ?? null)
                ? trim($returnFilters['query'])
                : null,
            'product_type' => $returnFilters['product_type'] ?? null,
            'category_id' => isset($returnFilters['category_id'])
                ? (int) $returnFilters['category_id']
                : null,
            'visibility_status' => $returnFilters['visibility_status'] ?? null,
        ];

        if ($type === 'standard') {
            $product = StandardProduct::findOrFail($id);
            $data = app(\App\Http\Requests\EditStandardProductRequest::class)->validated();
            $newImageReference = null;

            try {
                if ($request->hasFile('image')) {
                    $newImageReference = $request->file('image')->store('product-images', 'public');
                }

                $this->productService->editStandardProduct(
                    $id,
                    $data['product_name'],
                    $data['category_id'] ?? null,
                    isset($data['display_price']) ? (float) $data['display_price'] : null,
                    $data['description'] ?? null,
                    $newImageReference,
                );
            } catch (\Throwable $exception) {
                if ($newImageReference !== null) {
                    $this->deleteStoredImage($newImageReference);
                }

                throw $exception;
            }
        } elseif ($type === 'customizable') {
            $product = CustomizablePrintProduct::findOrFail($id);
            $data = app(\App\Http\Requests\EditCustomizableProductRequest::class)->validated();
            $newImageReference = null;

            try {
                if ($request->hasFile('image')) {
                    $newImageReference = $request->file('image')->store('product-images', 'public');
                }

                $this->productService->editCustomizableProduct(
                    $id,
                    $data['product_name'],
                    $data['description'] ?? null,
                    $newImageReference,
                );
            } catch (\Throwable $exception) {
                if ($newImageReference !== null) {
                    $this->deleteStoredImage($newImageReference);
                }

                throw $exception;
            }
        } else {
            abort(404);
        }

        if ($request->hasFile('image')) {
            if ($product->image_reference !== null && $product->image_reference !== $newImageReference) {
                $this->deleteStoredImage($product->image_reference);
            }
        } elseif ($request->boolean('remove_image') && $product->image_reference !== null) {
            $this->deleteStoredImage($product->image_reference);
            $product->update(['image_reference' => null]);
        }

        if (array_key_exists('visibility_status', $data)) {
            $this->productService->setVisibility(
                $id,
                $type,
                \App\Enums\ProductVisibilityStatus::from($data['visibility_status']),
            );

            $message = $data['visibility_status'] === 'Active'
                ? 'Product activated successfully.'
                : 'Product deactivated successfully.';
        } else {
            $message = 'Product updated successfully.';
        }

        return redirect()
            ->route('staff.products.index', array_filter(
                $redirectFilters,
                fn ($value) => $value !== null && $value !== ''
            ))
            ->with('success', $message);
    }

    /**
     * Builds a public URL for an image_reference value.
     *
     * Stored relative paths are resolved through the public disk. Absolute
     * URLs and root-relative paths are returned unchanged.
     */
    private function resolveImageUrl(?string $imageReference): ?string
    {
        if ($imageReference === null || $imageReference === '') {
            return null;
        }

        if (
            str_starts_with($imageReference, 'http://') ||
            str_starts_with($imageReference, 'https://') ||
            str_starts_with($imageReference, '/')
        ) {
            return $imageReference;
        }

        return Storage::disk('public')->url($imageReference);
    }

    /**
     * Deletes a stored product image if it belongs to the public disk.
     *
     * Absolute URLs and root-relative paths are ignored because they are not
     * managed by Laravel storage.
     */
    private function deleteStoredImage(?string $imageReference): void
    {
        if ($imageReference === null || $imageReference === '') {
            return;
        }

        if (
            str_starts_with($imageReference, 'http://') ||
            str_starts_with($imageReference, 'https://') ||
            str_starts_with($imageReference, '/')
        ) {
            return;
        }

        Storage::disk('public')->delete($imageReference);
    }
}
