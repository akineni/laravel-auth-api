<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam success_url string required The frontend URL to redirect to after successful SSO. Example: https://app.example.com/auth/sso/success
 */
class GetSsoAuthUrlRequest extends FormRequest
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
            'success_url' => 'required|url',
        ];
    }

    public function messages(): array
    {
        return [
            'success_url.required' => 'The success URL is required.',
            'success_url.url' => 'The success URL must be a valid URL.',
        ];
    }
}
