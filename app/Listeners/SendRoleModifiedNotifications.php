<?php

namespace App\Listeners;

use App\Enums\RoleModificationContextEnum;
use App\Events\RoleModified;
use App\Notifications\RoleAssignedNotification;
use App\Notifications\RoleRevokedNotification;
use App\Notifications\UserRoleModifiedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\Notification\{
    NotificationFanOutService,
    NotificationRecipientResolver
};

class SendRoleModifiedNotifications
{
    /**
     * Create the event listener.
     */
    public function __construct(
        protected NotificationRecipientResolver $recipientResolver,
        protected NotificationFanOutService $fanOutService
    ) {}

    /**
     * Handle the event.
     */
    public function handle(RoleModified $event): void
    {
        $this->notifySubject($event);
        $this->notifyAdmins($event);
    }

    protected function notifySubject(RoleModified $event): void
    {
        if (! $this->shouldNotifySubject($event)) {
            return;
        }

        $notification = match ($event->action) {
            'assigned' => new RoleAssignedNotification($event->role->name),
            'revoked' => new RoleRevokedNotification($event->role->name),
            default => null,
        };

        if (! $notification) {
            return;
        }

        $event->subject->notify($notification);
    }

    protected function notifyAdmins(RoleModified $event): void
    {
        $admins = $this->recipientResolver->resolveAdmins($event->actor);

        $notification = new UserRoleModifiedNotification(
            subject: $event->subject,
            roleName: $event->role->name,
            action: $event->action,
            actor: $event->actor
        );

        $this->fanOutService->notifyMany($admins, $notification);
    }

    protected function shouldNotifySubject(RoleModified $event): bool
    {
        if (
            $event->action === 'assigned' &&
            $event->context === RoleModificationContextEnum::USER_CREATION
        ) {
            return false;
        }

        return true;
    }
}