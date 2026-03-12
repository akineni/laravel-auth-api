<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        // return $this->user()?->can(\App\Enums\PermissionEnum::USER_MANAGEMENT_CREATE);
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'gender' => ['nullable', new Enum(GenderEnum::class)],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'postcode' => ['nullable', 'string', 'max:50'],
            'assigned_role_id' => ['required', 'uuid', 'exists:roles,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'assigned_role_id.required' => 'Please specify a role.',
            'assigned_role_id.exists' => 'The selected role does not exist.',
        ];
    }
}
