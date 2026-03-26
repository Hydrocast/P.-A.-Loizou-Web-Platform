<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

/**
 * Validates the order list filter parameters submitted by staff.
 *
 * All fields are optional. When omitted the corresponding filter is not applied.
 *
 * The after_or_equal rule for end_date is applied only when a start_date is
 * provided. If only one date is supplied, the constraint is skipped, allowing
 * valid filtering by a single date.
 *
 * sort_order controls the order list direction by order creation date.
 * When omitted, the frontend and backend default to newest first.
 *
 * Valid filter combinations:
 *   - status only
 *   - start_date only
 *   - end_date only
 *   - start_date + end_date (end_date must be on or after start_date)
 *   - status with any date combination
 *   - sort_order with or without any other filter
 */
class FilterOrdersRequest extends FormRequest
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
            'order_status' => ['nullable', new Enum(OrderStatus::class)],
            'start_date'   => ['nullable', 'date'],
            'end_date'     => [
                'nullable',
                'date',
                Rule::when(
                    fn () => ! empty($this->input('start_date')),
                    ['after_or_equal:start_date']
                ),
            ],
            'sort_order'   => ['nullable', Rule::in(['asc', 'desc'])],
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
            'start_date.date'         => 'Start date must be a valid date.',
            'end_date.date'           => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
            'sort_order.in'           => 'Sort order must be either ascending or descending.',
        ];
    }
}