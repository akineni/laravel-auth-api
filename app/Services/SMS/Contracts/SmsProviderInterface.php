<?php

namespace App\Services\SMS\Contracts;

interface SmsProviderInterface
{
    /**
     * Send an SMS message to the given phone number.
     *
     * Implementations of this interface are responsible for delivering
     * the message through a specific SMS provider (e.g. Twilio, Termii,
     * Africa's Talking, etc).
     *
     * @param string $to The destination phone number in international format.
     * @param string $message The SMS message body to be sent.
     */
    public function send(string $to, string $message): void;
}