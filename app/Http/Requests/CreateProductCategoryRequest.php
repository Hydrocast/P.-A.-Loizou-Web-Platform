<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the create product category form submitted by staff.
 *
 * category_name must be unique. Duplicate name checking is also enforced in
 * ProductService::createCategory() for defence in depth, but the unique rule
 * here provides an immediate validation error before the service is called.
 */
class CreateProductCategoryRequest extends FormRequest
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
            'category_name' => ['required', 'string', 'min:2', 'max:50', 'unique:product_categories,category_name'],
            'description'   => ['nullable', 'string', 'max:500'],
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
            'category_name.min'    => 'Category name must be at least 2 characters.',
            'category_name.max'    => 'Category name must not exceed 50 characters.',
            'category_name.unique' => 'A category with this name already exists.',
            'description.max'      => 'Description must not exceed 500 characters.',
        ];
    }
}