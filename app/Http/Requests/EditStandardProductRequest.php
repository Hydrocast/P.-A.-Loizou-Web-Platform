<?php

namespace App\Http\Requests;

use App\Enums\ProductVisibilityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates the edit standard product form submitted by staff.
 *
 * All editable fields are included. visibility_status may be changed in the
 * same form as the product fields. A new image is optional —
 * when omitted the existing image is retained.
 *
 * remove_image is optional and, when true, clears the existing image only if
 * no replacement image is uploaded in the same request.
 */
class EditStandardProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
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
            'product_name'      => ['required', 'string', 'min:2', 'max:100'],
            'category_id'       => ['nullable', 'integer', 'exists:product_categories,category_id'],
            'display_price'     => ['nullable', 'numeric', 'min:0'],
            'description'       => ['nullable', 'string', 'max:2000'],
            'image'             => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:10240'],
            'remove_image'      => ['nullable', 'boolean'],
            'visibility_status' => ['required', new Enum(ProductVisibilityStatus::class)],
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
            'product_name.min'           => 'Product name must be at least 2 characters.',
            'product_name.max'           => 'Product name must not exceed 100 characters.',
            'category_id.exists'         => 'The selected category does not exist.',
            'display_price.min'          => 'Price must be 0 or greater.',
            'description.max'            => 'Description must not exceed 2000 characters.',
            'image.mimes'                => 'Image must be a PNG, JPG, or JPEG file.',
            'image.max'                  => 'Image must not exceed 10 MB.',
            'remove_image.boolean'       => 'Remove image must be true or false.',
            'visibility_status.required' => 'Please select a visibility status.',
        ];
    }
}