<?php

namespace App\Http\Requests;

use App\Enums\AccountStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates a staff account status change request.
 *
 * Only the account_status field is required, and must be a valid AccountStatus enum.
 * The actual business rules (self-deactivation, last admin) are enforced in the service.
 */
class UpdateStaffStatusRequest extends FormRequest
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
            'account_status' => ['required', new Enum(AccountStatus::class)],
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
            'account_status.required' => 'Please select an account status.',
        ];
    }
}