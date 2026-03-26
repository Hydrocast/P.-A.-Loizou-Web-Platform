<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an add-saved-design-to-cart request from an authenticated customer.
 *
 * Only the design identifier is required. Ownership verification and product
 * availability checks are handled by CartService::addSavedDesignToCart().
 * Quantity defaults to 1 at the service layer.
 */
class AddSavedDesignToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'design_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'design_id.required' => 'No design was specified.',
        ];
    }
}
