<?php

namespace App\Services\SMS\Providers;

use App\Services\SMS\Contracts\SmsProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TermiiSmsProvider implements SmsProviderInterface
{
    protected string $apiKey;
    protected string $from;
    protected string $baseUrl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.termii.api_key');
        $this->from   = (string) config('services.termii.from');
        $this->baseUrl = rtrim((string) config('services.termii.base_url'), '/');
    }

    public function send(string $to, string $message): void
    {
        $normalizedTo = $this->normalizePhone($to);

        Log::info('Termii: Dispatching SMS', [
            'to'      => $normalizedTo,
            'from'    => $this->from,
            'message' => $message,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/api/sms/send", [
            'api_key' => $this->apiKey,
            'to'      => $normalizedTo,
            'from'    => $this->from,
            'sms'     => $message,
            'type'    => 'plain',
            'channel' => 'dnd', // Use 'dnd' for delivery to DND numbers, or 'generic' for regular delivery
        ]);

        if ($response->failed()) {
            Log::error('Termii: HTTP request failed', [
                'to'          => $normalizedTo,
                'http_status' => $response->status(),
                'response'    => $response->body(),
            ]);

            throw new RuntimeException('Termii SMS failed: ' . $response->body());
        }

        $data = $response->json();

        if (($data['code'] ?? null) !== 'ok') {
            Log::error('Termii: SMS rejected by provider', [
                'to'       => $normalizedTo,
                'code'     => $data['code'] ?? 'missing',
                'message'  => $data['message'] ?? 'no message returned',
                'response' => $data,
            ]);

            throw new RuntimeException('Termii SMS failed: ' . $response->body());
        }

        Log::info('Termii: SMS delivered successfully', [
            'to'         => $normalizedTo,
            'message_id' => $data['message_id'] ?? null,
            'balance'    => $data['balance'] ?? null,
        ]);
    }

    protected function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', trim($phone));
        return ltrim($phone, '+');
    }
}