<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the date range filter submitted by an administrator for the
 * sales analytics dashboard.
 *
 * Both dates are optional at the request level so the page can load with
 * a default date range. When both are omitted, the controller supplies a
 * last-30-days default range.
 *
 * If only one date is supplied, that single date is validated normally.
 * If both are supplied, end_date must be on or after start_date.
 *
 * Logical date range validation is also enforced in
 * AnalyticsService::validateDateRange() for defence in depth.
 */
class GenerateAnalyticsDashboardRequest extends FormRequest
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
            'start_date' => ['nullable', 'date'],
            'end_date'   => [
                'nullable',
                'date',
                Rule::when(
                    fn () => !empty($this->input('start_date')),
                    ['after_or_equal:start_date']
                ),
            ],
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
        ];
    }
}
