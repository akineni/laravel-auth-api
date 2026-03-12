<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\PermissionEnum;
use App\Enums\UserStatusEnum;
use App\Rules\AvatarInputRule;
use App\Rules\PasswordRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        // return (bool) $this->user()?->can(PermissionEnum::USER_MANAGEMENT_EDIT);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'firstname' => ['sometimes', 'string', 'max:255'],
            'lastname' => ['sometimes', 'string', 'max:255'],
            'gender' => ['sometimes', 'nullable', new Enum(GenderEnum::class)],
            'phone_number' => ['sometimes', 'nullable', 'string', 'max:20'],
            'postcode' => ['sometimes', 'nullable', 'string', 'max:20'],
            'state' => ['sometimes', 'nullable', 'string', 'max:255'],
            'country' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address' => ['sometimes', 'nullable', 'string', 'max:1000'],

            'email' => [
                'sometimes',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->ignore($user?->id)
                    ->whereNull('deleted_at'),
            ],

            'password' => ['sometimes', ...PasswordRules::default()],

            'status' => ['sometimes', Rule::in(array_column(UserStatusEnum::cases(), 'value'))],
            'can_login' => ['sometimes', 'boolean'],
            'two_fa' => ['sometimes', 'boolean'],

            'roles' => ['sometimes', 'array'],
            'roles.*' => ['required', 'uuid', 'exists:roles,id', 'distinct'],

            'avatar' => ['sometimes', 'nullable', new AvatarInputRule()],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.array' => 'Roles must be an array.',
            'roles.*.uuid' => 'Each role must be a valid role ID.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
            'roles.*.distinct' => 'Each role must be unique.',
        ];
    }
}