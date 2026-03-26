<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an add-to-cart request from an authenticated customer.
 *
 * Submitted from the design workspace after the customer has finished their
 * customisation. design_data is the complete FabricJS JSON string captured
 * at submit time. preview_image_reference is the cloud URL of the PNG
 * thumbnail uploaded client-side immediately before this request is submitted.
 */
class AddToCartRequest extends FormRequest
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
            'product_id'              => ['required', 'integer', 'exists:customizable_print_products,product_id'],
            'quantity'                => ['required', 'integer', 'min:1', 'max:99'],
            'design_data'             => ['required', 'string'],
            'preview_image_reference' => ['nullable', 'string'],
            'customization_options' => ['nullable', 'array'],
            'customization_options.shirt_color' => ['nullable', 'array'],
            'customization_options.shirt_color.id' => ['nullable', 'string'],
            'customization_options.shirt_color.label' => ['nullable', 'string'],
            'customization_options.print_sides' => ['nullable', 'array'],
            'customization_options.print_sides.value' => ['nullable', 'string'],
            'customization_options.print_sides.label' => ['nullable', 'string'],
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
            'product_id.exists'    => 'The specified product does not exist.',
            'quantity.min'         => 'Quantity must be at least 1.',
            'quantity.max'         => 'Quantity must not exceed 99.',
            'design_data.required' => 'No design data was provided.',
        ];
    }
}