<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchFilterRequest extends FormRequest
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
            'per_page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string', // in:active,inactive
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => 'The per_page value must be an integer.',
            'per_page.min' => 'The per_page value must be at least 1.',
            'search.max' => 'The search value may not be greater than 255 characters.',
            // 'status.in' => "The status must be either 'active' or 'inactive'.",
            'start_date.date' => 'The start date must be a valid date in the format YYYY-MM-DD.',
            'end_date.date' => 'The end date must be a valid date in the format YYYY-MM-DD.',
            'end_date.after_or_equal' => 'The end date must be a date after or equal to the start date.',
        ];
    }

    public function perPage(): ?int { return $this->integer('per_page'); }
    public function search(): ?string { return $this->string('search')->toString(); }
    public function status(): ?string { return $this->string('status')->toString(); }
    public function startDate(): ?string { return $this->input('start_date'); }
    public function endDate(): ?string { return $this->input('end_date'); }
}
