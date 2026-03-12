<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
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
            'challenge_token' => 'required|string|exists:one_time_passwords,challenge_token',
            'otp' => 'required|string|digits:' . config('otp.length', 6),
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        $length = config('otp.length', 6);

        return [
            'challenge_token.required' => 'OTP challenge token is required.',
            'challenge_token.exists' => 'Invalid or expired OTP challenge.',
            'otp.required' => 'OTP code is required.',
            'otp.digits' => "OTP must be a {$length}-digit code.",
        ];
    }
}
