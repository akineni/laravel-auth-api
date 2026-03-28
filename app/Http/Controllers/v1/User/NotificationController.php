<?php

namespace App\Http\Controllers\v1\User;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\ListNotificationsRequest;
use App\Http\Resources\{ApiCollection, NotificationResource};
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

/**
 * Notifications
 *
 * APIs for managing user notifications.
 *
 * @group Users
 */
class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * List Notifications
     *
     * Retrieve the authenticated user's notifications.
     *
     * @subgroup Notifications
     * @authenticated
     */
    public function index(ListNotificationsRequest $request)
    {
        $notifications = $this->notificationService->getPaginatedForUser(
            $request->user(),
            $request->filters()
        );

        return ApiResponse::success(
            'Notifications retrieved successfully',
            ApiCollection::for($notifications, NotificationResource::class)
        );
    }

    /**
     * Unread Notifications Count
     *
     * Retrieve the authenticated user's unread notifications count.
     *
     * @subgroup Notifications
     * @authenticated
     */
    public function unreadCount(Request $request)
    {
        $count = $this->notificationService->getUnreadCount($request->user());

        return ApiResponse::success('Unread notifications count retrieved successfully', [
            'unread_count' => $count,
        ]);
    }

    /**
     * Mark Notification As Read
     *
     * Mark a single notification as read.
     *
     * @subgroup Notifications
     * @authenticated
     */
    public function markAsRead(Request $request, string $notification)
    {
        $this->notificationService->markAsRead($request->user(), $notification);

        return ApiResponse::success('Notification marked as read successfully');
    }

    /**
     * Mark All Notifications As Read
     *
     * Mark all unread notifications as read.
     *
     * @subgroup Notifications
     * @authenticated
     */
    public function markAllAsRead(Request $request)
    {
        $this->notificationService->markAllAsRead($request->user());

        return ApiResponse::success('All notifications marked as read successfully');
    }

    /**
     * Delete Notification
     *
     * Delete a notification.
     *
     * @subgroup Notifications
     * @authenticated
     */
    public function destroy(Request $request, string $notification)
    {
        $this->notificationService->deleteForUser($request->user(), $notification);

        return ApiResponse::success('Notification deleted successfully');
    }
}