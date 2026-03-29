<?php

namespace App\Services;

use App\Models\CustomizablePrintProduct;
use App\Models\OrderItem;
use App\Models\SavedDesign;
use App\Support\DesignDocument;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Manages saved designs and design snapshot operations.
 *
 * A saved design is immutable. Once created, its design_data cannot be modified.
 * This is enforced by never exposing an update method for design_data.
 *
 * Stored design strings support two formats:
 *   1. legacy raw FabricJS JSON
 *   2. a wrapped design document containing Fabric canvas JSON plus
 *      customization metadata such as selected shirt colour or print sides
 *
 * The design workspace is managed client-side with FabricJS. Server-side
 * responsibilities are:
 *   1. Persisting final design snapshots
 *   2. Loading saved designs back into the workspace
 *   3. Creating immutable snapshots for cart items
 *   4. Serving print references for staff
 *
 * Preview images are generated client-side and uploaded directly to cloud storage.
 * This service receives the URL, not the image file.
 */
class DesignService
{
    /**
     * Persists a new saved design for a customer.
     *
     * design_data is the stored design document string.
     * This may be either legacy raw FabricJS JSON or the newer wrapped
     * design document format that also carries customization metadata.
     * preview_image_reference is the cloud URL of the uploaded PNG thumbnail.
     * print_file_reference is the print-ready image reference preserved for
     * later transfer into cart items and order items.
     *
     * @throws ValidationException if design name invalid or product inactive.
     */
    public function saveDesign(
        int $customerId,
        int $productId,
        string $designName,
        string $designData,
        ?string $previewImageReference,
        ?string $printFileReference,
    ): SavedDesign {
        $this->validateDesignName($designName);

        $product = CustomizablePrintProduct::find($productId);

        if ($product === null || ! $product->isActive()) {
            throw ValidationException::withMessages([
                'product_id' => 'This product is not available for customization.',
            ]);
        }

        return SavedDesign::create([
            'customer_id' => $customerId,
            'product_id' => $productId,
            'design_name' => $designName,
            'design_data' => $designData,
            'preview_image_reference' => $previewImageReference,
            'print_file_reference' => $printFileReference,
            'date_created' => now(),
        ]);
    }

    /**
     * Loads a saved design for workspace initialization.
     *
     * Verifies ownership and product availability before returning.
     * The saved design itself is never modified.
     *
     * @throws ValidationException if design not found, doesn't belong to customer,
     *                             or its product is inactive.
     */
    public function loadDesign(int $customerId, int $designId): SavedDesign
    {
        $design = SavedDesign::with('product')->find($designId);

        if ($design === null || ! $design->belongsToCustomer($customerId)) {
            throw ValidationException::withMessages([
                'design_id' => 'This design could not be found.',
            ]);
        }

        if (! $design->isProductAvailable()) {
            throw ValidationException::withMessages([
                'design_id' => 'The product for this design is no longer available for customization.',
            ]);
        }

        return $design;
    }

    /**
     * Returns all saved designs belonging to the customer.
     * Ordered by most recently created. Used for the My Designs grid.
     */
    public function getSavedDesigns(int $customerId): Collection
    {
        return SavedDesign::with('product')
            ->where('customer_id', $customerId)
            ->orderByDesc('date_created')
            ->get()
            ->map(function (SavedDesign $design) {
                $design->setAttribute(
                    'shirt_color_label',
                    DesignDocument::extractShirtColorLabel($design->design_data),
                );

                $design->setAttribute(
                    'print_sides_label',
                    DesignDocument::extractPrintSidesLabel($design->design_data),
                );

                $design->setAttribute(
                    'size_label',
                    DesignDocument::extractSizeLabel($design->design_data),
                );

                return $design;
            });
    }

    /**
     * Creates an immutable design snapshot string for cart and order storage.
     *
     * The snapshot may be either:
     * - legacy raw FabricJS JSON
     * - a wrapped design document containing canvas JSON plus customization metadata
     *
     * This method makes the "snapshot at this moment" intent explicit while
     * preserving backward compatibility with older stored records.
     */
    public function createCartSnapshot(string $designData): string
    {
        return $designData;
    }

    /**
     * Retrieves the print reference data for a given order item.
     *
     * Returns the order item with its design_snapshot and preview_image_reference
     * for staff display and download.
     *
     * @throws ValidationException if the order item does not exist.
     */
    public function exportPrintReference(int $orderItemId): OrderItem
    {
        $orderItem = OrderItem::with('product')->find($orderItemId);

        if ($orderItem === null) {
            throw ValidationException::withMessages([
                'order_item_id' => 'This print reference is not available.',
            ]);
        }

        return $orderItem;
    }

    /**
     * Validates design name length (1-100 characters).
     *
     * @throws ValidationException
     */
    private function validateDesignName(string $name): void
    {
        $length = mb_strlen(trim($name));

        if ($length < 1 || $length > 100) {
            throw ValidationException::withMessages([
                'design_name' => 'Design name must be between 1 and 100 characters.',
            ]);
        }
    }
}
