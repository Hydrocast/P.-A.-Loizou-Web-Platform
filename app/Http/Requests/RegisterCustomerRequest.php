<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the customer self-registration form.
 *
 * Email, full_name, and password are required. phone_number is optional,
 * but when provided it must contain exactly 8 numeric digits.
 * Email must be unique across the customers table.
 * Password must be confirmed and meet the minimum length requirement.
 */
class RegisterCustomerRequest extends FormRequest
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
            'email'        => ['required', 'string', 'email', 'max:100', 'unique:customers,email'],
            'full_name'    => ['required', 'string', 'min:2', 'max:50'],
            'phone_number' => ['nullable', 'string', 'regex:/^\d{8}$/'],
            'password'     => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
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
            'email.unique'          => 'An account with this email address already exists.',
            'full_name.min'         => 'Full name must be at least 2 characters.',
            'full_name.max'         => 'Full name must not exceed 50 characters.',
            'phone_number.regex'    => 'Phone number must be exactly 8 numeric digits.',
            'password.min'          => 'Password must be at least 8 characters.',
            'password.max'          => 'Password must not exceed 64 characters.',
            'password.confirmed'    => 'The password confirmation does not match.',
        ];
    }
}