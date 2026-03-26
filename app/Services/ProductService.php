<?php

namespace App\Services;

use App\Enums\ProductVisibilityStatus;
use App\Models\CustomizablePrintProduct;
use App\Models\ProductCategory;
use App\Models\StandardProduct;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Manages the product catalog and product categories for customers and staff.
 *
 * Handles creation, editing, and visibility management for both standard
 * and customizable products, as well as the full category lifecycle.
 *
 * Image upload is handled by the controller. This service receives only
 * the resulting cloud storage URL.
 */
class ProductService
{
    // Maximum allowed price for standard products
    private const MAX_PRICE = 100000;

    /**
     * Creates a new standard product.
     * New products are created with Active visibility by default.
     *
     * @throws ValidationException if any field fails validation.
     */
    public function createStandardProduct(
        string $productName,
        ?int $categoryId,
        ?float $displayPrice,
        ?string $description,
        ?string $imageReference,
    ): StandardProduct {
        $this->validateProductName($productName);
        $this->validateProductPrice($displayPrice);
        $this->validateProductDescription($description);

        return StandardProduct::create([
            'product_name' => $productName,
            'category_id' => $categoryId,
            'display_price' => $displayPrice,
            'description' => $description,
            'image_reference' => $imageReference,
            'visibility_status' => ProductVisibilityStatus::Active,
        ]);
    }

    /**
     * Updates editable fields on a standard product.
     *
     * Passing null for imageReference leaves existing image unchanged.
     *
     * @throws ValidationException if any field fails validation.
     */
    public function editStandardProduct(
        int $productId,
        string $productName,
        ?int $categoryId,
        ?float $displayPrice,
        ?string $description,
        ?string $imageReference,
    ): void {
        $this->validateProductName($productName);
        $this->validateProductPrice($displayPrice);
        $this->validateProductDescription($description);

        $product = StandardProduct::findOrFail($productId);

        $updates = [
            'product_name' => $productName,
            'category_id' => $categoryId,
            'display_price' => $displayPrice,
            'description' => $description,
        ];

        if ($imageReference !== null) {
            $updates['image_reference'] = $imageReference;
        }

        $product->update($updates);
    }

    /**
     * Updates editable fields on a customizable product.
     *
     * Passing null for imageReference leaves existing image unchanged.
     *
     * @throws ValidationException if any field fails validation.
     */
    public function editCustomizableProduct(
        int $productId,
        string $productName,
        ?string $description,
        ?string $imageReference,
    ): void {
        $this->validateProductName($productName);
        $this->validateProductDescription($description);

        $product = CustomizablePrintProduct::findOrFail($productId);

        $updates = [
            'product_name' => $productName,
            'description' => $description,
        ];

        if ($imageReference !== null) {
            $updates['image_reference'] = $imageReference;
        }

        $product->update($updates);
    }

    /**
     * Sets the visibility status of a product (Active/Inactive).
     * Applies to both product types.
     */
    public function setVisibility(int $productId, string $productType, ProductVisibilityStatus $status): void
    {
        $product = $this->findProduct($productId, $productType);
        $product->update(['visibility_status' => $status]);
    }

    /**
     * Searches active products by name and description.
     *
     * Only active products are returned. Both standard and customizable
     * products are included. Query limited to 50 characters.
     *
     * Customizable products are returned first, followed by standard
     * products, to keep catalog ordering consistent.
     *
     * @throws ValidationException if query exceeds 50 characters.
     */
    public function searchProducts(string $query): Collection
    {
        if (mb_strlen($query) > 50) {
            throw ValidationException::withMessages([
                'query' => 'Search query must not exceed 50 characters.',
            ]);
        }

        $term = '%' . $query . '%';

        $standard = StandardProduct::where('visibility_status', ProductVisibilityStatus::Active)
            ->where(function ($q) use ($term) {
                $q->where('product_name', 'like', $term)
                  ->orWhere('description', 'like', $term);
            })
            ->get();

        $customizable = CustomizablePrintProduct::where('visibility_status', ProductVisibilityStatus::Active)
            ->where(function ($q) use ($term) {
                $q->where('product_name', 'like', $term)
                  ->orWhere('description', 'like', $term);
            })
            ->get();

        return $customizable->concat($standard);
    }

    /**
     * Retrieves and filters active products for catalog browsing.
     *
     * Filters are applied with AND logic.
     *
     * Ordering rules:
     * - Customizable products appear first when both product types are shown.
     * - Standard products appear after customizable products.
     * - Price sort applies within the standard product group only.
     *
     * @param int|null    $categoryId   Filter to a specific category (standard only)
     * @param string|null $productType  'standard', 'customizable', or null for both
     * @param string|null $sortOrder    'asc' or 'desc' for price sort
     */
    public function filterProducts(?int $categoryId, ?string $productType, ?string $sortOrder): Collection
    {
        $standardQuery = StandardProduct::where('visibility_status', ProductVisibilityStatus::Active);

        if ($categoryId !== null) {
            $standardQuery->where('category_id', $categoryId);
        }

        if ($sortOrder === 'asc') {
            $standardQuery->orderBy('display_price');
        } elseif ($sortOrder === 'desc') {
            $standardQuery->orderByDesc('display_price');
        }

        if ($productType === 'standard') {
            return $standardQuery->get();
        }

        if ($productType === 'customizable') {
            return CustomizablePrintProduct::where('visibility_status', ProductVisibilityStatus::Active)->get();
        }

        $standardProducts = $standardQuery->get();

        if ($categoryId !== null) {
            return $standardProducts;
        }

        $customizableProducts = CustomizablePrintProduct::where('visibility_status', ProductVisibilityStatus::Active)
            ->get();

        return $customizableProducts->concat($standardProducts);
    }

    /**
     * Retrieves a single product by ID and type for detail view.
     *
     * Returns null if product does not exist or is inactive.
     */
    public function getActiveProduct(int $productId, string $productType): StandardProduct|CustomizablePrintProduct|null
    {
        $product = $this->findProduct($productId, $productType);
        return $product?->isActive() ? $product : null;
    }

    /**
     * Creates a new product category.
     *
     * @throws ValidationException if name invalid or already in use.
     */
    public function createCategory(string $categoryName, ?string $description): ProductCategory
    {
        $this->validateCategoryName($categoryName);

        if (ProductCategory::where('category_name', $categoryName)->exists()) {
            throw ValidationException::withMessages([
                'category_name' => 'A category with this name already exists.',
            ]);
        }

        return ProductCategory::create([
            'category_name' => $categoryName,
            'description' => $description,
        ]);
    }

    /**
     * Updates an existing category.
     *
     * @throws ValidationException if updated name already used by another category.
     */
    public function editCategory(int $categoryId, string $categoryName, ?string $description): void
    {
        $this->validateCategoryName($categoryName);

        $category = ProductCategory::findOrFail($categoryId);

        if (ProductCategory::where('category_name', $categoryName)
            ->where('category_id', '!=', $categoryId)
            ->exists()) {
            throw ValidationException::withMessages([
                'category_name' => 'A category with this name already exists.',
            ]);
        }

        $category->update([
            'category_name' => $categoryName,
            'description' => $description,
        ]);
    }

    /**
     * Deletes a product category.
     *
     * Cannot be deleted while it contains active products. Products in the
     * deleted category will have category_id set to NULL by database.
     *
     * @throws ValidationException if category contains active products.
     */
    public function deleteCategory(int $categoryId): void
    {
        $category = ProductCategory::findOrFail($categoryId);

        if ($category->containsActiveProducts()) {
            throw ValidationException::withMessages([
                'category_id' => 'This category cannot be deleted while it contains active products.',
            ]);
        }

        $category->delete();
    }

    /**
     * Returns all product categories ordered alphabetically.
     */
    public function getAllCategories(): Collection
    {
        return ProductCategory::orderBy('category_name')->get();
    }

    /**
     * Finds a product by ID and type.
     */
    private function findProduct(int $productId, string $productType): StandardProduct|CustomizablePrintProduct|null
    {
        return match ($productType) {
            'standard' => StandardProduct::find($productId),
            'customizable' => CustomizablePrintProduct::find($productId),
            default => null,
        };
    }

    /**
     * Validates product name length (2-100 characters).
     *
     * @throws ValidationException
     */
    private function validateProductName(string $name): void
    {
        $length = mb_strlen(trim($name));

        if ($length < 2 || $length > 100) {
            throw ValidationException::withMessages([
                'product_name' => 'Product name must be between 2 and 100 characters.',
            ]);
        }
    }

    /**
     * Validates product price is within allowed range (0-100,000).
     *
     * @throws ValidationException
     */
    private function validateProductPrice(?float $price): void
    {
        if ($price === null) {
            return;
        }

        if ($price < 0) {
            throw ValidationException::withMessages([
                'display_price' => 'Price must be 0 or greater.',
            ]);
        }

        if ($price > self::MAX_PRICE) {
            throw ValidationException::withMessages([
                'display_price' => 'Price cannot exceed ' . number_format(self::MAX_PRICE, 2) . '.',
            ]);
        }
    }

    /**
     * Validates description does not exceed 2000 characters.
     *
     * @throws ValidationException
     */
    private function validateProductDescription(?string $description): void
    {
        if ($description !== null && mb_strlen($description) > 2000) {
            throw ValidationException::withMessages([
                'description' => 'Description must not exceed 2000 characters.',
            ]);
        }
    }

    /**
     * Validates category name length (2-50 characters).
     *
     * @throws ValidationException
     */
    private function validateCategoryName(string $name): void
    {
        $length = mb_strlen(trim($name));

        if ($length < 2 || $length > 50) {
            throw ValidationException::withMessages([
                'category_name' => 'Category name must be between 2 and 50 characters.',
            ]);
        }
    }
}