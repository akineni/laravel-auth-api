<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NotificationRepositoryInterface
{
    /**
     * Paginate user notifications.
     */
    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator;

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount(User $user): int;

    /**
     * Mark a notification as read.
     */
    public function markAsRead(User $user, string $notificationId): bool;

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(User $user): int;

    /**
     * Delete a notification for user.
     */
    public function deleteForUser(User $user, string $notificationId): bool;
}