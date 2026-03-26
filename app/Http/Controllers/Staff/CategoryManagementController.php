<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateProductCategoryRequest;
use App\Http\Requests\EditProductCategoryRequest;
use App\Models\ProductCategory;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles staff category management operations.
 *
 * This controller manages all product category functionality for staff members
 * including listing, creating, editing, and deleting categories.
 * All methods require staff authentication.
 */
class CategoryManagementController extends Controller
{
    public function __construct(private ProductService $productService) {}

    // Render category list inside the existing staff dashboard shell
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'query' => ['nullable', 'string', 'max:50'],
        ]);

        $searchQuery = trim($validated['query'] ?? '');

        $categoriesQuery = ProductCategory::query()->orderBy('category_name');

        if ($searchQuery !== '') {
            $categoriesQuery->where('category_name', 'like', '%' . $searchQuery . '%');
        }

        $categories = $categoriesQuery
            ->get()
            ->map(fn ($category) => [
                'categoryId' => $category->category_id,
                'categoryName' => $category->category_name,
                'description' => $category->description ?? '',
            ])
            ->values();

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'categories',
            'categories' => $categories,
            'categoryFilters' => [
                'query' => $searchQuery,
            ],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    // Create new product category and redirect back to category list in dashboard shell
    public function store(CreateProductCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $this->productService->createCategory(
            $data['category_name'],
            $data['description'] ?? null,
        );

        return redirect()->route('staff.categories.index')
            ->with('success', 'Category created successfully.');
    }

    // Update category and redirect back to category list in dashboard shell
    public function update(EditProductCategoryRequest $request, ProductCategory $category): RedirectResponse
    {
        $data = $request->validated();

        $this->productService->editCategory(
            $category->category_id,
            $data['category_name'],
            $data['description'] ?? null,
        );

        return redirect()->route('staff.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    // Delete category and redirect back to category list in dashboard shell
    public function destroy(ProductCategory $category): RedirectResponse
    {
        try {
            $this->productService->deleteCategory($category->category_id);

            return redirect()->route('staff.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?? 'This category could not be deleted.';

            return redirect()->route('staff.categories.index')
                ->with('error', $message);
        }
    }
}