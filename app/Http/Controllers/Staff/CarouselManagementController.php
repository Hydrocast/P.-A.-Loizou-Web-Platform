<?php

namespace App\Http\Controllers\Staff;

use App\Enums\ProductType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCarouselSlideRequest;
use App\Http\Requests\EditCarouselSlideRequest;
use App\Http\Requests\ReorderCarouselSlidesRequest;
use App\Models\CarouselSlide;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use App\Services\CarouselService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Handles staff carousel slide management.
 *
 * This controller manages all carousel slide operations for staff members
 * including listing slides, creating, updating, deleting, and reordering.
 *
 * Slides may use either:
 * - a custom slide image stored in image_reference, or
 * - the linked product image when image_reference is null.
 */
class CarouselManagementController extends Controller
{
    public function __construct(private CarouselService $carouselService) {}

    // Render carousel slide management list inside the existing staff dashboard shell
    public function index(): Response
    {
        $slides = $this->carouselService->getAllSlides()
            ->map(function ($slide) {
                $linkedProduct = $slide->linkedProduct();
                $linkedProductImageReference = $this->carouselService->getLinkedProductImageReference(
                    $slide->product_id,
                    $slide->product_type,
                );
                $resolvedImageReference = $this->carouselService->resolveSlideImageReference($slide);

                return [
                    'slideId' => $slide->slide_id,
                    'title' => $slide->title,
                    'description' => $slide->description ?? '',
                    'imageReference' => $slide->image_reference,
                    'imageUrl' => $this->resolveImageUrl($resolvedImageReference),
                    'hasCustomImage' => !empty($slide->image_reference),
                    'linkedProductImageUrl' => $this->resolveImageUrl($linkedProductImageReference),
                    'hasLinkedProductImage' => !empty($linkedProductImageReference),
                    'usingLinkedProductImage' => empty($slide->image_reference) && !empty($linkedProductImageReference),
                    'linkedProductKey' => $slide->product_id && $slide->product_type
                        ? $slide->product_type->value . ':' . $slide->product_id
                        : '',
                    'linkedProductName' => $linkedProduct?->product_name,
                    'productId' => $slide->product_id,
                    'productType' => $slide->product_type?->value,
                    'displaySequence' => $slide->display_sequence,
                ];
            })
            ->values();

        $standardProducts = StandardProduct::where('visibility_status', 'Active')
            ->orderBy('product_name')
            ->get()
            ->map(fn ($product) => [
                'value' => 'standard:' . $product->product_id,
                'label' => $product->product_name,
                'productId' => $product->product_id,
                'productType' => 'standard',
                'imageReference' => $product->image_reference,
                'imageUrl' => $this->resolveImageUrl($product->image_reference),
                'hasImage' => !empty($product->image_reference),
            ]);

        $customizableProducts = CustomizablePrintProduct::where('visibility_status', 'Active')
            ->orderBy('product_name')
            ->get()
            ->map(fn ($product) => [
                'value' => 'customizable:' . $product->product_id,
                'label' => $product->product_name,
                'productId' => $product->product_id,
                'productType' => 'customizable',
                'imageReference' => $product->image_reference,
                'imageUrl' => $this->resolveImageUrl($product->image_reference),
                'hasImage' => !empty($product->image_reference),
            ]);

        $linkedProducts = $standardProducts
            ->concat($customizableProducts)
            ->values();

        return Inertia::render('Staff/StaffDashboard', [
            'tab' => 'carousel',
            'slides' => $slides,
            'linkedProducts' => $linkedProducts,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    // Create a new carousel slide and redirect back to carousel list in dashboard shell
    public function store(CreateCarouselSlideRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $productId = $data['product_id'] ?? null;
        $productType = isset($data['product_type'])
            ? ProductType::from($data['product_type'])
            : null;

        $uploadedImageReference = null;

        try {
            if ($request->hasFile('image')) {
                $uploadedImageReference = $request->file('image')->store('carousel-slides', 'public');
            }

            $this->ensureSlideHasImageSource(
                $uploadedImageReference,
                $productId,
                $productType,
            );

            $this->carouselService->createSlide(
                $data['title'],
                $data['description'] ?? null,
                $uploadedImageReference,
                $productId,
                $productType,
            );
        } catch (\Throwable $exception) {
            if ($uploadedImageReference !== null) {
                $this->deleteStoredImage($uploadedImageReference);
            }

            throw $exception;
        }

        return redirect()->route('staff.carousel.index')
            ->with('success', 'Slide created successfully.');
    }

    // Update an existing carousel slide and redirect back to carousel list in dashboard shell
    public function update(EditCarouselSlideRequest $request, CarouselSlide $slide): RedirectResponse
    {
        $data = $request->validated();

        $productId = $data['product_id'] ?? null;
        $productType = isset($data['product_type'])
            ? ProductType::from($data['product_type'])
            : null;

        $uploadedImageReference = null;
        $currentCustomImageReference = $slide->image_reference;
        $resultingImageReference = $currentCustomImageReference;
        $useLinkedProductImage = $request->boolean('use_linked_product_image');

        try {
            if ($request->hasFile('image')) {
                $uploadedImageReference = $request->file('image')->store('carousel-slides', 'public');
                $resultingImageReference = $uploadedImageReference;
            } elseif ($useLinkedProductImage) {
                $resultingImageReference = null;
            }

            $this->ensureSlideHasImageSource(
                $resultingImageReference,
                $productId,
                $productType,
            );

            $this->carouselService->updateSlide(
                $slide->slide_id,
                $data['title'],
                $data['description'] ?? null,
                $resultingImageReference,
                $productId,
                $productType,
            );
        } catch (\Throwable $exception) {
            if ($uploadedImageReference !== null) {
                $this->deleteStoredImage($uploadedImageReference);
            }

            throw $exception;
        }

        if (
            $uploadedImageReference !== null &&
            $currentCustomImageReference !== null &&
            $currentCustomImageReference !== $uploadedImageReference
        ) {
            $this->deleteStoredImage($currentCustomImageReference);
        } elseif ($useLinkedProductImage && $currentCustomImageReference !== null) {
            $this->deleteStoredImage($currentCustomImageReference);
        }

        return redirect()->route('staff.carousel.index')
            ->with('success', 'Slide updated successfully.');
    }

    // Delete a carousel slide and redirect back to carousel list in dashboard shell
    public function destroy(CarouselSlide $slide): RedirectResponse
    {
        if ($slide->image_reference !== null) {
            $this->deleteStoredImage($slide->image_reference);
        }

        $this->carouselService->deleteSlide($slide->slide_id);

        return redirect()->route('staff.carousel.index')
            ->with('success', 'Slide deleted successfully.');
    }

    // Reorder carousel slides and redirect back to carousel list in dashboard shell
    public function reorder(ReorderCarouselSlidesRequest $request): RedirectResponse
    {
        try {
            $this->carouselService->reorderSlides($request->validated()['slide_ids']);

            return redirect()->route('staff.carousel.index')
                ->with('success', 'Slide order updated successfully.');
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first()
                ?? 'Slide order could not be updated.';

            return redirect()->route('staff.carousel.index')
                ->with('error', $message);
        }
    }

    /**
     * Ensures the slide will have at least one usable image source after save.
     *
     * A valid image source is either:
     * - a custom slide image_reference, or
     * - an image on the linked product when the slide has no custom image.
     *
     * @throws ValidationException
     */
    private function ensureSlideHasImageSource(
        ?string $imageReference,
        ?int $productId,
        ?ProductType $productType,
    ): void {
        if ($imageReference !== null && trim($imageReference) !== '') {
            return;
        }

        $linkedProductImageReference = $this->carouselService->getLinkedProductImageReference(
            $productId,
            $productType,
        );

        if ($linkedProductImageReference !== null && trim($linkedProductImageReference) !== '') {
            return;
        }

        throw ValidationException::withMessages([
            'image' => 'Upload a custom slide image or link a product that already has an image.',
        ]);
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
     * Deletes a stored carousel image if it belongs to the public disk.
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