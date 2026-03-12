<x-mail::message>
# Hello {{ $firstname }}

{{ $contextMessage }}

**Your OTP code is:**  
# {{ $otp }}

This code will expire in **{{ $expiryMinutes }} minutes**.

If you did not request this, please secure your account immediately.

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>