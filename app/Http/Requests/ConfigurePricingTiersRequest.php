<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the tiered pricing configuration form submitted by administrators.
 *
 * The tiers array must contain between 1 and 5 entries. Each entry must have
 * a minimum_quantity, maximum_quantity, and unit_price. Individual field
 * constraints are validated here. Structural constraints — first tier starts at
 * 1, no gaps, no overlaps — require comparing tiers against each other and are
 * therefore enforced in PricingConfigurationService::validateTierStructure(),
 * which runs after this request passes.
 *
 * The 'tiers' input is expected as an array of objects from the form:
 *   tiers[0][minimum_quantity], tiers[0][maximum_quantity], tiers[0][unit_price]
 *   tiers[1][minimum_quantity], ...
 */
class ConfigurePricingTiersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tiers'                        => ['required', 'array', 'min:1', 'max:5'],
            'tiers.*.minimum_quantity'     => ['required', 'integer', 'min:1'],
            'tiers.*.maximum_quantity'     => ['required', 'integer', 'min:1'],
            'tiers.*.unit_price'           => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get the custom error messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tiers.required'                    => 'At least one pricing tier is required.',
            'tiers.min'                         => 'At least one pricing tier is required.',
            'tiers.max'                         => 'A maximum of five pricing tiers is allowed.',
            'tiers.*.minimum_quantity.required' => 'Each tier must have a minimum quantity.',
            'tiers.*.minimum_quantity.min'      => 'Minimum quantity must be at least 1.',
            'tiers.*.maximum_quantity.required' => 'Each tier must have a maximum quantity.',
            'tiers.*.maximum_quantity.min'      => 'Maximum quantity must be at least 1.',
            'tiers.*.unit_price.required'       => 'Each tier must have a unit price.',
            'tiers.*.unit_price.min'            => 'Unit price must be 0.00 or greater.',
        ];
    }
}