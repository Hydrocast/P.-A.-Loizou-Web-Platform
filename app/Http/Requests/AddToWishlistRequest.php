<?php

namespace App\Http\Requests;

use App\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates an add-to-wishlist request from an authenticated customer.
 *
 * Both product_id and product_type are required because two separate tables
 * are used and product_id alone is not sufficient to identify the product.
 * Product existence and availability are verified by WishlistService.
 */
class AddToWishlistRequest extends FormRequest
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
            'product_id'   => ['required', 'integer', 'min:1'],
            'product_type' => ['required', new Enum(ProductType::class)],
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
            'product_id.required'   => 'No product was specified.',
            'product_type.required' => 'No product type was specified.',
        ];
    }
}