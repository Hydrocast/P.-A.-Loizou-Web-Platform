<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the password reset form.
 *
 * The token and email arrive as route parameters or hidden fields and are
 * included here so they are available via validated() when passed to
 * CustomerAuthService::resetPassword(). Token validity and expiry are
 * checked by the service, not here.
 */
class ResetPasswordRequest extends FormRequest
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
            'token'    => ['required', 'string'],
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8', 'max:64', 'confirmed'],
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
            'token.required'        => 'The reset token is missing.',
            'password.min'          => 'Password must be at least 8 characters.',
            'password.max'          => 'Password must not exceed 64 characters.',
            'password.confirmed'    => 'The password confirmation does not match.',
        ];
    }
}