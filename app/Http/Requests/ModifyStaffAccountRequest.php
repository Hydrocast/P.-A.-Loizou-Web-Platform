<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the modify staff account form.
 *
 * Covers editable fields: full_name and an optional new password.
 * Account status changes are handled separately by UpdateStaffStatusRequest.
 *
 * Password is nullable – if omitted the existing password is not changed.
 */
class ModifyStaffAccountRequest extends FormRequest
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
            'full_name' => ['nullable', 'string', 'max:100'],
            'password'  => ['nullable', 'string', 'min:8', 'max:64'],
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
            'password.min'  => 'Password must be at least 8 characters.',
            'password.max'  => 'Password must not exceed 64 characters.',
        ];
    }
}