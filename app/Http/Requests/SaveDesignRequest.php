<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the save design request from an authenticated customer.
 *
 * design_name must be between 1 and 100 characters.
 * design_data is the complete serialised FabricJS canvas state (JSON string)
 * captured client-side at save time.
 * preview_image_reference is the cloud storage URL of the PNG thumbnail
 * uploaded client-side before this request is submitted.
 * product_id identifies which customizable product this design belongs to.
 */
class SaveDesignRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth('customer')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:customizable_print_products,product_id'],
            'design_name' => ['required', 'string', 'min:1', 'max:100'],
            'design_data' => ['required', 'string'],
            'preview_image_reference' => ['nullable', 'string'],
            'print_file_reference' => ['nullable', 'string'],
            'customization_options' => ['nullable', 'array'],
            'customization_options.shirt_color' => ['nullable', 'array'],
            'customization_options.shirt_color.id' => ['nullable', 'string'],
            'customization_options.shirt_color.label' => ['nullable', 'string'],
            'customization_options.print_sides' => ['nullable', 'array'],
            'customization_options.print_sides.value' => ['nullable', 'string'],
            'customization_options.print_sides.label' => ['nullable', 'string'],
            'customization_options.size' => ['nullable', 'array'],
            'customization_options.size.value' => ['nullable', 'string'],
            'customization_options.size.label' => ['nullable', 'string'],
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
            'product_id.exists' => 'The specified product does not exist.',
            'design_name.min' => 'Design name must be at least 1 character.',
            'design_name.max' => 'Design name must not exceed 100 characters.',
            'design_data.required' => 'No design data was provided.',
        ];
    }
}
