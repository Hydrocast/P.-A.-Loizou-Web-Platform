<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates the edit carousel slide form submitted by staff.
 *
 * title is required. description and linked product are optional.
 * Staff may upload a replacement custom image, or choose to use the linked
 * product image when a linked product exists and already has one.
 */
class EditCarouselSlideRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:2', 'max:50'],
            'description' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'file', 'mimes:png,jpg,jpeg', 'max:10240'],
            'use_linked_product_image' => ['nullable', 'boolean'],
            'product_id' => ['nullable', 'integer', 'min:1', 'required_with:product_type'],
            'product_type' => ['nullable', new Enum(ProductType::class), 'required_with:product_id'],
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
            'title.min' => 'Title must be at least 2 characters.',
            'title.max' => 'Title must not exceed 50 characters.',
            'description.max' => 'Description must not exceed 100 characters.',
            'image.mimes' => 'Image must be a PNG, JPG, or JPEG file.',
            'image.max' => 'Image must not exceed 10 MB.',
            'use_linked_product_image.boolean' => 'Use linked product image must be true or false.',
            'product_id.required_with' => 'A product type must be selected when a product is linked.',
            'product_type.required_with' => 'A product must be selected when a product type is provided.',
        ];
    }
}