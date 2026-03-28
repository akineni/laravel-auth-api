<?php

namespace App\Services\Notification;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NotificationService
{
    public function __construct(
        protected NotificationRepositoryInterface $notificationRepository
    ) {}

    public function getPaginatedForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->notificationRepository->paginateForUser($user, $filters);
    }

    public function getUnreadCount(User $user): int
    {
        return $this->notificationRepository->getUnreadCount($user);
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        return $this->notificationRepository->markAsRead($user, $notificationId);
    }

    public function markAllAsRead(User $user): int
    {
        return $this->notificationRepository->markAllAsRead($user);
    }

    public function deleteForUser(User $user, string $notificationId): bool
    {
        return $this->notificationRepository->deleteForUser($user, $notificationId);
    }
}