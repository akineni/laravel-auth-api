<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Models\User;
use App\Rules\PasswordRules;
use App\Services\UsernameService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    protected array $usernameSuggestions = [];

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
            'firstname' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],

            'username' => [
                'nullable',
                'string',
                'min:3',
                'max:50',
                'regex:/^(?!.*[_.]{2})[a-zA-Z0-9]+([._]?[a-zA-Z0-9]+)*$/',
                // 'unique:users,username', // Manual check for suggestions
            ],

            'email' => ['required', 'email', 'unique:users,email'],
            'password' => PasswordRules::default(),
            'phone_number' => ['nullable', 'string', 'max:20'],

            'gender' => [
                'nullable',
                'string',
                Rule::in(GenderEnum::values()),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator) {
                $username = $this->input('username');

                if (blank($username)) {
                    return;
                }

                /** @var UsernameService $usernameService */
                $usernameService = app(UsernameService::class);
                $normalized = $usernameService->normalize($username);

                if (!$usernameService->exists($normalized, User::class)) {
                    return;
                }

                $this->usernameSuggestions = $usernameService->suggest($normalized, User::class);

                $validator->errors()->add('username', 'This username is already taken.');
            },
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        $response = [
            'status' => 'error',
            'message' => 'The given data was invalid.',
            'errors' => $validator->errors(),
        ];

        if (!empty($this->usernameSuggestions)) {
            $response['meta'] = [
                'username_suggestions' => $this->usernameSuggestions,
            ];

            $response['message'] = 'This username is already taken.';
        }

        throw new HttpResponseException(
            response()->json($response, 422)
        );
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'First name is required.',
            'firstname.string' => 'First name must be a valid string.',
            'firstname.max' => 'First name must not be greater than 255 characters.',

            'lastname.required' => 'Last name is required.',
            'lastname.string' => 'Last name must be a valid string.',
            'lastname.max' => 'Last name must not be greater than 255 characters.',

            'username.string' => 'Username must be a valid string.',
            'username.min' => 'Username must be at least 3 characters.',
            'username.max' => 'Username must not be greater than 50 characters.',
            'username.regex' => 'Username may only contain letters, numbers, dots, and underscores, and cannot start, end, or contain consecutive dots or underscores.',
            // 'username.unique' => 'This username is already taken.',

            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'email.unique' => 'This email is already registered.',

            'password.required' => 'Password is required.',

            'phone_number.string' => 'Phone number must be a valid string.',
            'phone_number.max' => 'Phone number must not be greater than 20 characters.',

            'gender.string' => 'Gender must be a valid string.',
            'gender.in' => 'The selected gender is invalid.',
        ];
    }
}