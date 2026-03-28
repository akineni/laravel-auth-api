<?php

namespace App\Services\Notification;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Notification;

class NotificationFanOutService
{
    public function notifyMany(Collection $recipients, Notification $notification): void
    {
        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }
    }
}