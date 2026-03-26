<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the edit product category form submitted by staff.
 *
 * The unique rule on category_name excludes the category currently being
 * edited so that saving without changing the name does not trigger a duplicate
 * error.
 */
class EditProductCategoryRequest extends FormRequest
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
        // The category ID is resolved from the route parameter.
        $categoryId = $this->route('category');

        return [
            'category_name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                Rule::unique('product_categories', 'category_name')->ignore($categoryId, 'category_id'),
            ],
            'description' => ['nullable', 'string', 'max:500'],
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