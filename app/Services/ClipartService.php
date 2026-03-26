<?php

namespace App\Services;

use App\Models\Clipart;
use Illuminate\Support\Collection;

/**
 * Provides read‑only access to clipart assets for the design workspace.
 *
 * Clipart items are static and have no relationships with other entities.
 * This service supplies clipart data to populate the clipart browser
 * during workspace initialisation.
 */
class ClipartService
{
    /**
     * Return all clipart items ordered by name.
     *
     * Used to populate the clipart browser in the design workspace.
     *
     * @return Collection<int, Clipart>
     */
    public function getAllClipart(): Collection
    {
        return Clipart::orderBy('clipart_name')->get();
    }

    /**
     * Retrieve a single clipart item by its ID.
     *
     * Validates clipart availability when a customer selects an item.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getClipartById(int $clipartId): Clipart
    {
        return Clipart::findOrFail($clipartId);
    }
}