<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the create standard product form submitted by staff.
 *
 * product_name and display_price are required. category_id, description,
 * and image are optional. Image must be PNG, JPG, or JPEG and must not
 * exceed 10 MB.
 */
class CreateStandardProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Authorization is handled by middleware and policy, so always true.
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
            'product_name'  => ['required', 'string', 'min:2', 'max:100'],
            'category_id'   => ['nullable', 'integer', 'exists:product_categories,category_id'],
            'display_price' => ['nullable', 'numeric', 'min:0'],
            'description'   => ['nullable', 'string', 'max:2000'],
            'image'         => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:10240'],
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
            'product_name.min'    => 'Product name must be at least 2 characters.',
            'product_name.max'    => 'Product name must not exceed 100 characters.',
            'category_id.exists'  => 'The selected category does not exist.',
            'display_price.min'   => 'Price must be 0 or greater.',
            'description.max'     => 'Description must not exceed 2000 characters.',
            'image.mimes'         => 'Image must be a PNG, JPG, or JPEG file.',
            'image.max'           => 'Image must not exceed 10 MB.',
        ];
    }
}