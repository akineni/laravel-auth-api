<?php

namespace App\Services\Notification;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class NotificationRecipientResolver
{
    /**
     * Resolve admin recipients.
     */
    public function resolveAdmins(?Model $exclude = null): Collection
    {
        $query = User::query()->whereHas('roles', function ($query) {
            $query->whereIn('name', RoleEnum::adminRoles());
        });

        if ($exclude) {
            // Exclude a specific user (e.g., the actor) to avoid self-notification
            $query->whereKeyNot($exclude->getKey());
        }

        return $query->get();
    }
}