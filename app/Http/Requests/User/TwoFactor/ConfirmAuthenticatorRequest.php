<?php

namespace App\Http\Requests\User\TwoFactor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmAuthenticatorRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Confirm authenticator setup request.
     *
     * @bodyParam secret string required The authenticator secret generated during setup. Example: JBSWY3DPEHPK3PXP
     * @bodyParam code string required The 6-digit code from the authenticator app. Example: 123456
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'secret' => ['required', 'string'],
            'code' => ['required', 'digits:6'],
        ];
    }
}
