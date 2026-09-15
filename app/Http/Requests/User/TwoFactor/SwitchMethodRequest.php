<?php

namespace App\Http\Requests\User\TwoFactor;

use App\Enums\TwoFactorMethodEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SwitchMethodRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Switch two-factor method request.
     *
     * @bodyParam method string required Which medium two-factor should use. Example: authenticator_app
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'method' => ['required', Rule::in(array_column(TwoFactorMethodEnum::cases(), 'value'))],
        ];
    }
}
