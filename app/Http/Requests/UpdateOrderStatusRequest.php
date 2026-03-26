<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates an order status update submitted by staff.
 *
 * The status value must be one of the defined OrderStatus enum cases.
 * No transition rules are enforced here — they are handled by the service.
 *
 * send_email is optional and only relevant when the selected status is
 * Ready for Pickup.
 */
class UpdateOrderStatusRequest extends FormRequest
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
            'order_status' => ['required', new Enum(OrderStatus::class)],
            'send_email' => ['nullable', 'boolean'],
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
            'order_status.required' => 'Please select a status.',
            'send_email.boolean' => 'Send email must be true or false.',
        ];
    }
}