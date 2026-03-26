<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a carousel slide reorder operation submitted by staff.
 *
 * slide_ids must be a non-empty array of integers representing the desired
 * display order. Whether each ID exists in the database is verified by
 * CarouselService::reorderSlides().
 */
class ReorderCarouselSlidesRequest extends FormRequest
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
            'slide_ids'   => ['required', 'array', 'min:1'],
            'slide_ids.*' => ['required', 'integer', 'min:1'],
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
            'slide_ids.required'   => 'No slide order was provided.',
            'slide_ids.min'        => 'At least one slide must be included.',
            'slide_ids.*.integer'  => 'Each slide identifier must be a valid integer.',
        ];
    }
}