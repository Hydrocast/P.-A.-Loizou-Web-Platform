<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates the date range filter submitted by an administrator for the
 * sales analytics dashboard.
 *
 * Administrators may either:
 * - choose a preset range (today, last_7_days, last_30_days, year_to_date, all_time)
 * - provide a custom date range manually
 *
 * Manual start/end dates remain optional so the page can still load with
 * default controller-supplied behavior when no filters are present.
 *
 * If both manual dates are supplied, end_date must be on or after start_date.
 *
 * Logical date range validation is also enforced in
 * AnalyticsService::validateDateRange() for defence in depth.
 */
class GenerateAnalyticsDashboardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
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
            'preset' => [
                'nullable',
                'string',
                Rule::in(['today', 'last_7_days', 'last_30_days', 'year_to_date', 'all_time']),
            ],
            'start_date' => ['nullable', 'date'],
            'end_date' => [
                'nullable',
                'date',
                Rule::when(
                    fn () => ! empty($this->input('start_date')),
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
            'preset.in' => 'Please choose a valid analytics date preset.',
            'start_date.date' => 'Start date must be a valid date.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.after_or_equal' => 'End date must be on or after the start date.',
        ];
    }
}
