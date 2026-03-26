<?php

namespace App\Http\Requests;

use App\Enums\StaffRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates the create staff account form.
 *
 * Only administrators reach this form. Authorization is enforced by the
 * 'auth:staff' middleware and StaffAccountPolicy before this request is
 * instantiated.
 */
class CreateStaffAccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Authorization is handled by middleware and policy, so always true.
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
            'username'  => ['required', 'string', 'max:100', 'unique:staff,username'],
            'password'  => ['required', 'string', 'min:8', 'max:64'],
            'role'      => ['required', new Enum(StaffRole::class)],
            'full_name' => ['nullable', 'string', 'max:100'],
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
            'username.unique'   => 'A staff account with this username already exists.',
            'password.min'      => 'Password must be at least 8 characters.',
            'password.max'      => 'Password must not exceed 64 characters.',
            'role.required'     => 'Please select a role for this account.',
        ];
    }
}