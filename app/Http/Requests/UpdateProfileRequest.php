<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the customer profile update form.
 *
 * full_name is mandatory. phone_number is optional but when provided must
 * contain exactly 8 numeric digits.
 */
class UpdateProfileRequest extends FormRequest
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
            'full_name'    => ['required', 'string', 'min:2', 'max:50'],
            'phone_number' => ['nullable', 'string', 'regex:/^\d{8}$/'],
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
            'full_name.required' => 'Full name is required.',
            'full_name.min'      => 'Full name must be at least 2 characters.',
            'full_name.max'      => 'Full name must not exceed 50 characters.',
            'phone_number.regex' => 'Phone number must be exactly 8 numeric digits.',
        ];
    }
}