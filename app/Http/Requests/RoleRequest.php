<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        // return $this->user()?->hasAnyPermission([
        //     PermissionEnum::USER_MANAGEMENT_CREATE,
        //     PermissionEnum::USER_MANAGEMENT_EDIT,
        // ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guard = 'api';

        $role = $this->route('role');
        $roleId = $role?->id;

        return [
            'name' => [
                $roleId ? 'sometimes' : 'required',
                'string',
                'max:50',
                Rule::unique('roles', 'name')
                    ->where('guard_name', $guard)
                    ->ignore($roleId), // ignored only when updating, NULL on create
            ],

            'permissions' => 'sometimes|array',
            'permissions.*' => [
                'string',
                Rule::exists('permissions', 'name')->where(function ($query) use ($guard) {
                    $query->where('guard_name', $guard);
                }),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This role name already exists.',
            'permissions.array' => 'Permissions must be provided as an array of permission names.',
            'permissions.*.exists' => 'One or more provided permissions do not exist for this guard.',
        ];
    }
}
