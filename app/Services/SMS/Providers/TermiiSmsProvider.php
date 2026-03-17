<?php

namespace App\Services\SMS\Providers;

use App\Services\SMS\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TermiiSmsProvider implements SmsProviderInterface
{
    protected string $apiKey;
    protected string $from;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.termii.api_key');
        $this->from = (string) config('services.termii.from');
        $this->baseUrl = rtrim((string) config('services.termii.base_url'), '/');
    }

    public function send(string $to, string $message): void
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/api/sms/send", [
            'api_key' => $this->apiKey,
            'to' => $this->normalizePhone($to),
            'from' => $this->from,
            'sms' => $message,
            'type' => 'plain',
            'channel' => 'generic', // change to 'dnd' for OTP if enabled on your account
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Termii SMS failed: ' . $response->body());
        }

        $data = $response->json();

        if (($data['code'] ?? null) !== 'ok') {
            throw new RuntimeException('Termii SMS failed: ' . $response->body());
        }
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', trim($phone));
        return ltrim($phone, '+');
    }
}