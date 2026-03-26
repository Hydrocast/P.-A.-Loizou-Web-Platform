<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\BrowseProductsRequest;
use App\Http\Requests\SearchProductsRequest;
use App\Models\CustomizablePrintProduct;
use App\Models\ProductCategory;
use App\Models\PricingTier;
use App\Models\StandardProduct;
use App\Models\WishlistItem;
use App\Services\ProductService;
use App\Support\DesignProfileRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProductCatalogController extends Controller
{
    public function __construct(private ProductService $productService) {}

    public function index(BrowseProductsRequest $request): Response
    {
        $validated = $request->validated();

        $query = trim((string) ($validated['query'] ?? ''));
        $page = max((int) ($validated['page'] ?? 1), 1);
        $perPage = (int) ($validated['per_page'] ?? 12);

        $products = $this->productService->filterProducts(
            isset($validated['category_id']) && $validated['category_id'] !== null
                ? (int) $validated['category_id']
                : null,
            $validated['product_type'] ?? null,
            $validated['sort_order'] ?? 'asc',
        );

        if ($query !== '') {
            $needle = mb_strtolower($query);

            $products = $products->filter(function ($product) use ($needle) {
                $name = mb_strtolower((string) ($product->product_name ?? ''));
                $description = mb_strtolower((string) ($product->description ?? ''));

                return str_contains($name, $needle) || str_contains($description, $needle);
            })->values();
        }

        $customerId = Auth::guard('customer')->id();

        $wishlistLookup = collect();

        if ($customerId) {
            $wishlistLookup = WishlistItem::where('customer_id', $customerId)
                ->get()
                ->keyBy(fn ($item) => $item->product_type->value . '-' . $item->product_id);
        }

        $products = $products->map(function ($product) use ($wishlistLookup) {
            $isStandard = $product instanceof StandardProduct;
            $isCustomizable = $product instanceof CustomizablePrintProduct;

            $productType = $isStandard ? 'standard' : 'customizable';

            $wishlistKey = $productType . '-' . $product->product_id;
            $wishlistItem = $wishlistLookup->get($wishlistKey);

            return [
                'product_id' => $product->product_id,
                'standard_product_id' => $isStandard ? $product->product_id : null,
                'customizable_product_id' => $isCustomizable ? $product->product_id : null,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'image_reference' => $product->image_reference,
                'image_url' => $this->resolveImageUrl($product->image_reference),
                'display_price' => $isStandard ? $product->display_price : null,
                'category_id' => $isStandard ? $product->category_id : null,
                'in_wishlist' => $wishlistItem !== null,
                'wishlist_item_id' => $wishlistItem?->wishlist_item_id,
            ];
        })->values();

        $paginatedProducts = $this->paginateCollection(
            $products,
            $perPage,
            $page,
            $request
        );

        return Inertia::render('Public/ProductCatalog', [
            'products' => $paginatedProducts->items(),
            'categories' => $this->productService->getAllCategories(),
            'filters' => [
                'query' => $query !== '' ? $query : null,
                'category_id' => $validated['category_id'] ?? null,
                'product_type' => $validated['product_type'] ?? null,
                'sort_order' => $validated['sort_order'] ?? 'asc',
                'page' => $paginatedProducts->currentPage(),
                'per_page' => $paginatedProducts->perPage(),
            ],
            'pagination' => [
                'current_page' => $paginatedProducts->currentPage(),
                'last_page' => $paginatedProducts->lastPage(),
                'per_page' => $paginatedProducts->perPage(),
                'total' => $paginatedProducts->total(),
            ],
        ]);
    }

    public function search(SearchProductsRequest $request): Response
    {
        $query = $request->validated()['query'];

        $products = $this->productService->searchProducts($query);

        $customerId = Auth::guard('customer')->id();

        $wishlistLookup = collect();

        if ($customerId) {
            $wishlistLookup = WishlistItem::where('customer_id', $customerId)
                ->get()
                ->keyBy(fn ($item) => $item->product_type->value . '-' . $item->product_id);
        }

        $products = $products->map(function ($product) use ($wishlistLookup) {
            $isStandard = $product instanceof StandardProduct;
            $isCustomizable = $product instanceof CustomizablePrintProduct;

            $productType = $isStandard ? 'standard' : 'customizable';

            $wishlistKey = $productType . '-' . $product->product_id;
            $wishlistItem = $wishlistLookup->get($wishlistKey);

            return [
                'product_id' => $product->product_id,
                'standard_product_id' => $isStandard ? $product->product_id : null,
                'customizable_product_id' => $isCustomizable ? $product->product_id : null,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'image_reference' => $product->image_reference,
                'image_url' => $this->resolveImageUrl($product->image_reference),
                'display_price' => $isStandard ? $product->display_price : null,
                'category_id' => $isStandard ? $product->category_id : null,
                'in_wishlist' => $wishlistItem !== null,
                'wishlist_item_id' => $wishlistItem?->wishlist_item_id,
            ];
        })->values();

        return Inertia::render('Public/ProductCatalog', [
            'products' => $products,
            'categories' => $this->productService->getAllCategories(),
            'filters' => ['query' => $query],
        ]);
    }

    public function show(string $type, int $id): Response|RedirectResponse
    {
        $product = $this->productService->getActiveProduct($id, $type);

        if (! $product) {
            return redirect()->route('catalog')
                ->with('error', 'Product not found or is no longer available.');
        }

        $customerId = Auth::guard('customer')->id();

        $wishlistItem = null;

        if ($customerId) {
            $wishlistItem = WishlistItem::where('customer_id', $customerId)
                ->where('product_id', $product->product_id)
                ->first();
        }

        $category = null;
        $pricingTiers = collect();
        $colorOptions = [];
        $productDetails = null;

        if ($type === 'standard' && ! empty($product->category_id)) {
            $category = ProductCategory::where('category_id', $product->category_id)->first();
        }

        if ($type === 'customizable') {
            $pricingTiers = PricingTier::where('product_id', $product->product_id)
                ->orderBy('minimum_quantity')
                ->get();

            if ($product instanceof CustomizablePrintProduct) {
                $colorOptions = DesignProfileRegistry::getProductDetailColorOptions(
                    $product->design_profile_key,
                );

                $productDetails = DesignProfileRegistry::getProductDetails(
                    $product->design_profile_key,
                );
            }
        }

        return Inertia::render('Public/ProductDetail', [
            'product' => [
                'product_id' => $product->product_id,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'image_reference' => $product->image_reference,
                'image_url' => $this->resolveImageUrl($product->image_reference),
                'display_price' => $type === 'standard' ? $product->display_price : null,
                'category_id' => $type === 'standard' ? $product->category_id : null,
            ],
            'type' => $type,
            'inWishlist' => $wishlistItem !== null,
            'wishlistItemId' => $wishlistItem?->wishlist_item_id,
            'category' => $category,
            'pricingTiers' => $pricingTiers,
            'colorOptions' => $colorOptions,
            'productDetails' => $productDetails,
        ]);
    }

    /**
     * Paginates an already-built collection while preserving current query parameters.
     */
    private function paginateCollection($items, int $perPage, int $page, Request $request): LengthAwarePaginator
    {
        $total = $items->count();
        $pageItems = $items->forPage($page, $perPage)->values();

        return new LengthAwarePaginator(
            $pageItems,
            $total,
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );
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
}