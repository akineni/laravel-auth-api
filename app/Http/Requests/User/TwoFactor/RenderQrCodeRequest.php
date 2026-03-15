<?php

namespace App\Http\Requests\User\TwoFactor;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @queryParam secret string required The Base32 secret generated during authenticator setup. Example: JBSWY3DPEHPK3PXP
 */
class RenderQrCodeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Render authenticator QR code request
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'secret' => ['required', 'string', 'min:16'],
        ];
    }
}
