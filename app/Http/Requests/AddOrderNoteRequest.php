<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates an internal order note submitted by staff.
 *
 * note_text must not be empty and must not exceed 1000 characters.
 */
class AddOrderNoteRequest extends FormRequest
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
            'note_text' => ['required', 'string', 'max:1000'],
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
            'note_text.required' => 'Note text cannot be empty.',
            'note_text.max'      => 'Note text must not exceed 1000 characters.',
        ];
    }
}