<?php

namespace App\Services\Sms;

use App\Services\SMS\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Log;

class SmsService
{
    public function __construct(
        protected SmsProviderInterface $provider
    ) {}

    public function send(string $phone, string $message)
    {
        try {
            $response = $this->provider->send($phone, $message);

            return $response;
        } catch (\Throwable $e) {
            Log::error('SMS sending failed', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
