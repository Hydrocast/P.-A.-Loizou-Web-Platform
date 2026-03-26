<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the order submission form at checkout.
 *
 * The customer provides contact information that is frozen onto the order
 * record at submission. This data is independent of their account profile —
 * editing a profile later will not affect existing orders.
 *
 * phone number must be exactly 8 numeric digits.
 */
class SubmitOrderRequest extends FormRequest
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
            'customer_name'  => ['required', 'string', 'min:2', 'max:50'],
            'customer_email' => ['required', 'string', 'email', 'max:100'],
            'customer_phone' => ['required', 'string', 'regex:/^\d{8}$/'],
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
            'customer_name.min'     => 'Full name must be at least 2 characters.',
            'customer_name.max'     => 'Full name must not exceed 50 characters.',
            'customer_email.email'  => 'Please enter a valid email address.',
            'customer_phone.regex'  => 'Phone number must be exactly 8 numeric digits.',
        ];
    }
}