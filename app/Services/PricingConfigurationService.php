<?php

namespace App\Services;

use App\Models\CustomizablePrintProduct;
use App\Models\PricingTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Manages tiered pricing configuration for customizable products.
 *
 * This service validates all tier rules before saving and is used by
 * CheckoutService to find the correct price for a cart item.
 *
 * Enforced rules:
 *   - Maximum five tiers per product
 *   - First tier must start at quantity 1
 *   - No gaps between consecutive tiers
 *   - No overlapping quantity ranges
 *   - Each tier: min quantity ≥ 1, max ≥ min, price between 0 and 100,000
 */
class PricingConfigurationService
{
    /**
     * Replace all pricing tiers for a product with the provided configuration.
     *
     * The operation is atomic: all existing tiers are deleted and the new set
     * is inserted in a single transaction. If validation fails, no changes are made.
     *
     * @param array<int, array{minimum_quantity: int, maximum_quantity: int, unit_price: float}> $tiers
     * @throws ValidationException on any validation failure
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if product not found
     */
    public function configurePricingTiers(int $productId, array $tiers): void
    {
        $this->checkProductExistsAndIsCustomizable($productId);

        $tierModels = array_map(
            fn($t) => new PricingTier([
                'product_id' => $productId,
                'minimum_quantity' => $t['minimum_quantity'],
                'maximum_quantity' => $t['maximum_quantity'],
                'unit_price' => $t['unit_price'],
            ]),
            $tiers,
        );

        $this->validateTiersIndividually($tierModels);
        $this->validateTierStructure($tierModels);
        $this->replacePricingTiers($productId, $tierModels);
    }

    /**
     * Validate the structural integrity of a complete set of tiers as a group.
     *
     * Checks: maximum five tiers, first tier starts at 1, no gaps, no overlaps.
     *
     * @param PricingTier[] $tiers
     * @throws ValidationException on any structural violation
     */
    public function validateTierStructure(array $tiers): void
    {
        if (count($tiers) === 0) {
            throw ValidationException::withMessages([
                'tiers' => 'At least one pricing tier is required.'
            ]);
        }

        if (count($tiers) > 5) {
            throw ValidationException::withMessages([
                'tiers' => 'A product may have a maximum of five pricing tiers.',
            ]);
        }

        $sorted = collect($tiers)->sortBy('minimum_quantity')->values();

        if ((int) $sorted->first()->minimum_quantity !== 1) {
            throw ValidationException::withMessages([
                'tiers' => 'The first pricing tier must start at a minimum quantity of 1.',
            ]);
        }
        
        foreach ($sorted as $index => $tier) {
            if ($index === 0) {
                continue;
            }

            $previous = $sorted[$index - 1];

            // Check for gaps: previous max + 1 must equal this min
            if ((int) $tier->minimum_quantity !== (int) $previous->maximum_quantity + 1) {
                throw ValidationException::withMessages([
                    'tiers' => 'Pricing tiers must be contiguous with no gaps between them.',
                ]);
            }
        }
    }

    /**
     * Validate each individual tier's field values independently.
     *
     * @param PricingTier[] $tiers
     * @throws ValidationException if any individual tier is invalid
     */
    public function validateTiersIndividually(array $tiers): void
    {
        foreach ($tiers as $index => $tier) {
            $position = $index + 1;

            if ((int) $tier->minimum_quantity < 1) {
                throw ValidationException::withMessages([
                    'tiers' => "Tier {$position}: minimum quantity must be at least 1.",
                ]);
            }

            if ((int) $tier->maximum_quantity < (int) $tier->minimum_quantity) {
                throw ValidationException::withMessages([
                    'tiers' => "Tier {$position}: maximum quantity must be greater than or equal to minimum quantity.",
                ]);
            }

            if ((float) $tier->unit_price < 0) {
                throw ValidationException::withMessages([
                    'tiers' => "Tier {$position}: unit price must be 0.00 or greater.",
                ]);
            }

            if ((float) $tier->unit_price > 100000) {
                throw ValidationException::withMessages([
                    'tiers' => "Tier {$position}: unit price cannot exceed 100,000.00.",
                ]);
            }
        }
    }

    /**
     * Verify that the referenced product exists and is a customizable print product.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException if product not found
     */
    public function checkProductExistsAndIsCustomizable(int $productId): CustomizablePrintProduct
    {
        return CustomizablePrintProduct::findOrFail($productId);
    }

    /**
     * Retrieve all pricing tiers for a product ordered by minimum_quantity.
     */
    public function getPricingTiersForProduct(int $productId): Collection
    {
        return PricingTier::where('product_id', $productId)
            ->orderBy('minimum_quantity')
            ->get();
    }

    /**
     * Find the pricing tier that applies to the given quantity.
     *
     * Returns the tier whose range contains the quantity. Used by CheckoutService
     * when calculating the unit price for each cart item.
     *
     * @throws ValidationException if no tier covers the given quantity
     */
    public function findTierForQuantity(int $productId, int $quantity): PricingTier
    {
        $tier = PricingTier::where('product_id', $productId)
            ->where('minimum_quantity', '<=', $quantity)
            ->where('maximum_quantity', '>=', $quantity)
            ->first();

        if ($tier === null) {
            throw ValidationException::withMessages([
                'quantity' => "No pricing tier is configured for a quantity of {$quantity}.",
            ]);
        }

        return $tier;
    }

    /**
     * Delete all existing tiers for the product and insert the new set atomically.
     *
     * @param PricingTier[] $tiers
     */
    private function replacePricingTiers(int $productId, array $tiers): void
    {
        DB::transaction(function () use ($productId, $tiers) {
            PricingTier::where('product_id', $productId)->delete();

            foreach ($tiers as $tier) {
                $tier->save();
            }
        });
    }
}