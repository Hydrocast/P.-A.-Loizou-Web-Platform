<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the product browse and filter parameters submitted by customers.
 *
 * All fields are optional. When omitted the corresponding filter is not applied.
 * Filters are applied using AND logic in ProductService::filterProducts().
 */
class BrowseProductsRequest extends FormRequest
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
            'query' => ['nullable', 'string', 'max:50'],
            'category_id' => ['nullable', 'integer'],
            'product_type' => ['nullable', 'in:standard,customizable'],
            'sort_order' => ['nullable', 'in:asc,desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'in:12,24,36,100'],
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
            'category_id.exists' => 'The selected category does not exist.',
            'product_type.in' => 'Product type must be standard or customizable.',
            'sort_order.in' => 'Sort order must be asc or desc.',
            'page.integer' => 'Page number must be a valid integer.',
            'page.min' => 'Page number must be at least 1.',
            'per_page.in' => 'Per page must be 12, 24, 36, or 100.',
        ];
    }
}