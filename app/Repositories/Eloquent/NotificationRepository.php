<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contracts\NotificationRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Notifications\DatabaseNotification;

class NotificationRepository implements NotificationRepositoryInterface
{
    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? config('app.pagination_per_page');

        $query = $user->notifications()
            ->with('notifiable')
            ->latest();

        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyStatusFilter($query, $filters['status'] ?? null);
        $this->applyTypeFilter($query, $filters['type'] ?? null);

        return $query->paginate($perPage);
    }

    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $this->findForUserOrFail($user, $notificationId);

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return true;
    }

    public function markAllAsRead(User $user): int
    {
        return $user->unreadNotifications()->update([
            'read_at' => now(),
        ]);
    }

    public function deleteForUser(User $user, string $notificationId): bool
    {
        $notification = $this->findForUserOrFail($user, $notificationId);

        return (bool) $notification->delete();
    }

    public function findForUserOrFail(
        User $user,
        string $notificationId,
        bool $markAsRead = true
    ): DatabaseNotification
    {
        $notification = $user->notifications()
            ->where('id', $notificationId)
            ->with('notifiable')
            ->firstOrFail();

        if ($markAsRead && is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        return $notification;
    }

    protected function applySearch(MorphMany $query, ?string $search): void
    {
        if (! is_string($search) || trim($search) === '') {
            return;
        }

        $term = trim($search);

        $query->where(function (Builder $builder) use ($term) {
            $builder
                ->where('type', 'LIKE', "%{$term}%")
                ->orWhere('data->type', 'LIKE', "%{$term}%")
                ->orWhere('data->title', 'LIKE', "%{$term}%")
                ->orWhere('data->message', 'LIKE', "%{$term}%");
        });
    }

    protected function applyStatusFilter(MorphMany $query, ?string $status): void
    {
        if (! is_string($status) || trim($status) === '') {
            return;
        }

        $status = trim($status);

        if ($status === 'read') {
            $query->whereNotNull('read_at');
        }

        if ($status === 'unread') {
            $query->whereNull('read_at');
        }
    }

    protected function applyTypeFilter(MorphMany $query, ?string $type): void
    {
        if (! is_string($type) || trim($type) === '') {
            return;
        }

        $type = trim($type);

        $query->where(function (Builder $builder) use ($type) {
            $builder
                ->where('type', 'LIKE', "%{$type}%")
                ->orWhere('data->type', 'LIKE', "%{$type}%");
        });
    }
}