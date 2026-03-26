<?php

namespace App\Services;

use App\Enums\ProductType;
use App\Enums\ProductVisibilityStatus;
use App\Models\CarouselSlide;
use App\Models\CustomizablePrintProduct;
use App\Models\StandardProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Manages the homepage promotional carousel.
 *
 * Handles creation, editing, deletion, and reordering of slides.
 * Display sequence values are always a gapless sequence starting at 1,
 * recalculated automatically after deletions and reorders.
 *
 * Optional product links follow the same two-table discriminator pattern
 * used by WishlistItem.
 *
 * image_reference stores an optional custom slide image. When null, the slide
 * may fall back to the linked product image if one exists.
 */
class CarouselService
{
    /**
     * Retrieve all slides ordered by display_sequence for homepage rendering.
     */
    public function retrieveActiveSlides(): Collection
    {
        return CarouselSlide::orderBy('display_sequence')->get();
    }

    /**
     * Retrieve all slides for the staff management interface.
     */
    public function getAllSlides(): Collection
    {
        return CarouselSlide::orderBy('display_sequence')->get();
    }

    /**
     * Retrieve a single slide by ID.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getSlideById(int $slideId): CarouselSlide
    {
        return CarouselSlide::findOrFail($slideId);
    }

    /**
     * Create a new carousel slide.
     *
     * The slide is assigned the next available display_sequence position.
     * If a product is linked, it must currently be active.
     *
     * imageReference stores an optional custom slide image. When null, the
     * slide may display the linked product image instead.
     *
     * @throws ValidationException if title, description, or linked product is invalid
     */
    public function createSlide(
        string $title,
        ?string $description,
        ?string $imageReference,
        ?int $productId,
        ?ProductType $productType,
    ): CarouselSlide {
        $this->validateTitle($title);
        $this->validateDescription($description);

        if ($productId !== null) {
            $this->validateLinkedProduct($productId, $productType);
        }

        $nextSequence = (CarouselSlide::max('display_sequence') ?? 0) + 1;

        return CarouselSlide::create([
            'title' => $title,
            'description' => $description,
            'image_reference' => $imageReference,
            'product_id' => $productId,
            'product_type' => $productType,
            'display_sequence' => $nextSequence,
        ]);
    }

    /**
     * Update the editable fields of an existing slide.
     *
     * imageReference stores an optional custom slide image. When null, the
     * slide may display the linked product image instead.
     *
     * @throws ValidationException if title, description, or linked product is invalid
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateSlide(
        int $slideId,
        string $title,
        ?string $description,
        ?string $imageReference,
        ?int $productId,
        ?ProductType $productType,
    ): void {
        $this->validateTitle($title);
        $this->validateDescription($description);

        $slide = CarouselSlide::findOrFail($slideId);

        if ($productId !== null) {
            $this->validateLinkedProduct($productId, $productType);
        }

        $slide->update([
            'title' => $title,
            'description' => $description,
            'image_reference' => $imageReference,
            'product_id' => $productId,
            'product_type' => $productType,
        ]);
    }

    /**
     * Delete a slide and recalculate the display_sequence of remaining slides.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function deleteSlide(int $slideId): void
    {
        $slide = CarouselSlide::findOrFail($slideId);

        DB::transaction(function () use ($slide) {
            $slide->delete();
            $this->recalculateSequences();
        });
    }

    /**
     * Reorder slides by applying a new sequence from the provided ordered list of IDs.
     *
     * Validates that all provided IDs exist before applying any changes.
     *
     * @param int[] $orderedSlideIds Slide IDs in the desired display order
     * @throws ValidationException if any slide ID does not exist
     */
    public function reorderSlides(array $orderedSlideIds): void
    {
        $existing = CarouselSlide::whereIn('slide_id', $orderedSlideIds)
            ->pluck('slide_id')
            ->toArray();

        $missing = array_diff($orderedSlideIds, $existing);

        if (!empty($missing)) {
            throw ValidationException::withMessages([
                'slides' => 'One or more slide identifiers could not be found.',
            ]);
        }

        $totalSlides = CarouselSlide::count();
        if (count($orderedSlideIds) !== $totalSlides) {
            throw ValidationException::withMessages([
                'slides' => 'All slides must be included in the reorder.',
            ]);
        }

        DB::transaction(function () use ($orderedSlideIds) {
            foreach ($orderedSlideIds as $position => $slideId) {
                CarouselSlide::where('slide_id', $slideId)
                    ->update(['display_sequence' => $position + 1]);
            }
        });
    }

    /**
     * Returns the linked product image reference, or null if unavailable.
     */
    public function getLinkedProductImageReference(?int $productId, ?ProductType $productType): ?string
    {
        if ($productId === null || $productType === null) {
            return null;
        }

        $product = match($productType) {
            ProductType::Standard => StandardProduct::find($productId),
            ProductType::Customizable => CustomizablePrintProduct::find($productId),
        };

        return $product?->image_reference;
    }

    /**
     * Resolves the effective image reference for a slide.
     *
     * Custom slide image takes priority. If none exists, the linked product
     * image is used when available.
     */
    public function resolveSlideImageReference(CarouselSlide $slide): ?string
    {
        if (!empty($slide->image_reference)) {
            return $slide->image_reference;
        }

        return $this->getLinkedProductImageReference($slide->product_id, $slide->product_type);
    }

    /**
     * Verify that the linked product exists and is currently active.
     *
     * @throws ValidationException if the product is inactive or does not exist
     */
    private function validateLinkedProduct(int $productId, ?ProductType $productType): void
    {
        $product = match($productType) {
            ProductType::Standard => StandardProduct::find($productId),
            ProductType::Customizable => CustomizablePrintProduct::find($productId),
            default => null,
        };

        if ($product === null || $product->visibility_status !== ProductVisibilityStatus::Active) {
            throw ValidationException::withMessages([
                'product_id' => 'The linked product does not exist or is not currently active.',
            ]);
        }
    }

    /**
     * Validate the title length.
     *
     * Title must be between 2 and 50 characters (inclusive).
     *
     * @throws ValidationException if the title is invalid
     */
    private function validateTitle(string $title): void
    {
        $length = mb_strlen(trim($title));
        if ($length < 2 || $length > 50) {
            throw ValidationException::withMessages([
                'title' => 'Title must be between 2 and 50 characters.',
            ]);
        }
    }

    /**
     * Validate the description length.
     *
     * Description is optional, but if provided must not exceed 100 characters.
     *
     * @throws ValidationException if the description is too long
     */
    private function validateDescription(?string $description): void
    {
        if ($description !== null && mb_strlen($description) > 100) {
            throw ValidationException::withMessages([
                'description' => 'Description must not exceed 100 characters.',
            ]);
        }
    }

    /**
     * Reassign display_sequence values to all remaining slides in current order.
     * Called after a deletion to ensure the sequence is gapless starting from 1.
     */
    private function recalculateSequences(): void
    {
        $slides = CarouselSlide::orderBy('display_sequence')->get();

        foreach ($slides as $index => $slide) {
            $slide->update(['display_sequence' => $index + 1]);
        }
    }
}