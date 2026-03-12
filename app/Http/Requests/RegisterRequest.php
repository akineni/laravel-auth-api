<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Rules\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
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
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => PasswordRules::default(),
            'phone_number' => 'nullable|string|max:20',
            'gender' => [
                'nullable',
                'string',
                Rule::in(GenderEnum::values()),
            ],
        ];
    }
}
