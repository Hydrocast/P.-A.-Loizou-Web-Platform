<?php

namespace App\Http\Requests;

use App\Enums\ProductVisibilityStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates the edit customizable product form submitted by staff.
 *
 * Staff can update name, description, image, and visibility status.
 * Template configuration is fixed after creation and cannot be edited here.
 *
 * remove_image is optional and, when true, clears the existing image only if
 * no replacement image is uploaded in the same request.
 */
class EditCustomizableProductRequest extends FormRequest
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
            'product_name'      => ['required', 'string', 'min:2', 'max:100'],
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
            'description.max'            => 'Description must not exceed 2000 characters.',
            'image.mimes'                => 'Image must be a PNG, JPG, or JPEG file.',
            'image.max'                  => 'Image must not exceed 10 MB.',
            'remove_image.boolean'       => 'Remove image must be true or false.',
            'visibility_status.required' => 'Please select a visibility status.',
        ];
    }
}