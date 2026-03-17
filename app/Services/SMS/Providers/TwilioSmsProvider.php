<?php

namespace App\Services\SMS\Providers;

use App\Services\SMS\Contracts\SmsProviderInterface;
use Twilio\Rest\Client;

class TwilioSmsProvider implements SmsProviderInterface
{
    protected Client $client;
    protected string $from;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );

        $this->from = config('services.twilio.from');
    }

    public function send(string $to, string $message): void
    {
        $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }
}